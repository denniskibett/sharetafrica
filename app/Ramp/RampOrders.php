<?php

namespace App\Ramp;

use App\Integrations\RailResult;
use App\Integrations\Rails;
use App\Ledger\Ledger;
use App\Ledger\LedgerException;
use App\Ledger\Money;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Personal "pay anyone" orders (plan U3): quote → collect from the payer's M-PESA → pay the recipient → paid,
 * or a full refund if the payout fails. The only place an order changes state.
 *
 * Money rules:
 *  - The ledger posts only on a rail's final answer: collection confirmed → Dr rail / Cr 2200 order;
 *    payout confirmed → Dr 2200 / Cr rail, 4100 fee, 2300 excise, 4900 rounding; refund confirmed → Dr 2200 / Cr rail.
 *  - Each order has its own 2200 account, which is back to 0.00 when the order ends (ledger:check warns if not).
 *  - Rails are never called inside a database transaction, and nothing is ever re-sent: no answer = status check.
 *  - apply() is idempotent, so a callback and a status check arriving together post once.
 */
class RampOrders
{
    public function __construct(private Ledger $ledger, private Rails $rails) {}

    // ---------------------------------------------------------------- customer steps

    public function quote(User $user, string $type, array $destination, string $amount, ?string $note = null): RampOrder
    {
        $payer = filled($user->phone) ? Destination::phone((string) $user->phone, 'payer') : throw ValidationException::withMessages(['payer' => 'Add your M-PESA number to your profile first.']);
        [$dest, $party] = Destination::normalise($type, $destination);

        try {
            $cents = Money::cents($amount);
        } catch (LedgerException) {
            throw ValidationException::withMessages(['amount' => 'Enter an amount like 1,500 (whole shillings).']);
        }
        if ($cents % 100 !== 0) {
            throw ValidationException::withMessages(['amount' => 'M-PESA sends whole shillings: leave out the cents.']);
        }
        if ($cents < config('pricing.min_amount')) {
            throw ValidationException::withMessages(['amount' => 'The smallest payment is KES '.Money::format(config('pricing.min_amount')).'.']);
        }
        if ($type === 'phone' && $party === $payer) {
            throw ValidationException::withMessages(['phone' => 'That is your own number.']);
        }

        $price = Pricing::quote($cents);
        $this->checkLimits($user, $price['total']);

        return RampOrder::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'rail' => $this->rails->default(),
            'destination_type' => $type,
            'destination' => $dest,
            'payer_phone' => $payer,
            'amount' => $cents,
            'currency' => config('ledger.currency'),
            'note' => $note ? Str::limit(strip_tags($note), 140, '') : null,
            'status' => 'quoted',
            'quote_expires_at' => now()->addMinutes(config('pricing.quote_minutes')),
            'timeline' => [$this->event('Quote ready: '.Money::format($price['total']).' to pay')],
        ] + $price);
    }

    /** The payer confirms: we ask their M-PESA to pay (STK push). */
    public function confirm(RampOrder $order): RampOrder
    {
        $request = DB::transaction(function () use ($order) {
            $o = RampOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($o->status !== 'quoted') {
                return null; // already confirmed: confirming twice does nothing
            }
            if ($o->quote_expires_at->isPast()) {
                $this->move($o, 'expired', 'Quote expired before it was confirmed');

                return 'expired';
            }
            $this->checkLimits($o->user, $o->total, $o->id);
            $req = $this->newRequest($o, 'collect', $o->payer_phone, $o->total);
            $o->collect_request_id = $req->id;
            $this->move($o, 'collecting', 'M-PESA prompt sent to '.$o->payer_phone);

            return $req;
        });
        if ($request === 'expired') {
            throw ValidationException::withMessages(['order' => 'This quote has expired. Get a new one.']);
        }
        $request && $this->send($request);

        return $order->fresh();
    }

    /** Ask the rail about whatever the order is waiting for. Safe to call any time (screens poll it). */
    public function refresh(RampOrder $order): RampOrder
    {
        $id = match ($order->status) {
            'collecting' => $order->collect_request_id,
            'paying' => $order->payout_request_id,
            'refunding' => $order->refund_request_id,
            default => null,
        };
        $request = $id ? RailRequest::find($id) : null;
        if ($request && ! $request->final()) {
            try {
                $this->apply($request, $this->rails->for($request->rail)->status($request), fromStatusCheck: true);
            } catch (Throwable $e) {
                report($e);
                $request->forceFill(['status' => 'unknown', 'last_checked_at' => now()])->save();
            }
        }

        return $order->fresh();
    }

    // ---------------------------------------------------------------- what a rail says

    /**
     * Record a rail's answer and move the order on. Callbacks from real rails call this too. Idempotent.
     * Anything to send next (the payout, a refund) goes out after the transaction commits.
     */
    public function apply(RailRequest $request, RailResult $result, bool $fromStatusCheck = false): void
    {
        $next = DB::transaction(function () use ($request, $result, $fromStatusCheck) {
            $req = RailRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($req->final()) {
                return null;
            }
            $req->fill([
                'status' => $result->status,
                'provider_reference' => $result->providerReference ?? $req->provider_reference,
                'receipt' => $result->receipt ?? $req->receipt,
                'failure' => $result->message,
                'checks' => $req->checks + ($fromStatusCheck ? 1 : 0),
                'last_checked_at' => $fromStatusCheck ? now() : $req->last_checked_at,
                'completed_at' => $result->final() ? now() : null,
            ])->save();
            if (! $result->final()) {
                return null;
            }

            $order = RampOrder::where(match ($req->meta['role'] ?? '') {
                'collect' => 'collect_request_id', 'payout' => 'payout_request_id', default => 'refund_request_id',
            }, $req->id)->lockForUpdate()->first();

            return $order ? $this->advance($order, $req) : null;
        });
        $next && $this->send($next);
    }

    /** One status check per waiting order that has been quiet for a while (php artisan ramp:resolve, every minute). */
    public function resolveWaiting(): int
    {
        $count = 0;
        RampOrder::whereIn('status', ['collecting', 'paying', 'refunding'])
            ->where('updated_at', '<', now()->subSeconds(config('rails.status_check_after_seconds')))
            ->orderBy('id')->each(function (RampOrder $o) use (&$count) {
                $before = $o->status;
                $count += $this->refresh($o)->status !== $before ? 1 : 0;
            });

        return $count;
    }

    public function expireQuotes(): int
    {
        $count = 0;
        RampOrder::where('status', 'quoted')->where('quote_expires_at', '<', now())->each(function (RampOrder $o) use (&$count) {
            DB::transaction(function () use ($o, &$count) {
                $locked = RampOrder::whereKey($o->id)->lockForUpdate()->first();
                if ($locked->status === 'quoted') {
                    $this->move($locked, 'expired', 'Quote expired');
                    $count++;
                }
            });
        });

        return $count;
    }

    // ---------------------------------------------------------------- internals

    /** Inside apply()'s transaction: post to the ledger and move the order. Returns a request to send next, if any. */
    private function advance(RampOrder $o, RailRequest $req): ?RailRequest
    {
        $orderAcct = $this->ledger->account('2200', $o);
        $role = $req->meta['role'];
        $ok = $req->status === 'succeeded';

        if ($role === 'collect' && $o->status === 'collecting') {
            if (! $ok) {
                $this->move($o, 'failed', 'Not paid: '.$req->failure, $req->failure);

                return null;
            }
            $this->ledger->post("ramp:{$o->uuid}:collected", 'ramp_collect', [
                Ledger::debit($this->ledger->account($this->rails->account($o->rail, 'collect')), $o->total),
                Ledger::credit($orderAcct, $o->total),
            ], ['source' => $o, 'memo' => "Collected from {$o->payer_phone} · M-PESA {$req->receipt}"]);
            $payout = $this->newRequest($o, 'payout', $this->party($o), $o->amount);
            $o->payout_request_id = $payout->id;
            $this->move($o, 'paying', "Paid by M-PESA {$req->receipt}. Sending to {$o->recipient()}");

            return $payout;
        }

        if ($role === 'payout' && $o->status === 'paying') {
            if ($ok) {
                $legs = array_values(array_filter([
                    Ledger::debit($orderAcct, $o->total),
                    Ledger::credit($this->ledger->account($this->rails->account($o->rail, 'payout')), $o->amount),
                    $o->fee ? Ledger::credit($this->ledger->account('4100'), $o->fee) : null,
                    $o->excise ? Ledger::credit($this->ledger->account('2300'), $o->excise) : null,
                    $o->rounding ? Ledger::credit($this->ledger->account('4900'), $o->rounding) : null,
                ]));
                $this->ledger->post("ramp:{$o->uuid}:paid", 'ramp_payout', $legs, ['source' => $o, 'memo' => "Paid {$o->recipient()} · ref {$req->receipt}"]);
                $this->move($o, 'paid', "Received by {$o->recipient()} · ref {$req->receipt}");

                return null;
            }
            $refund = $this->newRequest($o, 'refund', $o->payer_phone, $o->total);
            $o->refund_request_id = $refund->id;
            $this->move($o, 'refunding', "Could not pay the recipient ({$req->failure}). Refunding ".Money::format($o->total).' to '.$o->payer_phone, $req->failure);

            return $refund;
        }

        if ($role === 'refund' && $o->status === 'refunding') {
            if ($ok) {
                $this->ledger->post("ramp:{$o->uuid}:refunded", 'ramp_refund', [
                    Ledger::debit($orderAcct, $o->total),
                    Ledger::credit($this->ledger->account($this->rails->account($o->rail, 'payout')), $o->total),
                ], ['source' => $o, 'memo' => "Refunded to {$o->payer_phone} · ref {$req->receipt}"]);
                $this->move($o, 'refunded', 'Refunded in full to '.$o->payer_phone." · ref {$req->receipt}");

                return null;
            }
            $this->move($o, 'refund_failed', 'Refund could not be sent: our team has been alerted', $req->failure);
        }

        return null;
    }

    /** Hand a request to its rail, outside any transaction. A crash or timeout leaves it "unknown" for a status check. */
    private function send(RailRequest $request): void
    {
        $rail = $this->rails->for($request->rail);
        try {
            $result = $request->kind === 'collect' ? $rail->collect($request) : $rail->payout($request);
        } catch (Throwable $e) {
            report($e);
            $result = RailResult::unknown('No answer from the provider: we will check, not resend.');
        }
        $this->apply($request, $result);
    }

    private function newRequest(RampOrder $o, string $role, string $party, int $amount): RailRequest
    {
        return RailRequest::create([
            'rail' => $o->rail,
            'kind' => $role === 'collect' ? 'collect' : 'payout',
            'reference' => "ramp:{$o->uuid}:{$role}",
            'amount' => $amount,
            'currency' => $o->currency,
            'party' => $party,
            'meta' => ['role' => $role, 'order' => $o->uuid, 'destination_type' => $role === 'payout' ? $o->destination_type : 'phone', 'destination' => $role === 'payout' ? $o->destination : null],
            'status' => 'pending',
        ]);
    }

    private function party(RampOrder $o): string
    {
        $d = $o->destination;

        return $d['phone'] ?? $d['paybill'] ?? $d['till'] ?? $d['account'];
    }

    private function move(RampOrder $o, string $status, string $event, ?string $failure = null): void
    {
        $o->status = $status;
        $o->failure = $failure ?? $o->failure;
        $o->timeline = array_merge($o->timeline ?? [], [$this->event($event)]);
        $o->save();
    }

    private function event(string $text): array
    {
        return ['at' => now()->timezone('Africa/Nairobi')->format('H:i:s'), 'text' => $text];
    }

    /** Limits by KYC level, per payment and per day (config/pricing.php). */
    private function checkLimits(User $user, int $total, ?int $exceptOrder = null): void
    {
        $level = $user->kyc_level ?: 'none';
        $limits = config("pricing.limits.{$level}") ?? config('pricing.limits.none');
        if ($limits['per_order'] <= 0) {
            throw ValidationException::withMessages(['amount' => 'Verify your name and M-PESA number (basic KYC) before paying.']);
        }
        if ($total > $limits['per_order']) {
            throw ValidationException::withMessages(['amount' => 'Your limit per payment is KES '.Money::format($limits['per_order']).'. Upgrade your verification to send more.']);
        }
        $today = RampOrder::where('user_id', $user->id)->where('created_at', '>=', now()->startOfDay())
            ->whereIn('status', ['collecting', 'paying', 'paid', 'refunding'])->when($exceptOrder, fn ($q) => $q->whereKeyNot($exceptOrder))->sum('total');
        if ($today + $total > $limits['per_day']) {
            throw ValidationException::withMessages(['amount' => 'This would pass your daily limit of KES '.Money::format($limits['per_day']).'. Upgrade your verification to send more.']);
        }
    }
}

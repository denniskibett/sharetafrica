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
use App\Support\Handle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Personal "pay anyone" orders (plan U3): quote → collect from the payer's M-PESA → pay the recipient → paid,
 * or a full refund if the payout fails. The only place an order changes state.
 * Variations: funding "wallet" pays from the payer's Sharet wallet (2100) instead of collecting first (a withdrawal
 * is a wallet payment to your own M-PESA or bank); a top-up (destination_type "wallet") collects from M-PESA into the
 * wallet and stops there; a transfer (destination_type "sharet") moves wallet to wallet at once, no rail.
 *
 * Money rules:
 *  - The ledger posts only on a rail's final answer: collection confirmed → Dr rail / Cr 2200 order;
 *    payout confirmed → Dr 2200 / Cr rail, 4100 fee, 2300 excise, 4900 rounding; refund confirmed → Dr 2200 / Cr rail.
 *  - Wallet orders: confirm → Dr 2100 wallet / Cr 2200; a failed payout returns it (Dr 2200 / Cr 2100), no rail.
 *    Top-ups: collection confirmed → Dr rail / Cr 2100 wallet.
 *  - Each order has its own 2200 account, which is back to 0.00 when the order ends (ledger:check warns if not).
 *  - Rails are never called inside a database transaction, and nothing is ever re-sent: no answer = status check.
 *  - apply() is idempotent, so a callback and a status check arriving together post once.
 */
class RampOrders
{
    public function __construct(private Ledger $ledger, private Rails $rails) {}

    // ---------------------------------------------------------------- customer steps

    public function quote(User $user, string $type, array $destination, string $amount, ?string $note = null, string $funding = 'mpesa', ?array $price = null): RampOrder
    {
        // A business wallet pays from its balance and has no M-PESA number of its own
        $payer = $user->is_business_wallet && $funding === 'wallet' ? '' : $this->payer($user);
        [$dest, $party] = Destination::normalise($type, $destination);
        $cents = $this->cents($amount);
        if ($type === 'phone' && $party === $payer && $funding !== 'wallet') {
            throw ValidationException::withMessages(['phone' => 'That is your own number.']);
        }

        // someone whose money is real (real collections) must not have it "sent" by the simulator: refuse until payouts are real
        if ($this->rails->collecting($user) !== $this->rails->default() && $this->rails->paying($user, $type) === 'simulator') {
            throw ValidationException::withMessages(['amount' => 'Sending to M-PESA and banks is not live yet: your money stays safe in your wallet. Sharet @name transfers work.']);
        }

        // API payments bring their own price from the business tariff (App\Api\Tariffs); the app uses the personal one
        $price = $price !== null ? array_intersect_key($price, array_flip(['fee', 'excise', 'rounding', 'total'])) : Pricing::quote($cents);
        $this->checkLimits($user, $price['total']);
        if ($funding === 'wallet' && $this->walletBalance($user) < $price['total']) {
            throw ValidationException::withMessages(['amount' => 'Your wallet has KES '.Money::format($this->walletBalance($user)).'. Add money or pay from M-PESA.']);
        }

        return RampOrder::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'rail' => $this->rails->paying($user, $type),
            'destination_type' => $type,
            'destination' => $dest,
            'payer_phone' => $payer,
            'funding' => $funding === 'wallet' ? 'wallet' : 'mpesa',
            'amount' => $cents,
            'currency' => config('ledger.currency'),
            'note' => $note ? Str::limit(strip_tags($note), 140, '') : null,
            'status' => 'quoted',
            'quote_expires_at' => now()->addMinutes(config('pricing.quote_minutes')),
            'timeline' => [$this->event('Quote ready: '.Money::format($price['total']).' to pay')],
        ] + $price);
    }

    /** "Add money": collect from the payer's M-PESA into their wallet. No fee. Confirmed like any order. */
    public function topUp(User $user, string $amount, ?string $payerPhone = null): RampOrder
    {
        // $payerPhone: someone else pays into this wallet (API payment requests, docs/v2/INTEGRATION.md §3)
        $payer = $payerPhone !== null ? Destination::phone($payerPhone, 'phone') : $this->payer($user);
        $cents = $this->cents($amount);
        $this->checkLimits($user, $cents);

        return RampOrder::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'rail' => $this->rails->collecting($user),
            'destination_type' => 'wallet',
            'destination' => ['wallet' => 'Sharet wallet'],
            'payer_phone' => $payer,
            'amount' => $cents, 'fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $cents,
            'currency' => config('ledger.currency'),
            'status' => 'quoted',
            'quote_expires_at' => now()->addMinutes(config('pricing.quote_minutes')),
            'timeline' => [$this->event($payerPhone !== null ? 'Collecting '.Money::format($cents).' from '.$payer : 'Adding '.Money::format($cents).' to your wallet')],
        ]);
    }

    /** Wallet to wallet, to another Sharet user's @handle. Immediate, no fee, no rail: Dr 2100 sender / Cr 2100 recipient. */
    public function transfer(User $from, string $handle, string $amount, ?string $note = null, ?string $ref = null): RampOrder
    {
        $to = Handle::find($handle) ?? throw ValidationException::withMessages(['handle' => 'No Sharet wallet has that @name.']);
        if ($to->is($from)) {
            throw ValidationException::withMessages(['handle' => 'That is your own wallet.']);
        }
        $cents = $this->cents($amount);
        $this->checkLimits($from, $cents);

        try {
            return DB::transaction(function () use ($from, $to, $cents, $note, $ref) {
                $o = RampOrder::create([
                    'uuid' => (string) Str::uuid(), 'user_id' => $from->id, 'recipient_user_id' => $to->id, 'rail' => 'internal',
                    'destination_type' => 'sharet', 'destination' => ['handle' => Handle::for($to), 'name' => $to->name] + ($ref ? ['ref' => $ref] : []),   // $ref: the payment request it pays
                    'payer_phone' => filled($from->phone) ? Destination::phone((string) $from->phone, 'payer') : '', 'funding' => 'wallet',
                    'amount' => $cents, 'fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $cents, 'currency' => config('ledger.currency'),
                    'note' => $note ? Str::limit(strip_tags($note), 140, '') : null, 'status' => 'paid', 'quote_expires_at' => now(),
                    'timeline' => [$this->event('Sent from your wallet to @'.Handle::for($to))],
                ]);
                $this->ledger->post("transfer:{$o->uuid}", 'wallet_transfer', [
                    Ledger::debit($this->ledger->account('2100', $from), $cents),
                    Ledger::credit($this->ledger->account('2100', $to), $cents),
                ], ['source' => $o, 'memo' => '@'.Handle::for($from).' to @'.Handle::for($to)]);

                return $o;
            });
        } catch (LedgerException $e) {
            throw $e->reason === 'insufficient_funds'
                ? ValidationException::withMessages(['amount' => 'Your wallet has KES '.Money::format($this->walletBalance($from)).'.']) : $e;
        }
    }

    /** What the user's wallet holds now (cents). */
    public function walletBalance(User $user): int
    {
        return $this->ledger->account('2100', $user)->available();
    }

    /**
     * "Add money" by card on a hosted card page (Cloud9 checkout). The customer pays the card charge on top; the wallet
     * gets the amount. Returns [order, card page url]. The PAYIN webhook (or the return page's status check) books it.
     */
    public function topUpByCard(User $user, string $amount): array
    {
        $order = $this->topUp($user, $amount);
        $req = DB::transaction(function () use ($order) {
            $o = RampOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $o->rail = 'cloud9';
            $req = RailRequest::create([
                'rail' => 'cloud9', 'kind' => 'collect', 'reference' => "ramp:{$o->uuid}:collect", 'amount' => $o->total, 'currency' => $o->currency,
                'party' => $o->payer_phone, 'status' => 'pending',
                'meta' => ['role' => 'collect', 'order' => $o->uuid, 'destination_type' => 'phone', 'destination' => null, 'method' => 'card'],
            ]);
            $o->collect_request_id = $req->id;
            $this->move($o, 'collecting', 'Card page opened for '.Money::format($o->total));

            return $req;
        });
        try {
            [$checkout, $url, $charge] = app(\App\Integrations\Cloud9\Cloud9Rail::class)->checkout($req, $user);
        } catch (Throwable $e) {
            report($e);
            $this->apply($req, RailResult::failed('The card page could not be opened. Try again or use M-PESA.'));
            throw ValidationException::withMessages(['amount' => 'The card page could not be opened. Try again or add money by M-PESA.']);
        }
        $req->forceFill(['provider_reference' => $checkout, 'meta' => array_merge($req->meta, ['checkout_id' => $checkout, 'card_charge' => $charge])])->save();

        return [$order->fresh(), $url];
    }

    /**
     * Money paid to a person's own account number from the M-PESA menu (a Cloud9 paybill deposit we did not ask for).
     * Booked as a top-up: the customer pays the payment charge. Idempotent by the deposit id.
     */
    public function creditPaybill(User $user, array $deposit, RailResult $result): ?RampOrder
    {
        $id = (string) ($deposit['id'] ?? '');
        if ($id === '' || $result->status !== 'succeeded') {
            return null;
        }
        $req = DB::transaction(function () use ($user, $deposit, $id) {
            if (RailRequest::where('rail', 'cloud9')->where('kind', 'collect')->where('provider_reference', $id)->lockForUpdate()->exists()) {
                return null;
            }
            $cents = (int) round(((float) ($deposit['amount'] ?? 0)) * 100);
            $from = trim((string) ($deposit['narration'] ?? '')) ?: (string) ($deposit['phone_number'] ?? 'M-PESA');
            $o = RampOrder::create([
                'uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'rail' => 'cloud9', 'destination_type' => 'wallet',
                'destination' => ['wallet' => 'Sharet wallet', 'via' => 'account_number', 'from' => mb_substr($from, 0, 80)],
                'payer_phone' => (string) ($deposit['phone_number'] ?? ''), 'funding' => 'mpesa',
                'amount' => $cents, 'fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $cents, 'currency' => config('ledger.currency'),
                'status' => 'collecting', 'quote_expires_at' => now(),
                'timeline' => [$this->event('Paid to your account number from M-PESA · '.mb_substr($from, 0, 80))],
            ]);
            $req = RailRequest::create([
                'rail' => 'cloud9', 'kind' => 'collect', 'reference' => "ramp:{$o->uuid}:collect", 'amount' => $cents, 'currency' => $o->currency,
                'party' => $o->payer_phone, 'status' => 'pending', 'provider_reference' => $id,
                'meta' => ['role' => 'collect', 'order' => $o->uuid, 'destination_type' => 'phone', 'destination' => null, 'method' => 'account_number',
                    'callbacks' => [['at' => now()->toIso8601String(), 'body' => $deposit]]],
            ]);
            $o->forceFill(['collect_request_id' => $req->id])->save();

            return $req;
        });
        if (! $req) {
            return null;
        }
        $this->apply($req, $result);

        return RampOrder::where('collect_request_id', $req->id)->first();
    }

    /** The payer confirms: we ask their M-PESA to pay (STK push), or take it from their wallet and pay at once. */
    public function confirm(RampOrder $order): RampOrder
    {
        try {
            $request = $this->startConfirmed($order);
        } catch (LedgerException $e) {
            throw $e->reason === 'insufficient_funds'
                ? ValidationException::withMessages(['amount' => 'Your wallet no longer has enough for this payment.']) : $e;
        }
        if ($request === 'expired') {
            throw ValidationException::withMessages(['order' => 'This quote has expired. Get a new one.']);
        }
        $request && $this->send($request);

        return $order->fresh();
    }

    private function startConfirmed(RampOrder $order): RailRequest|string|null
    {
        return DB::transaction(function () use ($order) {
            $o = RampOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($o->status !== 'quoted') {
                return null; // already confirmed: confirming twice does nothing
            }
            if ($o->quote_expires_at->isPast()) {
                $this->move($o, 'expired', 'Quote expired before it was confirmed');

                return 'expired';
            }
            $this->checkLimits($o->user, $o->total, $o->id);
            if ($o->funding === 'wallet') {
                $this->ledger->post("ramp:{$o->uuid}:funded", 'wallet_spend', [
                    Ledger::debit($this->ledger->account('2100', $o->user), $o->total),
                    Ledger::credit($this->ledger->account('2200', $o), $o->total),
                ], ['source' => $o, 'memo' => "From wallet to {$o->recipient()}"]);
                $req = $this->newRequest($o, 'payout', $this->party($o), $o->amount);
                if (! $this->reserveFloat($req)) {
                    throw ValidationException::withMessages(['amount' => "Sharet's M-PESA float is low right now: nothing was taken. Try again shortly."]);
                }
                $o->payout_request_id = $req->id;
                $this->move($o, 'paying', 'Taken from your wallet. Sending to '.$o->recipient());

                return $req;
            }
            $req = $this->newRequest($o, 'collect', $o->payer_phone, $o->total);
            $o->collect_request_id = $req->id;
            $this->move($o, 'collecting', 'M-PESA prompt sent to '.$o->payer_phone);

            return $req;
        });
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
        if ($request?->meta['awaiting_float'] ?? false) {
            return $order->fresh(); // never sent yet: nothing to ask the rail about
        }
        $wait = (int) config('rails.callback_wait.'.$request?->rail, 0);
        if ($request && $wait && empty($request->meta['callbacks']) && $request->created_at->gt(now()->subSeconds($wait))) {
            return $order->fresh(); // the rail answers by callback: give it time before asking
        }
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
                // what the provider kept (Cloud9): booked as Sharet's cost when the order is posted; and the network the money
                // really moved on (M-PESA, bank, card…), whose own reference is the receipt. Three references per payment:
                // Sharet's (the order), the rail's (provider_reference) and the network's (receipt).
                'meta' => array_merge($req->meta ?? [], array_filter(['charge' => $result->charge ?: null, 'network' => $result->network ?? ($req->rail === 'daraja' ? 'M-PESA' : null)])),
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

    /**
     * Money M-PESA confirms after we marked the collection failed (a late callback, or a status check that answered
     * too early). The money is real, so it is booked: a top-up completes; any other payment's money goes to the payer's
     * wallet (the payment itself stays failed and is not sent). Idempotent: the same journal key as a normal collection.
     */
    public function lateCollect(RailRequest $request, RailResult $result): ?RampOrder
    {
        return DB::transaction(function () use ($request, $result) {
            $req = RailRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $o = RampOrder::where('collect_request_id', $req->id)->lockForUpdate()->first();
            if (! $o || $req->status !== 'failed' || $o->status !== 'failed' || ($req->meta['role'] ?? null) !== 'collect') {
                return $o;
            }
            $req->fill(['status' => 'succeeded', 'receipt' => $result->receipt, 'failure' => null, 'completed_at' => now()])->save();
            $topUp = $o->destination_type === 'wallet';
            $req->forceFill(['meta' => array_merge($req->meta ?? [], array_filter(['charge' => $result->charge ?: null, 'network' => $result->network]))])->save();
            $credited = $topUp ? $this->chargeToCustomer($o, $req) : $o->total;
            $this->ledger->post("ramp:{$o->uuid}:collected", 'ramp_collect', [
                ...$this->collectedLegs($req, $o->total, $topUp),
                Ledger::credit($this->ledger->account('2100', $o->user), $credited),
            ], ['source' => $o, 'memo' => 'Late M-PESA confirmation from '.$o->payer_phone.' · '.$result->receipt]);
            $o->failure = null;
            $this->move($o, $topUp ? 'paid' : 'failed', $topUp
                ? "M-PESA confirmed · {$result->receipt} · added to your wallet"
                : "M-PESA confirmed late · {$result->receipt} · KES ".Money::format($o->total).' added to your wallet instead');

            \App\Models\SupportCase::raise($o, 'LATE_CONFIRMATION', 'M-PESA confirmed KES '.Money::format($o->total).' ('.$result->receipt.') after the payment was shown as failed. Tell the customer it is '
                .($topUp ? 'in their wallet.' : 'in their wallet and the payment was not sent.'));

            return $o;
        });
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
            $topUp = $o->destination_type === 'wallet';
            $credited = $topUp ? $this->chargeToCustomer($o, $req) : $o->total;
            $this->ledger->post("ramp:{$o->uuid}:collected", 'ramp_collect', [
                ...$this->collectedLegs($req, $o->total, $topUp),
                Ledger::credit($topUp ? $this->ledger->account('2100', $o->user) : $orderAcct, $credited),
            ], ['source' => $o, 'memo' => ($topUp ? (\App\Models\ApiPaymentRequest::where('public_id', $o->sharetReference())->where('user_id', $o->user_id)->exists()
                ? 'Payment request paid by M-PESA from ' : 'Wallet top-up from ') : 'Collected from ')."{$o->payer_phone} · ".($req->meta['network'] ?? 'M-PESA')." {$req->receipt}"]);
            if ($topUp) {
                $network = ($req->meta['method'] ?? null) === 'card' ? 'card' : ($req->meta['network'] ?? 'M-PESA');
                $cardCharge = (int) ($req->meta['card_charge'] ?? 0);
                $this->move($o, 'paid', match (true) {
                    (bool) $o->fee => 'Added '.Money::format($o->amount)." to your wallet · {$network} {$req->receipt} · payment charge ".Money::format($o->fee),
                    $cardCharge > 0 => 'Added '.Money::format($o->amount)." to your wallet · card · card charge ".Money::format($cardCharge).' paid on your card',
                    default => "Added to your wallet · {$network} {$req->receipt}",
                });

                return null;
            }
            $payout = $this->newRequest($o, 'payout', $this->party($o), $o->amount);
            $o->payout_request_id = $payout->id;
            $this->move($o, 'paying', "Paid by M-PESA {$req->receipt}. Sending to {$o->recipient()}");

            return $this->reserveFloat($payout) ? $payout : $this->awaitFloat($o, $payout);
        }

        if ($role === 'payout' && $o->status === 'paying') {
            if ($ok) {
                $legs = array_values(array_filter([
                    Ledger::debit($orderAcct, $o->total),
                    ...$this->paidOutLegs($req, $o->amount),
                    $o->fee ? Ledger::credit($this->ledger->account('4100'), $o->fee) : null,
                    $o->excise ? Ledger::credit($this->ledger->account('2300'), $o->excise) : null,
                    $o->rounding ? Ledger::credit($this->ledger->account('4900'), $o->rounding) : null,
                ]));
                $this->settleFloat($req, "ramp:{$o->uuid}:paid", 'ramp_payout', $legs, ['source' => $o, 'memo' => "Paid {$o->recipient()} · ref {$req->receipt}"]);
                $this->move($o, 'paid', "Received by {$o->recipient()} · ref {$req->receipt}");

                return null;
            }
            $this->releaseFloat($req);
            if ($o->funding === 'wallet') {
                $this->ledger->post("ramp:{$o->uuid}:refunded", 'wallet_refund', [
                    Ledger::debit($orderAcct, $o->total),
                    Ledger::credit($this->ledger->account('2100', $o->user), $o->total),
                ], ['source' => $o, 'memo' => 'Returned to wallet: '.$req->failure]);
                $this->move($o, 'refunded', "Could not pay the recipient ({$req->failure}). ".Money::format($o->total).' is back in your wallet', $req->failure);

                return null;
            }
            $refund = $this->newRequest($o, 'refund', $o->payer_phone, $o->total);
            $o->refund_request_id = $refund->id;
            $this->move($o, 'refunding', "Could not pay the recipient ({$req->failure}). Refunding ".Money::format($o->total).' to '.$o->payer_phone, $req->failure);

            return $this->reserveFloat($refund) ? $refund : $this->awaitFloat($o, $refund);
        }

        if ($role === 'refund' && $o->status === 'refunding') {
            if ($ok) {
                $this->settleFloat($req, "ramp:{$o->uuid}:refunded", 'ramp_refund', [
                    Ledger::debit($orderAcct, $o->total),
                    ...$this->paidOutLegs($req, $o->total),
                ], ['source' => $o, 'memo' => "Refunded to {$o->payer_phone} · ref {$req->receipt}"]);
                $this->move($o, 'refunded', 'Refunded in full to '.$o->payer_phone." · ref {$req->receipt}");

                return null;
            }
            $this->releaseFloat($req);
            $this->move($o, 'refund_failed', 'Refund could not be sent: our team has been alerted', $req->failure);
        }

        return null;
    }

    /**
     * Money in from a rail: what landed in its account, and what the provider kept. On a wallet top-up the customer
     * pays the charge (their wallet gets the rest: chargeToCustomer); on a payment collected to send on, it comes out
     * of the Sharet fee they were shown (Sharet's cost, 5130).
     */
    private function collectedLegs(RailRequest $req, int $gross, bool $customerPays = false): array
    {
        $charge = min((int) ($req->meta['charge'] ?? 0), $gross);

        return array_values(array_filter([
            Ledger::debit($this->ledger->account($this->rails->account($req->rail, 'collect')), $gross - $charge),
            $charge && ! $customerPays ? Ledger::debit($this->ledger->account('5130'), $charge) : null,
        ]));
    }

    /**
     * A top-up the rail charged for (Cloud9 takes its charge out of the deposit): the customer pays it. The order keeps
     * what they paid by M-PESA as total, the charge as fee, and what reached the wallet as amount. Returns that amount.
     */
    private function chargeToCustomer(RampOrder $o, RailRequest $req): int
    {
        $charge = min((int) ($req->meta['charge'] ?? 0), $o->total);
        if ($charge) {
            $o->forceFill(['fee' => $charge, 'amount' => $o->total - $charge]);
        }

        return $o->total - $charge;
    }

    /** Money out through a rail: the amount sent plus what the provider charged on top (Sharet's cost, 5130). */
    private function paidOutLegs(RailRequest $req, int $amount): array
    {
        $charge = (int) ($req->meta['charge'] ?? 0);

        return array_values(array_filter([
            Ledger::credit($this->ledger->account($this->rails->account($req->rail, 'payout')), $amount + $charge),
            $charge ? Ledger::debit($this->ledger->account('5130'), $charge) : null,
        ]));
    }

    /**
     * Hand a request to its rail, outside any transaction. A crash or timeout leaves it "unknown" for a status check.
     * If the rail refuses before accepting (nothing was sent: no provider id) and the switch names a fallback, the
     * request moves to that rail and is tried there once. A timeout never falls back: the first rail may still pay.
     */
    private function send(RailRequest $request): void
    {
        $result = $this->attempt($request);
        if ($result->status === 'failed' && ! $result->providerReference && ! ($request->meta['fell_back_from'] ?? null)) {
            $order = RampOrder::where('uuid', $request->meta['order'] ?? '-')->first();
            if (($fallback = $this->rails->fallbackFor($request, $order?->user)) && $this->moveToRail($request, $fallback, $result->message)) {
                $request = $request->fresh();
                $result = $this->attempt($request);
            }
        }
        $this->apply($request, $result);
    }

    private function attempt(RailRequest $request): RailResult
    {
        try {
            $rail = $this->rails->for($request->rail);

            return $request->kind === 'collect' ? $rail->collect($request) : $rail->payout($request);
        } catch (Throwable $e) {
            report($e);

            return RailResult::unknown('No answer from the provider: we will check, not resend.');
        }
    }

    /** Re-point a refused request at another rail (its float hold moves too). False when that rail has no float for it. */
    private function moveToRail(RailRequest $request, string $rail, ?string $why): bool
    {
        return DB::transaction(function () use ($request, $rail, $why) {
            $req = RailRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($req->status !== 'pending') {
                return false;
            }
            $from = $req->rail;
            $held = $req->kind === 'payout' && \App\Models\LedgerHold::where('reference', 'float:'.$req->reference)->where('status', 'open')->exists();
            $held && $this->releaseFloat($req);
            $req->forceFill(['rail' => $rail, 'meta' => array_merge($req->meta ?? [], ['fell_back_from' => $from, 'fell_back_because' => $why])])->save();
            if ($held && ! $this->reserveFloat($req)) {
                $req->forceFill(['rail' => $from])->save();
                $this->reserveFloat($req);

                return false;
            }
            $req->kind === 'payout' && RampOrder::where('uuid', $req->meta['order'] ?? '-')->update(['rail' => $rail]);   // the order's rail is its payout rail

            return true;
        });
    }

    private function newRequest(RampOrder $o, string $role, string $party, int $amount): RailRequest
    {
        return RailRequest::create([
            'rail' => $role === 'collect' ? $this->rails->collecting($o->user) : $this->rails->paying($o->user, $role === 'refund' ? 'phone' : $o->destination_type),
            'kind' => $role === 'collect' ? 'collect' : 'payout',
            'reference' => "ramp:{$o->uuid}:{$role}",
            'amount' => $amount,
            'currency' => $o->currency,
            'party' => $party,
            'meta' => ['role' => $role, 'order' => $o->uuid, 'destination_type' => $role === 'payout' ? $o->destination_type : 'phone', 'destination' => $role === 'payout' ? $o->destination : null],
            'status' => 'pending',
        ]);
    }

    /**
     * Escrow for the float: hold the payout's amount on its rail's payout account until M-PESA's result, so two payouts
     * can never spend the same float and nothing is sent the books can't cover. False = not enough float.
     */
    private function reserveFloat(RailRequest $req): bool
    {
        try {
            $this->ledger->hold($this->ledger->account($this->rails->account($req->rail, 'payout')), $req->amount, 'float:'.$req->reference, 'Payout in flight');

            return true;
        } catch (LedgerException $e) {
            if ($e->reason !== 'insufficient_funds') {
                throw $e;
            }

            return false;
        }
    }

    /** M-PESA confirmed the payout: the hold becomes the posting (Ledger::capture). Older payouts without a hold just post. */
    private function settleFloat(RailRequest $req, string $key, string $type, array $legs, array $options): void
    {
        \App\Models\LedgerHold::where('reference', 'float:'.$req->reference)->where('status', 'open')->exists()
            ? $this->ledger->capture('float:'.$req->reference, $key, $type, $legs, $options)
            : $this->ledger->post($key, $type, $legs, $options);
    }

    private function releaseFloat(RailRequest $req): void
    {
        if (\App\Models\LedgerHold::where('reference', 'float:'.$req->reference)->where('status', 'open')->exists()) {
            $this->ledger->release('float:'.$req->reference);
        }
    }

    /** Not enough float to send: keep it unsent, tell the desk, and let ramp:resolve send it once the float is topped up. */
    private function awaitFloat(RampOrder $o, RailRequest $req): ?RailRequest
    {
        $req->forceFill(['meta' => ($req->meta ?? []) + ['awaiting_float' => true]])->save();
        $o->timeline = array_merge($o->timeline ?? [], [$this->event('Waiting for Sharet to top up its M-PESA float before sending')]);
        $o->save();
        \App\Models\SupportCase::raise($o, 'FLOAT_LOW', 'KES '.Money::format($req->amount).' is waiting to be sent: the M-PESA float is too low. Move money to the utility account, record it (ledger:float), and it goes out within a minute.', 15);

        return null;
    }

    /** ramp:resolve: send the payouts that waited for float, oldest first, as far as the float now allows. */
    public function sendAwaitingFloat(): int
    {
        $sent = 0;
        RailRequest::whereIn('status', ['pending'])->where('meta->awaiting_float', true)->orderBy('id')->each(function (RailRequest $req) use (&$sent) {
            $go = DB::transaction(function () use ($req) {
                $r = RailRequest::whereKey($req->id)->lockForUpdate()->first();
                if (! ($r->meta['awaiting_float'] ?? false) || ! $this->reserveFloat($r)) {
                    return false;
                }
                $r->forceFill(['meta' => array_diff_key($r->meta, ['awaiting_float' => 1])])->save();

                return true;
            });
            if ($go) {
                $this->send($req->fresh());
                $sent++;
            }
        });

        return $sent;
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
        app(\App\Api\Webhooks::class)->orderMoved($o);   // API payments riding on this order follow it (and webhooks fire after commit)
    }

    private function event(string $text): array
    {
        return ['at' => now()->timezone('Africa/Nairobi')->format('H:i:s'), 'text' => $text];
    }

    private function payer(User $user): string
    {
        return filled($user->phone) ? Destination::phone((string) $user->phone, 'payer')
            : throw ValidationException::withMessages(['payer' => 'Add your M-PESA number to your profile first.']);
    }

    /** "1,500" → cents, whole shillings and at least the minimum. */
    private function cents(string $amount): int
    {
        try {
            $cents = Money::cents($amount);
        } catch (LedgerException) {
            throw ValidationException::withMessages(['amount' => 'Enter an amount like 1,500 (whole shillings).']);
        }
        if ($cents % 100 !== 0) {
            throw ValidationException::withMessages(['amount' => 'M-PESA sends whole shillings: leave out the cents.']);
        }
        if ($cents < config('pricing.min_amount')) {
            throw ValidationException::withMessages(['amount' => 'The smallest amount is KES '.Money::format(config('pricing.min_amount')).'.']);
        }

        return $cents;
    }

    /** Limits by KYC level, per payment and per day (config/pricing.php). Top-ups count only against their own payment. */
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
        $today = RampOrder::where('user_id', $user->id)->where('destination_type', '!=', 'wallet')->where('created_at', '>=', now('Africa/Nairobi')->startOfDay()->utc()) // a Kenyan day
            ->whereIn('status', ['collecting', 'paying', 'paid', 'refunding'])->when($exceptOrder, fn ($q) => $q->whereKeyNot($exceptOrder))->sum('total');
        if ($today + $total > $limits['per_day']) {
            throw ValidationException::withMessages(['amount' => 'This would pass your daily limit of KES '.Money::format($limits['per_day']).'. Upgrade your verification to send more.']);
        }
    }
}

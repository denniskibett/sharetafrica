<?php

namespace App\Merchant;

use App\Ledger\Money;
use App\Models\MerchantRefund;
use App\Models\MerchantSale;
use App\Models\MerchantTill;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Support\Handle;
use App\Support\TransactionPin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Refunds: a cashier asks, the owner approves with their Sharet PIN (or refunds directly). Money goes back the way it
 * came, from the shop's wallet: to the payer's Sharet wallet (free, instant) or their M-PESA number (a payout, its fee
 * paid by the shop). A refund never exceeds what is left of the sale; partial refunds are allowed.
 */
final class Refunds
{
    public function __construct(private RampOrders $orders) {}

    public function ask(MerchantSale $sale, string $amount, ?string $reason, ?MerchantTill $till): MerchantRefund
    {
        if ($sale->status !== 'paid') {
            throw ValidationException::withMessages(['sale' => 'Only a paid sale can be refunded.']);
        }
        if ($till) {
            if ($sale->till_id !== $till->id) {
                throw ValidationException::withMessages(['sale' => 'You can ask for refunds of your own till\'s sales only.']);
            }
            if ($sale->paid_at->lt(now()->subDays(config('merchant.refund_days')))) {
                throw ValidationException::withMessages(['sale' => 'This sale is older than '.config('merchant.refund_days').' days. Ask the owner.']);
            }
        }
        $cents = $this->cents($amount);

        return DB::transaction(function () use ($sale, $cents, $reason, $till) {
            $s = MerchantSale::whereKey($sale->id)->lockForUpdate()->first();
            $asked = (int) MerchantRefund::where('sale_id', $s->id)->where('status', 'asked')->sum('amount');
            $left = $s->amount - $s->refunded - $asked;
            if ($cents > $left) {
                throw ValidationException::withMessages(['amount' => $left > 0 ? 'At most KES '.Money::format($left).' can still be refunded.' : 'This sale has been refunded already.']);
            }

            return MerchantRefund::create(['uuid' => (string) Str::uuid(), 'sale_id' => $s->id, 'company_id' => $s->company_id, 'till_id' => $till?->id,
                'amount' => $cents, 'reason' => filled($reason) ? Str::limit(strip_tags((string) $reason), 140, '') : null, 'status' => 'asked']);
        });
    }

    /** The owner approves with their PIN: the money leaves the shop's wallet now. */
    public function approve(MerchantRefund $refund, User $owner, ?string $pin): MerchantRefund
    {
        TransactionPin::check($owner, $pin);
        $refund = DB::transaction(function () use ($refund, $owner) {
            $r = MerchantRefund::whereKey($refund->id)->lockForUpdate()->first();
            if ($r->status !== 'asked') {
                throw ValidationException::withMessages(['refund' => 'This refund is '.$r->status.' already.']);
            }
            $s = MerchantSale::whereKey($r->sale_id)->lockForUpdate()->first();
            if ($s->refunded + $r->amount > $s->amount) {
                throw ValidationException::withMessages(['amount' => 'This would refund more than the sale.']);
            }
            $s->increment('refunded', $r->amount);   // reserved now; given back if the payout fails
            $r->update(['status' => 'approved', 'decided_by' => $owner->id, 'decided_at' => now()]);

            return $r;
        });

        try {
            $order = $this->send($refund->fresh());
        } catch (ValidationException $e) {
            $this->failed($refund->fresh(), (string) collect($e->errors())->flatten()->first());
            throw $e;
        }
        $refund->update(['ramp_order_id' => $order->id]);
        self::orderMoved($order->fresh());

        return $refund->fresh();
    }

    /** The owner refunds a sale directly (no cashier asked): one step, with the PIN. */
    public function refund(MerchantSale $sale, User $owner, string $amount, ?string $reason, ?string $pin): MerchantRefund
    {
        TransactionPin::check($owner, $pin);   // before anything is recorded

        return $this->approve($this->ask($sale, $amount, $reason, null), $owner, $pin);
    }

    public function decline(MerchantRefund $refund, User $owner): MerchantRefund
    {
        $n = MerchantRefund::whereKey($refund->id)->where('status', 'asked')->update(['status' => 'declined', 'decided_by' => $owner->id, 'decided_at' => now()]);
        if (! $n) {
            throw ValidationException::withMessages(['refund' => 'This refund is '.$refund->fresh()->status.' already.']);
        }

        return $refund->fresh();
    }

    /** Back the way it came: a Sharet payer gets a wallet transfer, an M-PESA payer an M-PESA payout. */
    private function send(MerchantRefund $r): RampOrder
    {
        $sale = $r->sale;
        $shop = $sale->company;
        $wallet = Shop::wallet($shop);
        $paid = $sale->order ?? $sale->request?->order;
        $note = 'Refund from '.$shop->name;
        if ($paid && $paid->destination_type === 'sharet' && ($payer = User::find($paid->user_id))) {
            return $this->orders->transfer($wallet, Handle::for($payer), Money::plain($r->amount), $note);
        }
        $phone = $paid?->payer_phone;
        if (! $phone) {
            throw ValidationException::withMessages(['refund' => 'We could not find who paid this sale. Contact support@sharet.africa.']);
        }
        $order = $this->orders->quote($wallet, 'phone', ['phone' => $phone], Money::plain($r->amount), $note, 'wallet');

        return $this->orders->confirm($order);
    }

    /** A refund's payout moved: paid → done; failed → the amount counts as not refunded again. */
    public static function orderMoved(RampOrder $o): void
    {
        $r = MerchantRefund::where('ramp_order_id', $o->id)->where('status', 'approved')->first();
        if (! $r) {
            return;
        }
        if ($o->status === 'paid') {
            $r->update(['status' => 'paid']);
            self::returnFee($r->sale->fresh());
        } elseif (in_array($o->status, ['failed', 'rejected', 'reversed', 'expired'], true)) {
            app(self::class)->failed($r, 'The refund could not be sent: '.($o->failure ?: 'try again'));
        }
    }

    private function failed(MerchantRefund $r, string $why): void
    {
        DB::transaction(function () use ($r, $why) {
            $locked = MerchantRefund::whereKey($r->id)->lockForUpdate()->first();
            if ($locked->status !== 'approved') {
                return;
            }
            MerchantSale::whereKey($locked->sale_id)->decrement('refunded', $locked->amount);
            $locked->update(['status' => 'failed', 'failure' => Str::limit($why, 190, '')]);
        });
    }

    /** If the owner chose so (merchant.refund_returns_fee): a sale refunded in full gets Sharet's fee back. */
    private static function returnFee(MerchantSale $s): void
    {
        if (! config('merchant.refund_returns_fee') || $s->refunded < $s->amount || $s->fee <= 0) {
            return;
        }
        $j = \App\Models\LedgerJournal::where('idempotency_key', "merchant-fee:{$s->uuid}")->first();
        $j && app(\App\Ledger\Ledger::class)->reverse($j, 'Merchant fee returned: sale refunded in full');
    }

    public static function present(MerchantRefund $r): array
    {
        $sale = $r->sale;

        return array_filter([
            'uuid' => $r->uuid, 'status' => $r->status, 'amount' => Money::format($r->amount), 'reason' => $r->reason,
            'sale' => ['uuid' => $sale->uuid, 'amount' => Money::format($sale->amount), 'via' => ['mpesa' => 'M-PESA', 'sharet' => 'Sharet', 'account' => 'Account number'][$sale->paid_via] ?? null,
                'payer' => Sales::present($sale)['payer'] ?? null, 'time' => $sale->paid_at?->timezone(config('merchant.timezone'))->format('j M H:i')],
            'asked_by' => $r->till ? ['cashier' => $r->till->firstName(), 'till' => $r->till->label()] : null,
            'goes_back_to' => ($sale->order ?? $sale->request?->order)?->destination_type === 'sharet' ? 'Their Sharet wallet, at once' : 'Their M-PESA, within minutes',
            'failure' => $r->failure,
            'time' => $r->created_at?->timezone(config('merchant.timezone'))->format('H:i'),
        ], fn ($v) => $v !== null);
    }

    private function cents(string $amount): int
    {
        try {
            $cents = Money::cents($amount);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['amount' => 'Enter an amount like 850.']);
        }
        if ($cents <= 0 || $cents % 100 !== 0) {
            throw ValidationException::withMessages(['amount' => 'Refund whole shillings, more than zero.']);
        }

        return $cents;
    }
}

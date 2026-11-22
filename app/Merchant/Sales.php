<?php

namespace App\Merchant;

use App\Api\PaymentRequests;
use App\Integrations\Sms\Sms;
use App\Ledger\Ledger;
use App\Ledger\Money;
use App\Models\ApiPaymentRequest;
use App\Models\Company;
use App\Models\MerchantClose;
use App\Models\MerchantSale;
use App\Models\MerchantTill;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Taking money for a shop. Every sale is a payment request of kind "merchant" to the shop's wallet, so it uses the same
 * pay page (/r/{token}: M-PESA prompt or Sharet wallet + PIN), the same M-PESA collection, AML screening and ledger
 * journals as everything else:
 *
 *   mpesa  the till prompts the customer's phone (STK push); the M-PESA PIN on their phone approves it
 *   qr     the till shows a QR of the pay page with the amount in it (Sharet members pay from their wallet)
 *   link   the owner shares a payment link (fixed amount, one payment)
 *   account  money paid to the shop's account number from the M-PESA menu (booked when it arrives)
 *
 * When a sale is paid, Sharet's merchant fee is taken from the shop's wallet in its own journal
 * (Dr 2100 shop · Cr 4110 merchant fees · Cr 2300 excise inside the fee). End of day closes each till.
 */
final class Sales
{
    public const METHODS = ['mpesa', 'qr', 'link'];

    public function __construct(private PaymentRequests $requests, private Ledger $ledger) {}

    /** Start a sale. $till null: the owner charges. $phone: the customer's M-PESA number (method mpesa). */
    public function charge(Company $shop, ?MerchantTill $till, string $amount, string $method, ?string $phone = null, ?string $description = null): MerchantSale
    {
        if (! $shop->verified_at) {
            throw ValidationException::withMessages(['amount' => 'Sharet is still checking your business. You can take payments once it is verified (usually a day).']);
        }
        $cents = $this->cents($amount);
        if ($till && $cents > config('merchant.cashier_max_sale') * 100) {
            throw ValidationException::withMessages(['amount' => 'A cashier can charge up to KES '.number_format(config('merchant.cashier_max_sale')).' a sale. Ask the owner for more.']);
        }
        $wallet = Shop::wallet($shop);
        $minutes = $method === 'link' ? 60 * 24 * 7 : 15;
        $sale = DB::transaction(function () use ($shop, $till, $cents, $method, $description, $wallet, $minutes) {
            $r = ApiPaymentRequest::create([
                'public_id' => PaymentRequests::newReference(true), 'kind' => 'merchant', 'token' => Str::random(40),
                'api_key_id' => null, 'user_id' => $wallet->id, 'environment' => 'live', 'amount' => $cents,
                'reference' => filled($description) ? Str::limit(strip_tags((string) $description), 140, '') : ($till ? $shop->name.' · '.$till->label() : $shop->name),
                'channels' => ['sharet', 'mpesa'], 'status' => 'open', 'expires_at' => now()->addMinutes($minutes),
            ]);

            return MerchantSale::create(['uuid' => (string) Str::uuid(), 'company_id' => $shop->id, 'till_id' => $till?->id, 'method' => $method,
                'payment_request_id' => $r->id, 'amount' => $cents, 'status' => 'pending',
                'description' => filled($description) ? Str::limit(strip_tags((string) $description), 140, '') : null]);
        });

        if ($method === 'mpesa') {
            try {
                $r = $this->requests->payWithMpesa($sale->request, (string) $phone);   // AML screening and the STK push happen in RampOrders::confirm
            } catch (\Throwable $e) {
                $sale->request->update(['status' => 'cancelled']);
                $sale->update(['status' => 'failed']);
                throw $e;
            }
            $r->ramp_order_id && $sale->forceFill(['ramp_order_id' => $r->ramp_order_id])->save();
        }

        return $sale->fresh();
    }

    /** The cashier waits on the sale: follow the M-PESA prompt (status check) and return it as it is now. */
    public function follow(MerchantSale $sale): MerchantSale
    {
        $r = $sale->request;
        if ($sale->status === 'pending' && $r?->order && ! $r->order->final()) {
            app(\App\Ramp\RampOrders::class)->refresh($r->order);   // moves the order: requestPaid / orderMoved update the sale
        }
        if ($sale->fresh()->status === 'pending' && $r && $r->fresh()->status === 'expired') {
            $sale->update(['status' => 'cancelled']);
        }

        return $sale->fresh();
    }

    public function cancel(MerchantSale $sale): MerchantSale
    {
        if ($sale->status !== 'pending') {
            throw ValidationException::withMessages(['sale' => 'This sale is '.$sale->status.'.']);
        }
        $r = $sale->request;
        if ($r && $r->order && ! $r->order->final()) {
            throw ValidationException::withMessages(['sale' => 'The customer has an M-PESA prompt open. Wait for it to finish.']);
        }
        $r && $r->status === 'open' && $this->requests->cancel($r);
        $sale->update(['status' => 'cancelled']);

        return $sale->fresh();
    }

    // ------------------------------------------------------------ hooks (PaymentRequests::markPaid, Webhooks::orderMoved)

    /** A merchant payment request was paid (M-PESA, Sharet wallet, or M-PESA then wallet). */
    public static function requestPaid(ApiPaymentRequest $r): void
    {
        if ($r->kind !== 'merchant' || $r->status !== 'paid') {
            return;
        }
        if ($sale = MerchantSale::where('payment_request_id', $r->id)->first()) {
            app(self::class)->markPaid($sale, $r->paid_via === 'sharet' ? 'sharet' : 'mpesa', $r->order);
        }
    }

    /** Any order moved: a shop's account-number deposit arrived, a prompt failed, or a refund finished. */
    public static function orderMoved(RampOrder $o): void
    {
        if ($o->status === 'paid' && $o->destination_type === 'wallet' && ($o->destination['via'] ?? null) === 'account_number') {
            $wallet = User::find($o->user_id);
            if ($wallet?->is_business_wallet && $wallet->company_id && \App\Models\MerchantTill::where('company_id', $wallet->company_id)->exists()) {
                app(self::class)->recordDeposit($wallet, $o);
            }

            return;
        }
        if (in_array($o->status, ['failed', 'rejected', 'expired'], true)) {
            MerchantSale::where('ramp_order_id', $o->id)->where('status', 'pending')->where('method', 'mpesa')->update(['status' => 'failed']);
        }
        Refunds::orderMoved($o);
    }

    /** Paid: book Sharet's fee from the shop's wallet, once. */
    public function markPaid(MerchantSale $sale, string $via, ?RampOrder $order): MerchantSale
    {
        return DB::transaction(function () use ($sale, $via, $order) {
            $s = MerchantSale::whereKey($sale->id)->lockForUpdate()->first();
            if ($s->status === 'paid') {
                return $s;
            }
            $fee = self::fee($s->amount, $via);
            if ($fee > 0) {
                $bp = (int) config('pricing.excise_bp');
                $excise = intdiv($fee * $bp, 10_000 + $bp);   // excise duty inside the fee (fee = income + 15% of it)
                $wallet = Shop::wallet($s->company);
                $this->ledger->post("merchant-fee:{$s->uuid}", 'merchant_fee', array_values(array_filter([
                    Ledger::debit($this->ledger->account('2100', $wallet), $fee),
                    Ledger::credit($this->ledger->account('4110'), $fee - $excise),
                    $excise > 0 ? Ledger::credit($this->ledger->account('2300'), $excise) : null,
                ])), ['source_type' => $s->getMorphClass(), 'source_id' => $s->id, 'memo' => 'Merchant fee · '.$s->company->name.($order ? ' · '.$order->sharetReference() : '')]);
            }
            $s->forceFill(['status' => 'paid', 'paid_via' => $via, 'paid_at' => now(), 'fee' => $fee, 'ramp_order_id' => $order?->id ?? $s->ramp_order_id])->save();

            return $s;
        });
    }

    /** Money paid to the shop's account number from the M-PESA menu: a sale with no till. Idempotent by the order. */
    public function recordDeposit(User $wallet, RampOrder $o): ?MerchantSale
    {
        if (MerchantSale::where('ramp_order_id', $o->id)->exists()) {
            return null;
        }
        $shop = Company::find($wallet->company_id);
        try {
            $sale = MerchantSale::create(['uuid' => (string) Str::uuid(), 'company_id' => $shop->id, 'method' => 'account', 'ramp_order_id' => $o->id,
                'amount' => $o->amount, 'status' => 'pending', 'description' => $o->destination['from'] ?? null]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return null;
        }

        return $this->markPaid($sale, 'account', $o);
    }

    public static function fee(int $amount, string $via): int
    {
        $bp = (int) (config('merchant.fee_bp.'.$via) ?? config('merchant.fee_bp.mpesa'));

        return $bp > 0 ? Money::percent($amount, $bp) : 0;
    }

    // ------------------------------------------------------------ totals, close

    /** Paid sales and refunds of one Kenyan day (or since the till's last close, $open). */
    public static function totals(Company $shop, ?MerchantTill $till = null, ?string $date = null, bool $open = false): array
    {
        [$day, $from, $to] = Shop::day($date);
        $q = MerchantSale::where('company_id', $shop->id)->where('status', 'paid')->whereBetween('paid_at', [$from, $to])
            ->when($till, fn ($w) => $w->where('till_id', $till->id))->when($open, fn ($w) => $w->whereNull('close_id'));
        $sales = $q->get(['amount', 'fee', 'refunded']);

        return ['day' => $day, 'sales' => $sales->count(), 'gross' => (int) $sales->sum('amount'), 'fees' => (int) $sales->sum('fee'),
            'refunds' => (int) $sales->sum('refunded'), 'net' => (int) ($sales->sum('amount') - $sales->sum('fee') - $sales->sum('refunded'))];
    }

    /** End of day for one till: its paid sales not yet closed are closed; the till's phones lock (PIN to open again). */
    public static function close(Company $shop, ?MerchantTill $till, string $by, ?string $date = null): ?MerchantClose
    {
        [$day] = Shop::day($date);
        $close = DB::transaction(function () use ($shop, $till, $by, $day) {
            $q = MerchantSale::where('company_id', $shop->id)->where('status', 'paid')->whereNull('close_id')
                ->when($till, fn ($w) => $w->where('till_id', $till->id), fn ($w) => $w->whereNull('till_id'))->lockForUpdate();
            $sales = $q->get();
            if ($sales->isEmpty() && $by === 'auto') {
                return null;
            }
            $close = MerchantClose::create(['company_id' => $shop->id, 'till_id' => $till?->id, 'day' => $day, 'sales' => $sales->count(),
                'gross' => (int) $sales->sum('amount'), 'fees' => (int) $sales->sum('fee'), 'refunds' => (int) $sales->sum('refunded'), 'closed_by' => $by]);
            MerchantSale::whereIn('id', $sales->pluck('id'))->update(['close_id' => $close->id]);

            return $close;
        });
        $till && Tills::lock($till);

        return $close;
    }

    /**
     * The shop's time came (settle_at, Kenyan time): every till closes, then, if switched on (merchant.auto_settle) and
     * the shop chose a bank or M-PESA, the wallet's balance is paid out (the payout fee comes out of it). Once a day.
     */
    public function closeDay(Company $shop): array
    {
        $s = Shop::settings($shop);
        $today = now(config('merchant.timezone'))->toDateString();
        if (($s['last_closed_day'] ?? null) === $today) {
            return ['closed' => 0, 'settlement' => null];
        }
        $n = 0;
        foreach (MerchantTill::where('company_id', $shop->id)->get() as $till) {
            self::close($shop, $till, 'auto') && $n++;
        }
        self::close($shop, null, 'auto') && $n++;
        Shop::put($shop, ['last_closed_day' => $today]);
        $settlement = config('merchant.auto_settle') && $s['settlement']['mode'] !== 'keep' ? $this->settle($shop->fresh()) : null;

        return ['closed' => $n, 'settlement' => $settlement];
    }

    /** Pay the shop's balance to its bank or M-PESA (RampOrders quote + confirm, from the wallet). null if nothing to pay. */
    public function settle(Company $shop): ?array
    {
        $s = Shop::settings($shop)['settlement'];
        $wallet = Shop::wallet($shop);
        $orders = app(\App\Ramp\RampOrders::class);
        $balance = intdiv($orders->walletBalance($wallet), 100) * 100;
        if ($balance < (int) config('pricing.min_amount') || $s['mode'] === 'keep') {
            return null;
        }
        [$type, $dest] = $s['mode'] === 'bank' ? ['bank', ['bank' => $s['bank'], 'account' => $s['account']]] : ['phone', ['phone' => $s['phone']]];
        try {
            $amount = $balance;
            for ($i = 0; $i < 4; $i++) {   // the fee comes out of the balance: find the largest amount that fits
                $q = \App\Ramp\Pricing::quote($amount, null, $wallet);
                if ($q['total'] <= $balance) {
                    break;
                }
                $amount -= intdiv($q['total'] - $balance + 99, 100) * 100;
            }
            $order = $orders->quote($wallet, $type, $dest, Money::plain($amount), 'Settlement · '.$shop->name, 'wallet');
            $order = $orders->confirm($order);
            $last = ['amount' => $order->amount, 'order' => $order->uuid, 'at' => now()->toIso8601String(), 'status' => $order->status];
        } catch (ValidationException $e) {
            $last = ['amount' => $amount ?? $balance, 'order' => null, 'at' => now()->toIso8601String(), 'status' => 'failed',
                'failure' => collect($e->errors())->flatten()->first()];
        }
        Shop::put($shop, ['last_settlement' => $last]);

        return $last;
    }

    // ------------------------------------------------------------ presenting

    public static function present(MerchantSale $s, bool $owner = false): array
    {
        $o = $s->order ?? $s->request?->order;
        $payer = null;
        if ($o && $o->destination_type === 'sharet') {
            $payer = ($u = User::find($o->user_id)) ? self::short($u->name) : null;
        } elseif ($o && $o->payer_phone) {
            $payer = self::maskPhone($o->payer_phone);
        }
        $via = ['mpesa' => 'M-PESA', 'sharet' => 'Sharet', 'account' => 'Account number'][$s->paid_via] ?? null;

        return array_filter([
            'uuid' => $s->uuid, 'status' => $s->status, 'method' => $s->method, 'paid_via' => $s->paid_via, 'via' => $via,
            'amount' => Money::format($s->amount), 'refunded' => $s->refunded ? Money::format($s->refunded) : null,
            'fee' => $owner ? Money::format($s->fee) : null,
            'payer' => $payer, 'description' => $s->description,
            'till' => $s->till ? ['number' => $s->till->number, 'label' => $s->till->label(), 'cashier' => $s->till->firstName()] : null,
            'reference' => $s->status === 'paid' && $o ? $o->reference() : $s->request?->public_id,
            'pay_url' => $s->status === 'pending' && $s->request ? route('pay-request.show', $s->request->token) : null,
            'time' => ($s->paid_at ?? $s->created_at)?->timezone(config('merchant.timezone'))->format('H:i'),
            'paid_at' => $s->paid_at?->toIso8601String(),
            'refund_pending' => $s->refunds()->where('status', 'asked')->exists() ?: null,
        ], fn ($v) => $v !== null);
    }

    public static function presentClose(?MerchantClose $c): ?array
    {
        return $c ? ['day' => $c->day->toDateString(), 'sales' => $c->sales, 'gross' => Money::format($c->gross), 'fees' => Money::format($c->fees),
            'refunds' => Money::format($c->refunds), 'net' => Money::format($c->gross - $c->fees - $c->refunds), 'by' => $c->closed_by,
            'at' => $c->created_at?->timezone(config('merchant.timezone'))->format('H:i')] : null;
    }

    /** Text the customer a receipt with the Sharet reference. */
    public function receipt(MerchantSale $sale, ?string $phone = null): bool
    {
        if ($sale->status !== 'paid') {
            throw ValidationException::withMessages(['sale' => 'Only a paid sale has a receipt.']);
        }
        $o = $sale->order;
        $to = $phone ? \App\Ramp\Destination::phone($phone) : ($o?->payer_phone ?: ($o && $o->destination_type === 'sharet' ? User::find($o->user_id)?->phone : null));
        if (! $to) {
            throw ValidationException::withMessages(['phone' => 'Enter the customer\'s phone number.']);
        }
        $ref = $o?->sharetReference() ?? $sale->request?->public_id;

        return Sms::send($to, "Sharet receipt: KES ".Money::format($sale->amount)." paid to {$sale->company->name}"
            .($sale->till ? ' ('.$sale->till->label().')' : '').' on '.$sale->paid_at->timezone(config('merchant.timezone'))->format('j M Y H:i').". Ref {$ref}.", 'merchant-receipt:'.$sale->uuid.':'.$to);
    }

    public static function short(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return count($parts) > 1 ? $parts[0].' '.mb_strtoupper(mb_substr(end($parts), 0, 1)).'.' : $parts[0];
    }

    public static function maskPhone(string $phone): string
    {
        $d = preg_replace('/\D/', '', $phone);

        return strlen($d) >= 12 ? '0'.substr($d, 3, 3).' ••• '.substr($d, -3) : $phone;
    }

    private function cents(string $amount): int
    {
        try {
            $cents = Money::cents($amount);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['amount' => 'Enter an amount like 850.']);
        }
        if ($cents % 100 !== 0) {
            throw ValidationException::withMessages(['amount' => 'Charge whole shillings: leave out the cents.']);
        }
        if ($cents < (int) config('pricing.min_amount')) {
            throw ValidationException::withMessages(['amount' => 'The smallest sale is KES '.Money::format((int) config('pricing.min_amount')).'.']);
        }

        return $cents;
    }
}

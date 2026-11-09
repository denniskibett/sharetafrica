<?php

namespace App\Api;

use App\Models\ApiKey;
use App\Models\ApiPaymentRequest;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\Destination;
use App\Ramp\RampOrders;
use App\Support\Handle;
use App\Support\TransactionPin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Payment requests: money in (docs/v2/INTEGRATION.md §3).
 *  - A business creates one with a "collect" key and gets a hosted pay page (/r/{token}).
 *  - Sharet wallet: the payer signs in and approves with their PIN; a free wallet-to-wallet transfer pays it.
 *    No API call can take money from a customer's wallet without that approval.
 *  - M-PESA: the page sends a prompt to the payer's phone; the collection lands in the business's wallet.
 *  - Sandbox requests (SHTEST…, earlier pr_test_) never touch the ledger: the page offers "pay" and "fail" buttons.
 * Webhooks: payment_request.paid, payment_request.expired.
 */
final class PaymentRequests
{
    public const CHANNELS = ['sharet', 'mpesa'];

    public function __construct(private RampOrders $orders, private Webhooks $webhooks) {}

    public function create(ApiKey $key, User $payee, array $input): ApiPaymentRequest
    {
        if ($key->isLive() && ! $key->company?->verified_at) {
            throw new ApiError(403, 'business_not_verified', 'Your business isn\'t verified yet', 'Live payment requests need a verified business. Use a sk_test_ key meanwhile.');
        }
        $cents = PaymentInput::read(['amount' => $input['amount'] ?? '', 'to' => ['sharet_id' => $payee->sharet_id]], new User)['cents'];

        $payer = (array) ($input['payer'] ?? []);
        $payerUser = isset($payer['sharet_id']) || isset($payer['handle']) ? PaymentInput::account($payer['sharet_id'] ?? null, $payer['handle'] ?? null) : null;
        if ((isset($payer['sharet_id']) || isset($payer['handle'])) && ! $payerUser) {
            throw new ApiError(404, 'account_not_found', 'No such Sharet account', 'Check the payer\'s @name or Sharet ID.');
        }
        if ($payerUser?->is($payee)) {
            throw new ApiError(422, 'own_account', 'That\'s your own account', 'The payer must be someone else.');
        }
        try {
            $payerPhone = isset($payer['phone']) ? Destination::phone((string) $payer['phone'], 'payer.phone') : null;
        } catch (ValidationException $e) {
            throw new ApiError(422, 'invalid_payer', 'Check the payer\'s phone', (string) collect($e->errors())->flatten()->first());
        }

        $channels = array_values(array_intersect(self::CHANNELS, (array) ($input['channels'] ?? self::CHANNELS)));
        if (! $channels) {
            throw new ApiError(422, 'invalid_channels', 'Choose how they can pay', 'channels: "sharet", "mpesa" or both.');
        }
        $minutes = max(5, min(60 * 24 * 30, (int) ($input['expires_in_minutes'] ?? 60 * 24 * 7)));
        $splits = ! empty($input['splits']) ? Splits::resolve((array) $input['splits'], $cents, $payee) : null;
        $live = $key->isLive();

        return ApiPaymentRequest::create([
            'public_id' => self::newReference($live),
            'token' => Str::random(40),
            'api_key_id' => $key->id, 'user_id' => $payee->id, 'environment' => $key->environment,
            'amount' => $cents,
            'reference' => filled($input['reference'] ?? null) ? Str::limit(strip_tags((string) $input['reference']), 140, '') : null,
            'payer_user_id' => $payerUser?->id, 'payer_phone' => $payerPhone,
            'channels' => $channels,
            'metadata' => is_array($input['metadata'] ?? null) ? array_slice($input['metadata'], 0, 20, true) : null,
            'splits' => $splits,
            'status' => 'open',
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * The one reference for a payment, whatever it is paid with (Sharet wallet, M-PESA, later Airtel), in the M-PESA-like
     * Sharet format (SHUK7Q2M4D; SHTEST… in the sandbox, see SharetReference). It is the request's API id, and every order that pays it carries the same reference
     * (receipts, SMS, history, transaction_id). Requests made before this keep their old pr_ id.
     */
    public static function newReference(bool $live): string
    {
        return \App\Support\SharetReference::next($live);
    }

    /** Tag an order that pays $r with the request's reference (only for SH references; old pr_ requests keep the order's own). */
    public static function tag(RampOrder $order, ApiPaymentRequest $r): RampOrder
    {
        if (str_starts_with($r->public_id, 'SH')) {
            $order->update(['destination' => array_merge($order->destination ?? [], ['ref' => $r->public_id])]);
        }

        return $order;
    }

    public static function present(ApiPaymentRequest $r): array
    {
        return [
            'id' => $r->public_id,
            'object' => 'payment_request',
            'environment' => $r->environment,
            'status' => $r->status,
            'amount' => PaymentInput::amount($r->amount),
            'currency' => config('ledger.currency'),
            'reference' => $r->reference,
            'payee' => ['sharet_id' => $r->payee->sharet_id, 'handle' => '@'.Handle::for($r->payee)],
            'payer' => $r->payer ? ['sharet_id' => $r->payer->sharet_id, 'handle' => '@'.Handle::for($r->payer)] : ($r->payer_phone ? ['phone' => $r->payer_phone] : null),
            'channels' => $r->channels,
            'pay_url' => route('pay-request.show', $r->token),
            'paid_via' => $r->paid_via,
            // Sharet's transaction ID for the payment (the business's reference); the M-PESA receipt and number stay with Sharet
            'transaction_id' => $r->status === 'paid' ? ($r->order?->sharetReference() ?? null) : null,
            'paid_at' => $r->paid_at?->toIso8601String(),
            'expires_at' => $r->expires_at->toIso8601String(),
            'metadata' => $r->metadata,
            'splits' => $r->splits ? array_map(fn ($l) => ['sharet_id' => $l['sharet_id'], 'amount' => PaymentInput::amount($l['amount'])], $r->splits) : null,
            'split_status' => $r->split_status,
            'split_failure' => $r->split_failure,
            'created_at' => $r->created_at?->toIso8601String(),
        ];
    }

    /** Sharet wallet: the signed-in payer approves with their PIN; a free wallet-to-wallet transfer pays the request. */
    public function payWithSharet(ApiPaymentRequest $r, User $payer, ?string $pin): ApiPaymentRequest
    {
        $this->assertPayable($r, 'sharet');
        if ($r->payer_user_id && $r->payer_user_id !== $payer->id) {
            throw ValidationException::withMessages(['pay' => 'This request is for another Sharet account.']);
        }
        if ($payer->is($r->payee)) {
            throw ValidationException::withMessages(['pay' => 'You can\'t pay your own request.']);
        }
        TransactionPin::check($payer, $pin);

        if ($r->environment === 'test') {
            return $this->markPaid($r, 'sandbox', null);
        }

        return DB::transaction(function () use ($r, $payer) {
            $locked = ApiPaymentRequest::whereKey($r->id)->lockForUpdate()->first();
            $this->assertPayable($locked, 'sharet');
            $order = self::tag($this->orders->transfer($payer, Handle::for($r->payee), PaymentInput::amount($r->amount), $r->reference ?: 'Payment request'), $r);

            return $this->markPaid($locked, 'sharet', $order);
        });
    }

    /** M-PESA: prompt the payer's phone; the collection credits the business's wallet. Paid when the order is. */
    public function payWithMpesa(ApiPaymentRequest $r, string $phone, ?User $signedIn = null): ApiPaymentRequest
    {
        $this->assertPayable($r, 'mpesa');
        $phone = Destination::phone($phone, 'phone');
        if ($r->environment === 'test') {
            return $this->markPaid($r, 'sandbox', null);
        }
        if ($r->order && ! $r->order->final()) {
            return $r;   // a prompt is already on its way
        }
        // A live request is paid with real money only: never let the simulator stand in for M-PESA (it "succeeds"
        // without a Safaricom prompt, and the business would mark an unpaid invoice paid).
        if (app()->isProduction() && app(\App\Integrations\Rails::class)->collecting($r->payee) === 'simulator') {
            throw ValidationException::withMessages(['phone' => 'M-PESA payments aren\'t switched on for this business yet. Pay from a Sharet wallet, or ask the business for another way to pay.']);
        }
        $order = self::tag($this->orders->topUp($r->payee, PaymentInput::amount($r->amount), $phone), $r);   // before confirm: the prompt SMS quotes it
        if ($signedIn && ! $signedIn->is($r->payee)) {
            $order->update(['destination' => ($order->destination ?? []) + ['paid_by' => $signedIn->id]]);   // shows in their history too
        }
        $order = $this->orders->confirm($order->fresh());
        $r->update(['ramp_order_id' => $order->id]);   // the paying number stays on the order (Sharet-internal), not on the request
        $this->orderMoved($order->fresh());

        return $r->fresh();
    }

    /**
     * Signed in, top up by M-PESA, then pay (paid_via "mpesa_via_wallet"): the prompt credits the PAYER's own wallet,
     * and once that top-up is paid the same amount moves on to the payee as a wallet transfer (see orderMoved). The
     * payer sees both in their history. If the onward transfer fails, the money stays in their wallet, the request
     * stays open, and the page says so. The M-PESA PIN on their phone is the approval (as for any M-PESA payment).
     */
    public function topUpAndPay(ApiPaymentRequest $r, User $payer, string $phone): RampOrder
    {
        $this->assertPayable($r, 'mpesa');
        $this->assertPayer($r, $payer);
        $phone = Destination::phone($phone, 'phone');
        if ($pending = self::topUpFor($r, $payer, true)) {
            return $pending;   // a prompt is already on its way
        }
        if ($r->environment === 'test') {
            $this->markPaid($r, 'sandbox', null);

            return new RampOrder;
        }
        if (app()->isProduction() && app(\App\Integrations\Rails::class)->collecting($payer) === 'simulator') {
            throw ValidationException::withMessages(['phone' => 'M-PESA top-ups aren\'t switched on for your account yet. Pay from your wallet, or with M-PESA without signing in.']);
        }
        $order = self::tag($this->orders->topUp($payer, PaymentInput::amount($r->amount), $phone), $r);
        $order->update(['destination' => ($order->destination ?? []) + ['pay_request' => $r->id], 'note' => 'Top-up to pay '.($r->reference ?: 'a payment request')]);
        $order = $this->orders->confirm($order->fresh());
        $this->orderMoved($order->fresh());

        return $order->fresh();
    }

    /**
     * Claim on join: once a person's M-PESA number is verified (basic KYC or above), earlier M-PESA payments they made
     * from it on pay pages show in their history (destination.paid_by). Display only; never the ledger. Returns how many.
     */
    public static function claimFor(User $user): int
    {
        if ($user->is_business_wallet || in_array($user->kyc_level ?: 'none', ['none'], true) || blank($user->phone)) {
            return 0;
        }
        try {
            $phone = Destination::phone((string) $user->phone);
        } catch (ValidationException) {
            return 0;
        }
        $n = 0;
        RampOrder::whereIn('id', ApiPaymentRequest::whereNotNull('ramp_order_id')->select('ramp_order_id'))
            ->where('payer_phone', $phone)->where('destination_type', 'wallet')->where('user_id', '!=', $user->id)
            ->where('status', 'paid')->where('rail', '!=', 'simulator')   // only money that really moved
            ->whereNull('destination->paid_by')->get()
            ->each(function (RampOrder $o) use ($user, &$n) {
                $o->update(['destination' => $o->destination + ['paid_by' => $user->id]]);
                $n++;
            });

        return $n;
    }

    /** The payer's latest top-up for this request ($pending: only one still waiting for M-PESA). */
    public static function topUpFor(ApiPaymentRequest $r, User $payer, bool $pending = false): ?RampOrder
    {
        $o = RampOrder::where('user_id', $payer->id)->where('destination->pay_request', $r->id)->latest('id')->first();

        return $o && (! $pending || ! $o->final()) ? $o : null;
    }

    /** Sandbox only: the page's "Pay" / "Fail" buttons. */
    public function sandbox(ApiPaymentRequest $r, bool $pay): ApiPaymentRequest
    {
        abort_unless($r->environment === 'test', 404);
        $this->assertPayable($r, null);

        return $pay ? $this->markPaid($r, 'sandbox', null) : tap($r)->update(['status' => 'cancelled']);
    }

    /** The M-PESA order behind a request moved: paid → the request is paid. */
    public function orderMoved(RampOrder $order): void
    {
        if ($order->status === 'paid' && ($rid = $order->destination['pay_request'] ?? null) && ! isset($order->destination['onward'])) {
            $this->payOnward($order, (int) $rid);

            return;
        }
        $r = ApiPaymentRequest::where('ramp_order_id', $order->id)->first();
        if ($r && $r->status === 'open' && $order->status === 'paid') {
            $this->markPaid($r, 'mpesa', $order);
        }
    }

    /** A top-up for a request was paid: move the amount on to the payee, once. Failure leaves the money in the wallet. */
    private function payOnward(RampOrder $topUp, int $requestId): void
    {
        DB::transaction(function () use ($topUp, $requestId) {
            $o = RampOrder::whereKey($topUp->id)->lockForUpdate()->first();
            if (isset($o->destination['onward'])) {
                return;   // already handled by another call
            }
            $r = ApiPaymentRequest::whereKey($requestId)->lockForUpdate()->first();
            try {
                $this->assertPayable($r, null);
                $payer = $o->user;
                $this->assertPayer($r, $payer);
                $transfer = self::tag(DB::transaction(fn () => $this->orders->transfer($payer, Handle::for($r->payee), PaymentInput::amount($r->amount), $r->reference ?: 'Payment request')), $r);
                $this->markPaid($r, 'mpesa_via_wallet', $transfer);
                $o->update(['destination' => $o->destination + ['onward' => $transfer->id]]);
            } catch (\Throwable $e) {
                $why = $e instanceof ValidationException ? collect($e->errors())->flatten()->first() : 'Something went wrong on our side.';
                $o->update(['destination' => $o->destination + ['onward' => 'failed', 'onward_failure' => $why]]);
                $e instanceof ValidationException || report($e);
            }
        });
    }

    private function assertPayer(ApiPaymentRequest $r, User $payer): void
    {
        if ($r->payer_user_id && $r->payer_user_id !== $payer->id) {
            throw ValidationException::withMessages(['pay' => 'This request is for another Sharet account.']);
        }
        if ($payer->is($r->payee)) {
            throw ValidationException::withMessages(['pay' => 'You can\'t pay your own request.']);
        }
    }

    public function cancel(ApiPaymentRequest $r): ApiPaymentRequest
    {
        if ($r->status === 'open') {
            $r->update(['status' => 'cancelled']);
        }

        return $r->fresh();
    }

    /** Close open requests past their time and tell the business. */
    public function expireDue(): int
    {
        $n = 0;
        ApiPaymentRequest::where('status', 'open')->where('expires_at', '<', now())->orderBy('id')->each(function (ApiPaymentRequest $r) use (&$n) {
            if ($r->order && ! $r->order->final()) {
                return;   // an M-PESA prompt is still being answered
            }
            $r->update(['status' => 'expired']);
            $this->webhooks->fire($r->payee, $r->environment, 'payment_request.expired', self::present($r));
            $n++;
        });

        return $n;
    }

    private function markPaid(ApiPaymentRequest $r, string $via, ?RampOrder $order): ApiPaymentRequest
    {
        $r->update(['status' => 'paid', 'paid_via' => $via, 'paid_at' => now(), 'ramp_order_id' => $order?->id ?? $r->ramp_order_id]);
        $this->webhooks->fire($r->payee, $r->environment, 'payment_request.paid', self::present($r->fresh()));
        if ($r->splits) {
            $this->settle($r->fresh());
        }

        return $r->fresh();
    }

    /**
     * Pay every split leg from the business's wallet to the recipients' Sharet IDs, all or nothing: the legs run in one
     * database transaction, so if any leg is refused none of them happen and the money stays in the business's wallet
     * (settlement.failed, retry with POST /payment-requests/{id}/settle). Free and instant (Sharet ↔ Sharet).
     */
    public function settle(ApiPaymentRequest $r): ApiPaymentRequest
    {
        if (! $r->splits || $r->status !== 'paid' || $r->split_status === 'settled') {
            return $r;
        }
        if ($r->environment === 'test') {
            $r->update(['split_status' => 'settled', 'split_failure' => null]);
        } else {
            try {
                DB::transaction(function () use ($r) {
                    foreach ($r->splits as $leg) {
                        $to = \App\Models\User::findOrFail($leg['user_id']);
                        $this->orders->transfer($r->payee, Handle::for($to), PaymentInput::amount($leg['amount']), Str::limit('Split · '.($r->reference ?: $r->public_id), 140, ''));
                    }
                });
                $r->update(['split_status' => 'settled', 'split_failure' => null]);
            } catch (ValidationException $e) {
                $r->update(['split_status' => 'failed', 'split_failure' => (string) collect($e->errors())->flatten()->first()]);
            }
        }
        $r = $r->fresh();
        $this->webhooks->fire($r->payee, $r->environment, $r->split_status === 'settled' ? 'settlement.completed' : 'settlement.failed',
            ['payment_request' => $r->public_id, 'legs' => self::present($r)['splits'], 'failure' => $r->split_failure]);

        return $r;
    }

    private function assertPayable(?ApiPaymentRequest $r, ?string $channel): void
    {
        if (! $r || ! $r->isOpen()) {
            throw ValidationException::withMessages(['pay' => $r?->status === 'paid' ? 'This request is already paid.' : 'This request is no longer open.']);
        }
        if ($channel && ! in_array($channel, $r->channels, true)) {
            throw ValidationException::withMessages(['pay' => 'This request can\'t be paid that way.']);
        }
    }
}

<?php

namespace App\Api;

use App\Models\ApiKey;
use App\Models\ApiPayment;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\Pricing;
use App\Ramp\RampOrders;
use App\Support\Handle;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * POST /api/v1/payments (docs/v2/INTEGRATION.md §4, §10).
 *  - Sandbox keys (sk_test_) never touch the ledger: the payment is simulated, recorded in api_payments, and its
 *    webhook fires at once. Test number 0700 000 002 always fails, every other destination succeeds.
 *  - Live keys (sk_live_) need a verified business (KYB) and go through the same order engine as the app: the
 *    owner's wallet pays, limits apply, Sharet ↔ Sharet is free and instant, outside payouts are charged.
 */
final class Payments
{
    public const SANDBOX_FAIL_PHONE = '254700000002';

    public function __construct(private RampOrders $orders, private Webhooks $webhooks) {}

    public function create(ApiKey $key, User $user, array $input): ApiPayment
    {
        $in = PaymentInput::read($input, $user);
        $reference = filled($input['reference'] ?? null) ? Str::limit(strip_tags((string) $input['reference']), 140, '') : null;

        return $key->isLive() ? $this->live($key, $user, $in, $input, $reference) : $this->sandbox($key, $user, $in, $reference);
    }

    /** One item of a batch settlement: paid like any payment; a refusal is recorded as a failed item, never thrown. */
    public function createInSettlement(ApiKey $key, User $user, array $input, int $settlementId): ApiPayment
    {
        try {
            $p = $this->create($key, $user, $input);
            $p->update(['api_settlement_id' => $settlementId]);

            return $p->fresh();
        } catch (ApiError $e) {
            return ApiPayment::create([
                'public_id' => ($key->isLive() ? 'pay_' : 'pay_test_').Str::lower(Str::random(18)),
                'api_key_id' => $key->id, 'user_id' => $user->id, 'environment' => $key->environment, 'api_settlement_id' => $settlementId,
                'status' => 'failed', 'amount' => 0, 'fee' => 0, 'total' => 0,
                'to' => (array) ($input['to'] ?? []), 'reference' => isset($input['reference']) ? Str::limit((string) $input['reference'], 140, '') : null,
                'failure' => $e->detail,
            ]);
        }
    }

    /** Status of an API payment, refreshed from its order (live) so a GET is never stale. */
    public function current(ApiPayment $p): ApiPayment
    {
        if ($p->order) {
            $this->webhooks->orderMoved($p->order);
        }

        return $p->fresh();
    }

    public static function present(ApiPayment $p): array
    {
        return [
            'id' => $p->public_id,
            'object' => 'payment',
            'environment' => $p->environment,
            'status' => $p->status,
            'amount' => PaymentInput::amount($p->amount),
            'fee' => PaymentInput::amount($p->fee),
            'total' => PaymentInput::amount($p->total),
            'currency' => config('ledger.currency'),
            'to' => $p->to,
            'reference' => $p->reference,
            'failure' => $p->failure,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }

    /** paid / failed / refunded, or paying while the rail works. */
    public static function statusOf(RampOrder $o): string
    {
        return match ($o->status) {
            'paid' => 'paid',
            'failed', 'expired', 'cancelled' => 'failed',
            'refunded' => 'refunded',
            default => 'paying',
        };
    }

    private function sandbox(ApiKey $key, User $user, array $in, ?string $reference): ApiPayment
    {
        $fee = $in['type'] === 'sharet' ? ['fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $in['cents']] : Pricing::quote($in['cents']);
        $fails = ($in['destination']['phone'] ?? null) === self::SANDBOX_FAIL_PHONE;

        $p = ApiPayment::create([
            'public_id' => 'pay_test_'.Str::lower(Str::random(18)),
            'api_key_id' => $key->id, 'user_id' => $user->id, 'environment' => 'test',
            'status' => $fails ? 'failed' : 'paid',
            'amount' => $in['cents'], 'fee' => $fee['fee'] + $fee['excise'] + $fee['rounding'], 'total' => $fee['total'],
            'to' => ['type' => $in['type']] + $in['destination'], 'reference' => $reference,
            'failure' => $fails ? 'The test number 0700 000 002 always fails.' : null,
        ]);
        $this->webhooks->fire($user, 'test', 'payment.'.$p->status, self::present($p));

        return $p;
    }

    private function live(ApiKey $key, User $user, array $in, array $input, ?string $reference): ApiPayment
    {
        if (! $key->company?->verified_at) {
            throw new ApiError(403, 'business_not_verified', 'Your business isn\'t verified yet', 'Live payments need a verified business (registration, KRA PIN, directors, settlement account). Use a sk_test_ key meanwhile.');
        }

        try {
            if ($in['type'] === 'sharet') {
                $order = $this->orders->transfer($user, Handle::for($in['recipient']), PaymentInput::amount($in['cents']), $reference);
            } else {
                $quote = $this->orders->quote($user, PaymentInput::rampType($in['type']), (array) $input['to'], PaymentInput::amount($in['cents']), $reference, 'wallet');
                $order = $this->orders->confirm($quote);
            }
        } catch (ValidationException $e) {
            throw new ApiError(422, 'payment_refused', 'The payment was refused', (string) collect($e->errors())->flatten()->first(), ['errors' => $e->errors()]);
        }

        $p = ApiPayment::create([
            'public_id' => 'pay_'.Str::lower(Str::random(22)),
            'api_key_id' => $key->id, 'user_id' => $user->id, 'environment' => 'live', 'ramp_order_id' => $order->id,
            'status' => 'paying',
            'amount' => $order->amount, 'fee' => $order->fee + $order->excise + $order->rounding, 'total' => $order->total,
            'to' => ['type' => $in['type']] + $in['destination'], 'reference' => $reference,
        ]);
        $this->webhooks->orderMoved($order->fresh());

        return $p->fresh();
    }
}

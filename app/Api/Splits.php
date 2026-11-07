<?php

namespace App\Api;

use App\Models\User;

/**
 * Split settlement for payment requests (docs/v2/INTEGRATION.md §5).
 * Input: [{ "to": {"sharet_id"|"handle"} | "self", "percent": 90 } | { …, "amount": "31500.00" }, …]
 * Rules: legs go to Sharet accounts only (recipients send outside themselves, charged); percents and amounts must add
 * up to the whole; legs are whole shillings, any remainder stays with the business ("self"). Resolved to Sharet IDs
 * when the request is created, so renaming an @name later never redirects money.
 */
final class Splits
{
    public const MAX_LEGS = 10;

    /** @return list<array{sharet_id: string, user_id: int, amount: int}> the legs to pay out (self excluded) */
    public static function resolve(array $input, int $total, User $payee): array
    {
        if (count($input) < 1 || count($input) > self::MAX_LEGS) {
            throw new ApiError(422, 'invalid_splits', 'Check the splits', 'Send 1 to '.self::MAX_LEGS.' legs.');
        }
        $legs = [];
        $assigned = 0;
        $seen = [];
        $hasSelf = false;
        $percentSum = 0;
        foreach (array_values($input) as $i => $leg) {
            $to = $leg['to'] ?? null;
            if ($to === 'self') {
                $hasSelf = true;
                $user = null;
            } else {
                $to = (array) $to;
                if (isset($to['type']) && $to['type'] !== 'sharet') {
                    throw new ApiError(422, 'outside_split_not_available', 'Splits go to Sharet accounts', "Leg $i: split to a Sharet ID or @name; recipients can send to M-PESA or a bank themselves.");
                }
                $user = PaymentInput::account($to['sharet_id'] ?? null, $to['handle'] ?? null)
                    ?? throw new ApiError(404, 'account_not_found', 'No such Sharet account', "Leg $i: check the @name or Sharet ID.");
                if ($user->is($payee)) {
                    throw new ApiError(422, 'invalid_splits', 'Check the splits', "Leg $i: use \"self\" for your own share.");
                }
                if (isset($seen[$user->id])) {
                    throw new ApiError(422, 'invalid_splits', 'Check the splits', "Leg $i: {$user->sharet_id} appears twice.");
                }
                $seen[$user->id] = true;
            }

            if (isset($leg['percent'])) {
                $p = (float) $leg['percent'];
                if ($p <= 0 || $p > 100) {
                    throw new ApiError(422, 'invalid_splits', 'Check the splits', "Leg $i: percent must be above 0 and at most 100.");
                }
                $percentSum += $p;
                $cents = intdiv((int) round($total * $p / 100), 100) * 100;   // whole shillings, rounded down
            } elseif (isset($leg['amount'])) {
                $cents = PaymentInput::read(['amount' => (string) $leg['amount'], 'to' => ['sharet_id' => $payee->sharet_id]], new User)['cents'];
            } else {
                throw new ApiError(422, 'invalid_splits', 'Check the splits', "Leg $i: give a percent or an amount.");
            }
            $assigned += $cents;
            if ($user) {
                $legs[] = ['sharet_id' => $user->sharet_id, 'user_id' => $user->id, 'amount' => $cents];
            }
        }

        if ($percentSum > 100.0001 || $assigned > $total) {
            throw new ApiError(422, 'invalid_splits', 'Check the splits', 'The legs add up to more than the payment.');
        }
        $rest = $total - $assigned;
        if ($rest > 0 && ! $hasSelf && abs($percentSum - 100) > 0.0001) {
            throw new ApiError(422, 'invalid_splits', 'Check the splits', 'The legs must add up to the whole payment, or include a "self" leg for the rest.');
        }

        return $legs;   // whatever isn't in a leg (the "self" share and rounding) stays with the business
    }
}

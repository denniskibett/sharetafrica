<?php

namespace App\Ramp;

use App\Models\User;

/**
 * Sharet's markup on a money movement (config pricing.sharet_markup): 0.5% of the amount, at least KES 1, nothing up to
 * KES 100. A business wallet gets its volume discount (business tariff Table 2). Excise is set aside from it.
 */
final class Markup
{
    public static function cents(int $amount, ?User $user = null): int
    {
        $c = config('pricing.sharet_markup');
        if ($amount <= $c['free_up_to']) {
            return 0;
        }
        $m = max((int) $c['min'], intdiv($amount * (int) $c['bp'] + 5_000, 10_000));
        if ($user?->is_business_wallet) {
            $m -= intdiv($m * app(\App\Api\Tariffs::class)->discountBp($user), 10_000);
        }

        return $m;
    }

    /** The excise duty inside a markup (15% of it, config pricing.excise_bp). */
    public static function excise(int $markup): int
    {
        return intdiv($markup * (int) config('pricing.excise_bp') + 5_000, 10_000);
    }
}

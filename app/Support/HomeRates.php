<?php

namespace App\Support;

use App\Fx\FxException;
use App\Fx\Rates;
use App\Models\User;

/** The line under "Habari, Juma": the person's country and today's customer rates (Safaricom's rate less our margin). */
class HomeRates
{
    public const COUNTRIES = ['KE' => 'Kenya', 'UG' => 'Uganda', 'TZ' => 'Tanzania', 'RW' => 'Rwanda'];

    public static function country(User $user): array
    {
        $c = strtoupper((string) $user->country);
        $code = isset(self::COUNTRIES[$c]) ? $c : (array_search(ucfirst(strtolower((string) $user->country)), self::COUNTRIES, true) ?: 'KE');

        return [$code, self::COUNTRIES[$code]];
    }

    /** "1 KES = USD 0.0076" (customer rate), or null before treasury records today's rate. */
    public static function line(): ?string
    {
        $parts = [];
        foreach (['USD' => 4] as $to => $dp) {
            try {
                $q = app(Rates::class)->quote('KES', $to, 100);   // KES 1.00
                $parts[] = $to.' '.number_format((float) $q['customer_rate'], $dp);
            } catch (FxException) {
            }
        }

        return $parts ? '1 KES = '.implode(' · ', $parts) : null;
    }
}

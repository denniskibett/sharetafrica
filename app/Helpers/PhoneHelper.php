<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Safaricom mobile prefixes in Kenyan E.164 (254...) form.
     * Includes both legacy 07XX ranges and the newer 01XX ranges.
     */
    public const SAFARICOM_PREFIXES = [
        '25470', '25471', '25472', '25474', '25475', '25476', '25479',
        '25410', '25411', '25412', '25413', '25414', '25415', '25416',
        '25417', '25418', '25419',
    ];

    /**
     * Normalize any Kenyan phone number to 254XXXXXXXXX (12 digits).
     * Returns null if the number cannot be normalized.
     */
    public static function format(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        // 07XXXXXXXX or 01XXXXXXXX → 2547XXXXXXXX / 2541XXXXXXXX
        if (str_starts_with($digits, '0')) {
            $digits = '254' . substr($digits, 1);
        }
        // 7XXXXXXXX or 1XXXXXXXX (9 digits) → 254XXXXXXXXX
        elseif (strlen($digits) === 9 && preg_match('/^[71]/', $digits)) {
            $digits = '254' . $digits;
        }

        // Final validation: must be 2547XXXXXXXX or 2541XXXXXXXX
        if (strlen($digits) !== 12 || !str_starts_with($digits, '254')) {
            return null;
        }

        return $digits;
    }

    /**
     * Check if a phone number belongs to Safaricom (Kenya).
     */
    public static function isSafaricom(?string $phone): bool
    {
        $normalized = self::format($phone);

        if (!$normalized) {
            return false;
        }

        foreach (self::SAFARICOM_PREFIXES as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
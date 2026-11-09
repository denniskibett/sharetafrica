<?php

namespace App\Support;

use App\Models\ApiPaymentRequest;
use App\Models\RampOrder;

/**
 * One reference format across the Sharet ecosystem, read like an M-PESA receipt (UK96O15681: U = 2026, K = November):
 *
 *   SH + year letter + month letter + 6 characters      e.g. SHUK7Q2M4D   (live)
 *   SHTEST + the same                                   e.g. SHTESTUK7Q2M4D (sandbox)
 *
 * Year letter: A = 2006 … U = 2026, V = 2027 (as M-PESA). Month letter: A = January … L = December, so each month's
 * references start with the next letter. Unique across payment requests and orders: one payment, one reference,
 * whatever it is paid with (Sharet wallet, M-PESA, later Airtel). Older SH + 8 hex references stay valid.
 */
class SharetReference
{
    public const PATTERN = '/^SH(TEST)?[A-Z][A-L][0-9A-Z]{6}$/';

    private const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function next(bool $live = true): string
    {
        $now = now('Africa/Nairobi');
        $head = ($live ? 'SH' : 'SHTEST').chr(ord('A') + $now->year - 2006).chr(ord('A') + $now->month - 1);
        do {
            $tail = '';
            for ($i = 0; $i < 6; $i++) {
                $tail .= self::CHARS[random_int(0, 35)];
            }
            $ref = $head.$tail;
        } while (ApiPaymentRequest::where('public_id', $ref)->exists() || RampOrder::where('destination->ref', $ref)->exists());

        return $ref;
    }

    public static function valid(string $ref): bool
    {
        return (bool) preg_match(self::PATTERN, $ref);
    }
}

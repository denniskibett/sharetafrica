<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * The Sharet ID: a fixed account number for every person and business, e.g. "SH-4K7Q2M". It never changes and is never
 * reused. Settlements, webhooks and statements are tied to it; the @name is only an alias that resolves to it
 * (docs/v2/INTEGRATION.md §1). Six characters from an alphabet without look-alikes (no 0/O, 1/I/L, U).
 */
final class SharetId
{
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTVWXYZ';

    public const PATTERN = '/^SH-[23456789ABCDEFGHJKMNPQRSTVWXYZ]{6}$/';

    /** A new ID that no user or company has yet. */
    public static function generate(): string
    {
        do {
            $id = 'SH-';
            for ($i = 0; $i < 6; $i++) {
                $id .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (DB::table('users')->where('sharet_id', $id)->exists() || DB::table('companies')->where('sharet_id', $id)->exists());

        return $id;
    }

    /** "sh-4k7q2m " → "SH-4K7Q2M", or null if it can't be a Sharet ID. */
    public static function normalise(?string $raw): ?string
    {
        $id = 'SH-'.preg_replace('/^SH-?/', '', strtoupper(preg_replace('/\s+/', '', (string) $raw)));

        return preg_match(self::PATTERN, $id) ? $id : null;
    }
}

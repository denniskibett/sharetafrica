<?php

namespace App\Ledger;

/**
 * Money as whole cents (integers). Never floats: 0.1 + 0.2 is not 0.3 in floating point, and a ledger must be exact.
 * Shown with 2 decimal places ("1,000.00"). Percentages are basis points (1.5% = 150) and round half away from zero, once.
 */
final class Money
{
    /** "1,000", "1,000.5", "1000.50", "-20" → cents. Anything else (floats, 3 decimals, letters) is refused. */
    public static function cents(string|int $amount): int
    {
        if (is_int($amount)) {
            return self::guard($amount * 100);
        }
        $s = str_replace([' ', "\u{00A0}"], '', trim($amount));
        if (! preg_match('/^(-)?(\d{1,3}(?:,\d{3})+|\d+)(?:\.(\d{1,2}))?$/', $s, $m)) {
            throw LedgerException::make('invalid_amount', "Not an amount with at most 2 decimal places: {$amount}");
        }
        $whole = (int) str_replace(',', '', $m[2]);
        $fraction = (int) str_pad($m[3] ?? '0', 2, '0');

        return self::guard(($m[1] === '-' ? -1 : 1) * ($whole * 100 + $fraction));
    }

    /** cents → "1,000.00" (or "-1,000.00"). */
    public static function format(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs = abs($cents);

        return $sign.number_format(intdiv($abs, 100)).'.'.str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }

    /** cents → "1000.00" (no thousands separator; for APIs and CSV). */
    public static function plain(int $cents): string
    {
        return str_replace(',', '', self::format($cents));
    }

    /** A share of an amount: percent(98500, 150) = 1.5% of 985.00 = 14.78 → 1478. */
    public static function percent(int $cents, int $basisPoints): int
    {
        return self::divide($cents * $basisPoints, 10000);
    }

    /** Integer division that rounds half away from zero (the one rounding rule used everywhere). */
    public static function divide(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            throw LedgerException::make('invalid_amount', 'Divide by a positive whole number only.');
        }
        $q = intdiv(abs($numerator), $denominator);
        if ((abs($numerator) % $denominator) * 2 >= $denominator) {
            $q++;
        }

        return $numerator < 0 ? -$q : $q;
    }

    /** Whole shillings and what is left over: rails like M-PESA move whole shillings only. */
    public static function wholeShillings(int $cents): array
    {
        $whole = intdiv($cents, 100) * 100;

        return [$whole, $cents - $whole];
    }

    private static function guard(int $cents): int
    {
        // well inside BIGINT, and far beyond any real amount: refuses overflow from bad input
        if (abs($cents) > 1_000_000_000_000_000) {
            throw LedgerException::make('invalid_amount', 'Amount out of range.');
        }

        return $cents;
    }
}

<?php

namespace App\Ramp;

use Illuminate\Validation\ValidationException;

/** Checks and normalises where a payment goes: a phone, a paybill + account, a till, or a bank account. */
final class Destination
{
    public const TYPES = ['phone', 'paybill', 'till', 'bank'];

    /** @return array{0: array, 1: string} [the stored destination, the "party" the rail pays] */
    public static function normalise(string $type, array $in): array
    {
        return match ($type) {
            'phone' => [['phone' => $p = self::phone($in['phone'] ?? '', 'phone')], $p],
            'paybill' => [['paybill' => $b = self::shortcode($in['paybill'] ?? '', 'paybill'), 'account' => self::account($in['account'] ?? '')], $b],
            'till' => [['till' => $t = self::shortcode($in['till'] ?? '', 'till')], $t],
            'bank' => self::bank($in),
            default => throw ValidationException::withMessages(['type' => 'Choose phone, paybill, till or bank.']),
        };
    }

    /** 0712 345 678, +254 712 345 678, 254712345678 → 254712345678 (Safaricom 07xx/01xx). */
    public static function phone(string $raw, string $field = 'phone'): string
    {
        $d = preg_replace('/\D/', '', $raw);
        if (preg_match('/^0([17]\d{8})$/', $d, $m)) {
            $d = '254'.$m[1];
        } elseif (preg_match('/^([17]\d{8})$/', $d, $m)) {
            $d = '254'.$m[1];
        }
        if (! preg_match('/^254[17]\d{8}$/', $d)) {
            throw ValidationException::withMessages([$field => 'Enter a Kenyan mobile number, e.g. 0712 345 678.']);
        }

        return $d;
    }

    private static function shortcode(string $raw, string $field): string
    {
        $d = preg_replace('/\D/', '', $raw);
        if (! preg_match('/^\d{5,7}$/', $d)) {
            throw ValidationException::withMessages([$field => 'Enter the 5 to 7 digit business number.']);
        }

        return $d;
    }

    private static function account(string $raw): string
    {
        $a = trim($raw);
        if (! preg_match('/^[A-Za-z0-9 \-]{1,20}$/', $a)) {
            throw ValidationException::withMessages(['account' => 'Enter the account number (letters and numbers, up to 20).']);
        }

        return $a;
    }

    private static function bank(array $in): array
    {
        $bank = (string) ($in['bank'] ?? '');
        if (! array_key_exists($bank, config('pricing.banks'))) {
            throw ValidationException::withMessages(['bank' => 'Choose a bank.']);
        }
        $account = preg_replace('/\D/', '', (string) ($in['account'] ?? ''));
        if (! preg_match('/^\d{6,20}$/', $account)) {
            throw ValidationException::withMessages(['account' => 'Enter the bank account number (6 to 20 digits).']);
        }

        return [['bank' => $bank, 'account' => $account, 'via' => 'pesalink'], $account];
    }
}

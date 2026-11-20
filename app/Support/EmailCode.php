<?php

namespace App\Support;

use App\Mail\SharetMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * A 6-digit code emailed at sign-up, so only people who own the address can open an account.
 *
 * OFF until EMAIL_CODE_ON_SIGNUP=true. Switch it on only after `php artisan sharet:mail-test you@gmail.com`
 * lands in the inbox (not spam) and SPF, DKIM and DMARC all pass. Only accounts created while it is on are
 * asked for a code (users.email_code_required_at), so nobody already signed up is locked out.
 *
 * The code is kept hashed in the cache for 15 minutes; 5 wrong tries and a new one must be sent.
 */
class EmailCode
{
    public const TTL_MINUTES = 15;

    public const MAX_TRIES = 5;

    public static function enabled(): bool
    {
        return (bool) config('security.email_code_on_signup');
    }

    public static function pending(User $user): bool
    {
        return $user->email_code_required_at !== null && $user->email_verified_at === null;
    }

    public static function require(User $user): void
    {
        $user->forceFill(['email_code_required_at' => now()])->saveQuietly();
        self::send($user);
    }

    public static function send(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put(self::key($user), ['hash' => hash_hmac('sha256', $code, config('app.key')), 'tries' => 0], now()->addMinutes(self::TTL_MINUTES));
        Outbox::later($user->email, SharetMail::emailCode($code));
    }

    /** true = verified; otherwise the message to show. */
    public static function check(User $user, string $code): true|string
    {
        $entry = Cache::get(self::key($user));
        if (! $entry) {
            return 'That code has expired. Send a new one.';
        }
        if ($entry['tries'] >= self::MAX_TRIES) {
            return 'Too many wrong codes. Send a new one.';
        }
        if (! hash_equals($entry['hash'], hash_hmac('sha256', preg_replace('/\D/', '', $code), config('app.key')))) {
            $entry['tries']++;
            Cache::put(self::key($user), $entry, now()->addMinutes(self::TTL_MINUTES));

            return 'That code is not right. '.(self::MAX_TRIES - $entry['tries']).' tries left.';
        }
        Cache::forget(self::key($user));
        $user->forceFill(['email_verified_at' => now()])->saveQuietly();

        return true;
    }

    protected static function key(User $user): string
    {
        return "email-code:{$user->id}";
    }
}

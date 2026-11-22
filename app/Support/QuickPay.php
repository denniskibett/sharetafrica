<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * How a payment from the app is approved: the Sharet PIN, or the phone's own lock the app checked (fingerprint, face or
 * passcode). The phone lock counts when this phone has it on (set at sign-in with the PIN, or in Me with the PIN), up to
 * mobile.phone_lock_daily_max a day; or, on older app versions, quick pay within its limit (three times the limit a day).
 * Past either, the PIN.
 */
class QuickPay
{
    public const MAX = 500000;   // cents: KES 5,000, the highest limit a phone can have

    public static function approve(Request $request, User $user, int $cents): void
    {
        $limit = self::limit($request);
        if ($request->boolean('biometric') && ! $request->filled('pin') && self::phoneLock($request)) {
            $key = 'phonelock:'.$request->attributes->get('device_token_id').':'.now('Africa/Nairobi')->toDateString();
            if ((int) Cache::get($key, 0) + $cents > config('mobile.phone_lock_daily_max')) {
                throw ValidationException::withMessages(['pin' => 'Use your Sharet PIN for this payment.']);
            }
            Cache::put($key, (int) Cache::get($key, 0) + $cents, now()->addDay());

            return;
        }
        if ($request->boolean('biometric') && ! $request->filled('pin')) {
            $key = 'quickpay:'.$request->attributes->get('device_token_id').':'.now('Africa/Nairobi')->toDateString();
            if (! $limit || $cents > $limit || (int) Cache::get($key, 0) + $cents > 3 * $limit) {
                throw ValidationException::withMessages(['pin' => 'Use your Sharet PIN for this payment.']);
            }
            Cache::put($key, (int) Cache::get($key, 0) + $cents, now()->addDay());

            return;
        }
        TransactionPin::check($user, $request->input('pin'));
    }

    /** Whether this phone's own lock approves payments. */
    public static function phoneLock(Request $request): bool
    {
        $id = $request->attributes->get('device_token_id');

        return $id && (bool) DB::table('device_tokens')->where('id', $id)->value('phone_lock');
    }

    /** Turn the phone lock on or off for this phone: needs the PIN. */
    public static function setPhoneLock(Request $request, User $user, bool $on): void
    {
        TransactionPin::check($user, $request->input('pin'));
        DB::table('device_tokens')->where('id', $request->attributes->get('device_token_id'))->where('user_id', $user->id)->update(['phone_lock' => $on]);
    }

    /** This phone's quick-pay limit in cents, or null. */
    public static function limit(Request $request): ?int
    {
        $id = $request->attributes->get('device_token_id');

        return $id ? DB::table('device_tokens')->where('id', $id)->value('quick_pay_limit') : null;
    }

    /** Turn quick pay on (a limit in KES, up to 5,000) or off (0) for this phone: needs the PIN. */
    public static function set(Request $request, User $user, int $shillings): ?int
    {
        TransactionPin::check($user, $request->input('pin'));
        $cents = max(0, min(self::MAX, $shillings * 100)) ?: null;
        DB::table('device_tokens')->where('id', $request->attributes->get('device_token_id'))->where('user_id', $user->id)->update(['quick_pay_limit' => $cents]);

        return $cents;
    }
}

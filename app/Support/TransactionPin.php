<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * The PIN that approves money leaving a wallet (wallet payments, withdrawals, @name transfers). M-PESA-funded payments
 * are approved by the M-PESA PIN on the phone instead. Stored hashed; 5 wrong tries lock it for 15 minutes.
 */
final class TransactionPin
{
    public const TRIES = 5;

    public const LOCK_MINUTES = 15;

    public static function isSet(User $user): bool
    {
        return filled($user->transaction_pin);
    }

    /** First-time setup (changing it needs the current PIN). */
    public static function set(User $user, string $pin, ?string $current = null): void
    {
        if (self::isSet($user)) {
            self::check($user, (string) $current);
        }
        if (! preg_match('/^\d{4}$/', $pin) || in_array($pin, ['0000', '1111', '1234', '4321', '2580'], true) || count(array_unique(str_split($pin))) === 1) {
            throw ValidationException::withMessages(['pin' => 'Choose 4 digits that are not easy to guess (not 1234 or 0000).']);
        }
        $user->forceFill(['transaction_pin' => Hash::make($pin), 'pin_attempts' => 0, 'pin_locked_until' => null])->save();
    }

    /** Throws unless $pin is right. Counts wrong tries and locks after TRIES. */
    public static function check(User $user, ?string $pin): void
    {
        if (! self::isSet($user)) {
            throw ValidationException::withMessages(['pin' => 'Set your Sharet PIN first.']);
        }
        if ($user->pin_locked_until?->isFuture()) {
            throw ValidationException::withMessages(['pin' => 'Too many wrong PINs. Try again at '.$user->pin_locked_until->timezone('Africa/Nairobi')->format('H:i').'.']);
        }
        if (! is_string($pin) || ! Hash::check($pin, $user->transaction_pin)) {
            $tries = $user->pin_attempts + 1;
            $locked = $tries >= self::TRIES;
            $user->forceFill(['pin_attempts' => $locked ? 0 : $tries, 'pin_locked_until' => $locked ? now()->addMinutes(self::LOCK_MINUTES) : null])->save();
            throw ValidationException::withMessages(['pin' => $locked
                ? 'Too many wrong PINs. Wallet payments are paused for '.self::LOCK_MINUTES.' minutes.'
                : 'Wrong PIN. '.(self::TRIES - $tries).' '.(self::TRIES - $tries === 1 ? 'try' : 'tries').' left.']);
        }
        if ($user->pin_attempts) {
            $user->forceFill(['pin_attempts' => 0])->save();
        }
    }
}

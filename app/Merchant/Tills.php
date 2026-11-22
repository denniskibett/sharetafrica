<?php

namespace App\Merchant;

use App\Integrations\Sms\Sms;
use App\Models\Company;
use App\Models\MerchantTill;
use App\Models\User;
use App\Ramp\Destination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Tills and cashiers. The owner adds a cashier by phone: the till gets the next number and the cashier an invite code by
 * SMS. On the shop phone the cashier types their phone and the code once, and chooses a 4-digit cashier PIN: that phone
 * is now linked to the till (a till token, kept in the phone's secure storage). Each shift opens with the cashier PIN.
 * A paused till's PIN stops working at once; removing a cashier unlinks every phone.
 */
final class Tills
{
    public const PIN_TRIES = 5;

    public const LOCK_MINUTES = 15;

    /** @return array{0: MerchantTill, 1: string} the till and its invite code (sent by SMS too) */
    public static function invite(Company $shop, User $by, string $name, string $phone): array
    {
        $phone = Destination::phone($phone, 'phone');
        if (MerchantTill::where('company_id', $shop->id)->where('cashier_phone', $phone)->where('status', '!=', 'removed')->exists()) {
            throw ValidationException::withMessages(['phone' => 'That number is already a cashier here.']);
        }
        $till = DB::transaction(function () use ($shop, $by, $name, $phone) {
            $number = (int) MerchantTill::where('company_id', $shop->id)->lockForUpdate()->max('number') + 1;

            return MerchantTill::create(['company_id' => $shop->id, 'number' => $number, 'cashier_name' => Str::limit(trim(strip_tags($name)), 120, ''),
                'cashier_phone' => $phone, 'status' => 'invited', 'invited_by' => $by->id]);
        });

        return [$till, self::sendCode($till)];
    }

    /** A new invite code (a new phone, or a forgotten cashier PIN): the next link sets a new PIN. */
    public static function sendCode(MerchantTill $till): string
    {
        $code = (string) random_int(100000, 999999);
        $till->forceFill(['invite_hash' => Hash::make($code), 'invite_expires_at' => now()->addHours(config('merchant.invite_hours'))])->save();
        $shop = $till->company;
        Sms::send($till->cashier_phone, "Sharet: you are a cashier at {$shop->name}, {$till->label()}. On the shop phone open Sharet › I'm a cashier and enter code {$code} (valid "
            .config('merchant.invite_hours').' hours). Never share it.', 'merchant-invite:'.$till->id.':'.substr(hash('sha256', $code), 0, 8));

        return $code;
    }

    /** The shop phone links to a till: cashier phone + invite code + a new cashier PIN. Returns [till, till token]. */
    public static function link(string $phone, string $code, string $pin, string $deviceId): array
    {
        $phone = Destination::phone($phone, 'phone');
        $till = MerchantTill::where('cashier_phone', $phone)->whereIn('status', ['invited', 'active', 'paused'])->whereNotNull('invite_hash')
            ->where('invite_expires_at', '>', now())->get()->first(fn ($t) => Hash::check($code, $t->invite_hash));
        if (! $till) {
            throw ValidationException::withMessages(['code' => 'That code is wrong or has expired. Ask the shop owner for a new one.']);
        }
        if ($till->status === 'paused') {
            throw ValidationException::withMessages(['code' => 'This till is paused. Ask the shop owner.']);
        }
        self::assertPinShape($pin);
        $token = Str::random(64);
        DB::transaction(function () use ($till, $pin, $deviceId, $token) {
            $till->forceFill(['pin_hash' => Hash::make($pin), 'pin_attempts' => 0, 'pin_locked_until' => null, 'invite_hash' => null, 'invite_expires_at' => null,
                'status' => 'active', 'last_seen_at' => now()])->save();
            DB::table('merchant_till_devices')->where('till_id', $till->id)->where('device_id', $deviceId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
            DB::table('merchant_till_devices')->insert(['till_id' => $till->id, 'device_id' => $deviceId, 'token_hash' => hash('sha256', $token),
                'unlocked_until' => now()->addHours(config('merchant.session_hours')), 'last_used_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        });

        return [$till->fresh(), $token];
    }

    /** The cashier PIN opens the till on this phone for a shift. 5 wrong tries lock it for 15 minutes. */
    public static function signIn(MerchantTill $till, int $deviceRow, ?string $pin): void
    {
        if ($till->pin_locked_until?->isFuture()) {
            throw ValidationException::withMessages(['pin' => 'Too many wrong PINs. Try again at '.$till->pin_locked_until->timezone(config('merchant.timezone'))->format('H:i').'.']);
        }
        if (! is_string($pin) || ! $till->pin_hash || ! Hash::check($pin, $till->pin_hash)) {
            $tries = $till->pin_attempts + 1;
            $locked = $tries >= self::PIN_TRIES;
            $till->forceFill(['pin_attempts' => $locked ? 0 : $tries, 'pin_locked_until' => $locked ? now()->addMinutes(self::LOCK_MINUTES) : null])->save();
            throw ValidationException::withMessages(['pin' => $locked ? 'Too many wrong PINs. This till is locked for '.self::LOCK_MINUTES.' minutes.'
                : 'Wrong cashier PIN. '.(self::PIN_TRIES - $tries).' '.(self::PIN_TRIES - $tries === 1 ? 'try' : 'tries').' left.']);
        }
        $till->forceFill(['pin_attempts' => 0, 'last_seen_at' => now()])->save();
        DB::table('merchant_till_devices')->where('id', $deviceRow)->update(['unlocked_until' => now()->addHours(config('merchant.session_hours')), 'updated_at' => now()]);
    }

    /** Lock every phone of this till (end of day, sign out): the cashier PIN opens it again. */
    public static function lock(MerchantTill $till): void
    {
        DB::table('merchant_till_devices')->where('till_id', $till->id)->whereNull('revoked_at')->update(['unlocked_until' => null, 'updated_at' => now()]);
    }

    /** pause | resume | remove, by the owner. Paused: the PIN stops working at once. Removed: phones unlinked. */
    public static function set(MerchantTill $till, string $action): MerchantTill
    {
        if ($till->status === 'removed') {
            throw ValidationException::withMessages(['till' => 'This cashier was removed.']);
        }
        match ($action) {
            'pause' => $till->forceFill(['status' => 'paused'])->save() && self::lock($till),
            'resume' => $till->forceFill(['status' => $till->pin_hash ? 'active' : 'invited'])->save(),
            'remove' => $till->forceFill(['status' => 'removed', 'pin_hash' => null, 'invite_hash' => null])->save()
                && DB::table('merchant_till_devices')->where('till_id', $till->id)->whereNull('revoked_at')->update(['revoked_at' => now()]),
        };

        return $till->fresh();
    }

    public static function assertPinShape(string $pin): void
    {
        if (! preg_match('/^\d{4}$/', $pin) || in_array($pin, ['1234', '4321', '2580'], true) || count(array_unique(str_split($pin))) === 1) {
            throw ValidationException::withMessages(['pin' => 'Choose 4 digits that are not easy to guess (not 1234 or 0000).']);
        }
    }
}

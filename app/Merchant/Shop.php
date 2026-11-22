<?php

namespace App\Merchant;

use App\Models\Company;
use App\Models\User;
use App\Ramp\Destination;
use App\Services\Integrations;
use Illuminate\Validation\ValidationException;

/**
 * A shop on Sharet (Phase 2 · Merchant): a Company with its own Sharet wallet (Company::walletUser). The owner is a
 * personal account holder with the merchant (or business) role whose company it is; only they see the balance,
 * approve refunds, set settlement and manage tills. Settings live in companies.settings['merchant'].
 */
final class Shop
{
    /** The shop this person owns, or null. */
    public static function of(User $user): ?Company
    {
        if ($user->is_business_wallet || ! $user->company_id || ! $user->hasAnyRole(['merchant', 'business'])) {
            return null;
        }

        return Company::find($user->company_id);
    }

    /** The shop this person owns, or a 403 the app shows as "set up your shop". */
    public static function owned(User $user): Company
    {
        return self::of($user) ?? abort(response()->json(['error' => 'no_shop', 'message' => 'Set up your shop first: Me › Business, or sharet.africa › Business settings.'], 403));
    }

    /**
     * Set up a shop from the app: name and (optionally) KRA PIN. Like Business settings on the web, only a verified
     * personal account holder can; the shop takes money once Sharet has checked it (KYB, verified_at).
     */
    public static function setup(User $user, string $name, ?string $kraPin = null): Company
    {
        if (! in_array($user->kyc_level, ['basic', 'standard'], true)) {
            throw ValidationException::withMessages(['name' => 'Verify your personal account first (ID photos and a selfie): only verified account holders can open a shop.']);
        }
        $company = $user->company ?? new Company(['status' => true, 'type' => 'merchant', 'country' => 'KE']);
        $company->fill(['name' => trim($name)]);
        if ($kraPin) {
            $company->tax_id = strtoupper($kraPin);
        }
        $company->save();
        $user->company_id || $user->forceFill(['company_id' => $company->id])->save();
        $company->walletUser();
        $user->hasAnyRole(['merchant', 'business']) || $user->assignRole('merchant');

        return $company->fresh();
    }

    public static function wallet(Company $shop): User
    {
        return $shop->walletUser();
    }

    /** @return array{settle_at: string, settlement: array{mode: string, bank: ?string, account: ?string, phone: ?string}, last_settlement: ?array} */
    public static function settings(Company $shop): array
    {
        $m = (array) ($shop->settings['merchant'] ?? []);

        return [
            'settle_at' => $m['settle_at'] ?? config('merchant.settle_at'),
            'settlement' => ($m['settlement'] ?? []) + ['mode' => 'keep', 'bank' => null, 'account' => null, 'phone' => null],
            'last_settlement' => $m['last_settlement'] ?? null,
            'last_closed_day' => $m['last_closed_day'] ?? null,
        ];
    }

    public static function put(Company $shop, array $merchant): void
    {
        $settings = $shop->settings ?? [];
        $settings['merchant'] = array_merge((array) ($settings['merchant'] ?? []), $merchant);
        $shop->forceFill(['settings' => $settings])->save();
    }

    /** Settlement: keep it in the wallet, or every day to a bank account (through the bank's paybill) or an M-PESA number. */
    public static function saveSettlement(Company $shop, array $in): array
    {
        $mode = $in['mode'] ?? 'keep';
        $time = $in['settle_at'] ?? self::settings($shop)['settle_at'];
        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $time)) {
            throw ValidationException::withMessages(['settle_at' => 'Choose a time like 17:00.']);
        }
        $s = ['mode' => $mode, 'bank' => null, 'account' => null, 'phone' => null];
        if ($mode === 'bank') {
            [$d] = Destination::normalise('bank', ['bank' => $in['bank'] ?? '', 'account' => $in['account'] ?? '']);
            $s = ['bank' => $d['bank'], 'account' => $d['account']] + $s;
        } elseif ($mode === 'mpesa') {
            $s['phone'] = Destination::phone((string) ($in['phone'] ?? ''), 'phone');
        }
        self::put($shop, ['settlement' => $s, 'settle_at' => $time]);

        return self::settings($shop->fresh());
    }

    /** The shop's account number on Sharet's paybill (Cloud9 issues it, once connected). null until then. */
    public static function accountNumber(Company $shop): ?array
    {
        $wallet = self::wallet($shop);
        if (! $wallet->cloud9_account && filled(Integrations::credentials('cloud9')['api_key'] ?? null)) {
            try {
                $a = app(\App\Integrations\Cloud9\Cloud9Rail::class)->createAccount($wallet);
                $wallet->forceFill(['cloud9_account_id' => $a['account_id'], 'cloud9_account' => $a['account'], 'cloud9_paybill' => $a['paybill']])->saveQuietly();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $wallet->cloud9_account ? ['paybill' => (string) $wallet->cloud9_paybill, 'account' => (string) $wallet->cloud9_account,
            'formatted' => \App\Support\AccountNumber::format((string) $wallet->cloud9_account)] : null;
    }

    /** "Today" for a shop: the Kenyan day, as UTC bounds. */
    public static function day(?string $date = null): array
    {
        $tz = config('merchant.timezone');
        $start = $date ? \Illuminate\Support\Carbon::parse($date, $tz)->startOfDay() : now($tz)->startOfDay();

        return [$start->toDateString(), $start->copy()->utc(), $start->copy()->addDay()->utc()];
    }
}

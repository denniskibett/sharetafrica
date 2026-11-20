<?php

namespace App\Support;

use App\Integrations\Cloud9\Cloud9Rail;
use App\Models\User;
use App\Services\Integrations;

/**
 * A person's own account number on Sharet's paybill (Cloud9 issues it). Shown on home and in Add money; anyone can pay
 * it from the M-PESA menu. Created the first time it is needed, once Cloud9 is connected; null until then.
 */
class AccountNumber
{
    /** @return array{paybill: string, account: string}|null */
    public static function for(User $user, bool $create = true): ?array
    {
        if ($user->is_business_wallet) {
            return null;
        }
        if (! $user->cloud9_account && $create && filled(Integrations::credentials('cloud9')['api_key'] ?? null)) {
            try {
                $a = app(Cloud9Rail::class)->createAccount($user);
                $user->forceFill(['cloud9_account_id' => $a['account_id'], 'cloud9_account' => $a['account'], 'cloud9_paybill' => $a['paybill']])->saveQuietly();
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        }

        return $user->cloud9_account ? ['paybill' => (string) $user->cloud9_paybill, 'account' => (string) $user->cloud9_account] : null;
    }

    /** 482915 → "48 2915": easier to read out and type. */
    public static function format(string $account): string
    {
        return strlen($account) > 4 ? substr($account, 0, -4).' '.substr($account, -4) : $account;
    }
}

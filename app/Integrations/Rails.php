<?php

namespace App\Integrations;

use App\Integrations\Contracts\PaymentRail;
use App\Ledger\LedgerException;

/** Finds the adapter for a rail name (config/rails.php) and the ledger accounts that mirror its money. */
class Rails
{
    public function default(): string
    {
        return config('rails.default');
    }

    /** The rail for this person's wallet top-ups (config rails.collect, limited by rails.collect_only_for), else the default. */
    public function collecting(?\App\Models\User $user = null): string
    {
        $only = config('rails.collect_only_for');
        $allowed = empty($only) || ($user && in_array(strtolower($user->email), array_map('strtolower', $only), true));

        return config('rails.collect') && $allowed ? config('rails.collect') : $this->default();
    }

    public function for(?string $name = null): PaymentRail
    {
        $name ??= $this->default();
        $class = config("rails.adapters.{$name}") ?? throw new \RuntimeException("Payment rail {$name} is not integrated yet.");
        if ($name === 'simulator' && app()->isProduction() && ! config('rails.simulator.allow_in_production')) {
            throw new \RuntimeException('The payment simulator is switched off in production. Set RAIL_DEFAULT to a real rail.');
        }

        return app($class);
    }

    /** Ledger account code where this rail's collections land ('collect') or payouts leave from ('payout'). */
    public function account(string $rail, string $kind): string
    {
        return config("rails.accounts.{$rail}.{$kind}") ?? throw LedgerException::make('unknown_account', "No ledger account for {$rail} {$kind}.");
    }
}

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
        return config('rails.collect') && $this->isNamed($user) ? config('rails.collect') : $this->default();
    }

    /** The rail that pays out for this person (config rails.payout, limited by rails.collect_only_for), else the default. */
    public function paying(?\App\Models\User $user = null): string
    {
        return config('rails.payout') && $this->isNamed($user) ? config('rails.payout') : $this->default();
    }

    private function isNamed(?\App\Models\User $user): bool
    {
        $only = array_map('strtolower', config('rails.collect_only_for'));
        if (empty($only) || ! $user) {
            return empty($only);
        }
        if (in_array(strtolower($user->email), $only, true)) {
            return true;
        }

        // A business wallet can't sign in and has a made-up email: it is named when one of its people is.
        return $user->is_business_wallet && $user->company_id
            && \App\Models\User::where('company_id', $user->company_id)->where('is_business_wallet', false)->get(['email'])
                ->contains(fn ($u) => in_array(strtolower($u->email), $only, true));
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

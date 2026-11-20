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

    /**
     * The rail for this person's collections (M-PESA prompts). The switch (System › Payment rails) decides when it has a
     * route for collections and the person is in its audience; otherwise config rails.collect for the test list, else the default.
     */
    public function collecting(?\App\Models\User $user = null): string
    {
        return $this->routed('collect', $user)
            ?? (config('rails.collect') && $this->isNamed($user) ? config('rails.collect') : $this->default());
    }

    /** The rail that pays out for this person, by where the money goes (phone, paybill, till, bank; business wallets: settlement). */
    public function paying(?\App\Models\User $user = null, string $destinationType = 'phone'): string
    {
        return $this->routed(self::movement($user, $destinationType), $user)
            ?? (config('rails.payout') && $this->isNamed($user) ? config('rails.payout') : $this->default());
    }

    /** Which kind of payment (RailRoute::MOVEMENTS) a payout is. */
    public static function movement(?\App\Models\User $user, string $destinationType): string
    {
        return match (true) {
            (bool) $user?->is_business_wallet => 'settlement',
            in_array($destinationType, ['paybill', 'till'], true) => 'payout_b2b',
            $destinationType === 'bank' => 'payout_bank',
            default => 'payout_phone',
        };
    }

    /** The other rail to try when the primary refuses before accepting, if the switch has one for this request. */
    public function fallbackFor(\App\Models\RailRequest $request, ?\App\Models\User $user): ?string
    {
        $movement = $request->kind === 'collect' ? 'collect' : self::movement($user, $request->meta['destination_type'] ?? 'phone');
        $route = \App\Models\RailRoute::for($movement);
        if (! $route || ! $route->fallback || $route->fallback === $request->rail || $route->rail !== $request->rail
            || ($route->audience !== 'everyone' && ! $this->isNamed($user))) {
            return null;
        }

        return $route->fallback;
    }

    private function routed(string $movement, ?\App\Models\User $user): ?string
    {
        $route = \App\Models\RailRoute::for($movement);

        return $route && ($route->audience === 'everyone' || $this->isNamed($user)) ? $route->rail : null;
    }

    /** Whether a person is on the test list (RAIL_COLLECT_ONLY_FOR), or the list is empty (everyone). */
    public function named(?\App\Models\User $user): bool
    {
        return $this->isNamed($user);
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

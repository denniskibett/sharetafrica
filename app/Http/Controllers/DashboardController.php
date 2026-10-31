<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Single entry point for /dashboard.
     * Builds $cardData based on the user's role and returns one view.
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        // Waiting-list / onboarding users are redirected to the onboarding flow
        if ($user->isOnWaitingList() || $user->needsOnboarding()) {
            return redirect()->route('onboarding.welcome');
        }

        $cardData = $this->buildCardData($user);

        return view('dashboard', [
            'cardData'   => $cardData,
            'heading'    => $this->headingFor($user),
            'subheading' => $this->subheadingFor($user),
            'recentActivity' => $this->recentActivityFor($user),
        ]);
    }

    // ==========================================================
    // CARD DATA — dispatcher
    // =========================================================
    protected function buildCardData(User $user): array
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $this->adminCardData();
        }

        if ($user->hasAnyRole(['support', 'operations'])) {
            return $this->opsCardData($user);
        }

        return match ($user->intent) {
            'merchant'   => $this->merchantCardData($user),
            'business'   => $this->businessCardData($user),
            'techie'     => $this->developerCardData($user),
            'individual' => $this->individualCardData($user),
            default      => ['user_role' => 'waiting_list'],
        };
    }

    // ==========================================================
    // INDIVIDUAL
    // =========================================================
    protected function individualCardData(User $user): array
    {
        $wallet = $user->getOrCreateWallet();

        return [
            'user_role'          => 'individual',
            'wallet_balance'     => $user->getWalletBalance(),
            'total_transactions' => $wallet->transactions()->count(),
            'total_sent'         => $wallet->transactions()->where('type', 'withdraw')->sum('amount') / 100,
            'total_received'     => $wallet->transactions()->where('type', 'deposit')->sum('amount') / 100,
        ];
    }

    // ==========================================================
    // MERCHANT
    // =========================================================
    protected function merchantCardData(User $user): array
    {
        return [
            'user_role'          => 'merchant',
            'wallet_balance'     => $user->getWalletBalance(),
            'received_today'     => 0,
            'received_month'     => 0,
            'settled_month'      => 0,
            'pending_settlement' => 0,
            'qr_payments'        => 0,
            'company_name'       => $user->company?->name,
        ];
    }

    // ==========================================================
    // BUSINESS
    // =========================================================
    protected function businessCardData(User $user): array
    {
        return [
            'user_role'            => 'business',
            'wallet_balance'       => $user->getWalletBalance(),
            'outstanding_invoices' => 0,
            'paid_invoices'        => 0,
            'total_suppliers'      => 0,
            'trade_finance_active' => 0,
            'trade_finance_limit'  => 0,
            'payouts_this_month'   => 0,
            'company_name'         => $user->company?->name,
        ];
    }

    // ==========================================================
    // TECHIE / DEVELOPER
    // =========================================================
    protected function developerCardData(User $user): array
    {
        return [
            'user_role'       => 'techie',
            'api_keys'        => 0,
            'sandbox_calls'   => 0,
            'live_calls'      => 0,
            'webhooks'        => 0,
            'error_rate'      => 0,
            'api_calls_today' => 0,
            'rate_limit'      => 1000,
        ];
    }

    // ==========================================================
    // ADMIN
    // =========================================================
    protected function adminCardData(): array
    {
        return [
            'user_role'          => 'admin',
            'total_users'        => User::count(),
            'total_companies'    => Company::count(),
            'waiting_list_count' => User::where('onboarding_status', 'waiting_list')->count(),
            'invited_count'      => User::where('onboarding_status', 'invited')->count(),
            'onboarded_count'    => User::where('onboarding_status', 'active')->count(),
            'individual_count'   => User::where('intent', 'individual')->count(),
            'merchant_count'     => User::where('intent', 'merchant')->count(),
            'business_count'     => User::where('intent', 'business')->count(),
            'techie_count'       => User::where('intent', 'techie')->count(),
            'transactions_today' => 0,
            'transactions_month' => 0,
        ];
    }

    // ==========================================================
    // OPS / SUPPORT
    // =========================================================
    protected function opsCardData(User $user): array
    {
        return [
            'user_role'          => $user->hasRole('support') ? 'support' : 'operations',
            'waiting_list_count' => User::where('onboarding_status', 'waiting_list')->count(),
            'invited_count'      => User::where('onboarding_status', 'invited')->count(),
            'in_progress_count'  => User::where('onboarding_status', 'in_progress')->count(),
            'kyc_pending'        => 0,
            'kyc_approved_today' => 0,
            'rails_live'         => 2,
            'rails_degraded'     => 0,
            'rails_down'         => 0,
            'liquidity_kes'      => 0,
            'liquidity_tzs'      => 0,
            'liquidity_ugx'      => 0,
        ];
    }

    // ==========================================================
    // HEADINGS
    // =========================================================
    protected function headingFor(User $user): string
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return 'Admin overview';
        }

        if ($user->hasAnyRole(['support', 'operations'])) {
            return $user->hasRole('support') ? 'Support desk' : 'Operations';
        }

        return match ($user->intent) {
            'merchant'   => 'Merchant dashboard',
            'business'   => 'Business dashboard',
            'techie'     => 'Developer dashboard',
            'individual' => 'Your wallet',
            default      => 'Welcome',
        };
    }

    protected function subheadingFor(User $user): string
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return 'Platform-wide metrics — users, companies, lanes, waiting list.';
        }

        if ($user->hasRole('support')) {
            return 'Waiting list, KYC queue, and user support.';
        }

        if ($user->hasRole('operations')) {
            return 'Rails, liquidity, and reconciliation.';
        }

        return match ($user->intent) {
            'merchant'   => 'Accept from any rail. Settle anywhere.',
            'business'   => 'Pay abroad. Get paid from abroad. Settle locally.',
            'techie'     => 'Build on the Sharet ledger. API, sandbox, webhooks.',
            'individual' => 'Send, receive, and manage your Sharet balance.',
            default      => 'Here is your Sharet overview.',
        };
    }

    // ==========================================================
    // RECENT ACTIVITY
    // =========================================================
    protected function recentActivityFor(User $user)
    {
        if (in_array($user->intent, ['individual', 'merchant', 'business'], true)) {
            $wallet = $user->getOrCreateWallet();
            return $wallet->transactions()->latest()->take(5)->get();
        }

        return collect();
    }
}
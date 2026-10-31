{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', auth()->user()->dashboard_title ?? 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">

        {{-- ============================================================
             WELCOME HEADER (all roles)
        ============================================================ --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                    <span x-text="welcomeMessage">Welcome</span>,
                    {{ auth()->user()->first_name ?: auth()->user()->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ now()->format('l, d F Y') }} ·
                    <span class="font-medium text-primary">{{ auth()->user()->dashboard_title ?? 'Dashboard' }}</span>
                </p>
            </div>

            {{-- Wallet balance chip --}}
            @if (method_exists(auth()->user(), 'isOnboarded') && auth()->user()->isOnboarded())
                <a href="{{ route('wallet.index') }}"
                   class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm transition hover:border-primary-30 hover:bg-primary-10
                          dark:border-gray-700 dark:bg-gray-800">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <div class="text-left">
                        <p class="text-[10px] uppercase tracking-widest text-gray-400">Wallet balance</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ config('wallet.default_currency', 'KES') }}
                            {{ number_format((float) auth()->user()->balanceFloat, 2) }}
                        </p>
                    </div>
                </a>
            @endif
        </div>

        {{-- ============================================================
             WAITING LIST — user has no lane yet
        ============================================================ --}}
        @if (auth()->user()->onboarding_status === 'waiting_list')
            <div class="rounded-2xl border border-primary-20 bg-gradient-to-br from-primary-10 to-secondary-10 p-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">You're on the waiting list</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-600 dark:text-gray-300">
                    Thanks for joining Sharet Africa. We're onboarding new users in waves so we can give every account the attention it deserves.
                    You'll get an email the moment your spot opens.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('waiting_list.show') }}"
                       class="rounded-full bg-primary px-5 py-2 text-sm font-medium text-white hover:brightness-95">
                        View waiting list status
                    </a>
                    <a href="{{ route('profile.index') }}"
                       class="rounded-full border border-primary-30 px-5 py-2 text-sm font-medium text-primary hover:bg-primary-10">
                        Complete your profile
                    </a>
                </div>
            </div>
        @endif

        {{-- ============================================================
             INVITED — user picked a lane, needs to finish onboarding
        ============================================================ --}}
        @if (auth()->user()->onboarding_status === 'invited')
            <div class="rounded-2xl border border-primary-20 bg-white p-8 dark:bg-gray-900">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Ready to get started</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    You've been invited to join Sharet Africa. Pick the lane that fits you best and finish your onboarding.
                </p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['intent' => 'individual', 'title' => 'Individual', 'desc' => 'Personal wallet, send money', 'route' => 'onboarding.individual'],
                        ['intent' => 'merchant', 'title' => 'Merchant', 'desc' => 'Accept payments, settle', 'route' => 'onboarding.merchant'],
                        ['intent' => 'business', 'title' => 'Business', 'desc' => 'Trade, invoices, treasury', 'route' => 'onboarding.business'],
                        ['intent' => 'techie', 'title' => 'Developer', 'desc' => 'API, sandbox, webhooks', 'route' => 'onboarding.developer'],
                    ] as $lane)
                        <a href="{{ route($lane['route']) }}"
                           class="rounded-xl border border-gray-200 p-5 transition hover:border-primary-30 hover:bg-primary-10
                                  dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">{{ $lane['title'] }}</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $lane['desc'] }}</p>
                            <span class="mt-3 inline-block text-xs font-medium text-primary">Start →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ============================================================
             ACTIVE USER — role-specific dashboard
        ============================================================ --}}
        @if (auth()->user()->onboarding_status === 'active')

            {{-- ADMIN / SUPER ADMIN --}}
            @hasanyrole('admin|super_admin')
                @include('partials.dashboard.admin', [
                    'stats' => $stats ?? [],
                    'waitingList' => $waitingList ?? collect(),
                    'recentUsers' => $recentUsers ?? collect(),
                    'companies' => $companies ?? collect(),
                ])
            @endhasanyrole

            {{-- SUPPORT / OPERATIONS --}}
            @hasanyrole('support|operations')
                @include('partials.dashboard.ops', [
                    'stats' => $stats ?? [],
                    'reconciliation' => $reconciliation ?? [],
                    'treasury' => $treasury ?? [],
                    'rails' => $rails ?? [],
                    'tickets' => $tickets ?? collect(),
                ])
            @endhasanyrole

            {{-- MERCHANT --}}
            @role('merchant')
                @include('partials.dashboard.merchant', [
                    'stats' => $stats ?? [],
                    'recentPayments' => $recentPayments ?? collect(),
                    'settlements' => $settlements ?? collect(),
                    'qrCodes' => $qrCodes ?? collect(),
                ])
            @endrole

            {{-- BUSINESS --}}
            @role('business')
                @include('partials.dashboard.business', [
                    'stats' => $stats ?? [],
                    'invoices' => $invoices ?? collect(),
                    'suppliers' => $suppliers ?? collect(),
                    'tradeFinance' => $tradeFinance ?? collect(),
                    'treasury' => $treasury ?? [],
                ])
            @endrole

            {{-- DEVELOPER / TECHIE --}}
            @role('techie')
                @include('partials.dashboard.developer', [
                    'stats' => $stats ?? [],
                    'apiKeys' => $apiKeys ?? collect(),
                    'webhooks' => $webhooks ?? collect(),
                    'recentLogs' => $recentLogs ?? collect(),
                ])
            @endrole

            {{-- INDIVIDUAL --}}
            @role('individual')
                @include('partials.dashboard.individual', [
                    'stats' => $stats ?? [],
                    'recentTransfers' => $recentTransfers ?? collect(),
                    'contacts' => $contacts ?? collect(),
                ])
            @endrole

        @endif

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function dashboard() {
    return {
        userName: '{{ auth()->user()->first_name ?: auth()->user()->name }}',
        userRole: '{{ auth()->user()->getRoleNames()->first() ?? "user" }}',
        intent: '{{ auth()->user()->intent ?? "undecided" }}',
        onboardingStatus: '{{ auth()->user()->onboarding_status ?? "waiting_list" }}',
        welcomeMessage: '',

        init() {
            this.setWelcomeMessage();
        },

        setWelcomeMessage() {
            const hour = new Date().getHours();
            if (hour < 12)      this.welcomeMessage = 'Good morning';
            else if (hour < 18) this.welcomeMessage = 'Good afternoon';
            else                this.welcomeMessage = 'Good evening';
        }
    };
}

/**
 * Shared helpers used by the dashboard partials.
 * Kept global so inline onclick handlers still work.
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard');
    });
}
</script>
@endsection
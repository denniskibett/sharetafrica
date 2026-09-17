{{-- resources/views/partials/card/card-dashboard.blade.php --}}
@props(['cardData' => []])

@php
    $roleName = $cardData['user_role'] ?? 'guest';
@endphp

<div class="grid grid-cols-1 gap-4 md:gap-6 mb-6"
     style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">

    {{-- ============================================================
         INDIVIDUAL — personal wallet
         ============================================================ --}}
    @if($roleName === 'individual')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Wallet Balance</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['wallet_balance'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">Active</span>
                <span class="text-xs text-gray-500">Ready to send</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Received</p>
                    <h4 class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                        KES {{ number_format($cardData['total_received'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">All-time deposits</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Sent</p>
                    <h4 class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                        KES {{ number_format($cardData['total_sent'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m-8 8h16"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Across all rails</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Transactions</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_transactions'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">All-time ledger entries</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MERCHANT — accept from any rail
         ============================================================ --}}
    @if($roleName === 'merchant')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Wallet Balance</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['wallet_balance'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">Receiving</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Received Today</p>
                    <h4 class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                        KES {{ number_format($cardData['received_today'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-gray-500">This month: KES {{ number_format($cardData['received_month'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Settled</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['settled_month'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Pending: KES {{ number_format($cardData['pending_settlement'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">QR Payments</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['qr_payments'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">In-person scans</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         BUSINESS — trade, invoices, suppliers
         ============================================================ --}}
    @if($roleName === 'business')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Wallet Balance</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['wallet_balance'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">
                    {{ $cardData['company_name'] ?? 'Business' }}
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Trade Finance Limit</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['trade_finance_limit'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-lime-100 dark:bg-lime-900">
                    <svg class="h-6 w-6 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Active: KES {{ number_format($cardData['trade_finance_active'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Outstanding Invoices</p>
                    <h4 class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">
                        {{ number_format($cardData['outstanding_invoices'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Paid: {{ number_format($cardData['paid_invoices'] ?? 0) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Suppliers</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_suppliers'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Across your corridors</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Payouts this month</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['payouts_this_month'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Sent to suppliers</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         TECHIE / DEVELOPER — API, sandbox, webhooks
         ============================================================ --}}
    @if($roleName === 'techie')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">API Keys</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['api_keys'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Active keys</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sandbox Calls</p>
                    <h4 class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($cardData['sandbox_calls'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Test environment</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Live Calls Today</p>
                    <h4 class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($cardData['api_calls_today'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Rate limit: {{ number_format($cardData['rate_limit'] ?? 1000) }}/s</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Webhooks</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['webhooks'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Registered endpoints</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         ADMIN — platform overview
         ============================================================ --}}
    @if($roleName === 'admin')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Users</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_users'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">
                    {{ number_format($cardData['onboarded_count'] ?? 0) }} active
                </span>
                <span class="text-xs text-gray-500">onboarded</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Companies</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_companies'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Registered businesses</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Waiting List</p>
                    <h4 class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($cardData['waiting_list_count'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600">
                    {{ number_format($cardData['invited_count'] ?? 0) }} invited
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">By Lane</p>
                    <h4 class="mt-2 text-lg font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['individual_count'] ?? 0) }} indv ·
                        {{ number_format($cardData['merchant_count'] ?? 0) }} merch
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">{{ number_format($cardData['business_count'] ?? 0) }} business · {{ number_format($cardData['techie_count'] ?? 0) }} techies</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Transactions Today</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['transactions_today'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">This month: {{ number_format($cardData['transactions_month'] ?? 0) }}</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         SUPPORT / OPERATIONS — waiting list, KYC, rails, treasury
         ============================================================ --}}
    @if(in_array($roleName, ['support', 'operations']))
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Waiting List</p>
                    <h4 class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($cardData['waiting_list_count'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600">
                    {{ number_format($cardData['invited_count'] ?? 0) }} invited
                </span>
                <span class="text-xs text-gray-500">in progress: {{ number_format($cardData['in_progress_count'] ?? 0) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">KYC Pending</p>
                    <h4 class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">
                        {{ number_format($cardData['kyc_pending'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Approved today: {{ number_format($cardData['kyc_approved_today'] ?? 0) }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rails Status</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['rails_live'] ?? 0) }} live
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                    <span class="text-gray-500">Degraded: {{ number_format($cardData['rails_degraded'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    <span class="text-gray-500">Down: {{ number_format($cardData['rails_down'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Liquidity (KES)</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES {{ number_format($cardData['liquidity_kes'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">TZS: {{ number_format($cardData['liquidity_tzs'] ?? 0, 2) }} · UGX: {{ number_format($cardData['liquidity_ugx'] ?? 0, 2) }}</span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         GUEST / FALLBACK
         ============================================================ --}}
    @if(!in_array($roleName, ['individual', 'merchant', 'business', 'techie', 'admin', 'support', 'operations']))
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ ucfirst($roleName) }}</h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Welcome to your dashboard</span>
            </div>
        </div>
    @endif

</div>
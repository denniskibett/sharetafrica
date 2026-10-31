{{-- resources/views/partials/card/card-companies.blade.php --}}
@props(['cardData' => []])

<div x-data="companyCards(@json($cardData))" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <!-- Total Companies -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Companies</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="formatNumber(totalCompanies)">0</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500" x-text="formatNumber(activeCompanies)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">active</span>
            <span class="rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500 ml-auto" x-text="formatNumber(pendingCompanies)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">pending</span>
        </div>
    </div>
    
    <!-- Total Users -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Users</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="formatNumber(totalUsers)">0</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500" x-text="formatNumber(verifiedUsers)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">verified</span>
            <span class="rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500 ml-auto" x-text="formatNumber(pendingUsers)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">pending</span>
        </div>
    </div>
    
    <!-- Platform Metrics -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Platform Metrics</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    <span x-text="formatNumber(totalUnits)">0</span> <span class="text-sm font-normal">units</span>
                </h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500" x-text="formatNumber(totalTenants)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">tenants</span>
            <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-600 dark:bg-purple-500/15 dark:text-purple-500 ml-auto">KES <span x-text="formatMoney(totalRevenue)">0</span></span>
            <span class="text-xs text-gray-500 dark:text-gray-400">revenue</span>
        </div>
    </div>
    
    <!-- Monthly Recurring Revenue -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Monthly Recurring Revenue</span>
                <h3 class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">
                    KES <span x-text="formatMoney(mrr)">0</span>
                </h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-cyan-50 px-2 py-0.5 text-xs font-medium text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-500" x-text="formatNumber(activeSubscriptions)"></span>
            <span class="text-xs text-gray-500 dark:text-gray-400">active subscriptions</span>
        </div>
    </div>
</div>

<script>
function companyCards(cardData) {
    return {
        totalCompanies: cardData?.total_companies || 0,
        activeCompanies: cardData?.active_companies || 0,
        pendingCompanies: cardData?.pending_companies || 0,
        totalUsers: cardData?.total_users || 0,
        verifiedUsers: cardData?.verified_users || 0,
        pendingUsers: cardData?.pending_verification_users || 0,
        totalUnits: cardData?.total_units || 0,
        totalTenants: cardData?.total_tenants || 0,
        totalRevenue: cardData?.total_revenue || 0,
        mrr: cardData?.monthly_recurring_revenue || 0,
        activeSubscriptions: cardData?.subscription_stats?.active || 0,
        
        formatNumber(value) {
            if (value === undefined || value === null) return '0';
            return Number(value).toLocaleString('en-KE');
        },
        
        formatMoney(value) {
            if (value === undefined || value === null) return '0.00';
            return Number(value).toLocaleString('en-KE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
    }
}
</script>
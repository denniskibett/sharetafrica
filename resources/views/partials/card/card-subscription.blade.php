{{-- resources/views/partials/card/card-subscription.blade.php --}}
@props([
    'planData' => [],
    'invoices' => [],
    'companies' => [],
    'accountManagers' => [],
])

<div class="grid grid-cols-1 gap-4 md:gap-6 mb-6"
     style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">

    {{-- Price Per Unit Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Price Per Unit</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    KES {{ number_format($planData['price_per_unit'] ?? 0, 0) }}
                </h4>
                <p class="text-xs text-gray-400">per unit / month</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if(!empty($planData['unit_range']))
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500">Unit Range:</span>
                <span class="rounded-full bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400">{{ $planData['unit_range'] }}</span>
            </div>
        @endif
    </div>

    {{-- Monthly Price Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Price</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    KES {{ number_format($planData['monthly_price'] ?? 0, 0) }}
                </h4>
                <p class="text-xs text-gray-400">based on min units</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        @if(!empty($planData['min_units']))
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500">Min Units:</span>
                <span class="rounded-full bg-purple-50 dark:bg-purple-900/30 px-2 py-0.5 text-xs font-medium text-purple-600 dark:text-purple-400">{{ $planData['min_units'] }}</span>
            </div>
        @endif
    </div>

    {{-- Yearly Price Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yearly Price</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    KES {{ number_format($planData['yearly_price'] ?? 0, 0) }}
                </h4>
                <p class="text-xs text-gray-400">with {{ $planData['discount_percentage'] ?? 0 }}% discount</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @php
            $savings = (($planData['monthly_price'] ?? 0) * 12) - ($planData['yearly_price'] ?? 0);
        @endphp
        <div class="mt-3 flex items-center gap-2 text-xs">
            <span class="text-gray-500">Savings:</span>
            <span class="rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-600 dark:text-green-400">KES {{ number_format($savings, 0) }}</span>
        </div>
    </div>

    {{-- Trial Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trial Period</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $planData['trial_days'] ?? 0 }} days
                </h4>
                <p class="text-xs text-gray-400">{{ ($planData['trial_days'] ?? 0) > 0 ? 'Free trial available' : 'No trial' }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900">
                <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if(!empty($planData['display_order']))
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500">Display Order:</span>
                <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $planData['display_order'] ?? 0 }}</span>
            </div>
        @endif
    </div>

    {{-- Subscribers Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Subscribers</p>
                <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ number_format($planData['subscriber_count'] ?? 0) }}
                </h4>
                <p class="text-xs text-gray-400">companies using this plan</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        @if(isset($planData['total_revenue']))
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500">Total Revenue:</span>
                <span class="rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-600 dark:text-green-400">KES {{ number_format($planData['total_revenue'] ?? 0, 0) }}</span>
            </div>
        @endif
    </div>
</div>
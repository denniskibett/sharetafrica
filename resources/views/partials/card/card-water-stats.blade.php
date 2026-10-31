{{-- resources/views/partials/card/card-water-stats.blade.php --}}
@props([
    'totalConsumption' => 0,
    'monthlyConsumption' => 0,
    'totalCharges' => 0,
    'averageConsumption' => 0,
    'totalReadings' => 0,
    'todayReadings' => 0,
    'monthReadings' => 0,
    'unitsWithReadings' => 0,
    'unitsWithoutReadings' => 0,
    'previousMonthConsumption' => 0,
    'trendPercentage' => 0
])

<div x-data="waterStatsCards({
    totalConsumption: {{ $totalConsumption }},
    monthlyConsumption: {{ $monthlyConsumption }},
    totalCharges: {{ $totalCharges }},
    averageConsumption: {{ $averageConsumption }},
    totalReadings: {{ $totalReadings }},
    todayReadings: {{ $todayReadings }},
    monthReadings: {{ $monthReadings }},
    unitsWithReadings: {{ $unitsWithReadings }},
    unitsWithoutReadings: {{ $unitsWithoutReadings }},
    previousMonthConsumption: {{ $previousMonthConsumption }},
    trendPercentage: {{ $trendPercentage }}
})" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    
    <!-- Total Consumption Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Water Consumed</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                    <span x-text="formatNumber(totalConsumption, 2)">0</span> 
                    <span class="text-sm font-normal text-gray-500">m³</span>
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500" x-text="formatNumber(totalReadings)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">total readings</span>
            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500 ml-auto" x-text="'+' + trendDisplay + '%'"></span>
        </div>
    </div>

    <!-- Monthly Consumption Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">This Month's Consumption</p>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400" x-text="currentMonthYear"></span>
                </div>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                    <span x-text="formatNumber(monthlyConsumption, 2)">0</span> 
                    <span class="text-sm font-normal text-gray-500">m³</span>
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500" x-text="formatNumber(monthReadings)">0</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">readings this month</span>
            <span x-show="trendPercentage !== 0" class="text-xs ml-auto" :class="trendPercentage >= 0 ? 'text-green-600' : 'text-red-600'">
                <span x-show="trendPercentage >= 0">↑</span>
                <span x-show="trendPercentage < 0">↓</span>
                <span x-text="Math.abs(trendPercentage)"></span>%
            </span>
        </div>
    </div>

    <!-- Total Charges Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Water Charges</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                    KES <span x-text="formatMoney(totalCharges)">0</span>
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-600 dark:bg-purple-500/15 dark:text-purple-500" x-text="formatMoney(averageChargePerReading)"></span>
            <span class="text-xs text-gray-500 dark:text-gray-400">avg per reading</span>
        </div>
    </div>

    <!-- Coverage & Progress Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Reading Coverage</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="coveragePercentage + '%'">0%</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2 rounded-full bg-yellow-500" :style="{ width: coveragePercentage + '%' }"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    <span class="text-gray-500">Covered: <span x-text="formatNumber(unitsWithReadings)"></span></span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    <span class="text-gray-500">Pending: <span x-text="formatNumber(unitsWithoutReadings)"></span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function waterStatsCards(config) {
    return {
        totalConsumption: config.totalConsumption || 0,
        monthlyConsumption: config.monthlyConsumption || 0,
        totalCharges: config.totalCharges || 0,
        averageConsumption: config.averageConsumption || 0,
        totalReadings: config.totalReadings || 0,
        todayReadings: config.todayReadings || 0,
        monthReadings: config.monthReadings || 0,
        unitsWithReadings: config.unitsWithReadings || 0,
        unitsWithoutReadings: config.unitsWithoutReadings || 0,
        previousMonthConsumption: config.previousMonthConsumption || 0,
        trendPercentage: config.trendPercentage || 0,
        
        get currentMonthYear() {
            const date = new Date();
            return date.toLocaleString('default', { month: 'short', year: 'numeric' });
        },
        
        get coveragePercentage() {
            const total = this.unitsWithReadings + this.unitsWithoutReadings;
            if (total === 0) return 0;
            return Math.round((this.unitsWithReadings / total) * 100);
        },
        
        get averageChargePerReading() {
            if (this.totalReadings === 0) return 0;
            return this.totalCharges / this.totalReadings;
        },
        
        get trendDisplay() {
            if (this.trendPercentage === 0) return 0;
            return Math.abs(this.trendPercentage);
        },
        
        formatNumber(value, decimals = 0) {
            if (value === undefined || value === null) return '0';
            return Number(value).toLocaleString('en-KE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        },
        
        formatMoney(value) {
            if (value === undefined || value === null) return '0.00';
            return Number(value).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }
}
</script>
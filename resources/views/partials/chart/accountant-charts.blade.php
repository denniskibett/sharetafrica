{{-- Accountant Dashboard Charts - TailAdmin Style --}}
<div class="space-y-6">
    <!-- Row 1: Monthly Revenue - Full Width -->
    <div class="grid grid-cols-1">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Monthly Revenue</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Revenue trends by month</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <select id="revenue-interval" data-chart="revenueBarChart" class="chart-period-filter rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <div x-data="{openDropDown: false}" class="relative h-fit">
                            <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                                </svg>
                            </button>
                            <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                <button onclick="window.exportChart('revenueBarChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                                <button onclick="window.exportChart('revenueBarChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                                <button onclick="window.exportChart('revenueBarChart', 'csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-6 dark:border-gray-800">
                <div id="revenueBarChart" 
                     data-dates='@json(array_keys($monthlyRevenue ?? []))'
                     data-counts='@json(array_values($monthlyRevenue ?? []))'
                     data-interval="monthly"
                     class="chartDarkStyle w-full" 
                     style="height: 320px; width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Payment Methods (1/4) + Collection Rate (1/2) + Invoice Status (1/4) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Payment Methods - 1/4 width -->
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Payment Methods</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Distribution</p>
                    </div>
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="window.exportChart('paymentDoughnutChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="window.exportChart('paymentDoughnutChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="window.exportChart('paymentDoughnutChart', 'csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                <div class="flex justify-center">
                    <div id="paymentDoughnutChart" 
                         data-labels='@json(array_keys($paymentMethods ?? []))'
                         data-values='@json(array_values($paymentMethods ?? []))'
                         class="chartDarkStyle w-full" 
                         style="height: 250px; width: 100%;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Rate - 1/2 width -->
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Collection Rate</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Overall collection performance</p>
                    </div>
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="window.exportChart('collectionRateRadialChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="window.exportChart('collectionRateRadialChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                <div class="flex justify-center">
                    <div id="collectionRateRadialChart" 
                         data-value='{{ $collectionRate ?? 85 }}'
                         class="chartDarkStyle w-full" 
                         style="height: 250px; width: 100%;">
                    </div>
                </div>
                <div class="text-center mt-2">
                    <span class="text-3xl font-bold text-gray-800 dark:text-white/90" id="collectionRateValue">{{ $collectionRate ?? 85 }}%</span>
                </div>
            </div>
        </div>

        <!-- Invoice Status - 1/4 width -->
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Invoice Status</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Breakdown</p>
                    </div>
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="window.exportChart('invoiceStatusPieChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="window.exportChart('invoiceStatusPieChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="window.exportChart('invoiceStatusPieChart', 'csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                <div class="flex justify-center">
                    <div id="invoiceStatusPieChart" 
                         data-labels='@json(array_keys($invoiceStatus ?? []))'
                         data-values='@json(array_values($invoiceStatus ?? []))'
                         class="chartDarkStyle w-full" 
                         style="height: 250px; width: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Revenue vs Expenses (1/2) + Performance Metrics (1/2) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue vs Expenses - 1/2 width -->
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Revenue vs Expenses</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Monthly comparison</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Period Filter for Revenue vs Expenses -->
                        <select data-chart="revenueExpenseLineChart" class="chart-period-filter rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <div x-data="{openDropDown: false}" class="relative h-fit">
                            <!-- ... existing dropdown ... -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800">
                <div class="p-6">
                    <div id="revenueExpenseLineChart" 
                        data-dates='@json(array_keys($monthlyRevenueExpense ?? []))'
                        data-revenue='@json(array_column($monthlyRevenueExpense ?? [], "revenue"))'
                        data-expenses='@json(array_column($monthlyRevenueExpense ?? [], "expense"))'
                        class="chartDarkStyle w-full" 
                        style="height: 300px; width: 100%;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics - 1/2 width -->
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Performance Metrics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Key performance indicators</p>
                    </div>
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="window.exportChart('performanceRadarChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="window.exportChart('performanceRadarChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800">
                <div class="p-6">
                    <div id="performanceRadarChart" 
                         data-labels='@json(array_keys($performanceMetrics ?? []))'
                         data-values='@json(array_values($performanceMetrics ?? []))'
                         class="chartDarkStyle w-full" 
                         style="height: 300px; width: 100%;">
                    </div>
                    @php
                        $metrics = $performanceMetrics ?? [];
                        $metricValues = array_values($metrics);
                        $avgScore = !empty($metricValues) ? round(array_sum($metricValues) / count($metricValues)) : 0;
                    @endphp
                    <div class="text-center mt-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Overall Performance</span>
                        <div class="text-3xl font-bold mt-1" style="color: var(--primary-color)">{{ $avgScore }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Accounts Receivable Aging - Full Width -->
    <div class="grid grid-cols-1">
        @php
            $agingLabels = isset($agingReport['labels']) ? $agingReport['labels'] : ['0-30 Days', '31-60 Days', '61-90 Days', '90+ Days'];
            $agingValues = isset($agingReport['values']) ? $agingReport['values'] : [0, 0, 0, 0];
        @endphp
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Accounts Receivable Aging</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Outstanding invoices by age</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <select data-chart="agingReportChart" class="chart-period-filter rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                            <option value="current">Current</option>
                            <option value="30">30 Days</option>
                            <option value="60">60 Days</option>
                            <option value="90" selected>90 Days</option>
                            <option value="180">180 Days</option>
                        </select>
                        <div x-data="{openDropDown: false}" class="relative h-fit">
                            <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'" class="transition-colors">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"/>
                                </svg>
                            </button>
                            <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                <button onclick="window.exportChart('agingReportChart', 'png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                                <button onclick="window.exportChart('agingReportChart', 'svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                                <button onclick="window.exportChart('agingReportChart', 'csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 p-6 dark:border-gray-800">
                <div id="agingReportChart" 
                     data-labels='@json($agingLabels)'
                     data-values='@json($agingValues)'
                     class="chartDarkStyle w-full" 
                     style="height: 280px; width: 100%;">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    /* Chart Dark Mode Styles */
    .chartDarkStyle .apexcharts-legend-text {
        color: #64748b !important;
    }
    .dark .chartDarkStyle .apexcharts-legend-text {
        color: #94a3b8 !important;
    }
    .chartDarkStyle .apexcharts-tooltip {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
    }
    .dark .chartDarkStyle .apexcharts-tooltip {
        background: #1f2937 !important;
        border: 1px solid #374151 !important;
    }
    .chartDarkStyle .apexcharts-tooltip-title {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    .dark .chartDarkStyle .apexcharts-tooltip-title {
        background: #111827 !important;
        border-bottom: 1px solid #374151 !important;
    }
</style>
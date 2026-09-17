{{-- resources/views/partials/dashboard/revenue-analytics.blade.php --}}
<div x-data="revenueAnalytics()" x-init="init()" class="space-y-6">
    
    <!-- Revenue Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                KES {{ number_format(array_sum($monthlyRevenue ?? []), 2) }}
            </p>
            <p class="text-xs text-green-600 dark:text-green-400">All time</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Average Monthly</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                KES {{ number_format(!empty($monthlyRevenue) ? array_sum($monthlyRevenue) / count($monthlyRevenue) : 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Last 12 months</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Payment Methods</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ count($paymentMethods ?? []) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Active payment channels</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Collection Rate</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ isset($stats['collection_rate']) ? number_format($stats['collection_rate'], 1) : '0' }}%
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Of total invoiced</p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">Monthly Revenue</h4>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Last 12 months</span>
                <button @click="toggleChartView" class="text-xs text-brand-500 hover:text-brand-600">
                    <span x-text="chartView === 'bar' ? 'Switch to Line' : 'Switch to Bar'"></span>
                </button>
            </div>
        </div>
        
        <div class="h-64">
            <canvas id="revenueChart" x-ref="revenueChart"></canvas>
        </div>
    </div>

    <!-- Payment Methods Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Methods Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Payment Methods</h4>
            <div class="h-64">
                <canvas id="paymentMethodsChart" x-ref="paymentMethodsChart"></canvas>
            </div>
        </div>
        
        <!-- Payment Methods Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Payment Method Breakdown</h4>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Method</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Amount</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php
                            $totalPaymentMethods = array_sum($paymentMethods ?? []);
                            $methodColors = [
                                'wallet' => '#465FFF',
                                'mpesa_stk' => '#10B981',
                                'mpesa_paybill' => '#059669',
                                'bank_transfer' => '#8B5CF6',
                                'cash' => '#F59E0B',
                                'manual_topup' => '#EF4444',
                                'message_paste' => '#3B82F6'
                            ];
                        @endphp
                        @forelse($paymentMethods ?? [] as $method => $amount)
                            <tr>
                                <td class="py-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" style="background: {{ $methodColors[$method] ?? '#6B7280' }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                    </span>
                                </td>
                                <td class="py-2 text-sm text-right text-gray-700 dark:text-gray-300">
                                    KES {{ number_format($amount, 2) }}
                                </td>
                                <td class="py-2 text-sm text-right text-gray-700 dark:text-gray-300">
                                    {{ $totalPaymentMethods > 0 ? number_format(($amount / $totalPaymentMethods) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No payment data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="py-2 text-sm font-semibold text-gray-800 dark:text-white/90">Total</td>
                            <td class="py-2 text-sm font-semibold text-right text-gray-800 dark:text-white/90">
                                KES {{ number_format($totalPaymentMethods, 2) }}
                            </td>
                            <td class="py-2 text-sm font-semibold text-right text-gray-800 dark:text-white/90">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Summary Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h4 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Monthly Revenue Summary</h4>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Month</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Revenue</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Change</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php
                        $monthlyData = $monthlyRevenue ?? [];
                        $previousMonth = null;
                    @endphp
                    @forelse($monthlyData as $month => $amount)
                        @php
                            $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
                            $change = $previousMonth !== null ? (($amount - $previousMonth) / max($previousMonth, 1)) * 100 : null;
                            $previousMonth = $amount;
                            $isPositive = $change !== null && $change >= 0;
                        @endphp
                        <tr>
                            <td class="py-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ $monthDate->format('M Y') }}
                            </td>
                            <td class="py-2 text-sm text-right text-gray-700 dark:text-gray-300">
                                KES {{ number_format($amount, 2) }}
                            </td>
                            <td class="py-2 text-sm text-right">
                                @if($change !== null)
                                    <span class="text-{{ $isPositive ? 'green' : 'red' }}-600">
                                        {{ $isPositive ? '+' : '' }}{{ number_format($change, 1) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-2 text-sm text-right">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                    {{ $amount > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $amount > 0 ? 'Collected' : 'No Revenue' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No revenue data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('revenueAnalytics', () => ({
        chartView: 'bar',
        revenueChartInstance: null,
        paymentChartInstance: null,
        
        init() {
            console.log('Revenue Analytics initialized');
            
            // Wait for DOM to be ready
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initRevenueChart();
                    this.initPaymentMethodsChart();
                }, 200);
            });
            
            // Watch for chart view changes
            this.$watch('chartView', () => {
                setTimeout(() => {
                    this.initRevenueChart();
                }, 100);
            });
        },
        
        toggleChartView() {
            this.chartView = this.chartView === 'bar' ? 'line' : 'bar';
        },
        
        getMethodColor(method) {
            const colors = {
                'wallet': '#465FFF',
                'mpesa_stk': '#10B981',
                'mpesa_paybill': '#059669',
                'bank_transfer': '#8B5CF6',
                'cash': '#F59E0B',
                'manual_topup': '#EF4444',
                'message_paste': '#3B82F6'
            };
            return colors[method] || '#6B7280';
        },
        
        initRevenueChart() {
            const canvas = this.$refs.revenueChart;
            if (!canvas) {
                console.warn('Revenue chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            if (this.revenueChartInstance) {
                this.revenueChartInstance.destroy();
                this.revenueChartInstance = null;
            }
            
            const monthlyData = @json($monthlyRevenue ?? []);
            const labels = Object.keys(monthlyData);
            const values = Object.values(monthlyData);
            
            console.log('Revenue chart data:', { labels, values });
            
            if (values.length === 0 || values.every(v => v === 0)) {
                this.revenueChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            data: [0],
                            backgroundColor: ['#E5E7EB'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                callbacks: { 
                                    label: function() { return 'No revenue data available'; } 
                                } 
                            }
                        }
                    }
                });
                return;
            }
            
            const isBar = this.chartView === 'bar';
            
            this.revenueChartInstance = new Chart(ctx, {
                type: isBar ? 'bar' : 'line',
                data: {
                    labels: labels.map(label => {
                        const date = new Date(label + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Revenue',
                        data: values,
                        backgroundColor: isBar ? 'rgba(70, 95, 255, 0.7)' : 'rgba(70, 95, 255, 0.1)',
                        borderColor: '#465FFF',
                        borderWidth: isBar ? 0 : 3,
                        pointBackgroundColor: '#465FFF',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: !isBar,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.raw.toLocaleString('en-KE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
            
            console.log('Revenue chart created successfully');
        },
        
        initPaymentMethodsChart() {
            const canvas = this.$refs.paymentMethodsChart;
            if (!canvas) {
                console.warn('Payment methods chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            if (this.paymentChartInstance) {
                this.paymentChartInstance.destroy();
                this.paymentChartInstance = null;
            }
            
            const paymentData = @json($paymentMethods ?? []);
            const labels = Object.keys(paymentData).map(key => 
                key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
            );
            const values = Object.values(paymentData);
            const colors = Object.keys(paymentData).map(key => this.getMethodColor(key));
            
            console.log('Payment methods data:', { labels, values, colors });
            
            if (values.length === 0 || values.every(v => v === 0)) {
                this.paymentChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            data: [1],
                            backgroundColor: ['#E5E7EB'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function() { return 'No payment data available'; }
                                }
                            }
                        }
                    }
                });
                return;
            }
            
            this.paymentChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return 'KES ' + context.parsed.toLocaleString('en-KE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
            
            console.log('Payment methods chart created successfully');
        }
    }));
});
</script>
{{-- resources/views/partials/dashboard/accountant.blade.php --}}
@extends('layouts.app')

@section('title', 'Accountant Dashboard')

@section('content')
<div x-data="accountantDashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-600 to-teal-600 p-6 shadow-lg">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="absolute -right-20 -top-20 h-64 w-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Accountant Dashboard</h2>
                                        <p class="text-green-100 mt-1" x-text="currentDate"></p>
                                        <div class="mt-2 flex items-center gap-2 text-green-100 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span>Company: <strong>{{ $company->name ?? 'N/A' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-green-100">Your Role</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                        Accountant
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        @include('partials.card.card-dashboard', ['cardData' => array_merge($stats, ['user_role' => 'accountant'])])

        <!-- CHARTS SECTION - SHOW IMMEDIATELY AFTER STATS CARDS -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Revenue Analytics</h3>
            @include('partials.chart.accountant-charts')
        </div>

        <!-- Pending Deposits Alert Banner -->
        <div x-show="pendingDepositsCount > 0" class="mt-6 mb-4">
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-400">
                                <span x-text="pendingDepositsCount"></span> Pending Deposit(s) Awaiting Approval
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-500">Manual top-ups and transaction messages need your verification</p>
                        </div>
                    </div>
                    <button @click="activeTab = 'pending'" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Review Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs - Tables Section -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-1">
                        <!-- Pending Deposits Tab -->
                        <button @click="activeTab = 'pending'" 
                            :class="activeTab === 'pending' ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400 border-b-2 -mb-px bg-yellow-50 dark:bg-yellow-900/10' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" 
                            class="px-4 py-2 text-sm font-medium transition-colors relative inline-flex items-center gap-2 rounded-t-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pending Deposits
                            <span x-show="pendingDepositsCount > 0" 
                                class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                <span x-text="pendingDepositsCount"></span>
                            </span>
                        </button>
                        
                        <!-- Recent Transactions Tab -->
                        <button @click="activeTab = 'transactions'" 
                            :class="activeTab === 'transactions' ? 'border-blue-500 text-blue-600 dark:text-blue-400 border-b-2 -mb-px bg-blue-50 dark:bg-blue-900/10' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" 
                            class="px-4 py-2 text-sm font-medium transition-colors relative inline-flex items-center gap-2 rounded-t-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            Recent Transactions
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <span x-text="transactionsCount"></span>
                            </span>
                        </button>
                        
                        <!-- Overdue Invoices Tab -->
                        <button @click="activeTab = 'overdue'" 
                            :class="activeTab === 'overdue' ? 'border-red-500 text-red-600 dark:text-red-400 border-b-2 -mb-px bg-red-50 dark:bg-red-900/10' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" 
                            class="px-4 py-2 text-sm font-medium transition-colors relative inline-flex items-center gap-2 rounded-t-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Overdue Invoices
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                <span x-text="overdueCount"></span>
                            </span>
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <!-- Pending Deposits Tab -->
                    <div x-show="activeTab === 'pending'">
                        @include('partials.table.table-transactions', [
                            'transactions' => $pendingTransactions ?? [],
                            'showActions' => true,
                            'emptyMessage' => 'No pending deposits awaiting approval'
                        ])
                    </div>
                    
                    <!-- Recent Transactions Tab -->
                    <div x-show="activeTab === 'transactions'">
                        @include('partials.table.table-transactions', [
                            'transactions' => $allTransactions ?? [],
                            'showActions' => false,
                            'emptyMessage' => 'No transactions found'
                        ])
                    </div>
                    
                    <!-- Overdue Invoices Tab -->
                    <div x-show="activeTab === 'overdue'">
                        @include('partials.table.table-invoices', [
                            'mappedInvoices' => collect($overdueInvoices ?? []), 
                            'mappedActiveTenancies' => collect()
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
function accountantDashboard() {
    return {
        activeTab: 'pending',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        // Counts - initialize with PHP data
        pendingDepositsCount: {{ count($pendingTransactions ?? []) }},
        transactionsCount: {{ count($allTransactions ?? []) }},
        overdueCount: {{ count($overdueInvoices ?? []) }},
        
        // Transaction Data - initialize with PHP data
        pendingTransactions: @json($pendingTransactions ?? []),
        allTransactions: @json($allTransactions ?? []),
        loading: false,
        
        // Chart initialization flag
        chartsInitialized: false,
        
        init() {
            console.log('Accountant Dashboard loaded');
            console.log('Pending Transactions:', this.pendingTransactions.length);
            console.log('All Transactions:', this.allTransactions.length);
            console.log('Pending Count:', this.pendingDepositsCount);
            console.log('Transactions Count:', this.transactionsCount);
            
            // Listen for refresh events from table component
            window.addEventListener('refresh-transactions', () => {
                this.loadAllTransactions();
                this.loadPendingTransactions();
            });
            
            // Listen for approve/reject events from table component
            window.addEventListener('approve-transaction', (event) => {
                this.approveTransaction(event.detail);
            });
            
            window.addEventListener('reject-transaction', (event) => {
                this.rejectTransaction(event.detail);
            });
            
            window.addEventListener('view-transaction', (event) => {
                console.log('View transaction:', event.detail);
                this.viewTransaction(event.detail);
            });
            
            // Initialize charts after a short delay to ensure DOM is ready
            this.initCharts();
        },
        
        initCharts() {
            // Check if charts already initialized
            if (this.chartsInitialized || window._chartsInitialized) {
                console.log('Charts already initialized, skipping');
                return;
            }
            
            // Check if ApexCharts is loaded
            if (typeof ApexCharts === 'undefined') {
                console.log('ApexCharts not loaded yet, waiting...');
                setTimeout(() => this.initCharts(), 500);
                return;
            }
            
            console.log('ApexCharts loaded, initializing accountant charts...');
            
            try {
                this.initializeAllCharts();
                this.chartsInitialized = true;
                window._chartsInitialized = true;
                console.log('All accountant charts initialized successfully!');
            } catch (error) {
                console.error('Error initializing charts:', error);
                // Retry after a delay
                setTimeout(() => this.initCharts(), 1000);
            }
        },
        
        initializeAllCharts() {
            // Get dynamic colors from CSS variables
            const primaryColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--primary-color').trim() || '#465fff';
            const secondaryColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--secondary-color').trim() || '#10B981';
            const warningColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--warning-color').trim() || '#F59E0B';
            const errorColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--error-color').trim() || '#EF4444';
            const successColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--success-color').trim() || '#10B981';
            
            // Helper functions
            const safeParseData = (data) => {
                if (!data || data === '[]' || data === '' || data === 'null' || data === 'undefined') return [];
                try {
                    const parsed = JSON.parse(data);
                    if (Array.isArray(parsed)) {
                        return parsed.map(item => {
                            if (typeof item === 'string' && !isNaN(item)) {
                                return parseFloat(item);
                            }
                            return item;
                        });
                    }
                    return parsed;
                } catch (e) {
                    console.warn('Error parsing data:', data, e);
                    return [];
                }
            };
            
            const calculateYAxisMax = (max) => {
                if (max === 0) return 5;
                const magnitude = Math.pow(10, Math.floor(Math.log10(max)));
                const normalized = max / magnitude;
                let step;
                if (normalized <= 1) step = 1;
                else if (normalized <= 2) step = 2;
                else if (normalized <= 5) step = 5;
                else step = 10;
                return Math.ceil(max / (step * magnitude)) * (step * magnitude);
            };
            
            // Store chart instances
            const chartInstances = {};
            
            // 1. Revenue Bar Chart
            this.initRevenueBarChart(chartInstances, primaryColor, safeParseData, calculateYAxisMax);
            
            // 2. Payment Methods Doughnut Chart
            this.initPaymentDoughnutChart(chartInstances, primaryColor, secondaryColor, warningColor, errorColor, safeParseData);
            
            // 3. Revenue vs Expenses Line Chart
            this.initRevenueExpenseLineChart(chartInstances, secondaryColor, errorColor, safeParseData);
            
            // 4. Invoice Status Pie Chart
            this.initInvoiceStatusPieChart(chartInstances, successColor, errorColor, warningColor, safeParseData);
            
            // 5. Collection Rate Radial Chart
            this.initCollectionRateRadialChart(chartInstances, successColor);
            
            // 6. Performance Radar Chart
            this.initPerformanceRadarChart(chartInstances, primaryColor, safeParseData);
            
            // 7. Aging Report Bar Chart
            this.initAgingReportChart(chartInstances, warningColor, safeParseData, calculateYAxisMax);
            
            // Store instances globally for export
            window._chartInstances = chartInstances;
        },
        
        initRevenueBarChart(instances, primaryColor, safeParseData, calculateYAxisMax) {
            const chartElement = document.querySelector("#revenueBarChart");
            if (!chartElement) return;
            
            let dates = safeParseData(chartElement.dataset.dates);
            let counts = safeParseData(chartElement.dataset.counts);
            
            if (!Array.isArray(counts)) counts = [];
            counts = counts.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Revenue raw data:', { dates, counts });
            
            // Filter out zero values
            const filteredData = dates.map((date, index) => ({
                date: date,
                count: counts[index] || 0
            })).filter(item => item.count > 0);
            
            dates = filteredData.map(item => item.date);
            counts = filteredData.map(item => item.count);
            
            console.log('Revenue filtered data:', { dates, counts });
            
            const hasValidData = dates.length > 0 && counts.length > 0 && counts.some(v => v > 0);
            if (!hasValidData) {
                chartElement.innerHTML = '<div class="text-center text-gray-500 py-10">No revenue data available</div>';
                return;
            }
            
            if (dates.length === 1) {
                dates = [dates[0], dates[0]];
                counts = [counts[0], counts[0]];
            }
            
            const maxCount = Math.max(...counts, 1);
            const yAxisMax = calculateYAxisMax(maxCount);
            
            chartElement.style.width = '100%';
            chartElement.style.height = '320px';
            
            const options = {
                series: [{ name: "Revenue", data: counts }],
                colors: [primaryColor],
                chart: {
                    fontFamily: "Outfit, sans-serif",
                    type: "bar",
                    height: 320,
                    width: '100%',
                    toolbar: { show: false },
                    animations: { enabled: true, speed: 800 },
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "39%",
                        borderRadius: 5,
                        borderRadiusApplication: "end",
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Outfit, sans-serif',
                        colors: ['#333'],
                        fontWeight: '600',
                    },
                    formatter: function(val) {
                        return 'KES ' + Number(val).toLocaleString();
                    }
                },
                xaxis: {
                    categories: dates,
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    labels: {
                        style: {
                            fontSize: "12px",
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                        },
                        rotate: -15,
                        trim: true,
                        hideOverlappingLabels: true,
                    }
                },
                yaxis: {
                    min: 0,
                    max: yAxisMax,
                    tickAmount: 5,
                    forceNiceScale: true,
                    decimalsInFloat: 0,
                    title: {
                        text: "Revenue (KES)",
                        style: {
                            fontSize: "13px",
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: 500,
                            color: '#64748b'
                        }
                    },
                    labels: {
                        formatter: (value) => Math.round(value).toString(),
                        style: {
                            fontSize: "12px",
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                        },
                        align: 'right',
                        padding: 5
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    crosshairs: {
                        show: true,
                        position: 'back',
                        stroke: { color: '#b6b6b6', width: 1, dashArray: 3 }
                    }
                },
                grid: {
                    show: true,
                    borderColor: "#e5e7eb",
                    strokeDashArray: 5,
                    position: "back",
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } },
                    padding: { left: 10, right: 10 }
                },
                legend: {
                    show: true,
                    position: "top",
                    horizontalAlign: "left",
                    fontFamily: "Outfit, sans-serif",
                },
                tooltip: {
                    y: { formatter: (val) => 'KES ' + Number(val).toLocaleString() },
                },
                states: {
                    hover: { filter: { type: 'lighten', value: 0.1 } }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 280 },
                        plotOptions: { bar: { columnWidth: "50%" } },
                        dataLabels: { style: { fontSize: '10px' } }
                    }
                }]
            };
            
            if (instances.revenue) instances.revenue.destroy();
            instances.revenue = new ApexCharts(chartElement, options);
            instances.revenue.render();
            console.log('Revenue chart rendered!');
            
            // Store update method
            instances.revenue.updateChartData = function(newDates, newCounts) {
                console.log('Updating Revenue chart:', { newDates, newCounts });
                
                let parsedCounts = Array.isArray(newCounts) ? newCounts.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0) : [];
                
                // Filter out zero values
                const filtered = newDates.map((date, index) => ({
                    date: date,
                    count: parsedCounts[index] || 0
                })).filter(item => item.count > 0);
                
                const filteredDates = filtered.map(item => item.date);
                const filteredCounts = filtered.map(item => item.count);
                
                if (filteredDates.length === 0) {
                    console.warn('No data after filtering');
                    return;
                }
                
                if (filteredDates.length === 1) {
                    filteredDates.push(filteredDates[0]);
                    filteredCounts.push(filteredCounts[0]);
                }
                
                const newMaxCount = Math.max(...filteredCounts, 1);
                const newYAxisMax = calculateYAxisMax(newMaxCount);
                
                this.updateOptions({
                    xaxis: { categories: filteredDates },
                    yaxis: { max: newYAxisMax }
                });
                this.updateSeries([{ name: "Revenue", data: filteredCounts }]);
            };
        },
        
        initPaymentDoughnutChart(instances, primaryColor, secondaryColor, warningColor, errorColor, safeParseData) {
            const element = document.getElementById('paymentDoughnutChart');
            if (!element) return;
            
            let labels = safeParseData(element.dataset.labels);
            let values = safeParseData(element.dataset.values);
            
            if (!Array.isArray(values)) values = [];
            values = values.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Payment data:', { labels, values });
            
            if (!labels.length || !values.length || !values.some(v => v > 0)) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No payment data available</div>';
                return;
            }
            
            const palette = [primaryColor, secondaryColor, warningColor, errorColor, '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'];
            element.style.width = '100%';
            element.style.height = '250px';
            
            const options = {
                series: values,
                colors: palette.slice(0, labels.length),
                chart: {
                    type: 'donut',
                    height: 250,
                    width: '100%',
                    toolbar: { show: false },
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                labels: labels,
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '11px',
                    fontFamily: 'Outfit, sans-serif',
                    labels: { colors: '#64748b' },
                    itemMargin: { horizontal: 5, vertical: 2 }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return 'KES ' + Number(opts.w.globals.series[opts.seriesIndex]).toLocaleString();
                    },
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Outfit, sans-serif',
                        fontWeight: '600'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '13px',
                                    fontFamily: 'Outfit, sans-serif',
                                    fontWeight: 600,
                                    color: '#64748b',
                                    formatter: function(w) {
                                        return 'KES ' + Number(w.globals.seriesTotals.reduce((a, b) => a + b, 0)).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: { formatter: function(val) { return 'KES ' + Number(val).toLocaleString(); } },
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif' }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 220 },
                        legend: { fontSize: '10px' },
                        dataLabels: { style: { fontSize: '10px' } }
                    }
                }]
            };
            
            if (instances.payment) instances.payment.destroy();
            instances.payment = new ApexCharts(element, options);
            instances.payment.render();
            console.log('Payment doughnut chart rendered!');
        },
        
        initRevenueExpenseLineChart(instances, secondaryColor, errorColor, safeParseData) {
            const element = document.getElementById('revenueExpenseLineChart');
            if (!element) return;
            
            let dates = safeParseData(element.dataset.dates);
            let revenueData = safeParseData(element.dataset.revenue);
            let expenseData = safeParseData(element.dataset.expenses);
            
            if (!Array.isArray(revenueData)) revenueData = [];
            if (!Array.isArray(expenseData)) expenseData = [];
            revenueData = revenueData.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            expenseData = expenseData.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Revenue vs Expense raw data:', { dates, revenueData, expenseData });
            
            // Check if we have valid data
            const hasValidData = dates.length > 0 && revenueData.length > 0 && 
                                (revenueData.some(v => v > 0) || expenseData.some(v => v > 0));
            
            if (!hasValidData) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No revenue/expense data available</div>';
                return;
            }
            
            // Filter out months where both revenue and expense are zero
            const filteredData = dates.map((date, index) => ({
                date: date,
                revenue: revenueData[index] || 0,
                expense: expenseData[index] || 0
            })).filter(item => item.revenue > 0 || item.expense > 0);
            
            dates = filteredData.map(item => item.date);
            revenueData = filteredData.map(item => item.revenue);
            expenseData = filteredData.map(item => item.expense);
            
            // If no data after filtering, show message
            if (dates.length === 0) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No revenue/expense data available</div>';
                return;
            }
            
            console.log('Revenue vs Expense filtered data:', { dates, revenueData, expenseData });
            
            element.style.width = '100%';
            element.style.height = '300px';
            
            const options = {
                series: [
                    { name: 'Revenue', data: revenueData, color: secondaryColor },
                    { name: 'Expenses', data: expenseData, color: errorColor }
                ],
                chart: {
                    type: 'area',
                    height: 300,
                    width: '100%',
                    toolbar: { show: false },
                    animations: { enabled: true, speed: 800 },
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        enabled: true,
                        opacityFrom: 0.55,
                        opacityTo: 0,
                    }
                },
                markers: {
                    size: 4,
                    colors: ['#fff'],
                    strokeColors: [secondaryColor, errorColor],
                    strokeWidth: 2,
                    hover: { size: 7, strokeWidth: 3 }
                },
                xaxis: {
                    categories: dates,
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                            fontWeight: 400
                        },
                        rotate: -15,
                        trim: true,
                        hideOverlappingLabels: true,
                        maxHeight: 40,
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    title: {
                        text: "Date",
                        style: {
                            fontSize: "12px",
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: 500,
                            color: '#64748b'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: "Amount (KES)",
                        style: {
                            fontSize: "12px",
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: 500,
                            color: '#64748b'
                        }
                    },
                    labels: {
                        formatter: function(value) {
                            if (value >= 1000000) {
                                return 'KES ' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return 'KES ' + (value / 1000).toFixed(1) + 'K';
                            }
                            return 'KES ' + Number(value).toLocaleString();
                        },
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                        },
                        offsetX: -5,
                        maxWidth: 80,
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    min: 0,
                    tickAmount: 5,
                    forceNiceScale: true,
                    decimalsInFloat: 0,
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 5,
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } },
                    padding: { left: 0, right: 10 }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '12px',
                    fontFamily: 'Outfit, sans-serif',
                    labels: { colors: '#64748b', useSeriesColors: false },
                    markers: { width: 12, height: 12, radius: 2, strokeWidth: 0 },
                    itemMargin: { horizontal: 10, vertical: 0 }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: function(val) { return 'KES ' + Number(val).toLocaleString(); } },
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif' }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 250 },
                        yaxis: { labels: { style: { fontSize: '10px' } } },
                        xaxis: { labels: { style: { fontSize: '10px' }, rotate: -30 } }
                    }
                }]
            };
            
            if (instances.revenueExpense) instances.revenueExpense.destroy();
            instances.revenueExpense = new ApexCharts(element, options);
            instances.revenueExpense.render();
            console.log('Revenue vs Expense chart rendered!');
            
            // Store update method for dynamic filtering
            instances.revenueExpense.updateChartData = function(newDates, newRevenue, newExpenses) {
                console.log('Updating Revenue vs Expense chart:', { newDates, newRevenue, newExpenses });
                
                // Parse and filter data
                let parsedRevenue = Array.isArray(newRevenue) ? newRevenue.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0) : [];
                let parsedExpenses = Array.isArray(newExpenses) ? newExpenses.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0) : [];
                
                // Filter out zero values
                const filtered = newDates.map((date, index) => ({
                    date: date,
                    revenue: parsedRevenue[index] || 0,
                    expense: parsedExpenses[index] || 0
                })).filter(item => item.revenue > 0 || item.expense > 0);
                
                const filteredDates = filtered.map(item => item.date);
                const filteredRevenue = filtered.map(item => item.revenue);
                const filteredExpense = filtered.map(item => item.expense);
                
                // Update chart
                this.updateOptions({
                    xaxis: { categories: filteredDates }
                });
                this.updateSeries([
                    { name: 'Revenue', data: filteredRevenue },
                    { name: 'Expenses', data: filteredExpense }
                ]);
            };
        },
        
        initInvoiceStatusPieChart(instances, successColor, errorColor, warningColor, safeParseData) {
            const element = document.getElementById('invoiceStatusPieChart');
            if (!element) return;
            
            let labels = safeParseData(element.dataset.labels);
            let values = safeParseData(element.dataset.values);
            
            if (!Array.isArray(values)) values = [];
            values = values.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Invoice status data:', { labels, values });
            
            if (!labels.length || !values.length || !values.some(v => v > 0)) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No invoice data available</div>';
                return;
            }
            
            const colorsMap = {
                'paid': successColor,
                'unpaid': errorColor,
                'partial': warningColor,
                'draft': '#94A3B8'
            };
            
            element.style.width = '100%';
            element.style.height = '250px';
            
            const options = {
                series: values,
                colors: labels.map(label => colorsMap[label.toLowerCase()] || '#94A3B8'),
                chart: {
                    type: 'pie',
                    height: 250,
                    width: '100%',
                    toolbar: { show: false },
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                labels: labels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '11px',
                    fontFamily: 'Outfit, sans-serif',
                    labels: { colors: '#64748b' },
                    itemMargin: { horizontal: 5, vertical: 2 }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return opts.w.globals.series[opts.seriesIndex];
                    },
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Outfit, sans-serif',
                        fontWeight: '600'
                    }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + ' invoices'; } },
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif' }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 220 },
                        legend: { fontSize: '10px' },
                        dataLabels: { style: { fontSize: '10px' } }
                    }
                }]
            };
            
            if (instances.invoiceStatus) instances.invoiceStatus.destroy();
            instances.invoiceStatus = new ApexCharts(element, options);
            instances.invoiceStatus.render();
            console.log('Invoice status chart rendered!');
        },
        
        initCollectionRateRadialChart(instances, successColor) {
            const element = document.getElementById('collectionRateRadialChart');
            if (!element) return;
            
            const value = parseFloat(element.dataset.value || '85');
            console.log('Collection rate value:', value);
            
            element.style.width = '100%';
            element.style.height = '250px';
            
            const options = {
                series: [value],
                colors: [successColor],
                chart: {
                    type: 'radialBar',
                    height: 250,
                    width: '100%',
                    toolbar: { show: false },
                    offsetY: -10,
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            margin: 0,
                            size: '70%',
                            background: 'transparent'
                        },
                        track: {
                            strokeWidth: '100%',
                            margin: 0,
                            background: '#e5e7eb'
                        },
                        dataLabels: {
                            name: {
                                show: true,
                                fontSize: '14px',
                                fontFamily: 'Outfit, sans-serif',
                                fontWeight: 500,
                                color: '#64748b',
                                offsetY: 10
                            },
                            value: { show: false }
                        }
                    }
                },
                grid: { padding: { top: 0, right: 0, bottom: 0, left: 0 } },
                labels: ['Collection Rate'],
                states: {
                    hover: { filter: { type: 'lighten', value: 0.15 } }
                }
            };
            
            if (instances.collectionRate) instances.collectionRate.destroy();
            instances.collectionRate = new ApexCharts(element, options);
            instances.collectionRate.render();
            console.log('Collection rate chart rendered!');
        },
        
        initPerformanceRadarChart(instances, primaryColor, safeParseData) {
            const element = document.getElementById('performanceRadarChart');
            if (!element) return;
            
            let labels = safeParseData(element.dataset.labels);
            let values = safeParseData(element.dataset.values);
            
            if (!Array.isArray(values)) values = [];
            values = values.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Performance data:', { labels, values });
            
            if (!labels.length || !values.length || !values.some(v => v > 0)) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No performance data available</div>';
                return;
            }
            
            element.style.width = '100%';
            element.style.height = '300px';
            
            const options = {
                series: [{ name: 'Performance', data: values }],
                colors: [primaryColor],
                chart: {
                    type: 'radar',
                    height: 300,
                    width: '100%',
                    toolbar: { show: false },
                    dropShadow: { enabled: true, blur: 3, left: 0, top: 1, opacity: 0.2 },
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                plotOptions: {
                    radar: {
                        size: 120,
                        polygons: {
                            strokeColors: '#e5e7eb',
                            connectorColors: '#e5e7eb',
                            fill: {
                                colors: [
                                    'rgba(var(--primary-rgb, 70, 95, 255), 0.05)',
                                    'rgba(var(--primary-rgb, 70, 95, 255), 0.02)'
                                ]
                            }
                        }
                    }
                },
                xaxis: {
                    categories: labels.map(label => label.replace(/_/g, ' ').toUpperCase()),
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                            fontWeight: 500
                        },
                        offsetY: 5
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) { return val + '%'; },
                        style: {
                            fontSize: '10px',
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#94a3b8'
                        }
                    }
                },
                markers: {
                    size: 5,
                    colors: ['#fff'],
                    strokeColor: primaryColor,
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                stroke: { width: 2, colors: [primaryColor] },
                fill: { opacity: 0.15, colors: [primaryColor] },
                tooltip: {
                    y: { formatter: function(val) { return val + '%'; } },
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif' }
                },
                legend: { show: false },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 250 },
                        plotOptions: { radar: { size: 100 } }
                    }
                }]
            };
            
            if (instances.performance) instances.performance.destroy();
            instances.performance = new ApexCharts(element, options);
            instances.performance.render();
            console.log('Performance radar chart rendered!');
        },
        
        initAgingReportChart(instances, warningColor, safeParseData, calculateYAxisMax) {
            const element = document.getElementById('agingReportChart');
            if (!element) return;
            
            let labels = safeParseData(element.dataset.labels);
            let values = safeParseData(element.dataset.values);
            
            if (!Array.isArray(values)) values = [];
            values = values.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0);
            
            console.log('Aging report raw data:', { labels, values });
            
            // Filter out zero values
            const filteredData = labels.map((label, index) => ({
                label: label,
                value: values[index] || 0
            })).filter(item => item.value > 0);
            
            labels = filteredData.map(item => item.label);
            values = filteredData.map(item => item.value);
            
            console.log('Aging report filtered data:', { labels, values });
            
            if (!labels.length || !values.length || !values.some(v => v > 0)) {
                element.innerHTML = '<div class="text-center text-gray-500 py-10">No aging data available</div>';
                return;
            }
            
            const maxValue = Math.max(...values, 1);
            const yAxisMax = calculateYAxisMax(maxValue);
            
            element.style.width = '100%';
            element.style.height = '280px';
            
            const options = {
                series: [{ name: 'Outstanding Amount', data: values }],
                colors: [warningColor],
                chart: {
                    type: 'bar',
                    height: 280,
                    width: '100%',
                    toolbar: { show: false },
                    animations: { enabled: true, speed: 800 },
                    fontFamily: "Outfit, sans-serif",
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '39%',
                        borderRadius: 5,
                        borderRadiusApplication: 'end',
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Outfit, sans-serif',
                        colors: ['#333'],
                        fontWeight: '600',
                    },
                    formatter: function(val) {
                        return 'KES ' + Number(val).toLocaleString();
                    }
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                        },
                        rotate: 0,
                        trim: true
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                },
                yaxis: {
                    min: 0,
                    max: yAxisMax,
                    tickAmount: 5,
                    forceNiceScale: true,
                    decimalsInFloat: 0,
                    title: {
                        text: "Amount (KES)",
                        style: {
                            fontSize: "13px",
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: 500,
                            color: '#64748b'
                        }
                    },
                    labels: {
                        formatter: (value) => Math.round(value).toString(),
                        style: {
                            fontSize: "12px",
                            fontFamily: 'Outfit, sans-serif',
                            colors: '#64748b',
                        },
                        align: 'right',
                        padding: 5
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    crosshairs: {
                        show: true,
                        position: 'back',
                        stroke: { color: '#b6b6b6', width: 1, dashArray: 3 }
                    }
                },
                grid: {
                    show: true,
                    borderColor: "#e5e7eb",
                    strokeDashArray: 5,
                    position: "back",
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } },
                    padding: { left: 10, right: 10 }
                },
                tooltip: {
                    y: { formatter: function(val) { return 'KES ' + Number(val).toLocaleString(); } },
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif' }
                },
                states: {
                    hover: { filter: { type: 'lighten', value: 0.1 } }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 250 },
                        plotOptions: { bar: { columnWidth: "50%" } },
                        dataLabels: { style: { fontSize: '10px' } }
                    }
                }]
            };
            
            if (instances.aging) instances.aging.destroy();
            instances.aging = new ApexCharts(element, options);
            instances.aging.render();
            console.log('Aging report chart rendered!');
            
            // Store update method
            instances.aging.updateChartData = function(newLabels, newValues) {
                console.log('Updating Aging chart:', { newLabels, newValues });
                
                let parsedValues = Array.isArray(newValues) ? newValues.map(v => typeof v === 'string' ? parseFloat(v) || 0 : v || 0) : [];
                
                // Filter out zero values
                const filtered = newLabels.map((label, index) => ({
                    label: label,
                    value: parsedValues[index] || 0
                })).filter(item => item.value > 0);
                
                const filteredLabels = filtered.map(item => item.label);
                const filteredValues = filtered.map(item => item.value);
                
                if (filteredLabels.length === 0) {
                    console.warn('No data after filtering');
                    return;
                }
                
                const newMaxValue = Math.max(...filteredValues, 1);
                const newYAxisMax = calculateYAxisMax(newMaxValue);
                
                this.updateOptions({
                    xaxis: { categories: filteredLabels },
                    yaxis: { max: newYAxisMax }
                });
                this.updateSeries([{ name: 'Outstanding Amount', data: filteredValues }]);
            };
        },
        
        async loadPendingTransactions() {
            this.loading = true;
            try {
                const response = await fetch('/api/wallet/pending-deposits', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.pendingTransactions = data.data || [];
                    this.pendingDepositsCount = this.pendingTransactions.length;
                    console.log('Pending updated:', this.pendingDepositsCount);
                }
            } catch (error) {
                console.error('Error loading pending deposits:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadAllTransactions() {
            this.loading = true;
            try {
                const response = await fetch('/api/wallet/transactions?per_page=1000&include_pending=true', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.allTransactions = data.data || [];
                    this.transactionsCount = this.allTransactions.length;
                    console.log('All transactions updated:', this.transactionsCount);
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async approveTransaction(tx) {
            if (!confirm(`Approve deposit of KES ${this.formatNumber(tx.amount)} for ${tx.tenant_name}?`)) return;
            
            try {
                const response = await fetch(`/api/wallet/approve-deposit/${tx.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    alert('Deposit approved successfully!');
                    await this.loadPendingTransactions();
                    await this.loadAllTransactions();
                    window.dispatchEvent(new CustomEvent('wallet-updated', {
                        detail: { refresh: true }
                    }));
                } else {
                    alert(data.error || 'Failed to approve deposit');
                }
            } catch (error) {
                console.error('Error approving deposit:', error);
                alert('An error occurred');
            }
        },
        
        async rejectTransaction(tx) {
            if (!confirm(`Reject deposit of KES ${this.formatNumber(tx.amount)} for ${tx.tenant_name}?`)) return;
            
            try {
                const response = await fetch(`/api/wallet/reject-deposit/${tx.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    alert('Deposit rejected successfully');
                    await this.loadPendingTransactions();
                    await this.loadAllTransactions();
                } else {
                    alert(data.error || 'Failed to reject deposit');
                }
            } catch (error) {
                console.error('Error rejecting deposit:', error);
                alert('An error occurred');
            }
        },
        
        viewTransaction(tx) {
            const details = [
                'Transaction Details',
                '━━━━━━━━━━━━━━━━━━━━━',
                `Amount: KES ${this.formatNumber(tx.amount)}`,
                `Tenant: ${tx.tenant_name || 'N/A'}`,
                `Unit: ${tx.tenant_unit || 'N/A'}`,
                `Method: ${tx.payment_method || 'N/A'}`,
                `Reference: ${tx.reference || 'N/A'}`,
                `Date: ${this.formatDate(tx.created_at)}`,
                `Status: ${tx.is_pending ? 'Pending Approval' : 'Completed'}`,
                tx.notes ? `Message: ${tx.notes}` : '',
                tx.bill_month ? `Bill Month: ${this.formatMonth(tx.bill_month)}` : '',
                tx.phone_number ? `Phone: ${tx.phone_number}` : '',
            ].filter(Boolean).join('\n');
            
            alert(details);
        },
        
        formatNumber(value) {
            return parseFloat(value || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        formatMonth(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', { year: 'numeric', month: 'long' });
        }
    };
}
</script>

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
@endsection
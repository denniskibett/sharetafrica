@props(['monthlyRevenue' => []])

<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Monthly Revenue Trend</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Last 12 months revenue overview</p>
    </div>
    
    <div class="h-80 w-full">
        <canvas id="revenueChart" x-init="initRevenueChart()" style="height: 100%; width: 100%;"></canvas>
    </div>
</div>

<script>
function initRevenueChart() {
    const ctx = document.getElementById('revenueChart')?.getContext('2d');
    if (!ctx) return;
    
    const monthlyRevenue = @json($monthlyRevenue);
    
    // Extract months and values
    const months = Object.keys(monthlyRevenue).map(month => {
        const date = new Date(month + '-01');
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    const values = Object.values(monthlyRevenue);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue (KES)',
                data: values,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#ffffff',
                pointHoverRadius: 6,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Revenue: KES ' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                        },
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        }
                    },
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Re-initialize chart when Alpine component loads
if (typeof Alpine !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        // Chart initialization will happen via x-init
    });
}
</script>
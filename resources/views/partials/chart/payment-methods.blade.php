@props(['paymentMethods' => []])

<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Payment Methods Distribution</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Revenue breakdown by payment method</p>
    </div>
    
    <div class="h-80 w-full">
        <canvas id="paymentMethodsChart" x-init="initPaymentMethodsChart()" style="height: 100%; width: 100%;"></canvas>
    </div>
</div>

<script>
function initPaymentMethodsChart() {
    const ctx = document.getElementById('paymentMethodsChart')?.getContext('2d');
    if (!ctx) return;
    
    const paymentMethods = @json($paymentMethods);
    
    // Define colors for different payment methods
    const colorMap = {
        'mpesa': '#00B2FF',
        'cash': '#10B981',
        'bank_transfer': '#6366F1',
        'card': '#F59E0B',
        'cheque': '#EF4444',
        'other': '#6B7280'
    };
    
    // Prepare data
    const labels = Object.keys(paymentMethods).map(method => {
        return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    });
    
    const values = Object.values(paymentMethods);
    const backgroundColors = Object.keys(paymentMethods).map(method => colorMap[method] || '#8B5CF6');
    
    // If no data, show empty state
    if (values.length === 0) {
        ctx.fillStyle = '#e5e7eb';
        ctx.font = '14px Inter, sans-serif';
        ctx.fillStyle = '#9ca3af';
        ctx.textAlign = 'center';
        ctx.fillText('No payment data available', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 12,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        },
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: KES ${value.toLocaleString()} (${percentage}%)`;
                        }
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
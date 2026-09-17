<!-- Water Reconciliation Modal -->
<div x-data="waterReconciliationModal()" x-init="init()" x-cloak>
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-99999 flex items-center justify-center p-5 overflow-y-auto">
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="close()"></div>
        <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <button @click="close()" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"/>
                </svg>
            </button>

            <div class="mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Reconcile Water Charges</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sync water meter readings with invoice water charges</p>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
                <span class="ml-3 text-gray-500">Reconciling water charges...</span>
            </div>

            <!-- Form -->
            <form x-show="!loading" @submit.prevent="reconcileWater()">
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Billing Month *</label>
                        <input type="month" x-model="billingMonth" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate (Optional)</label>
                        <select x-model="estateId" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Estates</option>
                            @foreach($estates ?? [] as $estate)
                                <option value="{{ is_array($estate) ? $estate['id'] : $estate->id }}">
                                    {{ is_array($estate) ? $estate['name'] : $estate->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            This will update all unpaid invoices for the selected month with water readings.
                            Invoices with no matching reading will be flagged.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button @click="close()" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">Cancel</button>
                    <button type="submit" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                        Reconcile Water
                    </button>
                </div>
            </form>

            <!-- Results -->
            <div x-show="results.length > 0 && !loading" class="mt-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Results</h4>
                <div class="max-h-60 overflow-y-auto space-y-1">
                    <template x-for="result in results" :key="result.invoice_id">
                        <div class="flex items-center justify-between text-sm p-2 rounded" 
                             :class="{
                                'bg-green-50 dark:bg-green-900/20': result.status === 'updated' || result.status === 'already_correct',
                                'bg-yellow-50 dark:bg-yellow-900/20': result.status === 'no_reading'
                             }">
                            <div>
                                <span x-text="'#' + result.invoice_id + ' - Unit ' + result.unit_number"></span>
                                <span x-show="result.status === 'updated'" class="text-green-600 dark:text-green-400 text-xs ml-2">✓ Updated</span>
                                <span x-show="result.status === 'already_correct'" class="text-blue-600 dark:text-blue-400 text-xs ml-2">✓ Already correct</span>
                                <span x-show="result.status === 'no_reading'" class="text-yellow-600 dark:text-yellow-400 text-xs ml-2">⚠️ No reading</span>
                            </div>
                            <div class="text-right">
                                <span x-show="result.status === 'updated'" x-text="'KES ' + result.old_charge + ' → KES ' + result.new_charge" class="text-sm"></span>
                                <span x-show="result.status === 'already_correct'" x-text="'KES ' + result.charge" class="text-sm"></span>
                                <span x-show="result.status === 'no_reading'" x-text="'KES ' + result.charge + ' (manual)'" class="text-sm text-yellow-600 dark:text-yellow-400"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // Water Reconciliation Modal Component
    Alpine.data('waterReconciliationModal', () => ({
        isOpen: false,
        loading: false,
        billingMonth: '',
        estateId: '',
        results: [],
        
        init() {
            // Set default billing month to current month
            const now = new Date();
            this.billingMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            
            // IMPORTANT: Expose this instance to the window object
            window.waterReconciliationModal = this;
            
            // Also expose a static open method for backward compatibility
            window.openWaterReconciliationModal = () => {
                this.open();
            };
            
            console.log('Water Reconciliation Modal initialized');
        },
        
        open() {
            this.isOpen = true;
            this.results = [];
            document.body.style.overflow = 'hidden';
        },
        
        close() {
            this.isOpen = false;
            this.results = [];
            document.body.style.overflow = '';
        },
        
async reconcileWater() {
    if (!this.billingMonth) {
        alert('Please select a billing month');
        return;
    }
    
    // Ensure the month is in Y-m format
    let month = this.billingMonth;
    if (month.match(/^\d{4}-\d{2}$/)) {
        // Already in correct format
    } else if (month.match(/^\d{4}-\d{2}-\d{2}$/)) {
        month = month.substring(0, 7);
    } else {
        alert('Invalid billing month format. Please select a valid month.');
        return;
    }
    
    this.loading = true;
    this.results = [];
    
    try {
        const response = await fetch('{{ route("invoices.bulk-reconcile") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                billing_month: month,
                estate_id: this.estateId || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.results = data.results || [];
            alert(data.message);
            
            // Refresh the table if the function exists
            if (window.invoiceTable && typeof window.invoiceTable.fetchInvoices === 'function') {
                await window.invoiceTable.fetchInvoices();
            }
            
            // Also try to refresh via Alpine component
            const tableComponent = document.querySelector('[x-data="invoiceTable()"]')?.__x?.$data;
            if (tableComponent && typeof tableComponent.fetchInvoices === 'function') {
                await tableComponent.fetchInvoices();
            }
        } else {
            alert(data.message || 'Reconciliation failed');
        }
    } catch (error) {
        console.error('Error reconciling water:', error);
        alert('Error reconciling water charges: ' + error.message);
    } finally {
        this.loading = false;
    }
}
    }));
});

// Also ensure the modal is available immediately after DOM load
document.addEventListener('DOMContentLoaded', () => {
    // If the modal hasn't been initialized yet, set a fallback
    if (!window.waterReconciliationModal) {
        window.waterReconciliationModal = {
            open: function() {
                // Try to find the Alpine component
                const modalEl = document.querySelector('[x-data="waterReconciliationModal()"]');
                if (modalEl && modalEl.__x) {
                    const data = modalEl.__x.$data;
                    if (data && typeof data.open === 'function') {
                        data.open();
                        return;
                    }
                }
                alert('Water Reconciliation Modal not ready. Please refresh the page.');
            }
        };
    }
});
</script>
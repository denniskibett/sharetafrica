{{-- resources/views/partials/card/card-invoices.blade.php --}}
@props([
    'totalDraft' => 0,
    'totalUnpaid' => 0,
    'totalPartial' => 0,
    'totalPaid' => 0,
    'unpaidCount' => 0,
    'paidCount' => 0,
    'overdueCount' => 0,
    'overdueAmount' => 0
])

<div x-data="invoiceCards({
    draft: {{ $totalDraft }},
    unpaid: {{ $totalUnpaid }},
    partial: {{ $totalPartial }},
    paid: {{ $totalPaid }},
    unpaidCount: {{ $unpaidCount }},
    paidCount: {{ $paidCount }},
    overdueCount: {{ $overdueCount }},
    overdueAmount: {{ $overdueAmount }}
})" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-semibold text-gray-800 dark:text-white/90">Invoice Overview</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Summary of all invoice statuses</p>
        </div>
        <div class="flex gap-3">
            <button @click="openBulkMissingModal()" class="bg-yellow-500 shadow-theme-xs hover:bg-yellow-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Generate Pending
            </button>
            <button @click="exportInvoices()" class="border border-gray-300 bg-white hover:bg-gray-50 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2.5V12.5M10 12.5L12.5 10M10 12.5L7.5 10M4.16667 15.8333H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.16667 17.5H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 rounded-xl border border-gray-200 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-y-0 dark:divide-gray-800 dark:border-gray-800">
        
        <!-- Draft -->
        <div class="border-b p-5 sm:border-r lg:border-b-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Draft</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                        KES <span x-text="formatMoney(draft)">0.00</span>
                    </h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Pending approval</span>
            </div>
        </div>

        <!-- Unpaid -->
        <div class="border-b p-5 sm:border-r lg:border-b-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Unpaid</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-500">
                        KES <span x-text="formatMoney(unpaid)">0.00</span>
                    </h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-500" x-text="formatNumber(unpaidCount)">0</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">invoices</span>
                <span x-show="overdueCount > 0" class="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-500/15 dark:text-orange-500 ml-auto" x-text="formatNumber(overdueCount) + ' overdue'"></span>
            </div>
        </div>

        <!-- Partially Paid -->
        <div class="border-b p-5 sm:border-r sm:border-b-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Partially Paid</p>
                    <h3 class="text-2xl font-bold text-yellow-600 dark:text-yellow-500">
                        KES <span x-text="formatMoney(partial)">0.00</span>
                    </h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Partial payments received</span>
            </div>
        </div>

        <!-- Paid -->
        <div class="p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Paid</p>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-500">
                        KES <span x-text="formatMoney(paid)">0.00</span>
                    </h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500" x-text="formatNumber(paidCount)">0</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">invoices</span>
            </div>
        </div>
    </div>

    <!-- Progress Bar - Collection Rate -->
    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Collection Rate</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="collectionRate + '%'">0%</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Overdue: KES <span x-text="formatMoney(overdueAmount)">0</span></span>
            </div>
        </div>
        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="h-2 rounded-full bg-green-500 transition-all duration-500" :style="{ width: collectionRate + '%' }"></div>
        </div>
    </div>
</div>

<script>
function invoiceCards(config) {
    return {
        draft: config.draft || 0,
        unpaid: config.unpaid || 0,
        partial: config.partial || 0,
        paid: config.paid || 0,
        unpaidCount: config.unpaidCount || 0,
        paidCount: config.paidCount || 0,
        overdueCount: config.overdueCount || 0,
        overdueAmount: config.overdueAmount || 0,
        
        get collectionRate() {
            const total = this.draft + this.unpaid + this.partial + this.paid;
            if (total === 0) return 0;
            return Math.round((this.paid / total) * 100);
        },
        
        formatMoney(value) {
            if (value === undefined || value === null) return '0.00';
            return Number(value).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        
        formatNumber(value) {
            if (value === undefined || value === null) return '0';
            return Number(value).toLocaleString('en-KE');
        },
        
        openBulkMissingModal() {
            if (typeof window.openBulkMissingModal === 'function') {
                window.openBulkMissingModal();
            } else if (typeof window.dispatchEvent === 'function') {
                window.dispatchEvent(new CustomEvent('open-bulk-invoice-modal'));
            } else {
                console.log('Bulk invoice modal not available');
            }
        },
        
        exportInvoices() {
            if (typeof window.exportInvoicesData === 'function') {
                window.exportInvoicesData();
            } else {
                // Fallback - trigger event
                window.dispatchEvent(new CustomEvent('export-invoices'));
            }
        }
    }
}
</script>
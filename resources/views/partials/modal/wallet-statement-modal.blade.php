{{-- resources/views/partials/modal/wallet-statement-modal.blade.php --}}
<div x-data="walletStatementModal()" x-init="init()" class="relative z-99999">
    {{-- Backdrop --}}
    <template x-if="isOpen">
        <div @click="closeModal"
            class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-999999"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"></div>
    </template>

    {{-- Modal Content --}}
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="fixed inset-0 flex items-center justify-center z-999999 p-4">
        
        <div class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Account Statement</h3>
                <button @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                {{-- Date Range Filter --}}
                <div class="mb-6 flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">From Date</label>
                        <input type="date" x-model="fromDate" @change="loadStatement"
                            class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">To Date</label>
                        <input type="date" x-model="toDate" @change="loadStatement"
                            class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
                    </div>
                    <button @click="exportStatement"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                        Export CSV
                    </button>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Opening Balance</p>
                        <p class="text-xl font-semibold text-gray-800 dark:text-white/90">
                            KES <span x-text="formatNumber(summary.opening_balance)"></span>
                        </p>
                    </div>
                    <div class="rounded-xl bg-green-50 p-4 dark:bg-green-900/20">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Credits</p>
                        <p class="text-xl font-semibold text-green-600">
                            KES <span x-text="formatNumber(summary.total_credits)"></span>
                        </p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4 dark:bg-red-900/20">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Debits</p>
                        <p class="text-xl font-semibold text-red-600">
                            KES <span x-text="formatNumber(summary.total_debits)"></span>
                        </p>
                    </div>
                    <div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-900/20">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Closing Balance</p>
                        <p class="text-xl font-semibold text-brand-600">
                            KES <span x-text="formatNumber(summary.closing_balance)"></span>
                        </p>
                    </div>
                </div>

                {{-- Loading State --}}
                <div x-show="loading" class="py-12 text-center">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
                    <p class="mt-2 text-sm text-gray-500">Loading statement...</p>
                </div>

                {{-- Transactions Table --}}
                <div x-show="!loading" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="tx in transactions" :key="tx.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3 text-sm text-gray-700" x-text="formatDate(tx.created_at)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700" x-text="tx.description || tx.type"></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="tx.type === 'deposit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                            x-text="tx.type === 'deposit' ? 'Credit' : 'Debit'">
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium"
                                        :class="tx.type === 'deposit' ? 'text-green-600' : 'text-red-600'">
                                        <span x-show="tx.type === 'deposit'">+</span>
                                        <span x-show="tx.type !== 'deposit'">-</span>
                                        KES <span x-text="formatNumber(tx.amount)"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="transactions.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    No transactions found for this period
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-200 dark:border-gray-800 p-6 flex justify-end">
                <button @click="closeModal"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('walletStatementModal', () => ({
        isOpen: false,
        loading: false,
        fromDate: '',
        toDate: '',
        transactions: [],
        summary: {
            opening_balance: 0,
            total_credits: 0,
            total_debits: 0,
            closing_balance: 0
        },

        init() {
            window.walletStatementModal = this;
            const today = new Date();
            this.toDate = today.toISOString().split('T')[0];
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            this.fromDate = firstDay.toISOString().split('T')[0];
        },

        async openModal() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
            await this.loadStatement();
        },

        closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        async loadStatement() {
            this.loading = true;
            try {
                const response = await fetch(`/api/wallet/statement?from_date=${this.fromDate}&to_date=${this.toDate}`);
                const data = await response.json();
                if (data.success) {
                    this.transactions = data.transactions;
                    this.summary = data.summary;
                }
            } catch (error) {
                console.error('Error loading statement:', error);
            } finally {
                this.loading = false;
            }
        },

        async exportStatement() {
            try {
                window.location.href = `/wallet/transactions/export?from_date=${this.fromDate}&to_date=${this.toDate}`;
            } catch (error) {
                console.error('Error exporting statement:', error);
            }
        },

        formatNumber(value) {
            return parseFloat(value || 0).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    }));
});
</script>
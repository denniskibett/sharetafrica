{{-- resources/views/partials/modal/wallet-payment-modal.blade.php --}}
<div x-data="walletPaymentModal()" x-init="init()" class="relative z-99999">
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

    {{-- Slide-over Content --}}
    <div x-show="isOpen"
        x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-cloak
        class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
        style="width: 42rem; max-width: calc(100% - 2rem);">

        <button @click="closeModal"
            class="group fixed right-3 top-3 z-9999999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
            </svg>
        </button>

        <div class="p-6 lg:p-8">
            <form @submit.prevent="submitPayment">
                @csrf
                <h4 class="mb-2 text-lg font-medium text-gray-800 dark:text-white/90">Pay Invoices</h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Use your wallet balance to pay pending invoices</p>

                {{-- Error Messages --}}
                <template x-if="formErrors.length > 0">
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                        <ul class="list-disc pl-5">
                            <template x-for="(error, index) in formErrors" :key="index">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                {{-- Debug/Error Logs Section --}}
                <div x-show="errorLogs.length > 0" class="mb-6 rounded-lg bg-yellow-50 p-4 text-sm dark:bg-yellow-900/20">
                    <div class="flex items-center justify-between mb-2">
                        <h5 class="font-semibold text-yellow-800 dark:text-yellow-400">🔍 Debug Information</h5>
                        <button @click="errorLogs = []" class="text-xs text-yellow-600 hover:text-yellow-800">Clear</button>
                    </div>
                    <div class="space-y-1 max-h-32 overflow-y-auto">
                        <template x-for="(log, index) in errorLogs" :key="log.timestamp + '_' + index">
                            <p class="text-xs font-mono" :class="log.type === 'error' ? 'text-red-600' : 'text-yellow-700'">
                                <span x-text="new Date(log.timestamp).toLocaleTimeString()"></span> - 
                                <span x-text="log.message"></span>
                            </p>
                        </template>
                    </div>
                </div>

                {{-- Success Message --}}
                <div x-show="successMessage" x-transition
                    class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400">
                    <p x-text="successMessage"></p>
                </div>

                {{-- Wallet Balance Summary --}}
                <div class="mb-6 p-4 bg-gradient-to-r from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20 rounded-xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Your Wallet Balance</p>
                            <p class="text-2xl font-bold text-brand-600 dark:text-brand-400" x-text="formattedWalletBalance"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Available for payment</p>
                            <button type="button" @click="refreshBalance" class="text-brand-500 hover:text-brand-600 text-sm flex items-center gap-1 mt-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Pending Invoices Section --}}
                <div class="mb-6">
                    <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-400">Select Invoice to Pay</label>
                    
                    <div x-show="loadingInvoices" class="text-center py-8">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
                        <p class="mt-2 text-sm text-gray-500">Loading your invoices...</p>
                    </div>

                    {{-- Network/Connection Error State --}}
                    <div x-show="!loadingInvoices && connectionError" class="text-center py-8 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.66 0 3-4.03 3-9s-1.34-9-3-9m0 18c-1.66 0-3-4.03-3-9s1.34-9 3-9"/>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-red-800 dark:text-red-400">Connection Error</p>
                        <p class="text-xs text-red-600 dark:text-red-300 mt-1" x-text="connectionErrorMessage"></p>
                        <button @click="fetchPendingInvoices" class="mt-3 text-sm text-brand-500 hover:text-brand-600">Try Again</button>
                    </div>

                    {{-- No Invoices State --}}
                    <div x-show="!loadingInvoices && !connectionError && pendingInvoices.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No pending invoices found.</p>
                        <p class="text-xs text-gray-400">All your invoices are paid up to date!</p>
                        <button @click="fetchPendingInvoices" class="mt-3 text-xs text-brand-500 hover:text-brand-600">Refresh</button>
                    </div>

                    {{-- Invoices List --}}
                    <div x-show="!loadingInvoices && !connectionError && pendingInvoices.length > 0" class="space-y-4">
                        <template x-for="invoice in pendingInvoices" :key="invoice.id">
                            <div @click="selectInvoice(invoice)"
                                class="cursor-pointer rounded-lg border-2 p-4 transition-all"
                                :class="selectedInvoiceId === invoice.id ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'">
                                
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h5 class="font-semibold text-gray-800 dark:text-white/90" x-text="invoice.invoice_number || 'INV-' + invoice.id"></h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Billing Month: <span x-text="formatMonth(invoice.billing_month)"></span>
                                        </p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="invoice.status === 'unpaid' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'"
                                        x-text="invoice.status === 'unpaid' ? 'Unpaid' : 'Partial'"></span>
                                </div>

                                {{-- Invoice Items --}}
                                <div class="space-y-2 mb-3">
                                    <template x-for="item in invoice.items" :key="item.id">
                                        <div class="flex justify-between items-center text-sm">
                                            <div class="flex-1">
                                                <span class="text-gray-600 dark:text-gray-400" x-text="item.description"></span>
                                                <span class="text-xs text-gray-400 ml-2" x-show="item.item_type" x-text="'(' + item.item_type + ')'"></span>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-medium text-gray-800 dark:text-white/90">
                                                    KES <span x-text="formatNumber(item.amount)"></span>
                                                </div>
                                                <div x-show="item.paid_amount > 0" class="text-xs text-green-600 dark:text-green-400">
                                                    Paid: KES <span x-text="formatNumber(item.paid_amount)"></span>
                                                </div>
                                                <div x-show="item.remaining_amount > 0 && item.remaining_amount < item.amount" class="text-xs text-orange-500">
                                                    Remaining: KES <span x-text="formatNumber(item.remaining_amount)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Invoice Summary --}}
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Total Amount:</span>
                                        <span class="font-semibold text-gray-800 dark:text-white/90">KES <span x-text="formatNumber(invoice.total_amount)"></span></span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm mt-1">
                                        <span class="text-gray-600 dark:text-gray-400">Paid:</span>
                                        <span class="text-green-600 dark:text-green-400">KES <span x-text="formatNumber(invoice.total_paid)"></span></span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm mt-1 font-semibold">
                                        <span class="text-gray-700 dark:text-gray-300">Remaining:</span>
                                        <span class="text-brand-600 dark:text-brand-400">KES <span x-text="formatNumber(invoice.remaining_amount)"></span></span>
                                    </div>
                                    <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                        <div class="bg-brand-500 h-1.5 rounded-full transition-all duration-300"
                                            :style="{ width: invoice.payment_percentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Payment Amount Section (shown when invoice selected) --}}
                <div x-show="selectedInvoice" class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Payment Amount</h5>
                    
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount to Pay *</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 dark:text-gray-400">KES</span>
                            </div>
                            <input type="number" step="0.01" x-model="paymentAmount" 
                                :max="maxPaymentAmount" :min="1"
                                @input="validatePaymentAmount"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-16 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-gray-500">Remaining balance on invoice: KES <span x-text="formatNumber(selectedInvoiceRemaining)"></span></p>
                            <button type="button" @click="paymentAmount = maxPaymentAmount" class="text-xs text-brand-500 hover:text-brand-600">
                                Pay Full Amount
                            </button>
                        </div>
                    </div>

                    {{-- Payment Summary --}}
                    <div class="mt-4 p-3 bg-white dark:bg-gray-900 rounded-lg">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Payment Amount:</span>
                                <span class="font-semibold text-gray-800 dark:text-white/90">KES <span x-text="formatNumber(paymentAmount || 0)"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Wallet Balance After:</span>
                                <span :class="paymentAmount > walletBalance ? 'text-red-600' : 'text-green-600'">
                                    KES <span x-text="formatNumber(walletBalance - (paymentAmount || 0))"></span>
                                </span>
                            </div>
                            <div x-show="paymentAmount > walletBalance" class="text-red-600 text-xs mt-1">
                                ⚠️ Insufficient balance. Please deposit more funds.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method Section --}}
                <div x-show="selectedInvoice" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-400 mb-2 flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Payment Method: Wallet Balance
                    </h5>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Funds will be deducted from your wallet and credited to the property management account.
                    </p>
                </div>

                {{-- Submit Button --}}
                <div class="flex gap-3">
                    <button type="button" @click="closeModal"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading || !isPaymentValid"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Pay Now</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing Payment...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('walletPaymentModal', () => ({
        // Modal State
        isOpen: false,
        loading: false,
        loadingInvoices: false,
        formErrors: [],
        successMessage: '',
        
        // Error Logging
        errorLogs: [],
        connectionError: false,
        connectionErrorMessage: '',
        
        // Wallet
        walletBalance: 0,
        
        // Invoices
        pendingInvoices: [],
        selectedInvoiceId: null,
        selectedInvoice: null,
        
        // Payment
        paymentAmount: '',
        
        // Computed
        get formattedWalletBalance() {
            return 'KES ' + this.formatNumber(this.walletBalance);
        },
        
        get selectedInvoiceRemaining() {
            return this.selectedInvoice ? this.selectedInvoice.remaining_amount : 0;
        },
        
        get maxPaymentAmount() {
            if (!this.selectedInvoice) return 0;
            return Math.min(this.selectedInvoice.remaining_amount, this.walletBalance);
        },
        
        get isPaymentValid() {
            if (!this.selectedInvoice) return false;
            if (!this.paymentAmount || parseFloat(this.paymentAmount) <= 0) return false;
            if (parseFloat(this.paymentAmount) > this.selectedInvoice.remaining_amount) return false;
            if (parseFloat(this.paymentAmount) > this.walletBalance) return false;
            return true;
        },
        
        // Logging Methods
        addErrorLog(message, type = 'error') {
            this.errorLogs.unshift({
                message: message,
                type: type,
                timestamp: Date.now()
            });
            // Keep only last 20 logs
            if (this.errorLogs.length > 20) this.errorLogs.pop();
            console.log(`[${type.toUpperCase()}]`, message);
        },
        
        // Methods
        init() {
            window.walletPaymentModal = this;
            this.addErrorLog('Payment modal initialized', 'info');
        },
        
        async openModal() {
            this.isOpen = true;
            this.resetForm();
            this.addErrorLog('Opening payment modal', 'info');
            await this.fetchWalletBalance();
            await this.fetchPendingInvoices();
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.isOpen = false;
            this.resetForm();
            document.body.style.overflow = '';
            this.addErrorLog('Payment modal closed', 'info');
        },
        
        resetForm() {
            this.formErrors = [];
            this.successMessage = '';
            this.selectedInvoiceId = null;
            this.selectedInvoice = null;
            this.paymentAmount = '';
            this.connectionError = false;
            this.connectionErrorMessage = '';
        },
        
        async fetchWalletBalance() {
            try {
                this.addErrorLog('Fetching wallet balance...', 'info');
                const response = await fetch('/api/wallet/balance', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    this.walletBalance = data.balance;
                    this.addErrorLog(`Wallet balance loaded: KES ${this.formatNumber(data.balance)}`, 'info');
                } else {
                    this.addErrorLog(`Failed to load balance: ${data.error || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Error fetching balance:', error);
                this.addErrorLog(`Balance fetch error: ${error.message}`, 'error');
                this.formErrors.push('Could not fetch wallet balance. Please refresh the page.');
            }
        },
        
        async fetchPendingInvoices() {
            this.loadingInvoices = true;
            this.connectionError = false;
            this.connectionErrorMessage = '';
            this.addErrorLog('Fetching pending invoices...', 'info');
            
            try {
                const response = await fetch('/api/wallet/pending-invoices', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                this.addErrorLog(`API Response Status: ${response.status} ${response.statusText}`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                this.addErrorLog(`API Response Data: ${JSON.stringify(data, null, 2).substring(0, 500)}`, 'info');
                
                if (data.success) {
                    this.pendingInvoices = data.invoices || [];
                    this.addErrorLog(`Successfully loaded ${this.pendingInvoices.length} pending invoice(s)`, 'info');
                    
                    if (this.pendingInvoices.length === 0) {
                        this.addErrorLog('No pending invoices found for this tenant', 'info');
                    } else {
                        this.pendingInvoices.forEach((inv, idx) => {
                            this.addErrorLog(`Invoice ${idx + 1}: #${inv.invoice_number} - KES ${inv.remaining_amount} (${inv.status})`, 'info');
                        });
                    }
                } else {
                    this.addErrorLog(`API returned error: ${data.error || 'Unknown error'}`, 'error');
                    this.connectionError = true;
                    this.connectionErrorMessage = data.error || 'Failed to load invoices. Please try again.';
                    this.pendingInvoices = [];
                }
            } catch (error) {
                console.error('Error fetching invoices:', error);
                this.addErrorLog(`Invoice fetch error: ${error.message}`, 'error');
                this.connectionError = true;
                this.connectionErrorMessage = error.message || 'Network error. Please check your connection.';
                this.pendingInvoices = [];
            } finally {
                this.loadingInvoices = false;
            }
        },
        
        selectInvoice(invoice) {
            this.addErrorLog(`Selected invoice: #${invoice.invoice_number} - Remaining: KES ${invoice.remaining_amount}`, 'info');
            
            if (this.selectedInvoiceId === invoice.id) {
                this.selectedInvoiceId = null;
                this.selectedInvoice = null;
                this.paymentAmount = '';
                this.addErrorLog('Invoice deselected', 'info');
            } else {
                this.selectedInvoiceId = invoice.id;
                this.selectedInvoice = invoice;
                this.paymentAmount = invoice.remaining_amount;
                this.addErrorLog(`Payment amount set to KES ${this.paymentAmount}`, 'info');
            }
            this.formErrors = [];
        },
        
        validatePaymentAmount() {
            if (!this.selectedInvoice) return;
            
            let amount = parseFloat(this.paymentAmount);
            
            if (isNaN(amount) || amount <= 0) {
                this.addErrorLog(`Invalid amount entered: ${this.paymentAmount}`, 'error');
                this.formErrors = ['Please enter a valid amount'];
                return;
            }
            
            if (amount > this.selectedInvoice.remaining_amount) {
                this.addErrorLog(`Amount KES ${amount} exceeds remaining balance KES ${this.selectedInvoice.remaining_amount}`, 'error');
                this.formErrors = [`Amount cannot exceed remaining balance of KES ${this.formatNumber(this.selectedInvoice.remaining_amount)}`];
                return;
            }
            
            if (amount > this.walletBalance) {
                this.addErrorLog(`Amount KES ${amount} exceeds wallet balance KES ${this.walletBalance}`, 'error');
                this.formErrors = [`Insufficient wallet balance. Available: KES ${this.formatNumber(this.walletBalance)}`];
                return;
            }
            
            this.addErrorLog(`Payment amount validated: KES ${amount}`, 'info');
            this.formErrors = [];
        },
        
        async refreshBalance() {
            this.addErrorLog('Manually refreshing balance...', 'info');
            await this.fetchWalletBalance();
            this.validatePaymentAmount();
        },
        
        async submitPayment() {
            this.formErrors = [];
            this.successMessage = '';
            
            if (!this.selectedInvoice) {
                this.addErrorLog('Payment attempted without selecting an invoice', 'error');
                this.formErrors.push('Please select an invoice to pay');
                return;
            }
            
            if (!this.isPaymentValid) {
                this.addErrorLog('Payment validation failed', 'error');
                this.validatePaymentAmount();
                return;
            }
            
            this.loading = true;
            this.addErrorLog(`Initiating payment of KES ${this.paymentAmount} for invoice #${this.selectedInvoice.invoice_number}`, 'info');
            
            try {
                const response = await fetch(`/api/wallet/pay-invoice/${this.selectedInvoice.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.paymentAmount)
                    })
                });
                
                this.addErrorLog(`Payment API Response Status: ${response.status}`, 'info');
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
                }
                
                const result = await response.json();
                this.addErrorLog(`Payment API Response: ${JSON.stringify(result, null, 2).substring(0, 500)}`, 'info');
                
                if (result.success) {
                    const paidAmount = parseFloat(this.paymentAmount);
                    const newBalance = result.data.new_balance;
                    
                    this.successMessage = result.message || `Successfully paid KES ${this.formatNumber(paidAmount)}!`;
                    this.addErrorLog(`Payment successful! New balance: KES ${newBalance}`, 'info');
                    
                    // Update wallet balance in this modal
                    if (newBalance !== undefined) {
                        this.walletBalance = newBalance;
                    }
                    
                    // CRITICAL: Force refresh the wallet component with the new balance
                    // Dispatch multiple events to ensure all components update
                    const updateEvent = new CustomEvent('wallet-updated', {
                        detail: { 
                            new_balance: newBalance,
                            transaction_id: result.data.transaction_id,
                            source: 'payment',
                            amount_paid: paidAmount,
                            previous_balance: this.walletBalance + paidAmount
                        }
                    });
                    
                    // Dispatch to both window and document
                    window.dispatchEvent(updateEvent);
                    document.dispatchEvent(updateEvent);
                    
                    // Also dispatch a specific event for the wallet component
                    const balanceEvent = new CustomEvent('wallet-balance-updated', {
                        detail: { 
                            balance: newBalance,
                            change: -paidAmount,
                            formatted: 'KES ' + this.formatNumber(newBalance)
                        }
                    });
                    window.dispatchEvent(balanceEvent);
                    document.dispatchEvent(balanceEvent);
                    
                    // Store in localStorage for cross-tab updates
                    try {
                        localStorage.setItem('wallet_last_update', JSON.stringify({
                            balance: newBalance,
                            timestamp: Date.now(),
                            change: -paidAmount
                        }));
                    } catch (e) {}
                    
                    // Refresh invoice list to update status
                    await this.fetchPendingInvoices();
                    
                    // Check if invoice is now fully paid or still has remaining
                    if (result.data.invoice) {
                        if (result.data.invoice.status === 'paid') {
                            this.addErrorLog(`Invoice #${this.selectedInvoice.invoice_number} is now fully paid`, 'info');
                            this.selectedInvoice = null;
                            this.selectedInvoiceId = null;
                            this.paymentAmount = '';
                        } else {
                            // Update the selected invoice with new data
                            const updatedInvoice = this.pendingInvoices.find(i => i.id === this.selectedInvoice.id);
                            if (updatedInvoice) {
                                this.selectedInvoice = updatedInvoice;
                                this.paymentAmount = updatedInvoice.remaining_amount;
                                this.addErrorLog(`Invoice updated. Remaining: KES ${updatedInvoice.remaining_amount}`, 'info');
                            }
                        }
                    }
                    
                    // Show success toast
                    this.showToast('success', `Payment of KES ${this.formatNumber(paidAmount)} completed! New balance: KES ${this.formatNumber(newBalance)}`);
                    
                    // Close modal after delay
                    setTimeout(() => {
                        this.closeModal();
                    }, 2000);
                    
                } else {
                    this.addErrorLog(`Payment failed: ${result.error || 'Unknown error'}`, 'error');
                    this.formErrors = [result.error || 'Payment failed. Please try again.'];
                }
            } catch (error) {
                console.error('Payment error:', error);
                this.addErrorLog(`Payment exception: ${error.message}`, 'error');
                this.formErrors = [`Error: ${error.message}`];
            } finally {
                this.loading = false;
            }
        },
        
        formatNumber(value) {
            if (!value && value !== 0) return '0.00';
            return parseFloat(value).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        
        formatMonth(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', { year: 'numeric', month: 'long' });
        },
        
        showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 z-50 rounded-lg px-4 py-2 text-white text-sm ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }));
});
</script>
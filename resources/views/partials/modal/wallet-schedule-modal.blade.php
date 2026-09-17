{{-- resources/views/partials/modal/wallet-schedule-modal.blade.php --}}
<div x-data="walletScheduleModal()" x-init="init()" class="relative z-99999">
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
            <form @submit.prevent="submitSchedule">
                @csrf
                <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Schedule Recurring Payment</h4>

                {{-- Error Messages --}}
                <template x-if="formErrors.length > 0">
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                        <ul class="list-disc pl-5">
                            <template x-for="error in formErrors" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                {{-- Bill Type Selection --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bill/Service Type *</label>
                    <div class="relative" x-data="{ open: false, selected: null }">
                        <button type="button" @click="open = !open"
                            class="flex h-11 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-normal text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <div class="flex items-center gap-3">
                                <template x-if="selected">
                                    <>
                                        <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="selected.bgClass">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                        <span x-text="selected.name"></span>
                                    </>
                                </template>
                                <template x-if="!selected">
                                    <span class="text-gray-400">Select bill to pay</span>
                                </template>
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="transition-transform" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79102 8.021L9.99935 13.2293L15.2077 8.021" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                            class="absolute left-0 z-10 mt-2 w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            <template x-for="bill in billTypes" :key="bill.id">
                                <button type="button" @click="selected = bill; selectedBillId = bill.id; open = false; loadBillDetails(bill)"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="bill.bgClass">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-medium" x-text="bill.name"></p>
                                        <p class="text-xs text-gray-400" x-text="bill.description"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Account Number (for the bill) --}}
                <div x-show="selectedBillId" class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Account Number *</label>
                    <input type="text" x-model="accountNumber" placeholder="Enter your account number"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Amount --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount per Payment *</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 dark:text-gray-400">KES</span>
                        </div>
                        <input type="number" step="0.01" x-model="amount" placeholder="0.00" required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-16 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                {{-- Frequency --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Frequency *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="frequency = 'weekly'"
                            class="py-2 text-sm rounded-lg border transition-all"
                            :class="frequency === 'weekly' ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' : 'border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-400'">
                            Weekly
                        </button>
                        <button type="button" @click="frequency = 'monthly'"
                            class="py-2 text-sm rounded-lg border transition-all"
                            :class="frequency === 'monthly' ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' : 'border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-400'">
                            Monthly
                        </button>
                        <button type="button" @click="frequency = 'quarterly'"
                            class="py-2 text-sm rounded-lg border transition-all"
                            :class="frequency === 'quarterly' ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' : 'border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-400'">
                            Quarterly
                        </button>
                        <button type="button" @click="frequency = 'annually'"
                            class="py-2 text-sm rounded-lg border transition-all"
                            :class="frequency === 'annually' ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' : 'border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-400'">
                            Annually
                        </button>
                    </div>
                </div>

                {{-- Start Date --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Start Date *</label>
                    <input type="date" x-model="startDate"
                        :min="minDate"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- End Options --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">End Option *</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="endOption" value="never" class="text-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Never (recur indefinitely)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="endOption" value="date" class="text-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">End on a specific date</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="endOption" value="occurrences" class="text-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">After a number of payments</span>
                        </label>
                    </div>
                </div>

                {{-- End Date (conditional) --}}
                <div x-show="endOption === 'date'" class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">End Date *</label>
                    <input type="date" x-model="endDate"
                        :min="startDate"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Number of Payments (conditional) --}}
                <div x-show="endOption === 'occurrences'" class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Number of Payments *</label>
                    <input type="number" x-model="occurrences" min="1" max="100"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Payment Method --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Source *</label>
                    <div class="relative" x-data="{ open: false, selected: 'Wallet Balance' }">
                        <button type="button" @click="open = !open"
                            class="flex h-11 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-normal text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <span x-text="selected"></span>
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="transition-transform" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79102 8.021L9.99935 13.2293L15.2077 8.021" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                            class="absolute left-0 z-10 mt-2 w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            <button type="button" @click="selected = 'Wallet Balance'; paymentSource = 'wallet'; open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <span>Wallet Balance</span>
                            </button>
                            <button type="button" @click="selected = 'Visa •••• 4983'; paymentSource = 'visa'; open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                <img src="{{ asset('src/images/payment-gateway/visa.png') }}" class="h-6 w-8" alt="">
                                <span>Visa •••• 4983</span>
                            </button>
                            <button type="button" @click="selected = 'Mastercard •••• 1234'; paymentSource = 'mastercard'; open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                <img src="{{ asset('src/images/payment-gateway/mastercard.png') }}" class="h-6 w-8" alt="">
                                <span>Mastercard •••• 1234</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Schedule Summary --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg" x-show="selectedBillId && amount">
                    <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Schedule Summary</h5>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bill/Service:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="selectedBill?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-medium text-gray-900 dark:text-white">KES <span x-text="formatNumber(amount)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Frequency:</span>
                            <span class="font-medium text-gray-900 dark:text-white capitalize" x-text="frequency"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Start Date:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="formatDate(startDate)"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                            <div class="flex justify-between font-semibold">
                                <span>First Payment:</span>
                                <span class="text-brand-600 dark:text-brand-400">KES <span x-text="formatNumber(amount)"></span> on <span x-text="formatDate(startDate)"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="closeModal"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading || !isFormValid"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50">
                        <span x-show="!loading">Schedule Payment</span>
                        <span x-show="loading">Scheduling...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('walletScheduleModal', () => ({
        isOpen: false,
        selectedBillId: null,
        selectedBill: null,
        accountNumber: '',
        amount: '',
        frequency: 'monthly',
        startDate: '',
        endOption: 'never',
        endDate: '',
        occurrences: '',
        paymentSource: 'wallet',
        formErrors: [],
        loading: false,

        billTypes: [
            { id: 1, name: 'Electricity Bill', description: 'KPLC Prepaid/Postpaid', bgClass: 'bg-yellow-500' },
            { id: 2, name: 'Water Bill', description: 'Nairobi Water Company', bgClass: 'bg-blue-500' },
            { id: 3, name: 'Internet Bill', description: 'Safaricom Home Fibre', bgClass: 'bg-purple-500' },
            { id: 4, name: 'Rent', description: 'Monthly Rent Payment', bgClass: 'bg-red-500' },
            { id: 5, name: 'Loan Repayment', description: 'Bank/Instalment Loan', bgClass: 'bg-indigo-500' },
            { id: 6, name: 'Insurance Premium', description: 'Monthly Insurance', bgClass: 'bg-pink-500' }
        ],

        get minDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },

        get isFormValid() {
            if (!this.selectedBillId) return false;
            if (!this.accountNumber) return false;
            if (!this.amount || parseFloat(this.amount) <= 0) return false;
            if (!this.startDate) return false;
            if (this.endOption === 'date' && !this.endDate) return false;
            if (this.endOption === 'occurrences' && (!this.occurrences || this.occurrences < 1)) return false;
            return true;
        },

        init() {
            window.walletScheduleModal = this;
            this.startDate = this.minDate;
        },

        openModal() {
            this.isOpen = true;
            this.resetForm();
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        resetForm() {
            this.selectedBillId = null;
            this.selectedBill = null;
            this.accountNumber = '';
            this.amount = '';
            this.frequency = 'monthly';
            this.startDate = this.minDate;
            this.endOption = 'never';
            this.endDate = '';
            this.occurrences = '';
            this.paymentSource = 'wallet';
            this.formErrors = [];
        },

        loadBillDetails(bill) {
            this.selectedBill = bill;
            // Could load preset amounts or account numbers here
        },

        formatNumber(value) {
            return parseFloat(value || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        },

        async submitSchedule() {
            this.formErrors = [];
            
            if (!this.isFormValid) {
                this.formErrors = ['Please fill in all required fields'];
                return;
            }

            this.loading = true;

            // Simulate API call - replace with actual endpoint
            setTimeout(() => {
                this.loading = false;
                this.closeModal();
                if (window.successModal) {
                    window.successModal.show('Success!', `Recurring payment scheduled successfully for ${this.selectedBill.name}.`);
                } else {
                    alert(`Recurring payment scheduled for ${this.selectedBill.name}`);
                }
            }, 1500);
        }
    }));
});
</script>
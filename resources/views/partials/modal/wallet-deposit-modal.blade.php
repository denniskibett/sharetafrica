{{-- resources/views/partials/modal/wallet-deposit-modal.blade.php --}}
<div x-data="walletDepositModal()" x-init="init()" class="relative z-99999">
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
            <form @submit.prevent="submitDeposit">
                @csrf
                <h4 class="mb-2 text-lg font-medium text-gray-800 dark:text-white/90">Deposit Funds</h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Add money to your wallet via M-Pesa, Bank Transfer, or Manual Top-up</p>

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

                {{-- Success Message --}}
                <div x-show="successMessage" x-transition
                    class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400">
                    <p x-text="successMessage"></p>
                </div>

                {{-- Toggle between Manual (STK Push), Message Paste, and Manual Top-up --}}
                <div class="mb-6 flex gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" @click="inputMode = 'manual'"
                        class="flex-1 py-2 text-sm font-medium rounded-md transition-all"
                        :class="inputMode === 'manual' ? 'bg-white dark:bg-gray-900 text-brand-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                        📱 STK Push / Manual
                    </button>
                    <button type="button" @click="inputMode = 'message'"
                        class="flex-1 py-2 text-sm font-medium rounded-md transition-all"
                        :class="inputMode === 'message' ? 'bg-white dark:bg-gray-900 text-brand-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                        📋 Paste Transaction Message
                    </button>
                    <button type="button" @click="inputMode = 'manual_topup'"
                        class="flex-1 py-2 text-sm font-medium rounded-md transition-all"
                        :class="inputMode === 'manual_topup' ? 'bg-white dark:bg-gray-900 text-brand-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                        💰 Manual Top-up
                    </button>
                </div>

                {{-- ==================== STK PUSH / MANUAL ENTRY MODE ==================== --}}
                <div x-show="inputMode === 'manual'" x-transition.duration.200ms>

                {{-- Tenant Account Details Section --}}
                <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <h5 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">👤 Your Account Details</h5>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Tenant Name:</span>
                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenantDetails.name || 'Loading...'"></p>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Unit Number:</span>
                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenantDetails.unit || '-'"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500 dark:text-gray-400">Wallet ID:</span>
                            <div class="flex items-center gap-2 mt-1">
                                <code class="rounded bg-gray-200 px-2 py-1 text-sm font-mono dark:bg-gray-700" x-text="tenantDetails.masked_wallet_number || tenantDetails.wallet_number"></code>
                                <button type="button" @click="copyToClipboard(tenantDetails.wallet_number, 'Wallet ID')"
                                    class="text-brand-500 hover:text-brand-600 text-xs flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                    {{-- Amount --}}
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount *</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 dark:text-gray-400">KES</span>
                            </div>
                            <input type="number" step="0.01" x-model="manualAmount" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-16 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Method *</label>
                        <div class="relative" x-data="{ open: false, selected: { name: 'M-Pesa STK Push', value: 'mpesa' } }">
                            <button type="button" @click="open = !open"
                                class="flex h-11 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-normal text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <div class="flex items-center gap-3">
                                    <img :src="getPaymentIcon(selected.value)" class="h-6 w-6" alt="">
                                    <span x-text="selected.name"></span>
                                </div>
                                <svg :class="open ? 'rotate-180' : ''" class="transition-transform" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79102 8.021L9.99935 13.2293L15.2077 8.021" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                class="absolute left-0 z-10 mt-2 w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <button type="button" @click="selected = { name: 'M-Pesa STK Push', value: 'mpesa' }; paymentMethod = 'mpesa'; open = false"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                    <img src="{{ asset('src/images/payment-gateway/mpesa.png') }}" class="h-6 w-6" alt="">
                                    M-Pesa STK Push
                                </button>
                                <button type="button" @click="selected = { name: 'Bank Transfer', value: 'bank' }; paymentMethod = 'bank'; open = false"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                    <img src="{{ asset('src/images/payment-gateway/bank.png') }}" class="h-6 w-6" alt="">
                                    Bank Transfer
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- M-Pesa Phone Number (conditional) --}}
                    <div x-show="paymentMethod === 'mpesa'" class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">M-Pesa Phone Number *</label>
                        <input type="tel" x-model="phoneNumber" placeholder="0712345678"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <p class="mt-1 text-xs text-gray-500">You will receive an STK push prompt on your phone to complete payment</p>
                    </div>

                    {{-- Reference (conditional) --}}
                    <div x-show="paymentMethod === 'bank'" class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Reference Number (Optional)</label>
                        <input type="text" x-model="reference" placeholder="e.g., TRX-12345"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    {{-- Bill Month --}}
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bill Month *</label>
                        <input type="month" x-model="billMonth" required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <p class="mt-1 text-xs text-gray-500">The month this payment is FOR (e.g., if paying June rent, select June 2026)</p>
                    </div>

                </div>

                {{-- ==================== PASTE TRANSACTION MESSAGE MODE ==================== --}}
                <div x-show="inputMode === 'message'" x-transition.duration.200ms>

                    {{-- Company Payment Details Section --}}
                    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        
                        {{-- Payment To - Wallet Section --}}
                        <div class="mb-4 pb-4 border-b border-blue-200 dark:border-blue-800">
                            <h5 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-400 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Payment To (My Wallet)
                            </h5>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-base font-bold text-gray-800 dark:text-white/90 font-mono" x-text="tenantDetails.wallet_number || 'Loading...'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Use this as account number when sending payment</div>
                                </div>
                                <button type="button" @click="copyToClipboard(tenantDetails.wallet_number, 'Wallet Number')"
                                    class="text-blue-600 hover:text-blue-700 text-xs flex items-center gap-1 px-2 py-1 rounded border border-blue-300 hover:bg-blue-100 dark:border-blue-600 dark:hover:bg-blue-900/30">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                        
                        {{-- Payment To - Estate & Company Section --}}
                        <div class="mb-4 pb-4 border-b border-blue-200 dark:border-blue-800">
                            <h5 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-400 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Payment To (Property)
                            </h5>
                            <div>
                                <div class="text-base font-bold text-gray-800 dark:text-white/90" x-text="companyDetails.estate_name || 'Loading...'"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="companyDetails.name"></div>
                            </div>
                        </div>
                        
                        {{-- Select Payment Method Section --}}
                        <h5 class="mb-3 text-sm font-semibold text-blue-800 dark:text-blue-400 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Select Payment Method
                        </h5>
                        
                        <div class="space-y-2">
                            <template x-for="pm in companyDetails.payment_methods" :key="pm.id">
                                <div @click="selectedPaymentMethod = pm; selectedPaymentMethodId = pm.id; updatePaymentMethodDetails(pm)" 
                                    class="cursor-pointer rounded-lg border-2 p-3 transition-all"
                                    :class="selectedPaymentMethodId === pm.id ? 'border-blue-500 bg-blue-100 dark:border-blue-400 dark:bg-blue-900/40' : 'border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800 hover:border-blue-300'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <!-- Icon based on payment method type -->
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center"
                                                :class="pm.type === 'mobile_money' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-blue-100 dark:bg-blue-900/30'">
                                                <!-- Mobile Money Icon -->
                                                <template x-if="pm.type === 'mobile_money'">
                                                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-3-3-3 3m6 6l-3 3-3-3"/>
                                                    </svg>
                                                </template>
                                                <!-- Bank Icon -->
                                                <template x-if="pm.type === 'bank'">
                                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m-7-5h14"/>
                                                    </svg>
                                                </template>
                                                <!-- Card Icon -->
                                                <template x-if="pm.type === 'card'">
                                                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                    </svg>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800 dark:text-white/90" x-text="pm.display_name || (pm.type === 'mobile_money' ? 'Mobile Money' : (pm.type === 'bank' ? 'Bank Transfer' : 'Card Payment'))"></div>
                                                <div class="text-xs text-gray-500" x-show="pm.type === 'mobile_money' && pm.paybill_number">
                                                    Paybill: <span class="font-mono" x-text="pm.paybill_number"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-show="pm.type === 'mobile_money' && pm.till_number">
                                                    Till Number: <span class="font-mono" x-text="pm.till_number"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-show="pm.type === 'mobile_money' && pm.account_number">
                                                    Account: <span class="font-mono" x-text="pm.account_number"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-show="pm.type === 'bank' && pm.account_number">
                                                    Account: <span class="font-mono" x-text="pm.account_number"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-show="pm.type === 'bank' && pm.bank_name">
                                                    Bank: <span x-text="pm.bank_name"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-show="pm.account_name">
                                                    Account Name: <span x-text="pm.account_name"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Selected Checkmark -->
                                        <div x-show="selectedPaymentMethodId === pm.id">
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Instructions -->
                                    <div x-show="pm.instructions" class="mt-2 pt-2 text-xs text-gray-500 border-t border-gray-200 dark:border-gray-700" x-text="pm.instructions"></div>
                                </div>
                            </template>
                            
                            <div x-show="companyDetails.payment_methods.length === 0" class="text-center text-gray-500 py-4">
                                No payment methods configured for this company.
                            </div>
                        </div>
                        
                        <!-- Hidden input to store selected payment method ID -->
                        <input type="hidden" x-model="selectedPaymentMethodId" name="payment_method_id">
                    </div>

                    {{-- Transaction Message Textarea --}}
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Transaction Message * 
                        </label>
                        <textarea
                            x-model="transactionMessage"
                            @input="parseTransactionMessage"
                            rows="5"
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 font-mono"
                            placeholder="Paste your M-Pesa or Bank transaction message here...&#10;&#10;Example:&#10;UF86O6Y6B8 Confirmed. KSH. 750 sent to DANAFF PROPERTY MANAGEMENT LIMITED (7263733) for account D16-05 via MySafaricom App on 08-06-2026 18:02."></textarea>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium">Tip:</span> Copy the entire transaction confirmation message from your phone and paste it here.
                        </p>
                    </div>

                    {{-- Parsed Details Preview --}}
                    <div x-show="hasParsedData" class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <h5 class="mb-3 text-sm font-semibold text-green-800 dark:text-green-400">📋 Extracted Payment Details</h5>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div x-show="parsedData.amount">
                                <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                                <p class="font-semibold text-green-700 dark:text-green-400" x-text="'KES ' + formatNumber(parsedData.amount)"></p>
                            </div>
                            <div x-show="parsedData.transaction_id">
                                <span class="text-gray-600 dark:text-gray-400">Transaction ID:</span>
                                <p class="font-mono text-sm text-gray-800 dark:text-white/90" x-text="parsedData.transaction_id"></p>
                            </div>
                            <div x-show="parsedData.date">
                                <span class="text-gray-600 dark:text-gray-400">Date:</span>
                                <p class="text-gray-800 dark:text-white/90" x-text="parsedData.date"></p>
                            </div>
                            <div x-show="parsedData.time">
                                <span class="text-gray-600 dark:text-gray-400">Time:</span>
                                <p class="text-gray-800 dark:text-white/90" x-text="parsedData.time"></p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-600 dark:text-gray-400">Bill Month:</span>
                                <p class="font-medium text-gray-800 dark:text-white/90" x-text="parsedData.bill_month || 'Not detected - please select below'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Validation Status --}}
                    <div x-show="parsedError && !hasParsedData" class="mb-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                        <div class="flex gap-2">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span x-text="parsedError"></span>
                        </div>
                    </div>

                    {{-- Manual Edit Section --}}
                    <div x-show="showManualEdit" class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h5 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">✏️ Edit fields below if extraction was incorrect</h5>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount *</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 dark:text-gray-400">KES</span>
                                    </div>
                                    <input type="number" step="0.01" x-model="manualAmountOverride" placeholder="0.00"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-16 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transaction ID</label>
                                <input type="text" x-model="manualTransactionIdOverride" placeholder="e.g., UF86O6Y6B8"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bill Month *</label>
                                <input type="month" x-model="manualBillMonthOverride"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone Number</label>
                                <input type="tel" x-model="manualPhoneNumberOverride" placeholder="0712345678"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ==================== MANUAL TOP-UP MODE (NEW - PENDING APPROVAL) ==================== --}}
                <div x-show="inputMode === 'manual_topup'" x-transition.duration.200ms>
                    <div class="mb-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                        <h5 class="mb-3 text-sm font-semibold text-yellow-800 dark:text-yellow-400 flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Manual Top-up (Pending Approval)
                        </h5>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                            ⚠️ Manual top-ups require accountant approval before funds are added to your wallet.
                            This is typically used for rent credits, adjustments, or cash payments.
                        </p>
                        
                        {{-- Amount --}}
                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount *</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 dark:text-gray-400">KES</span>
                                </div>
                                <input type="number" step="0.01" x-model="manualTopupAmount" placeholder="0.00"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-16 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                        </div>
                        
                        {{-- Notes --}}
                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes / Reason *</label>
                            <textarea x-model="manualTopupNotes" rows="3" placeholder="e.g., Rent credit for June, Cash payment received, Adjustment for overpayment, etc."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                            <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">This will be reviewed by the accountant before approval.</p>
                        </div>
                        
                        {{-- Bill Month --}}
                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bill Month *</label>
                            <input type="month" x-model="manualTopupBillMonth" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                </div>

                {{-- Deposit Summary --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Deposit Summary</h5>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Deposit Amount:</span>
                            <span class="text-lg font-bold text-brand-600 dark:text-brand-400">
                                KES <span x-text="formatNumber(getFinalAmount())"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90 capitalize" x-text="getFinalPaymentMethod()"></span>
                        </div>
                        <div x-show="getFinalTransactionId()" class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Transaction ID:</span>
                            <span class="text-sm font-mono text-gray-800 dark:text-white/90" x-text="getFinalTransactionId()"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Bill Month:</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="getFinalBillMonth()"></span>
                        </div>
                        <div x-show="inputMode === 'manual_topup' && manualTopupNotes" class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400">Notes:</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300 text-right max-w-[200px]" x-text="manualTopupNotes"></span>
                        </div>
                        <div x-show="inputMode === 'message' && !getFinalTransactionId()" class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending Approval</span>
                        </div>
                        <div x-show="inputMode === 'manual_topup'" class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending Accountant Approval</span>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex gap-3">
                    <button type="button" @click="closeModal"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading || !isFormValid"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Deposit Funds</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('walletDepositModal', () => ({
        // Modal State
        isOpen: false,
        loading: false,
        formErrors: [],
        successMessage: '',
        
        // Input Mode
        inputMode: 'manual',
        
        // Tenant & Company Details
        tenantDetails: {
            name: '',
            company: '',
            estate: '',
            unit: '',
            wallet_id: '',
            wallet_number: '',
            masked_wallet_number: ''
        },
        companyDetails: {
            name: '',
            estate_name: '',
            payment_methods: [],
            default_payment_method: null
        },
        
        // STK Push / Manual Entry Fields
        manualAmount: '',
        paymentMethod: 'mpesa',
        phoneNumber: '',
        reference: '',
        billMonth: new Date().toISOString().slice(0, 7),
        
        // Selected Payment Method
        selectedPaymentMethod: null,
        selectedPaymentMethodId: null,
        
        // Transaction Message Parsing
        transactionMessage: '',
        parsedData: {
            amount: null,
            transaction_id: null,
            date: null,
            time: null,
            sender: null,
            receiver: null,
            phone_number: null,
            paybill_number: null,
            account_number: null,
            new_balance: null,
            payment_method: null,
            bill_month: null
        },
        parsedError: null,
        showManualEdit: false,
        
        // Manual Override Fields
        manualAmountOverride: '',
        manualTransactionIdOverride: '',
        manualPhoneNumberOverride: '',
        manualBillMonthOverride: '',

        // NEW: Manual Top-up Fields
        manualTopupAmount: '',
        manualTopupNotes: '',
        manualTopupBillMonth: new Date().toISOString().slice(0, 7),

        // Computed Properties
        get hasParsedData() {
            return this.parsedData.amount !== null || 
                   this.parsedData.transaction_id !== null || 
                   this.parsedData.date !== null;
        },

        get isFormValid() {
            if (this.inputMode === 'manual') {
                if (!this.manualAmount || parseFloat(this.manualAmount) < 1) return false;
                if (this.paymentMethod === 'mpesa' && !this.phoneNumber) return false;
                if (this.paymentMethod === 'mpesa' && this.phoneNumber && !/^07[0-9]{8}$/.test(this.phoneNumber)) return false;
                if (!this.billMonth) return false;
                return true;
            } else if (this.inputMode === 'message') {
                const amount = this.getFinalAmount();
                const billMonth = this.getFinalBillMonth();
                return amount && parseFloat(amount) > 0 && billMonth;
            } else if (this.inputMode === 'manual_topup') {
                if (!this.manualTopupAmount || parseFloat(this.manualTopupAmount) < 1) return false;
                if (!this.manualTopupNotes || this.manualTopupNotes.trim() === '') return false;
                if (!this.manualTopupBillMonth) return false;
                return true;
            }
            return false;
        },

        // Methods
        init() {
            window.walletDepositModal = this;
            this.loadTenantAndCompanyDetails();
            this.manualBillMonthOverride = new Date().toISOString().slice(0, 7);
        },

        async loadTenantAndCompanyDetails() {
            try {
                const response = await fetch('/api/wallet/tenant-details');
                const data = await response.json();
                if (data.success) {
                    this.tenantDetails = {
                        name: data.tenant.name,
                        company: data.tenant.company,
                        estate: data.tenant.estate,
                        unit: data.tenant.unit,
                        wallet_id: data.tenant.wallet_id,
                        wallet_number: data.tenant.wallet_number,
                        masked_wallet_number: data.tenant.masked_wallet_number
                    };
                    this.companyDetails = {
                        name: data.company.name,
                        estate_name: data.company.estate_name,
                        payment_methods: data.company.payment_methods,
                        default_payment_method: data.company.default_payment_method
                    };
                }
            } catch (error) {
                console.error('Error loading tenant details:', error);
            }
        },

        updatePaymentMethodDetails(pm) {
            console.log('Selected payment method:', pm);
        },

        copyToClipboard(text, label) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const notification = document.createElement('div');
                notification.className = 'fixed bottom-4 right-4 z-50 rounded-lg bg-green-500 px-4 py-2 text-white text-sm';
                notification.innerText = `${label} copied!`;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            });
        },

        openModal() {
            this.isOpen = true;
            this.resetForm();
            this.loadTenantAndCompanyDetails();
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.isOpen = false;
            this.resetForm();
            document.body.style.overflow = '';
        },

        resetForm() {
            this.inputMode = 'manual';
            this.manualAmount = '';
            this.paymentMethod = 'mpesa';
            this.phoneNumber = '';
            this.reference = '';
            this.billMonth = new Date().toISOString().slice(0, 7);
            this.transactionMessage = '';
            this.selectedPaymentMethod = null;
            this.selectedPaymentMethodId = null;
            this.parsedData = {
                amount: null,
                transaction_id: null,
                date: null,
                time: null,
                sender: null,
                receiver: null,
                phone_number: null,
                paybill_number: null,
                account_number: null,
                new_balance: null,
                payment_method: null,
                bill_month: null
            };
            this.parsedError = null;
            this.showManualEdit = false;
            this.manualAmountOverride = '';
            this.manualTransactionIdOverride = '';
            this.manualPhoneNumberOverride = '';
            this.manualBillMonthOverride = new Date().toISOString().slice(0, 7);
            // Reset manual top-up fields
            this.manualTopupAmount = '';
            this.manualTopupNotes = '';
            this.manualTopupBillMonth = new Date().toISOString().slice(0, 7);
            this.formErrors = [];
            this.successMessage = '';
        },

        getPaymentIcon(method) {
            const icons = {
                'mpesa': '{{ asset("src/images/payment-gateway/mpesa.png") }}',
                'bank': '{{ asset("src/images/payment-gateway/bank.png") }}',
            };
            return icons[method] || icons['mpesa'];
        },

        formatNumber(value) {
            if (!value && value !== 0) return '0.00';
            return parseFloat(value).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        getFinalAmount() {
            if (this.inputMode === 'manual') {
                return this.manualAmount || 0;
            } else if (this.inputMode === 'message') {
                if (this.showManualEdit && this.manualAmountOverride) {
                    return this.manualAmountOverride;
                }
                return this.parsedData.amount || 0;
            } else if (this.inputMode === 'manual_topup') {
                return this.manualTopupAmount || 0;
            }
            return 0;
        },

        getFinalTransactionId() {
            if (this.inputMode === 'manual') {
                return this.reference;
            } else if (this.inputMode === 'message') {
                if (this.showManualEdit && this.manualTransactionIdOverride) {
                    return this.manualTransactionIdOverride;
                }
                return this.parsedData.transaction_id;
            } else if (this.inputMode === 'manual_topup') {
                return 'MANUAL-' + Date.now();
            }
            return null;
        },

        getFinalPaymentMethod() {
            if (this.inputMode === 'manual') {
                return this.paymentMethod;
            } else if (this.inputMode === 'message') {
                return 'message';
            } else if (this.inputMode === 'manual_topup') {
                return 'manual';
            }
            return 'mpesa';
        },

        getFinalPhoneNumber() {
            if (this.inputMode === 'manual') {
                return this.phoneNumber;
            } else if (this.inputMode === 'message') {
                if (this.showManualEdit && this.manualPhoneNumberOverride) {
                    return this.manualPhoneNumberOverride;
                }
                return this.parsedData.phone_number;
            }
            return null;
        },

        getFinalBillMonth() {
            if (this.inputMode === 'manual') {
                return this.billMonth;
            } else if (this.inputMode === 'message') {
                if (this.showManualEdit && this.manualBillMonthOverride) {
                    return this.manualBillMonthOverride;
                }
                if (this.parsedData.bill_month) {
                    return this.parsedData.bill_month;
                }
                if (this.parsedData.date) {
                    const [day, month, year] = this.parsedData.date.split('/');
                    if (month && year) {
                        return `${year}-${month.padStart(2, '0')}`;
                    }
                }
                return new Date().toISOString().slice(0, 7);
            } else if (this.inputMode === 'manual_topup') {
                return this.manualTopupBillMonth;
            }
            return new Date().toISOString().slice(0, 7);
        },

        parseTransactionMessage() {
            this.parsedError = null;
            const message = this.transactionMessage;
            
            if (!message || message.trim() === '') {
                this.resetParsedData();
                this.showManualEdit = false;
                return;
            }

            const newParsedData = {
                amount: null,
                transaction_id: null,
                date: null,
                time: null,
                sender: null,
                receiver: null,
                phone_number: null,
                paybill_number: null,
                account_number: null,
                new_balance: null,
                payment_method: 'mpesa',
                bill_month: null
            };

            // Transaction ID
            let txMatch = message.match(/^([A-Z0-9]{6,12})\s+Confirmed/i);
            if (!txMatch) txMatch = message.match(/(?:Transaction|Trx|Txn)(?:\s+ID)?:?\s*([A-Z0-9]{6,})/i);
            if (!txMatch) txMatch = message.match(/Code:\s*([A-Z0-9]{6,})/i);
            if (!txMatch) txMatch = message.match(/\b([A-Z0-9]{8,12})\b/);
            if (txMatch) newParsedData.transaction_id = txMatch[1];

            // Amount
            let amountMatch = message.match(/(?:KES|KSH|KSh|Ksh|Kenya Shillings?)[\s\.]*([\d,]+(?:\.\d{2})?)/i);
            if (!amountMatch) amountMatch = message.match(/Amount:\s*(?:KES|KSH)?\s*([\d,]+(?:\.\d{2})?)/i);
            if (!amountMatch) amountMatch = message.match(/(?:sent|received|paid|credited|debited)\s+(?:KES|KSH)?\s*([\d,]+(?:\.\d{2})?)/i);
            if (!amountMatch) amountMatch = message.match(/(?:KES|KSH)[\s\.]*([\d,]+(?:\.\d{2})?)/i);
            if (amountMatch) newParsedData.amount = parseFloat(amountMatch[1].replace(/,/g, ''));

            // Date & Bill Month
            let dateMatch = message.match(/(?:on\s+)?(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/);
            if (dateMatch) {
                const day = dateMatch[1].padStart(2, '0');
                const month = dateMatch[2].padStart(2, '0');
                const year = dateMatch[3];
                newParsedData.date = `${day}/${month}/${year}`;
                newParsedData.bill_month = `${year}-${month}`;
            }

            // Time
            let timeMatch = message.match(/(?:at\s+)?(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
            if (timeMatch) {
                let hour = timeMatch[1].padStart(2, '0');
                const minute = timeMatch[2];
                const ampm = timeMatch[3] || '';
                newParsedData.time = ampm ? `${hour}:${minute} ${ampm}` : `${hour}:${minute}`;
            }

            // Paybill
            let paybillMatch = message.match(/\((\d{5,7})\)/);
            if (!paybillMatch) paybillMatch = message.match(/(?:Paybill|Business No)[:\s]*(\d{5,7})/i);
            if (paybillMatch) newParsedData.paybill_number = paybillMatch[1];

            // Account Number
            let accountMatch = message.match(/(?:for account|account[:\s]+)([A-Z0-9\-\s]{4,20})/i);
            if (accountMatch) newParsedData.account_number = accountMatch[1].trim();

            // Phone Number
            let phoneMatch = message.match(/(?:from|sent from|phone|number)[:\s]*([+]?2547?\d{8}|07\d{8}|01\d{8})/i);
            if (!phoneMatch) phoneMatch = message.match(/\b(07\d{8}|01\d{8})\b/);
            if (phoneMatch) {
                let phone = phoneMatch[1];
                if (phone.startsWith('07') || phone.startsWith('01')) phone = '254' + phone.substring(1);
                newParsedData.phone_number = phone;
            }

            this.parsedData = newParsedData;

            // Pre-populate manual override fields
            if (this.parsedData.amount) this.manualAmountOverride = this.parsedData.amount;
            if (this.parsedData.transaction_id) this.manualTransactionIdOverride = this.parsedData.transaction_id;
            if (this.parsedData.phone_number) this.manualPhoneNumberOverride = this.parsedData.phone_number;
            if (this.parsedData.bill_month) this.manualBillMonthOverride = this.parsedData.bill_month;

            // Validate
            if (!this.parsedData.amount) {
                this.parsedError = '⚠️ Could not extract amount from this message. Please review and edit the fields below.';
                this.showManualEdit = true;
            } else if (this.parsedData.amount <= 0) {
                this.parsedError = '⚠️ Invalid amount extracted. Please check and correct below.';
                this.showManualEdit = true;
            } else {
                this.parsedError = null;
                this.showManualEdit = !this.parsedData.transaction_id || !this.parsedData.bill_month;
            }
        },

        resetParsedData() {
            this.parsedData = {
                amount: null,
                transaction_id: null,
                date: null,
                time: null,
                sender: null,
                receiver: null,
                phone_number: null,
                paybill_number: null,
                account_number: null,
                new_balance: null,
                payment_method: null,
                bill_month: null
            };
        },

        async submitDeposit() {
            this.formErrors = [];
            this.successMessage = '';
            
            const amount = this.getFinalAmount();
            if (!amount || parseFloat(amount) < 1) {
                this.formErrors.push('Please enter a valid amount (minimum KES 1.00)');
                return;
            }
            
            const billMonth = this.getFinalBillMonth();
            if (!billMonth) {
                this.formErrors.push('Please select the bill month');
                return;
            }

            // Additional validation for manual top-up
            if (this.inputMode === 'manual_topup') {
                if (!this.manualTopupNotes || this.manualTopupNotes.trim() === '') {
                    this.formErrors.push('Please provide notes/reason for manual top-up');
                    return;
                }
                if (parseFloat(this.manualTopupAmount) < 1) {
                    this.formErrors.push('Please enter a valid amount (minimum KES 1.00)');
                    return;
                }
            }

            this.loading = true;

            try {
                const paymentMethod = this.getFinalPaymentMethod();
                
                // Log what we're sending for debugging
                console.log('Submitting deposit with:', {
                    inputMode: this.inputMode,
                    paymentMethod: paymentMethod,
                    amount: amount,
                    billMonth: billMonth
                });
                
                const depositData = {
                    amount: parseFloat(amount),
                    payment_method: paymentMethod,  // This should be 'manual' for manual_topup
                    reference: this.getFinalTransactionId() || 'DEP-' + Date.now(),
                    phone_number: this.getFinalPhoneNumber() || null,
                    transaction_message: this.inputMode === 'message' ? this.transactionMessage : null,
                    bill_month: billMonth,
                };
                
                // Add notes for manual top-up
                if (this.inputMode === 'manual_topup') {
                    depositData.notes = this.manualTopupNotes;
                }

                console.log('Sending deposit data:', depositData);

                const response = await fetch('/api/wallet/deposit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(depositData)
                });
                
                const result = await response.json();
                console.log('Deposit response:', result);
                
                if (result.success) {
                    if (result.data.requires_approval) {
                        this.successMessage = `✅ Deposit request submitted! Amount KES ${this.formatNumber(depositData.amount)} is pending approval by accountant.`;
                        this.showToast('info', `KES ${this.formatNumber(depositData.amount)} pending approval`);
                    } else {
                        this.successMessage = `✅ Successfully deposited KES ${this.formatNumber(depositData.amount)} for ${billMonth}!`;
                        this.showToast('success', `KES ${this.formatNumber(depositData.amount)} deposited!`);
                    }
                    
                    const updateEvent = new CustomEvent('wallet-updated', {
                        detail: { 
                            new_balance: result.data.new_balance,
                            transaction_id: result.data.transaction_id,
                            amount: depositData.amount,
                            requires_approval: result.data.requires_approval
                        }
                    });
                    window.dispatchEvent(updateEvent);
                    document.dispatchEvent(updateEvent);
                    
                    localStorage.setItem('wallet_last_update', JSON.stringify({
                        balance: result.data.new_balance,
                        timestamp: Date.now()
                    }));
                    
                    setTimeout(() => {
                        this.closeModal();
                    }, 2500);
                } else {
                    this.formErrors = [result.error || result.message || 'Deposit failed. Please try again.'];
                }
            } catch (error) {
                console.error('Deposit error:', error);
                this.formErrors = ['An error occurred. Please try again.'];
            } finally {
                this.loading = false;
            }
        },

        showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 z-50 rounded-lg px-4 py-2 text-white text-sm ${type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : 'bg-red-500'}`;
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }));
});
</script>
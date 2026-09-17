{{-- resources/views/partials/modal/wallet-add-card-modal.blade.php --}}
<div x-data="walletAddCardModal()" x-init="init()" class="relative z-99999">
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

        {{-- Close Button --}}
        <button @click="closeModal"
            class="group fixed right-3 top-3 z-9999999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
            </svg>
        </button>

        <div class="p-6 lg:p-8">
            <form @submit.prevent="submitCard">
                @csrf
                <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Add New Card</h4>

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

                {{-- Card Type Selection --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Card Type *</label>
                    <div class="flex gap-4">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-all"
                            :class="cardType === 'visa' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'"
                            @click="cardType = 'visa'">
                            <input type="radio" x-model="cardType" value="visa" class="hidden">
                            <img src="{{ asset('src/images/payment-gateway/visa.png') }}" class="h-8 w-12" alt="Visa">
                            <span class="text-sm font-medium">Visa</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-all"
                            :class="cardType === 'mastercard' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'"
                            @click="cardType = 'mastercard'">
                            <input type="radio" x-model="cardType" value="mastercard" class="hidden">
                            <img src="{{ asset('src/images/payment-gateway/mastercard.png') }}" class="h-8 w-12" alt="Mastercard">
                            <span class="text-sm font-medium">Mastercard</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-all"
                            :class="cardType === 'amex' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'"
                            @click="cardType = 'amex'">
                            <input type="radio" x-model="cardType" value="amex" class="hidden">
                            <svg class="h-8 w-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="24" height="24" rx="4" fill="#006FCF"/>
                                <text x="12" y="16" text-anchor="middle" fill="white" font-size="8" font-weight="bold" font-family="Arial">AMEX</text>
                            </svg>
                            <span class="text-sm font-medium">American Express</span>
                        </label>
                    </div>
                </div>

                {{-- Card Number --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Card Number *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19"
                            @input="formatCardNumber"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm font-mono text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div class="mt-1 flex items-center gap-2">
                        <p class="text-xs text-gray-500">Enter 16-digit card number</p>
                        <div class="flex gap-1">
                            <img x-show="detectedCardType === 'visa'" src="{{ asset('src/images/payment-gateway/visa.png') }}" class="h-4" alt="">
                            <img x-show="detectedCardType === 'mastercard'" src="{{ asset('src/images/payment-gateway/mastercard.png') }}" class="h-4" alt="">
                            <span x-show="detectedCardType === 'amex'" class="text-[10px] font-bold text-blue-600">AMEX</span>
                        </div>
                    </div>
                </div>

                {{-- Cardholder Name --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cardholder Name *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="cardholderName" placeholder="JOHN DOE" 
                            @input="cardholderName = cardholderName.toUpperCase()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm uppercase text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Name as it appears on the card</p>
                </div>

                {{-- Expiry and CVC Row --}}
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expiry Date *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input type="text" x-model="expiry" placeholder="MM/YY" maxlength="5"
                                @input="formatExpiry"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm font-mono text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">CVC/CVV *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password" x-model="cvc" placeholder="123" maxlength="4"
                                @input="cvc = cvc.replace(/[^\d]/g, '')"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm font-mono text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">3-4 digit security code</p>
                    </div>
                </div>

                {{-- Set as Default --}}
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="setAsDefault" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Set as default payment method</span>
                    </label>
                </div>

                {{-- Card Preview --}}
                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-gray-800 to-gray-900 p-4 shadow-lg">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z"/>
                            </svg>
                            <span class="text-white/80 text-xs">Credit Card</span>
                        </div>
                        <div class="flex gap-2">
                            <img x-show="cardType === 'visa'" src="{{ asset('src/images/payment-gateway/visa.png') }}" class="h-6" alt="">
                            <img x-show="cardType === 'mastercard'" src="{{ asset('src/images/payment-gateway/mastercard.png') }}" class="h-6" alt="">
                            <svg x-show="cardType === 'amex'" class="h-6 w-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="24" height="24" rx="3" fill="#006FCF"/>
                                <text x="12" y="16" text-anchor="middle" fill="white" font-size="7" font-weight="bold" font-family="Arial">AMEX</text>
                            </svg>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-white/50 text-[10px] mb-1 tracking-wider">CARD NUMBER</p>
                        <p class="text-white font-mono text-base tracking-wider" x-text="displayCardNumber || '•••• •••• •••• ••••'"></p>
                    </div>
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-white/50 text-[10px] mb-1 tracking-wider">CARDHOLDER NAME</p>
                            <p class="text-white text-xs uppercase font-medium" x-text="cardholderName || 'YOUR NAME HERE'"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-white/50 text-[10px] mb-1 tracking-wider">EXPIRES</p>
                            <p class="text-white text-xs font-mono" x-text="expiry || 'MM/YY'"></p>
                        </div>
                    </div>
                </div>

                {{-- Billing Address Section --}}
                <div class="mb-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                    <h5 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Billing Address</h5>
                    
                    <div class="mb-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Street Address *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <input type="text" x-model="billingAddress" placeholder="Street address, P.O. Box"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Apartment/Suite (Optional)</label>
                        <input type="text" x-model="billingApt" placeholder="Apt, Suite, Unit, etc."
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">City *</label>
                            <input type="text" x-model="billingCity" placeholder="City"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Postal Code *</label>
                            <input type="text" x-model="billingPostalCode" placeholder="Postal code"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">State/Province</label>
                            <input type="text" x-model="billingState" placeholder="State/Province"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Country *</label>
                            <select x-model="billingCountry"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Country</option>
                                <option value="KE">Kenya</option>
                                <option value="US">United States</option>
                                <option value="UK">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                                <option value="NG">Nigeria</option>
                                <option value="ZA">South Africa</option>
                                <option value="GH">Ghana</option>
                                <option value="EG">Egypt</option>
                                <option value="MA">Morocco</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Save Card for Future Use --}}
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="saveForFuture" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Securely save this card for future payments</span>
                    </label>
                </div>

                {{-- Terms --}}
                <div class="mb-6 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" x-model="acceptTerms" class="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            I confirm that I am authorized to use this card and agree to the 
                            <a href="#" class="text-brand-500 hover:underline">Terms of Service</a> and 
                            <a href="#" class="text-brand-500 hover:underline">Privacy Policy</a>.
                            I understand that my card information will be stored securely for future transactions.
                        </span>
                    </label>
                </div>

                {{-- Security Notice --}}
                <div class="mb-6 flex items-center gap-2 rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Your card information is encrypted and secure. We use PCI-compliant payment processing.</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    <button type="button" @click="closeModal"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading || !isFormValid"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Add Card</span>
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
    Alpine.data('walletAddCardModal', () => ({
        // Modal State
        isOpen: false,
        loading: false,
        formErrors: [],
        successMessage: '',
        
        // Card Information
        cardType: 'visa',
        cardNumber: '',
        displayCardNumber: '',
        detectedCardType: null,
        cardholderName: '',
        expiry: '',
        cvc: '',
        setAsDefault: false,
        saveForFuture: true,
        
        // Billing Address
        billingAddress: '',
        billingApt: '',
        billingCity: '',
        billingState: '',
        billingPostalCode: '',
        billingCountry: 'KE',
        
        // Terms
        acceptTerms: false,

        // Computed Properties
        get isFormValid() {
            // Card number validation (16 digits for Visa/Mastercard, 15 for Amex)
            const cleanCardNumber = this.cardNumber.replace(/\s/g, '');
            const cardTypeValid = this.cardType === 'amex' ? cleanCardNumber.length === 15 : cleanCardNumber.length === 16;
            
            if (!cardTypeValid) return false;
            if (!this.cardholderName || this.cardholderName.length < 3) return false;
            if (!this.expiry || !this.expiry.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) return false;
            
            // Validate expiry is not in the past
            const [month, year] = this.expiry.split('/');
            const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
            const today = new Date();
            if (expiryDate < today) return false;
            
            // CVC validation (3 digits for Visa/Mastercard, 4 for Amex)
            const cvcValid = this.cardType === 'amex' ? this.cvc.length === 4 : this.cvc.length === 3;
            if (!cvcValid) return false;
            
            if (!this.billingAddress) return false;
            if (!this.billingCity) return false;
            if (!this.billingPostalCode) return false;
            if (!this.billingCountry) return false;
            if (!this.acceptTerms) return false;
            
            return true;
        },

        // Methods
        init() {
            window.walletAddCardModal = this;
        },

        openModal() {
            this.isOpen = true;
            this.resetForm();
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.isOpen = false;
            this.resetForm();
            document.body.style.overflow = '';
        },

        resetForm() {
            this.cardType = 'visa';
            this.cardNumber = '';
            this.displayCardNumber = '';
            this.detectedCardType = null;
            this.cardholderName = '';
            this.expiry = '';
            this.cvc = '';
            this.setAsDefault = false;
            this.saveForFuture = true;
            this.billingAddress = '';
            this.billingApt = '';
            this.billingCity = '';
            this.billingState = '';
            this.billingPostalCode = '';
            this.billingCountry = 'KE';
            this.acceptTerms = false;
            this.formErrors = [];
            this.successMessage = '';
        },

        formatCardNumber() {
            let value = this.cardNumber.replace(/\s/g, '').replace(/[^\d]/g, '');
            
            // Auto-detect card type
            if (value.length > 0) {
                if (value.startsWith('4')) {
                    this.detectedCardType = 'visa';
                } else if (value.startsWith('5') || (value.length >= 2 && parseInt(value.substring(0, 2)) >= 51 && parseInt(value.substring(0, 2)) <= 55)) {
                    this.detectedCardType = 'mastercard';
                } else if (value.startsWith('34') || value.startsWith('37')) {
                    this.detectedCardType = 'amex';
                } else {
                    this.detectedCardType = null;
                }
            } else {
                this.detectedCardType = null;
            }
            
            // Format based on card type
            let formatted = '';
            const isAmex = this.detectedCardType === 'amex';
            
            for (let i = 0; i < value.length && i < (isAmex ? 15 : 16); i++) {
                if (isAmex) {
                    if (i === 4 || i === 10) formatted += ' ';
                } else {
                    if (i > 0 && i % 4 === 0) formatted += ' ';
                }
                formatted += value[i];
            }
            
            this.cardNumber = formatted;
            
            // Create masked display
            const last4 = value.slice(-4);
            const masked = '•••• •••• •••• ' + last4;
            this.displayCardNumber = value.length > 0 ? (isAmex ? '•••• •••••• •' + last4 : masked) : '';
        },

        formatExpiry() {
            let value = this.expiry.replace(/[^\d]/g, '');
            if (value.length >= 2) {
                let month = parseInt(value.substring(0, 2));
                if (month > 12) month = 12;
                if (month < 1 && value.length > 1) month = 1;
                const monthStr = month.toString().padStart(2, '0');
                this.expiry = monthStr + '/' + value.substring(2, 4);
            } else {
                this.expiry = value;
            }
        },

        async submitCard() {
            this.formErrors = [];
            this.successMessage = '';
            
            if (!this.isFormValid) {
                if (!this.acceptTerms) {
                    this.formErrors.push('Please accept the Terms of Service');
                }
                if (!this.cardNumber.replace(/\s/g, '').match(/^\d+$/)) {
                    this.formErrors.push('Please enter a valid card number');
                }
                if (!this.expiry.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) {
                    this.formErrors.push('Please enter a valid expiry date (MM/YY)');
                }
                if (!this.billingAddress || !this.billingCity || !this.billingPostalCode) {
                    this.formErrors.push('Please complete the billing address');
                }
                if (this.formErrors.length === 0) {
                    this.formErrors.push('Please complete all required fields correctly');
                }
                return;
            }

            this.loading = true;

            // Simulate API call - Replace with actual endpoint
            try {
                // Simulate network delay
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // Create new card object
                const newCard = {
                    id: Date.now(),
                    cardType: this.detectedCardType || this.cardType,
                    cardNumber: this.cardNumber.slice(-4),
                    cardholderName: this.cardholderName,
                    expiry: this.expiry,
                    cvc: this.cvc,
                    active: true,
                    bgClass: this.cardType === 'visa' ? 'bg-gradient-to-r from-blue-900 to-indigo-900' : 'bg-gray-900 dark:bg-gray-950'
                };
                
                // Add to parent component's cards array if available
                if (window.walletComponent && window.walletComponent.cards) {
                    if (this.setAsDefault) {
                        // Remove default from other cards
                        window.walletComponent.cards = window.walletComponent.cards.map(c => ({ ...c, active: false }));
                        newCard.active = true;
                    }
                    window.walletComponent.cards.unshift(newCard);
                    
                    // Reinitialize swiper if needed
                    if (window.walletComponent.initSwiper) {
                        setTimeout(() => window.walletComponent.initSwiper(), 100);
                    }
                }
                
                this.successMessage = 'Card added successfully!';
                
                setTimeout(() => {
                    this.closeModal();
                    if (window.successModal) {
                        window.successModal.show('Success!', 'Your card has been added successfully.');
                    }
                }, 1000);
                
            } catch (error) {
                console.error('Error adding card:', error);
                this.formErrors = ['An error occurred while adding your card. Please try again.'];
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
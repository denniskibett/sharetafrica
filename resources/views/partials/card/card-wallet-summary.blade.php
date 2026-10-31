{{-- resources/views/partials/card/card-wallet-summary.blade.php --}}
<div x-data="walletComponent({
    initialBalance: {{ $walletData['balance'] ?? 0 }},
    initialFullWalletNumber: '{{ $walletData['full_wallet_number'] ?? '0000000000000000' }}',
    initialMaskedWalletNumber: '{{ $walletData['masked_wallet_number'] ?? '•••• •••• •••• 0000' }}',
    initialTotalSpent: {{ $walletData['total_spent'] ?? 0 }},
    initialRentSpent: {{ $walletData['rent_spent'] ?? 0 }},
    initialWaterSpent: {{ $walletData['water_spent'] ?? 0 }},
    initialElectricitySpent: {{ $walletData['electricity_spent'] ?? 0 }},
    initialBalanceChange: {{ $walletData['balance_change'] ?? 0 }},
    cards: {{ json_encode($walletData['cards'] ?? []) }}
})" class="space-y-5">
    {{-- Rest of the HTML remains the same, just remove the @json from inside --}}
    
    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        {{-- Wallet Column --}}
        <div class="xl:col-span-6 2xl:col-span-6">
            <div class="rounded-[18px] border border-gray-200 bg-gray-100 p-1.5 dark:border-gray-800 dark:bg-white/3">
                <div class="rounded-xl bg-white p-6 pb-8 dark:bg-gray-900">
                    {{-- Header with Currency and Date Selectors --}}
                    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row">
                        <div>
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">My Wallet</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Overview of your current funds</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            {{-- Currency Dropdown --}}
                            <div x-data="{ openCurrency: false }" @click.outside="openCurrency = false" class="relative">
                                <button @click="openCurrency = !openCurrency"
                                    class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-2.5 text-sm font-medium text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <span class="size-4 rounded-full inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-[10px] font-bold" x-text="selectedCurrency.charAt(0)"></span>
                                    <span x-text="selectedCurrency"></span>
                                    <svg :class="openCurrency ? 'rotate-180' : ''" width="18" height="18"
                                        viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.3125 7.21875L9 11.9063L13.6875 7.21875" stroke="currentColor"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <div x-show="openCurrency"
                                    class="absolute right-0 z-50 mt-1.5 w-44 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                    <template x-for="currency in currencies" :key="currency.code">
                                        <button @click="selectedCurrency = currency.code; openCurrency = false; fetchExchangeRates()"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5"
                                            :class="selectedCurrency === currency.code ? 'bg-gray-100 dark:bg-white/5 font-medium' : 'font-normal'">
                                            <span class="size-4 rounded-full inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-[10px] font-bold" x-text="currency.code.charAt(0)"></span>
                                            <span x-text="currency.code"></span>
                                            <span class="text-xs text-gray-400" x-text="currency.name"></span>
                                            <svg x-show="selectedCurrency === currency.code" class="ml-auto" width="14"
                                                height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.625 7L5.25 9.625L11.375 3.5" stroke="#465FFF"
                                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Period Dropdown --}}
                            <div x-data="{ openDate: false, selectedDate: 'This Month' }" @click.outside="openDate = false"
                                class="relative">
                                <button @click="openDate = !openDate"
                                    class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <span x-text="selectedDate"></span>
                                    <svg :class="openDate ? 'rotate-180' : ''" width="18" height="18"
                                        viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.3125 7.21875L9 11.9063L13.6875 7.21875" stroke="currentColor"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <div x-show="openDate"
                                    class="absolute right-0 z-50 mt-1.5 w-40 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                    <template x-for="period in periods" :key="period">
                                        <button @click="selectedDate = period; openDate = false; loadTransactionsByPeriod(period)"
                                            class="w-full rounded-lg px-2.5 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                            <span x-text="period"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Balance Section --}}
                    <div class="flex flex-col flex-wrap items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-6 dark:border-gray-800 sm:flex-row sm:items-end">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Balance</p>
                            <h3 class="mt-1 text-3xl font-medium text-gray-800 dark:text-white/90">
                                <span x-text="selectedCurrency"></span> 
                                <span x-text="formatNumber(convertedBalance)"></span>
                            </h3>
                            <p class="mt-2 flex items-center gap-1.5 text-sm font-normal text-gray-500 dark:text-gray-400">
                                <span class="text-success-600 flex items-center gap-1 font-medium">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.9974 2.66602L7.9974 13.3336M4 6.66334L7.99987 2.66602L12 6.66334"
                                            stroke="#039855" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-text="balanceChange"></span>%
                                </span>
                                from last month
                            </p>
                        </div>
                        <div class="ml-auto w-25 sm:w-[150px]">
                            <canvas id="wallet-spark-chart" width="150" height="40" class="w-full"></canvas>
                        </div>
                    </div>

                    {{-- Expense Breakdown --}}
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Spent</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                <span x-text="selectedCurrency"></span> 
                                <span x-text="formatNumber(convertedTotalSpent)"></span>
                            </p>
                            <span class="text-success-600 text-xs" x-show="balanceChange >= 0">+<span x-text="balanceChange"></span>%</span>
                            <span class="text-error-600 text-xs" x-show="balanceChange < 0"><span x-text="balanceChange"></span>%</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rent</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                <span x-text="selectedCurrency"></span> 
                                <span x-text="formatNumber(convertedRentSpent)"></span>
                            </p>
                            <span class="text-error-600 text-xs">-0%</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Water Bill</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                <span x-text="selectedCurrency"></span> 
                                <span x-text="formatNumber(convertedWaterSpent)"></span>
                            </p>
                            <span class="text-error-600 text-xs">+5%</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Electricity</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                <span x-text="selectedCurrency"></span> 
                                <span x-text="formatNumber(convertedElectricitySpent)"></span>
                            </p>
                            <span class="text-success-600 text-xs">-12%</span>
                        </div>
                    </div>

                   

                    {{-- Account Details Row --}}
                    <div class="mt-6 flex flex-col gap-2 border-t border-dashed border-gray-200 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                        <p class="shrink-0 text-sm text-gray-700 dark:text-gray-400">Primary Account:</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="shrink-0 text-lg font-medium text-gray-700 dark:text-gray-400 font-mono tracking-wide">
                                <span x-text="maskedWalletNumber || '•••• •••• •••• 0000'"></span>
                            </p>
                            <div x-data="{ copied: false }" class="shrink-0">
                                <button @click="copied = true; navigator.clipboard.writeText(fullWalletNumber); setTimeout(() => copied = false, 2000)"
                                    class="relative flex h-8 w-9 items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg x-show="!copied" width="20" height="20" fill="none" viewBox="0 0 20 20">
                                        <path d="M14.1559 14.1628H7.08724C6.39688 14.1628 5.83724 13.6032 5.83724 12.9128V5.84416M14.1559 14.1628V15.4161C14.1559 16.1065 13.5963 16.6661 12.9059 16.6661H4.58398C3.89363 16.6661 3.33398 16.1065 3.33398 15.4161V7.09416C3.33398 6.4038 3.89363 5.84416 4.58398 5.84416H5.83724M14.1559 14.1628H15.4144C16.1048 14.1628 16.6644 13.6032 16.6644 12.9128V4.58398C16.6644 3.89363 16.1048 3.33398 15.4144 3.33398H7.08724C6.39688 3.33398 5.83724 3.89363 5.83724 4.58398V5.84416" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg x-show="copied" class="text-success-500" width="20" height="20" fill="none" viewBox="0 0 20 20">
                                        <path d="M16.6668 5L7.50016 14.1667L3.3335 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            <button @click="openStatementModal"
                                class="flex h-8 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                See Details
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-3 px-3.5 pt-5 pb-4">
                    <button @click="openDepositModal"
                        class="bg-brand-500 hover:bg-brand-600 flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.9968 5.00356L5 15.0003M14.9977 12.4949L14.9953 5.00214L7.49917 4.99951" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Deposit
                    </button>
                    <button @click="openPaymentModal"
                        class="flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.00095 14.9963L14.9977 4.99954M5 7.50539L5.00238 14.9981L12.4985 15.0007"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Pay
                    </button>
                    <button @click="openScheduleModal"
                        class="flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Schedule
                    </button>
                    <button @click="openStatementModal"
                        class="flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.5 5.83333L10 10L17.5 5.83333M2.5 14.1667L10 18.3333L17.5 14.1667M2.5 10L10 14.1667L17.5 10"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Statement
                    </button>
                </div>
            </div>
        </div>

        {{-- Credit Cards Column --}}
        <div class="xl:col-span-6 2xl:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Cards</h3>
                    <button @click="openAddCardModal"
                        class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Add Card
                    </button>
                </div>

                {{-- Card Slider - Single card at a time --}}
                <div class="swiper my-cards-slider relative">
                    <div class="swiper-wrapper">
                        <template x-for="card in cards" :key="card.id">
                            <div class="swiper-slide">
                                <div class="relative flex flex-col gap-7 overflow-hidden rounded-[14px] p-6 min-h-[280px]"
                                    :class="card.bgClass">
                                    
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-white/10 to-transparent rounded-bl-[60px]"></div>
                                    
                                    {{-- Card Header --}}
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                                                <template x-if="card.cardType === 'mpesa'">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="12" cy="12" r="10" fill="#4CAF50"/>
                                                        <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                </template>
                                                <template x-if="card.cardType === 'airtel'">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="12" cy="12" r="10" fill="#E91E63"/>
                                                        <text x="12" y="16" text-anchor="middle" fill="white" font-size="10" font-weight="bold">A</text>
                                                    </svg>
                                                </template>
                                            </div>
                                            
                                            <span class="flex h-6 shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="card.active ? 'bg-success-500/10 text-success-500' : 'bg-white/10 text-gray-400'"
                                                x-text="card.active ? 'Active' : 'Inactive'"></span>
                                        </div>
                                        <div>
                                            <span class="text-white text-xs font-bold" x-text="card.display_name || (card.cardType === 'mpesa' ? 'M-Pesa' : 'Airtel Money')"></span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-base font-normal text-white" x-text="card.cardholderName"></h3>
                                    </div>
                                    
                                    <div class="flex justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="text-xs text-white/60">Phone Number</p>
                                            <p class="text-base font-normal text-white font-mono tracking-wide" x-text="card.cardNumber ? '•••• •••• •••• ' + card.cardNumber : 'Not set'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-white/60">EXP</p>
                                            <p class="text-base font-normal text-white font-mono" x-text="card.expiry || '--'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-white/60">CVC</p>
                                            <p class="text-base font-normal text-white font-mono" x-text="card.cvc || '--'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between border-b border-dashed border-gray-200 pt-4 pb-6 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Virtual Card</h3>
                    <div class="flex gap-1.5">
                        <button id="card-slider-prev"
                            class="flex h-8 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.58464 3.83325L5.41797 7.99992L9.58464 12.1666" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button id="card-slider-next"
                            class="flex h-8 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-900">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.91797 12.1666L10.0846 7.99992L5.91797 3.83325" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions Table - Full Width --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/3">
        <div class="mb-4 flex flex-col gap-5 px-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Transactions</h3>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""/>
                        </svg>
                    </span>
                    <input type="text" x-model="searchQuery" @input="filterTransactions" placeholder="Search transactions..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>
                <button @click="openStatementModal"
                    class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="fill-white stroke-current dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.5 5.83333L10 10L17.5 5.83333M2.5 14.1667L10 18.3333L17.5 14.1667M2.5 10L10 14.1667L17.5 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Full Statement
                </button>
            </div>
        </div>

        <div class="custom-scrollbar max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left whitespace-nowrap"><span class="text-theme-xs block font-medium text-gray-500 dark:text-gray-400">Transaction ID</span></th>
                        <th class="px-6 py-3 text-left whitespace-nowrap"><span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Description</span></th>
                        <th class="px-6 py-3 text-left whitespace-nowrap"><span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Type</span></th>
                        <th class="px-6 py-3 text-left whitespace-nowrap"><span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Date</span></th>
                        <th class="px-6 py-3 text-right whitespace-nowrap"><span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Amount</span></th>
                        <th class="px-6 py-3 text-center whitespace-nowrap"><span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400" x-text="'TXN-' + transaction.id"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex size-10 shrink-0 items-center justify-center rounded-full border border-gray-200 shadow-xs dark:border-gray-800"
                                        :class="transaction.type === 'deposit' ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'">
                                        <svg x-show="transaction.type === 'deposit'" class="size-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        <svg x-show="transaction.type !== 'deposit'" class="size-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m0 0l6-6m-6 6l6 6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90" x-text="transaction.description || transaction.type"></p>
                                        <!-- Show pending badge -->
                                        <span x-show="transaction.is_pending" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            Pending Approval
                                        </span>
                                        <p class="text-theme-xs text-gray-500 dark:text-gray-400" x-show="transaction.notes" x-text="transaction.notes"></p>
                                        <p class="text-theme-xs text-gray-500 dark:text-gray-400" x-show="transaction.initiated_by" x-text="'By: ' + transaction.initiated_by"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="transaction.type === 'deposit' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                    x-text="transaction.type === 'deposit' ? 'Deposit' : 'Withdrawal'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-700 dark:text-gray-400" x-text="formatDate(transaction.created_at)"></span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <p class="text-theme-sm font-medium" :class="transaction.type === 'deposit' ? 'text-success-600' : 'text-error-600'">
                                    <span x-text="transaction.type === 'deposit' ? '+' : '-'"></span>
                                    <span x-text="selectedCurrency"></span> 
                                    <span x-text="formatNumber(convertAmount(transaction.amount))"></span>
                                </p>
                                <p x-show="transaction.is_pending" class="text-theme-xs text-yellow-600 dark:text-yellow-400">
                                    Awaiting approval
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="transaction.is_pending ? 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'"
                                    x-text="transaction.is_pending ? 'Pending' : 'Completed'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="filteredTransactions.length === 0 && !loading" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No transactions found.</p>
        </div>

        <div x-show="loading" class="py-12 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
            <p class="mt-2 text-sm text-gray-500">Loading transactions...</p>
        </div>

        <div x-show="filteredTransactions.length > 0 && !loading" class="flex items-center justify-between border-t border-gray-100 px-6 py-4 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Showing <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> to <span x-text="Math.min(currentPage * itemsPerPage, filteredTransactions.length)"></span> of <span x-text="filteredTransactions.length"></span> entries</p>
            <div class="flex gap-2">
                <button @click="prevPage" :disabled="currentPage === 1" class="rounded-lg border border-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:border-gray-700">Previous</button>
                <span class="px-3 py-1 text-sm" x-text="`Page ${currentPage} of ${totalPages}`"></span>
                <button @click="nextPage" :disabled="currentPage === totalPages" class="rounded-lg border border-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:border-gray-700">Next</button>
            </div>
        </div>
    </div>

    {{-- Include Modal Partials --}}
    @include('partials.modal.wallet-deposit-modal')
    @include('partials.modal.wallet-payment-modal')
    @include('partials.modal.wallet-schedule-modal')
    @include('partials.modal.wallet-statement-modal')
    @include('partials.modal.wallet-add-card-modal')
</div>

<script>
// Replace the entire walletComponent function with this updated version

function walletComponent(config = {}) {
    return {
        // Wallet Data (from config)
        walletBalance: config.initialBalance || 0,
        fullWalletNumber: config.initialFullWalletNumber || '0000000000000000',
        maskedWalletNumber: config.initialMaskedWalletNumber || '•••• •••• •••• 0000',
        totalSpent: config.initialTotalSpent || 0,
        rentSpent: config.initialRentSpent || 0,
        waterSpent: config.initialWaterSpent || 0,
        electricitySpent: config.initialElectricitySpent || 0,
        balanceChange: config.initialBalanceChange || 0,
        cards: config.cards || [],

        // Transactions
        transactions: [],
        filteredTransactions: [],
        loading: false,
        
        // Currency & Exchange Rates
        selectedCurrency: 'KES',
        exchangeRates: { USD: 0, EUR: 0, GBP: 0, JPY: 0, KES: 1 },
        conversionInProgress: false,
        lastRateFetch: null,
        currencies: [
            { code: 'KES', name: 'Kenyan Shilling', rate: 1 },
            { code: 'USD', name: 'US Dollar', rate: 0.0076 },
            { code: 'EUR', name: 'Euro', rate: 0.0070 },
            { code: 'GBP', name: 'British Pound', rate: 0.0060 },
            { code: 'JPY', name: 'Japanese Yen', rate: 1.18 },
        ],
        periods: ['Today', 'This Week', 'This Month', 'Last Month', 'This Quarter', 'This Year'],
        
        // Pagination and Search
        currentPage: 1,
        itemsPerPage: 10,
        searchQuery: '',

        // Computed properties for converted values
        get convertedBalance() { 
            return this.convertAmount(this.walletBalance);
        },
        
        get convertedTotalSpent() {
            return this.convertAmount(this.totalSpent);
        },
        
        get convertedRentSpent() {
            return this.convertAmount(this.rentSpent);
        },
        
        get convertedWaterSpent() {
            return this.convertAmount(this.waterSpent);
        },
        
        get convertedElectricitySpent() {
            return this.convertAmount(this.electricitySpent);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredTransactions.length / this.itemsPerPage);
        },

        get paginatedTransactions() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredTransactions.slice(start, start + this.itemsPerPage);
        },

        init() {
            this.fetchExchangeRates();
            this.initSwiper();
            this.loadWalletData();
            this.loadTransactions();
            this.setupWalletEventListeners();
        },
        
        setupWalletEventListeners() {
            window.addEventListener('wallet-updated', (event) => {
                console.log('Wallet updated event received:', event.detail);
                if (event.detail && event.detail.new_balance !== undefined) {
                    this.walletBalance = event.detail.new_balance;
                    this.loadTransactions();
                } else {
                    this.refreshWalletData();
                }
            });
            
            document.addEventListener('wallet-updated', (event) => {
                if (event.detail && event.detail.new_balance !== undefined) {
                    this.walletBalance = event.detail.new_balance;
                    this.loadTransactions();
                }
            });
            
            window.addEventListener('storage', (event) => {
                if (event.key === 'wallet_last_update') {
                    try {
                        const data = JSON.parse(event.newValue);
                        if (data && data.balance !== undefined) {
                            this.walletBalance = data.balance;
                            this.loadTransactions();
                        }
                    } catch (e) {}
                }
            });
        },
        
        async refreshWalletData() {
            await Promise.all([
                this.loadWalletData(),
                this.loadTransactions()
            ]);
        },
        
        async loadWalletData() {
            try {
                const response = await fetch('/api/wallet/balance', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.success) {
                    this.walletBalance = data.balance;
                    this.fullWalletNumber = data.wallet_number || this.fullWalletNumber;
                    this.maskedWalletNumber = data.masked_wallet_number || this.maskedWalletNumber;
                }
            } catch (error) {
                console.error('Error loading wallet data:', error);
            }
        },
        
        async loadTransactions() {
            this.loading = true;
            try {
                const response = await fetch('/api/wallet/transactions?per_page=50', {
                    headers: { 
                        'Accept': 'application/json', 
                        'X-Requested-With': 'XMLHttpRequest' 
                    }
                });
                const data = await response.json();
                
                if (data.success !== false) {
                    let transactions = data.data || data || [];
                    // Add converted amount to each transaction for display
                    this.transactions = transactions.map(t => ({
                        ...t,
                        converted_amount: this.convertAmount(t.amount),
                        display_amount: this.formatNumber(this.convertAmount(t.amount))
                    }));
                    this.filteredTransactions = [...this.transactions];
                    this.currentPage = 1;
                } else {
                    console.error('Failed to load transactions:', data.error);
                    this.transactions = [];
                    this.filteredTransactions = [];
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
                this.transactions = [];
                this.filteredTransactions = [];
            } finally {
                this.loading = false;
            }
        },
        
        // FIXED: Currency conversion method
        convertAmount(amount) {
            if (!amount && amount !== 0) return 0;
            const rate = this.exchangeRates[this.selectedCurrency] || 1;
            const converted = parseFloat(amount) * parseFloat(rate);
            return isNaN(converted) ? 0 : converted;
        },
        
        // FIXED: Fetch exchange rates with fallback
        async fetchExchangeRates() {
            try {
                // Try free API first (no API key required)
                const response = await fetch('https://api.exchangerate-api.com/v4/latest/KES');
                if (response.ok) {
                    const data = await response.json();
                    if (data.rates) {
                        this.exchangeRates = {
                            KES: 1,
                            USD: data.rates.USD || 0.0076,
                            EUR: data.rates.EUR || 0.0070,
                            GBP: data.rates.GBP || 0.0060,
                            JPY: data.rates.JPY || 1.18,
                        };
                        this.lastRateFetch = new Date();
                        console.log('Exchange rates loaded:', this.exchangeRates);
                        return;
                    }
                }
                
                // Fallback to Frankfurter API
                const fallbackResponse = await fetch('https://api.frankfurter.app/latest?from=KES');
                if (fallbackResponse.ok) {
                    const data = await fallbackResponse.json();
                    if (data.rates) {
                        this.exchangeRates = {
                            KES: 1,
                            USD: data.rates.USD || 0.0076,
                            EUR: data.rates.EUR || 0.0070,
                            GBP: data.rates.GBP || 0.0060,
                            JPY: data.rates.JPY || 1.18,
                        };
                        this.lastRateFetch = new Date();
                        console.log('Exchange rates loaded (fallback):', this.exchangeRates);
                        return;
                    }
                }
                
                // Final fallback - static rates
                console.warn('Using fallback exchange rates');
                this.exchangeRates = {
                    KES: 1,
                    USD: 0.0076,
                    EUR: 0.0070,
                    GBP: 0.0060,
                    JPY: 1.18,
                };
                
            } catch (error) {
                console.error('Failed to fetch exchange rates:', error);
                // Use fallback rates
                this.exchangeRates = {
                    KES: 1,
                    USD: 0.0076,
                    EUR: 0.0070,
                    GBP: 0.0060,
                    JPY: 1.18,
                };
            }
        },
        
        // Force refresh exchange rates
        async refreshExchangeRates() {
            this.conversionInProgress = true;
            await this.fetchExchangeRates();
            // Refresh all displayed amounts
            this.loadTransactions();
            this.conversionInProgress = false;
        },
        
        // Handle currency change
        async changeCurrency(currencyCode) {
            this.selectedCurrency = currencyCode;
            // Refresh transactions to show converted amounts
            this.loadTransactions();
            // Optionally store preference
            localStorage.setItem('preferred_currency', currencyCode);
        },
        
        getTransactionDescription(transaction) {
            if (transaction.description) {
                return transaction.description;
            }
            if (transaction.type === 'deposit') {
                const method = transaction.payment_method || 'Unknown';
                const month = transaction.bill_month || '';
                return `Deposit via ${method}${month ? ` for ${month}` : ''}`;
            }
            return transaction.type === 'deposit' ? 'Wallet Deposit' : 'Wallet Withdrawal';
        },
        
        getAmountClass(transaction) {
            return transaction.type === 'deposit' 
                ? 'text-success-600 dark:text-success-400' 
                : 'text-error-600 dark:text-error-400';
        },
        
        getAmountSign(transaction) {
            return transaction.type === 'deposit' ? '+' : '-';
        },
        
        getStatusClass(transaction) {
            const status = transaction.status || (transaction.confirmed ? 'Completed' : 'Pending');
            switch(status.toLowerCase()) {
                case 'completed': return 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500';
                case 'pending': return 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500';
                case 'failed': return 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500';
                default: return 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400';
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            const now = new Date();
            const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) {
                return 'Today, ' + date.toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
            } else if (diffDays === 1) {
                return 'Yesterday, ' + date.toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
            } else if (diffDays < 7) {
                return date.toLocaleDateString('en-KE', { weekday: 'short' }) + ', ' + 
                       date.toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString('en-KE', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        },

        formatNumber(value) {
            if (value === undefined || value === null) return '0.00';
            return parseFloat(value).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        initSwiper() {
            setTimeout(() => {
                if (typeof Swiper !== 'undefined' && document.querySelector('.my-cards-slider')) {
                    if (this.swiperInstance) this.swiperInstance.destroy(true, true);
                    this.swiperInstance = new Swiper('.my-cards-slider', {
                        slidesPerView: 1, spaceBetween: 20,
                        navigation: { nextEl: '#card-slider-next', prevEl: '#card-slider-prev' },
                        loop: this.cards.length > 1,
                        autoplay: this.cards.length > 1 ? { delay: 5000, disableOnInteraction: false } : false,
                        speed: 400
                    });
                }
            }, 100);
        },

        filterTransactions() {
            if (!this.searchQuery) {
                this.filteredTransactions = [...this.transactions];
            } else {
                const query = this.searchQuery.toLowerCase();
                this.filteredTransactions = this.transactions.filter(t => 
                    (t.description && t.description.toLowerCase().includes(query)) ||
                    (t.type && t.type.toLowerCase().includes(query)) ||
                    (t.payment_method && t.payment_method.toLowerCase().includes(query)) ||
                    (t.reference && t.reference.toLowerCase().includes(query))
                );
            }
            this.currentPage = 1;
        },
        
        async loadTransactionsByPeriod(period) {
            this.loading = true;
            let fromDate = '';
            const today = new Date();
            
            switch(period) {
                case 'Today':
                    fromDate = today.toISOString().split('T')[0];
                    break;
                case 'This Week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    fromDate = weekAgo.toISOString().split('T')[0];
                    break;
                case 'This Month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'Last Month':
                    const lastMonth = new Date(today);
                    lastMonth.setMonth(today.getMonth() - 1);
                    fromDate = new Date(lastMonth.getFullYear(), lastMonth.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'This Quarter':
                    const quarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1);
                    fromDate = quarterStart.toISOString().split('T')[0];
                    break;
                case 'This Year':
                    fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    break;
                default:
                    fromDate = '';
            }
            
            try {
                const url = `/api/wallet/transactions?per_page=100${fromDate ? `&from_date=${fromDate}` : ''}`;
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                let transactions = data.data || data || [];
                this.transactions = transactions.map(t => ({
                    ...t,
                    converted_amount: this.convertAmount(t.amount),
                    display_amount: this.formatNumber(this.convertAmount(t.amount))
                }));
                this.filteredTransactions = [...this.transactions];
                this.currentPage = 1;
            } catch (error) {
                console.error('Error loading period transactions:', error);
            } finally {
                this.loading = false;
            }
        },

        prevPage() { if (this.currentPage > 1) this.currentPage--; },
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },

        // Modal Triggers
        openDepositModal() { if (window.walletDepositModal) window.walletDepositModal.openModal(); },
        openPaymentModal() { if (window.walletPaymentModal) window.walletPaymentModal.openModal(); },
        openScheduleModal() { if (window.walletScheduleModal) window.walletScheduleModal.openModal(); },
        openStatementModal() { if (window.walletStatementModal) window.walletStatementModal.openModal(); },
        openAddCardModal() { if (window.walletAddCardModal) window.walletAddCardModal.openModal(); }
    };
}
</script>
{{-- resources/views/partials/sidebar.blade.php --}}
@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    // Role and intent
    $userRole   = $user?->getRoleNames()->first() ?? 'guest';
    $userIntent = $user?->intent ?? 'undecided';

    // Spatie permissions — computed once, injected as JSON
    $userPermissions = $user
        ? $user->getAllPermissions()->pluck('name')->values()->toArray()
        : [];

    // Bavix wallet balance — safe fallback if the wallet doesn't exist yet
    $walletBalance  = 0;
    $walletCurrency = config('wallet.default_currency', 'KES');
    if ($user && method_exists($user, 'balanceFloat')) {
        try {
            $walletBalance = $user->balanceFloat ?? 0;
        } catch (\Throwable $e) {
            $walletBalance = 0;
        }
    }

    // Logo paths — checked once
    $logoLight = file_exists(public_path('images/logo/logo.svg'))
        ? asset('images/logo/logo.svg')
        : null;
    $logoDark  = file_exists(public_path('images/logo/logo-dark.svg'))
        ? asset('images/logo/logo-dark.svg')
        : null;
    $logoIcon  = file_exists(public_path('images/logo/logo-icon.svg'))
        ? asset('images/logo/logo-icon.svg')
        : null;
@endphp

<aside
    x-data="sidebarState"
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
>
    {{-- SIDEBAR HEADER --}}
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-8 sidebar-header pb-7"
    >
        <a href="{{ route('dashboard') }}" class="flex items-center">
            {{-- Expanded Logo --}}
            <span class="logo" :class="sidebarToggle ? 'hidden' : 'flex items-center'">
                @if($logoLight)
                    <img class="h-10 dark:hidden" src="{{ $logoLight }}" alt="{{ SystemHelper::appName() }}">
                    <img class="h-10 hidden dark:block" src="{{ $logoDark ?? $logoLight }}" alt="{{ SystemHelper::appName() }}">
                @else
                    <span class="flex items-center">
                        <span class="text-xl font-bold text-primary font-heading">Sharet</span>
                        <span class="text-xl font-bold text-secondary ml-1">Africa</span>
                    </span>
                @endif
            </span>

            {{-- Collapsed Icon --}}
            <span class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'">
                @if($logoIcon)
                    <img class="h-8" src="{{ $logoIcon }}" alt="{{ SystemHelper::appName() }}">
                @else
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary">
                        <span class="text-sm font-bold text-white">SA</span>
                    </span>
                @endif
            </span>
        </a>
    </div>
    {{-- SIDEBAR HEADER --}}

    {{-- WALLET BALANCE CARD — hidden for devs, staff, admins --}}
    @if($user && $userIntent !== 'techie' && !$user->hasAnyRole(['support', 'operations', 'admin', 'super_admin']))
        <div
            x-show="!sidebarToggle"
            x-transition
            class="mb-5 rounded-xl border border-gray-200 bg-gradient-to-br from-primary-10 to-secondary-10 p-4 dark:border-gray-800"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Wallet balance
                </span>
                <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $walletCurrency }} {{ number_format((float) $walletBalance, 2) }}
                </span>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('wallet.create') }}"
                   class="flex-1 rounded-lg bg-primary px-2 py-1.5 text-center text-xs font-medium text-white hover:brightness-95">
                    Deposit
                </a>
                <a href="{{ route('wallet.index') }}"
                   class="flex-1 rounded-lg border border-primary-30 px-2 py-1.5 text-center text-xs font-medium text-primary hover:bg-primary-10">
                    Wallet
                </a>
            </div>
        </div>
    @endif
    {{-- WALLET BALANCE CARD --}}

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav>
            <template x-for="(group, groupIndex) in filteredMenuData" :key="groupIndex">
                <div>
                    {{-- GROUP TITLE --}}
                    <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                        <span
                            class="menu-group-title"
                            :class="sidebarToggle ? 'lg:hidden' : ''"
                            x-text="group.title"
                        ></span>

                        <svg
                            :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill=""
                            />
                        </svg>
                    </h3>

                    {{-- ITEMS --}}
                    <ul class="flex flex-col gap-4 mb-6">
                        <template x-for="(item, itemIndex) in group.items" :key="itemIndex">
                            <li>

                                {{-- PARENT --}}
                                <a
                                    :href="item.children && item.children.length ? '#' : item.link"
                                    @click.prevent="
                                        if (item.children && item.children.length) {
                                            toggleSelected(item.name);
                                        } else {
                                            setActive(item.page, item.label, item.link);
                                        }
                                    "
                                    class="menu-item group"
                                    :class="getItemClasses(item)"
                                >
                                    <svg
                                        :class="getIconClasses(item)"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            :d="item.icon"
                                            fill=""
                                        />
                                    </svg>

                                    <span
                                        class="menu-item-text"
                                        :class="sidebarToggle ? 'lg:hidden' : ''"
                                        x-text="item.label"
                                    ></span>

                                    <svg
                                        x-show="item.children && item.children.length"
                                        class="absolute right-2.5 top-1/2 h-5 w-5 -translate-y-1/2 stroke-current"
                                        :class="[
                                            getArrowClasses(item),
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </a>

                                {{-- CHILDREN --}}
                                <div
                                    x-show="item.children && item.children.length && isSelected(item.name)"
                                    x-collapse
                                    class="overflow-hidden"
                                >
                                    <ul
                                        class="mt-2 flex flex-col gap-1 pl-9"
                                        :class="sidebarToggle ? 'lg:hidden' : ''"
                                    >
                                        <template x-for="(child, childIndex) in item.children" :key="childIndex">
                                            <li>
                                                <a
                                                    :href="child.link"
                                                    @click="
                                                        if (child.link === '#' || !child.link) {
                                                            $event.preventDefault();
                                                        }
                                                        setActive(child.page, child.label, child.link);
                                                    "
                                                    class="relative flex items-center rounded-lg px-4 py-2.5 text-sm font-medium duration-200"
                                                    :class="
                                                        isActive(child.page)
                                                            ? 'bg-primary-10 text-primary'
                                                            : 'text-gray-600 hover:bg-primary-10 hover:text-primary dark:text-gray-400'
                                                    "
                                                    x-text="child.label"
                                                ></a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            {{-- EMPTY STATE --}}
            <div
                x-show="filteredMenuData.length === 0"
                class="px-4 py-6 text-center text-sm text-gray-400"
            >
                No navigation items available.
            </div>
        </nav>
    </div>
</aside>

<script>
document.addEventListener('alpine:init', () => {

    Alpine.data('sidebarState', () => ({

        // Persist selection across reloads
        selected: localStorage.getItem('sidebar_selected') ?? '',
        activePage: localStorage.getItem('sidebar_active_page') ?? 'dashboard',
        activeItemLabel: localStorage.getItem('sidebar_active_label') ?? 'Dashboard',

        // ----- Auth context injected from Blade (single variables) -----
        userRole: @js($userRole),
        userIntent: @js($userIntent),
        userPermissions: @js($userPermissions),

        // ================================================================
        // MENU TREE
        // ================================================================
        menuData: [
            /* ============ MAIN ============ */
            {
                title: 'MENU',
                items: [
                    {
                        name: 'Dashboard',
                        label: 'Dashboard',
                        link: '/dashboard',
                        page: 'dashboard',
                        permission: 'dashboard.view',
                        icon: 'M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.2426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z'
                    },
                    {
                        name: 'Profile',
                        label: 'Profile',
                        link: '/profile',
                        page: 'profile',
                        permission: 'profile.view',
                        icon: 'M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z'
                    }
                ]
            },

            /* ============ WALLET — individual / merchant / business ============ */
            {
                title: 'WALLET',
                roles: ['individual', 'merchant', 'business'],
                items: [
                    {
                        name: 'Wallet',
                        label: 'Wallet',
                        link: '/#',
                        permission: 'wallet.view',
                        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                        children: [
                            { label: 'Overview',     link: '/wallet',              page: 'wallet',             permission: 'wallet.view' },
                            { label: 'Deposit',      link: '/wallet/create',       page: 'walletDeposit',      permission: 'wallet.deposit' },
                            { label: 'Withdraw',     link: '/wallet/withdraw',     page: 'walletWithdraw',     permission: 'wallet.withdraw' },
                            { label: 'Transfer',     link: '/wallet/transfer',     page: 'walletTransfer',     permission: 'wallet.transfer' },
                            { label: 'Transactions', link: '/wallet/transactions', page: 'walletTransactions', permission: 'wallet.transactions' }
                        ]
                    }
                ]
            },

            /* ============ MERCHANT ============ */
            {
                title: 'MERCHANT',
                roles: ['merchant'],
                items: [
                    {
                        name: 'MerchantTools',
                        label: 'Merchant Tools',
                        link: '/#',
                        permission: 'merchant.accept',
                        icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                        children: [
                            { label: 'Accept Payment', link: '#', page: 'merchantAccept', permission: 'merchant.accept' },
                            { label: 'Settle',         link: '#', page: 'merchantSettle', permission: 'merchant.settle' },
                            { label: 'Reconcile',      link: '#', page: 'merchantRecon',  permission: 'merchant.reconcile' },
                            { label: 'QR Code',        link: '#', page: 'merchantQR',     permission: 'merchant.qr' },
                            { label: 'Tools',          link: '#', page: 'merchantTools',  permission: 'merchant.tools' }
                        ]
                    }
                ]
            },

            /* ============ TRADE ============ */
            {
                title: 'TRADE',
                roles: ['business'],
                items: [
                    {
                        name: 'TradeTools',
                        label: 'Trade',
                        link: '/#',
                        permission: 'business.invoice',
                        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        children: [
                            { label: 'Invoices',        link: '#', page: 'tradeInvoices', permission: 'business.invoice' },
                            { label: 'Pay Supplier',    link: '#', page: 'tradePay',      permission: 'business.pay_supplier' },
                            { label: 'Receive Payment', link: '#', page: 'tradeReceive',  permission: 'business.receive_payment' },
                            { label: 'Trade Finance',   link: '#', page: 'tradeFinance',  permission: 'business.trade_finance' },
                            { label: 'Treasury',        link: '#', page: 'tradeTreasury', permission: 'business.treasury' },
                            { label: 'Documents',       link: '#', page: 'tradeDocs',     permission: 'business.docs' }
                        ]
                    }
                ]
            },

            /* ============ DEVELOPER ============ */
            {
                title: 'DEVELOPER',
                roles: ['techie'],
                items: [
                    {
                        name: 'DeveloperTools',
                        label: 'Developer',
                        link: '/#',
                        permission: 'developer.api_keys',
                        icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                        children: [
                            { label: 'API Keys',      link: '#', page: 'devKeys',      permission: 'developer.api_keys' },
                            { label: 'Sandbox',       link: '#', page: 'devSandbox',   permission: 'developer.sandbox' },
                            { label: 'Webhooks',      link: '#', page: 'devWebhooks',  permission: 'developer.webhooks' },
                            { label: 'Logs',          link: '#', page: 'devLogs',      permission: 'developer.logs' },
                            { label: 'Documentation', link: '#', page: 'devDocs',      permission: 'developer.docs' },
                            { label: 'Licensing',     link: '#', page: 'devLicensing', permission: 'developer.licensing' }
                        ]
                    }
                ]
            },

            /* ============ ONBOARDING ============ */
            {
                title: 'ONBOARDING',
                roles: ['support', 'operations', 'admin', 'super_admin'],
                items: [
                    {
                        name: 'OnboardingTools',
                        label: 'Onboarding',
                        link: '/#',
                        permission: 'waiting_list.view',
                        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        children: [
                            { label: 'Waiting List', link: '/admin/waiting-list', page: 'waitingList', permission: 'waiting_list.view' },
                            { label: 'Invite User',  link: '#',                   page: 'inviteUser',  permission: 'waiting_list.invite' },
                            { label: 'KYC Review',   link: '#',                   page: 'kycReview',   permission: 'kyc.review' },
                            { label: 'Approve KYC',  link: '#',                   page: 'kycApprove',  permission: 'kyc.approve' }
                        ]
                    }
                ]
            },

            /* ============ OPERATIONS ============ */
            {
                title: 'OPERATIONS',
                roles: ['support', 'operations', 'admin', 'super_admin'],
                items: [
                    {
                        name: 'OperationsTools',
                        label: 'Operations',
                        link: '/#',
                        permission: 'admin.reconciliation',
                        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        children: [
                            { label: 'Reconciliation', link: '#', page: 'reconciliation', permission: 'admin.reconciliation' },
                            { label: 'Treasury',       link: '#', page: 'treasury',       permission: 'admin.treasury' },
                            { label: 'Rails',          link: '#', page: 'rails',          permission: 'admin.rails' },
                            { label: 'Support Inbox',  link: '#', page: 'supportInbox',   permission: 'admin.support' }
                        ]
                    }
                ]
            },

            /* ============ ADMIN ============ */
            {
                title: 'ADMINISTRATION',
                roles: ['admin', 'super_admin'],
                items: [
                    {
                        name: 'AdminTools',
                        label: 'Administration',
                        link: '/#',
                        permission: 'admin.users',
                        icon: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z',
                        children: [
                            { label: 'Users',     link: '/users', page: 'users',     permission: 'admin.users' },
                            { label: 'Companies', link: '#',       page: 'companies', permission: 'admin.companies' },
                            { label: 'Roles',     link: '#',       page: 'roles',     permission: 'admin.roles' },
                            { label: 'Audit Log', link: '#',       page: 'audit',     permission: 'admin.audit' },
                            { label: 'System',    link: '#',       page: 'system',    permission: 'admin.system' }
                        ]
                    }
                ]
            }
        ],

        // ================================================================
        // FILTERING
        // ================================================================
        get isSuperAdmin() {
            return this.userRole === 'super_admin';
        },

        can(permission) {
            if (this.isSuperAdmin) return true;
            if (!permission) return true;
            return this.userPermissions.includes(permission);
        },

        hasRole(roles) {
            if (!roles || roles.length === 0) return true;
            if (this.isSuperAdmin) return true;
            return roles.includes(this.userRole);
        },

        get filteredMenuData() {
            return this.menuData
                .map(group => {
                    if (!this.hasRole(group.roles)) return null;

                    const items = group.items
                        .map(item => {
                            if (!item.children || item.children.length === 0) {
                                return this.can(item.permission) ? item : null;
                            }
                            const children = item.children.filter(child =>
                                this.can(child.permission)
                            );
                            if (this.can(item.permission) || children.length > 0) {
                                return { ...item, children };
                            }
                            return null;
                        })
                        .filter(Boolean);

                    return items.length > 0 ? { ...group, items } : null;
                })
                .filter(Boolean);
        },

        // ================================================================
        // INTERACTION
        // ================================================================
        toggleSelected(itemName) {
            this.selected = this.selected === itemName ? '' : itemName;
            localStorage.setItem('sidebar_selected', this.selected);
        },

        setActive(page, label, link) {
            this.activePage = page;
            this.activeItemLabel = label;

            localStorage.setItem('sidebar_active_page', page);
            localStorage.setItem('sidebar_active_label', label);

            const parent = this.findParentItem(page);
            this.selected = parent ? parent.name : '';
            localStorage.setItem('sidebar_selected', this.selected);

            if (link && link !== '#' && link !== '/#') {
                window.location.href = link;
            }
        },

        findParentItem(page) {
            if (!page) return null;
            for (const group of this.menuData) {
                for (const item of group.items) {
                    if (item.children?.some(child => child.page === page)) {
                        return item;
                    }
                }
            }
            return null;
        },

        setInitialActivePage() {
            const path = window.location.pathname;
            const exactMatches = {
                '/': 'dashboard',
                '/dashboard': 'dashboard',
                '/profile': 'profile',
                '/wallet': 'wallet',
                '/wallet/create': 'walletDeposit',
                '/wallet/withdraw': 'walletWithdraw',
                '/wallet/transfer': 'walletTransfer',
                '/wallet/transactions': 'walletTransactions',
                '/admin/waiting-list': 'waitingList',
                '/users': 'users'
            };
            const page = exactMatches[path] ?? 'dashboard';
            this.activePage = page;
            this.setActiveItemLabel(page);
            const parent = this.findParentItem(page);
            this.selected = parent ? parent.name : '';
        },

        setActiveItemLabel(page) {
            for (const group of this.menuData) {
                for (const item of group.items) {
                    if (!item.children && item.page === page) {
                        this.activeItemLabel = item.label;
                        return;
                    }
                    if (item.children) {
                        for (const child of item.children) {
                            if (child.page === page) {
                                this.activeItemLabel = child.label;
                                return;
                            }
                        }
                    }
                }
            }
        },

        isSelected(itemName) {
            return this.selected === itemName;
        },

        isActive(page) {
            return this.activePage === page;
        },

        isChildActive(item) {
            if (!item.children) return false;
            return item.children.some(child => this.isActive(child.page));
        },

        getItemClasses(item) {
            const isActive = this.isActive(item.page) || this.isChildActive(item);
            return isActive ? 'menu-item-active' : 'menu-item-inactive';
        },

        getIconClasses(item) {
            const isActive = this.isActive(item.page) || this.isChildActive(item);
            return isActive ? 'menu-item-icon-active' : 'menu-item-icon-inactive';
        },

        getArrowClasses(item) {
            if (!item.children?.length) return '';
            return this.selected === item.name
                ? 'rotate-180 text-primary'
                : 'text-gray-600 dark:text-gray-400';
        },

        init() {
            this.setInitialActivePage();
            console.log('✅ Sidebar ready', {
                role: this.userRole,
                intent: this.userIntent,
                permissions: this.userPermissions.length,
                visibleGroups: this.filteredMenuData.map(g => g.title)
            });
        }
    }));

});
</script>
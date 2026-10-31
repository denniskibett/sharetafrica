{{-- resources/views/partials/table/table-subscriptions.blade.php --}}
<!-- Subscriptions Table - Optimized -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="subscriptionsTable()" x-init="init()">
    <!-- Table Header -->
    <div class="flex flex-col justify-between gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row lg:items-center dark:border-gray-800">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Subscription Plans</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage per-unit subscription plans and pricing</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Search -->
            <div class="relative">
                <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                    </svg>
                </span>
                <input type="text" placeholder="Search plans..." x-model="searchQuery" @input="currentPage = 1" 
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button @click="openCreateModal()" class="bg-purple-500 shadow-theme-xs hover:bg-purple-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Add Plan
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
        <span class="ml-3 text-gray-500">Loading subscription plans...</span>
    </div>
    
    <!-- Error State -->
    <div x-show="!loading && error" class="text-center py-12">
        <div class="mx-auto h-12 w-12 text-red-500">
            <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">Error Loading Plans</h3>
        <p class="mt-1 text-sm text-gray-500" x-text="errorMessage || 'Could not load subscription plans. Please check your connection.'"></p>
        <p class="mt-1 text-xs text-gray-400" x-text="errorDetails || ''"></p>
        <div class="mt-6">
            <button @click="loadPlans()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                Retry
            </button>
        </div>
    </div>
    
    <!-- Table Content -->
    <div x-show="!loading && !error" class="custom-scrollbar overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('id')">
                        <div class="flex items-center gap-2">
                            <p>ID</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'id' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'id' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('name')">
                        <div class="flex items-center gap-2">
                            <p>Plan Name</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'name' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'name' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('price_per_unit')">
                        <div class="flex items-center gap-2">
                            <p>Price/Unit</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'price_per_unit' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'price_per_unit' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('trial_days')">
                        <div class="flex items-center gap-2">
                            <p>Trial</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'trial_days' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'trial_days' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('discount_percentage')">
                        <div class="flex items-center gap-2">
                            <p>Discount</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'discount_percentage' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'discount_percentage' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Features</th>
                    <th class="cursor-pointer p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('is_active')">
                        <div class="flex items-center gap-2">
                            <p>Status</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'is_active' && sortDirection === 'asc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'is_active' && sortDirection === 'desc' ? 'text-purple-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Subscribers</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="plan in paginatedPlans" :key="plan.id">
                    <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="p-3 whitespace-nowrap">
                            <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400" x-text="'#' + plan.id"></span>
                        </td>
                        <td class="p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white" x-text="plan.name"></p>
                            </div>
                        </td>
                        <td class="p-3 whitespace-nowrap text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">
                                KES <span x-text="plan.price_per_unit?.toLocaleString() || 0"></span>
                            </span>
                            <span class="text-gray-500">/ unit / month</span>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="plan.trial_days + ' days'"></span>
                            <p x-show="plan.trial_days === 0" class="text-xs text-gray-400">No trial</p>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <span x-text="plan.discount_percentage || 0"></span>%
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                <template x-for="(feature, index) in (plan.features || []).slice(0, 2)" :key="index">
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300" x-text="feature"></span>
                                </template>
                                <span x-show="(plan.features || []).length > 2" class="text-xs text-gray-500" x-text="'+ ' + ((plan.features || []).length - 2) + ' more'"></span>
                                <span x-show="!(plan.features || []).length" class="text-xs text-gray-400">No features</span>
                            </div>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <span :class="plan.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'" class="text-theme-xs rounded-full px-2 py-0.5 font-medium" x-text="plan.is_active ? 'Active' : 'Inactive'"></span>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <button @click.stop="viewSubscribers(plan.id)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                <span x-text="plan.subscribers_count || 0"></span> companies
                            </button>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                <div x-data="dropdown()" class="relative" @click.stop>
                                    <button @click="toggle" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" class="shadow-theme-lg dark:bg-gray-dark absolute right-0 z-50 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-ref="dropdown">
                                        <a :href="'/admin/subscriptions/plans/' + plan.id" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            View Details
                                        </a>
                                        <button @click="openEditModal(plan.id)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Edit</button>
                                        <button @click="togglePlanStatus(plan)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium" :class="plan.is_active ? 'text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10' : 'text-green-500 hover:bg-green-50 hover:text-green-700 dark:text-green-400 dark:hover:bg-green-500/10'">
                                            <span x-text="plan.is_active ? 'Deactivate' : 'Activate'"></span>
                                        </button>
                                        <button @click="deletePlan(plan.id)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    
    <!-- Empty State -->
    <div x-show="!loading && !error && filteredPlans.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No subscription plans found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new plan.</p>
        <div class="mt-6">
            <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                Add Plan
            </button>
        </div>
    </div>
    
    <!-- Pagination -->
    <div x-show="!loading && !error && filteredPlans.length > 0" class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
        <div class="pb-3 sm:pb-0">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                Showing <span x-text="((currentPage - 1) * itemsPerPage) + (paginatedPlans.length ? 1 : 0)"></span>
                to <span x-text="((currentPage - 1) * itemsPerPage) + paginatedPlans.length"></span>
                of <span x-text="filteredPlans.length"></span>
            </span>
        </div>
        <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="previousPage" :disabled="currentPage === 1">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/></svg></span>
            </button>
            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            <ul class="hidden items-center gap-0.5 sm:flex">
                <template x-for="page in visiblePages" :key="page">
                    <li><a href="#" @click.prevent="goToPage(page)" :class="page === currentPage ? 'bg-purple-500 text-white' : 'hover:bg-purple-500 text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white'" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium" x-text="page"></a></li>
                </template>
            </ul>
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="nextPage" :disabled="currentPage === totalPages">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/></svg></span>
            </button>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('partials.modal.subscriptions-create-modal')

<script>
const csrfTokenSubscriptions = "{{ csrf_token() }}";

document.addEventListener('alpine:init', () => {
    // Subscriptions Table Component - OPTIMIZED
    Alpine.data('subscriptionsTable', () => ({
        plans: [],
        sortBy: 'price_per_unit',
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: 10,
        searchQuery: '',
        loading: true,
        error: false,
        errorMessage: '',
        errorDetails: '',
        
        // Computed properties - optimized
        get filteredPlans() {
            let filtered = this.plans;
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(p => 
                    (p.name && p.name.toLowerCase().includes(query)) ||
                    (p.description && p.description.toLowerCase().includes(query))
                );
            }
            return filtered;
        },
        
        get sortedPlans() {
            const sorted = [...this.filteredPlans];
            const field = this.sortBy;
            const direction = this.sortDirection === 'asc' ? 1 : -1;
            
            sorted.sort((a, b) => {
                let valA = a[field];
                let valB = b[field];
                
                if (valA === null || valA === undefined) return direction;
                if (valB === null || valB === undefined) return -direction;
                
                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
                
                if (valA < valB) return -direction;
                if (valA > valB) return direction;
                return 0;
            });
            
            return sorted;
        },
        
        get paginatedPlans() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.sortedPlans.slice(start, start + this.itemsPerPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredPlans.length / this.itemsPerPage) || 1;
        },
        
        get visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },
        
        async init() {
            await this.loadPlans();
        },
        
        async loadPlans() {
            this.loading = true;
            this.error = false;
            
            try {
                const response = await fetch('/admin/subscriptions/api/plans/data', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfTokenSubscriptions
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    this.plans = result.plans || [];
                } else {
                    throw new Error(result.message || 'Failed to load plans');
                }
                
            } catch (error) {
                console.error('Error fetching plans:', error);
                this.error = true;
                this.errorMessage = error.message || 'Could not load subscription plans.';
                this.errorDetails = error.stack || '';
                this.plans = [];
            } finally {
                this.loading = false;
            }
        },
        
        sort(field) {
            if (this.sortBy === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortDirection = 'asc';
            }
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        
        nextPage() { 
            if (this.currentPage < this.totalPages) this.currentPage++; 
        },
        
        previousPage() { 
            if (this.currentPage > 1) this.currentPage--; 
        },
        
        openCreateModal() {
            if (window.subscriptionsCreateModal) {
                window.subscriptionsCreateModal.openModal();
            }
        },
        
        openEditModal(planId) {
            if (window.subscriptionsCreateModal) {
                window.subscriptionsCreateModal.openModal(planId);
            }
        },
        
        async togglePlanStatus(plan) {
            const newStatus = !plan.is_active;
            try {
                const response = await fetch(`/admin/subscriptions/plans/${plan.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfTokenSubscriptions,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        ...plan, 
                        is_active: newStatus 
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    plan.is_active = newStatus;
                    await this.loadPlans();
                    alert(result.message || 'Plan status updated successfully!');
                } else {
                    alert(result.message || 'Error updating plan status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating plan status');
            }
        },
        
        async deletePlan(planId) {
            if (!confirm('Are you sure you want to delete this plan? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/subscriptions/plans/${planId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfTokenSubscriptions,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await this.loadPlans();
                    alert(result.message);
                } else {
                    alert(result.message || 'Error deleting plan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting plan');
            }
        },
        
        viewSubscribers(planId) {
            window.location.href = `/admin/subscriptions/plans/${planId}#subscribers`;
        }
    }));
    
    // Dropdown Component
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; }
    }));
});
</script>
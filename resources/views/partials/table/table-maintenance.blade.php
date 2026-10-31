@props(['requests' => []])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="maintenanceTable()" x-init="init()">
    <!-- Table Header -->
    <div class="flex flex-col justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center dark:border-gray-800">
        <div>
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Maintenance Requests</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Track and manage all maintenance issues</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Status Filters -->
            <div class="flex h-10 items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800">
                <button @click="filterStatus = 'all'; currentPage = 1" :class="filterStatus === 'all' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'" class="text-sm h-8 rounded-md px-3 py-1.5 font-medium hover:text-gray-900 dark:hover:text-white transition-all">
                    All (<span x-text="statusCounts.all"></span>)
                </button>
                <button @click="filterStatus = 'open'; currentPage = 1" :class="filterStatus === 'open' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'" class="text-sm h-8 rounded-md px-3 py-1.5 font-medium hover:text-gray-900 dark:hover:text-white transition-all">
                    Open (<span x-text="statusCounts.open"></span>)
                </button>
                <button @click="filterStatus = 'in_progress'; currentPage = 1" :class="filterStatus === 'in_progress' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'" class="text-sm h-8 rounded-md px-3 py-1.5 font-medium hover:text-gray-900 dark:hover:text-white transition-all">
                    In Progress (<span x-text="statusCounts.in_progress"></span>)
                </button>
                <button @click="filterStatus = 'resolved'; currentPage = 1" :class="filterStatus === 'resolved' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'" class="text-sm h-8 rounded-md px-3 py-1.5 font-medium hover:text-gray-900 dark:hover:text-white transition-all">
                    Resolved (<span x-text="statusCounts.resolved"></span>)
                </button>
            </div>

            <!-- Priority Filter -->
            <select x-model="filterPriority" @change="currentPage = 1" class="dark:bg-dark-900 h-10 w-36 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="all">All Priorities</option>
                <option value="emergency">Emergency</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>

            <!-- Search -->
            <div class="relative">
                <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                    </svg>
                </span>
                <input type="text" placeholder="Search..." x-model="searchQuery" @input="currentPage = 1" class="dark:bg-dark-900 h-10 w-64 rounded-lg border border-gray-300 bg-transparent py-2 pl-9 pr-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button @click="openCreateModal()" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    New Request
                </button>
                <button @click="exportRequests()" class="border border-gray-300 shadow-theme-xs hover:bg-gray-50 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path d="M10 1.66667V12.5M10 12.5L13.3333 9.16667M10 12.5L6.66667 9.16667M4.16667 15H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.16667 18.3333H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
        <span class="ml-3 text-gray-500">Loading requests...</span>
    </div>
    
    <!-- Table Content -->
    <div x-show="!loading" class="custom-scrollbar overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900" @click="sort('request_number')">
                        <div class="flex items-center gap-2">
                            <span>Request #</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'request_number' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'request_number' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900" @click="sort('unit_number')">
                        <div class="flex items-center gap-2">
                            <span>Unit</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'unit_number' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'unit_number' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Tenant</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Title</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900" @click="sort('priority')">
                        <div class="flex items-center gap-2">
                            <span>Priority</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'priority' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'priority' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900" @click="sort('status')">
                        <div class="flex items-center gap-2">
                            <span>Status</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'status' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'status' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900" @click="sort('created_at')">
                        <div class="flex items-center gap-2">
                            <span>Reported</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'created_at' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'created_at' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <template x-for="request in paginatedRequests" :key="request.id">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="request.request_number"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-800 dark:text-white/90" x-text="request.unit_number"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="request.tenant_name"></span>
                        </td>
                        <td class="p-4">
                            <div class="max-w-xs">
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="request.title"></span>
                                <p class="text-xs text-gray-500 truncate" x-text="request.description"></p>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span :class="request.priority_color" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full">
                                <svg x-show="request.priority === 'emergency'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <svg x-show="request.priority === 'high'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                                <svg x-show="request.priority === 'medium'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                                </svg>
                                <svg x-show="request.priority === 'low'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                <span x-text="request.priority_label"></span>
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span :class="request.status_color" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full">
                                <span x-text="request.status_label"></span>
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(request.created_at)"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <button @click="viewRequestModal(request.id)" class="text-brand-500 hover:text-brand-600 text-sm font-medium">View</button>
                                <button @click="openEditModal(request.id)" class="text-blue-500 hover:text-blue-600 text-sm font-medium">Update</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    
    <!-- Empty State -->
    <div x-show="!loading && filteredRequests.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No maintenance requests</h3>
        <p class="mt-1 text-sm text-gray-500">No maintenance requests match your criteria.</p>
        <div class="mt-6">
            <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700">
                Create New Request
            </button>
        </div>
    </div>
    
    <!-- Pagination -->
    <div x-show="!loading && filteredRequests.length > 0" class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
        <div class="pb-3 sm:pb-0">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                Showing <span x-text="((currentPage - 1) * itemsPerPage) + (paginatedRequests.length ? 1 : 0)"></span>
                to <span x-text="((currentPage - 1) * itemsPerPage) + paginatedRequests.length"></span>
                of <span x-text="filteredRequests.length"></span>
            </span>
        </div>
        <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="previousPage" :disabled="currentPage === 1">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/>
                </svg>
            </button>
            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            <ul class="hidden items-center gap-0.5 sm:flex">
                <template x-for="page in visiblePages" :key="page">
                    <li><a href="#" @click.prevent="goToPage(page)" :class="page === currentPage ? 'bg-brand-500 text-white' : 'hover:bg-brand-500 text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white'" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium" x-text="page"></a></li>
                </template>
            </ul>
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="nextPage" :disabled="currentPage === totalPages">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('maintenanceTable', () => ({
        requests: @json($requests),
        searchQuery: '',
        filterStatus: 'all',
        filterPriority: 'all',
        sortBy: 'created_at',
        sortDirection: 'desc',
        currentPage: 1,
        itemsPerPage: 10,
        loading: false,
        
        init() {
            this.requests = this.requests.map(req => ({
                ...req,
                priority_label: this.getPriorityLabel(req.priority),
                priority_color: this.getPriorityColor(req.priority),
                status_label: this.getStatusLabel(req.status),
                status_color: this.getStatusColor(req.status),
                request_number: req.request_number || '#' + String(req.id).padStart(6, '0')
            }));
        },
        
        get statusCounts() {
            const counts = { all: this.requests.length, open: 0, in_progress: 0, resolved: 0 };
            this.requests.forEach(req => {
                if (req.status === 'open') counts.open++;
                else if (req.status === 'in_progress') counts.in_progress++;
                else if (req.status === 'resolved') counts.resolved++;
            });
            return counts;
        },

        viewRequestModal(id) {
            if (window.maintenanceViewModal) {
                window.maintenanceViewModal.openModal(id);
            }
        },
        
        get filteredRequests() {
            let filtered = this.requests;
            
            if (this.filterStatus !== 'all') {
                filtered = filtered.filter(req => req.status === this.filterStatus);
            }
            if (this.filterPriority !== 'all') {
                filtered = filtered.filter(req => req.priority === this.filterPriority);
            }
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(req => 
                    (req.unit_number && req.unit_number.toLowerCase().includes(query)) ||
                    (req.tenant_name && req.tenant_name.toLowerCase().includes(query)) ||
                    (req.title && req.title.toLowerCase().includes(query)) ||
                    (req.request_number && req.request_number.toLowerCase().includes(query))
                );
            }
            
            filtered = [...filtered].sort((a, b) => {
                let valA = a[this.sortBy];
                let valB = b[this.sortBy];
                if (this.sortBy === 'created_at') {
                    valA = valA ? new Date(valA).getTime() : 0;
                    valB = valB ? new Date(valB).getTime() : 0;
                }
                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
                if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            return filtered;
        },
        
        get paginatedRequests() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredRequests.slice(start, start + this.itemsPerPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredRequests.length / this.itemsPerPage);
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
        
        getPriorityLabel(priority) {
            const labels = { emergency: 'Emergency', high: 'High', medium: 'Medium', low: 'Low' };
            return labels[priority] || 'Medium';
        },
        
        getPriorityColor(priority) {
            const colors = {
                emergency: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                low: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
            };
            return colors[priority] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusLabel(status) {
            const labels = { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved' };
            return labels[status] || status;
        },
        
        getStatusColor(status) {
            const colors = {
                resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                open: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
            if (page >= 1 && page <= this.totalPages) this.currentPage = page;
        },
        
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
        previousPage() { if (this.currentPage > 1) this.currentPage--; },
        
        openCreateModal() {
            if (window.maintenanceModal) {
                window.maintenanceModal.openModal();
            }
        },
        
        viewRequest(id) {
            window.location.href = `/maintenance/${id}`;
        },
        
        updateRequest(id) {
            window.location.href = `/maintenance/${id}/edit`;
        },

        openEditModal(id) {
            // Fetch the data for this maintenance request
            fetch(`/maintenance/${id}/edit-data`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Dispatch event to open the modal with edit data
                        window.dispatchEvent(new CustomEvent('open-maintenance-edit', {
                            detail: data.request
                        }));
                    }
                })
                .catch(error => console.error('Error fetching maintenance data:', error));
        },
        
        exportRequests() {
            const headers = ['Request #', 'Unit', 'Tenant', 'Title', 'Description', 'Priority', 'Status', 'Reported Date'];
            const rows = this.filteredRequests.map(r => [
                r.request_number,
                r.unit_number,
                r.tenant_name,
                r.title,
                r.description,
                r.priority_label,
                r.status_label,
                this.formatDate(r.created_at)
            ]);
            const csv = [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `maintenance-requests-${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        }
    }));
});
</script>

<!-- Include Maintenance Create Modal -->
{{-- @include('partials.modal.maintenance-create-modal', ['units' => $units ?? []]) --}}

<!-- Include Maintenance Modals -->
{{-- @include('partials.modal.maintenance-create-modal', ['units' => $units ?? [], 'currentUnit' => $currentUnit ?? null]) --}}
@include('partials.modal.maintenance-create-modal', [
    'units' => $units ?? [], 
    'estates' => $estates ?? [],
    'currentUnit' => $currentUnit ?? null
])
@include('partials.modal.maintenance-view-modal')

<style>
[x-cloak] { display: none !important; }
.custom-scrollbar { scrollbar-width: thin; }
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }
.dark .custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
</style>
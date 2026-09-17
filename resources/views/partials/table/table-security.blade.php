@props(['logs' => [], 'units' => [], 'totalLogs' => 0, 'pendingCount' => 0, 'approvedCount' => 0, 'deniedCount' => 0])

<!-- Security Logs Table -->
<div x-data="securityTable()" x-init="init()" x-cloak>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Table Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between border-b border-gray-200 dark:border-gray-700 px-5 py-4 gap-4">
            <!-- Tabs - Balanced layout -->
            <div class="flex flex-wrap gap-1 sm:gap-2">
                <button
                    @click="activeTab = 'all'; filterLogs()"
                    :class="activeTab === 'all' 
                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                >
                    All Logs
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300" x-text="statusCounts.all"></span>
                </button>
                
                <button
                    @click="activeTab = 'pending'; filterLogs()"
                    :class="activeTab === 'pending' 
                        ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                >
                    Pending
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400" x-text="statusCounts.pending"></span>
                </button>
                
                <button
                    @click="activeTab = 'approved'; filterLogs()"
                    :class="activeTab === 'approved' 
                        ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                >
                    Approved
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400" x-text="statusCounts.approved"></span>
                </button>
                
                <button
                    @click="activeTab = 'denied'; filterLogs()"
                    :class="activeTab === 'denied' 
                        ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                >
                    Denied
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400" x-text="statusCounts.denied"></span>
                </button>
            </div>

            <!-- Search and Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Search Bar -->
                <div class="relative">
                    <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        x-model="searchTerm"
                        @input.debounce.300ms="filterLogs()"
                        placeholder="Search..." 
                        class="w-48 lg:w-64 h-9 pl-9 pr-3 text-sm border border-gray-300 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                </div>

                <!-- Access Type Filter -->
                <select 
                    x-model="filters.access_type"
                    @change="filterLogs()"
                    class="h-9 px-3 text-sm border border-gray-300 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option value="entry">Entry</option>
                    <option value="exit">Exit</option>
                    <option value="delivery">Delivery</option>
                    <option value="guest">Guest</option>
                    <option value="contractor">Contractor</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <!-- Clear Filters Button -->
                <button 
                    @click="clearFilters()"
                    x-show="hasActiveFilters"
                    class="h-9 px-3 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                >
                    Clear
                </button>

                <!-- Entries Per Page -->
                <select 
                    x-model="entriesPerPage" 
                    @change="updateTable()"
                    class="h-9 px-2 text-sm border border-gray-300 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <!-- Action Buttons -->
                <div class="flex gap-1.5">
                    <button 
                        @click="openQuickEntryModal()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 text-sm font-medium text-white bg-green-500 rounded-lg hover:bg-green-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Quick Entry
                    </button>

                    <button 
                        @click="openSecurityVisitorModal()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Visitor Mgmt
                    </button>

                    <button 
                        @click="openNewLogModal()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Log
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700" @click="sortBy('access_time')">
                            <div class="flex items-center gap-1">
                                <span>Date & Time</span>
                                <svg :class="sortColumn === 'access_time' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-400'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Person Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Access Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Verified By</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <template x-for="log in paginatedLogs" :key="log.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400" x-text="log.datetime_formatted"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600 dark:text-blue-400" x-text="(log.unit_number || 'U').charAt(0)"></span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white" x-text="log.unit_number"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-800 dark:text-white" x-text="log.person_name"></div>
                                <div x-show="log.visitor_phone" class="text-xs text-gray-500" x-text="log.visitor_phone"></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full"
                                      :class="log.access_type === 'entry' ? 'bg-blue-100 text-blue-700' : log.access_type === 'exit' ? 'bg-gray-100 text-gray-700' : log.access_type === 'delivery' ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700'"
                                      x-text="log.access_type.charAt(0).toUpperCase() + log.access_type.slice(1)"></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full"
                                      :class="log.status === 'approved' || log.status === 'granted' ? 'bg-green-100 text-green-700' : log.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'"
                                      x-text="log.status.charAt(0).toUpperCase() + log.status.slice(1)"></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500" x-text="log.verified_by || 'System'"></td>
                            <td class="px-4 py-3 text-center">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-1 w-36 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10" x-cloak>
                                        <button @click="viewLog(log.id); open = false" class="w-full px-3 py-2 text-left text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700">View</button>
                                        <button @click="editLog(log.id); open = false" class="w-full px-3 py-2 text-left text-sm text-green-600 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</button>
                                        <template x-if="log.status === 'pending'">
                                            <div>
                                                <button @click="approveLog(log.id); open = false" class="w-full px-3 py-2 text-left text-sm text-green-600 hover:bg-gray-100 dark:hover:bg-gray-700">Approve</button>
                                                <button @click="denyLog(log.id); open = false" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">Deny</button>
                                            </div>
                                        </template>
                                        <button @click="confirmDelete(log.id); open = false" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 border-t border-gray-200 dark:border-gray-700">Delete</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="filteredLogs.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>No access logs found</p>
                            <p class="text-sm mt-1" x-show="hasActiveFilters || searchTerm">Try adjusting your filters</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                <div class="text-sm text-gray-500">
                    Showing <span x-text="((currentPage - 1) * entriesPerPage) + 1"></span> to <span x-text="Math.min(currentPage * entriesPerPage, filteredLogs.length)"></span> of <span x-text="filteredLogs.length"></span> entries
                </div>
                <div class="flex items-center gap-1">
                    <button @click="prevPage()" :disabled="currentPage === 1" class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-800">Previous</button>
                    <template x-for="page in visiblePages" :key="page">
                        <button @click="goToPage(page)" :class="page === currentPage ? 'bg-brand-500 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-3 py-1 text-sm rounded-lg transition" x-text="page"></button>
                    </template>
                    <button @click="nextPage()" :disabled="currentPage === totalPages" class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-800">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="security-logs-data">
@json($logs ?? [])
</script>

<script>
function securityTable() {
    return {
        logs: [],
        filteredLogs: [],
        paginatedLogs: [],
        currentPage: 1,
        entriesPerPage: 10,
        searchTerm: '',
        filters: { access_type: '', date_from: '', date_to: '' },
        sortColumn: 'access_time',
        sortDirection: 'desc',
        activeTab: 'all',
        totalPages: 1,
        
        init() {
            const element = document.getElementById('security-logs-data');
            if (element) {
                this.logs = JSON.parse(element.textContent);
            }
            this.filterLogs();
        },
        
        get statusCounts() {
            return {
                all: this.logs.length,
                pending: this.logs.filter(l => l.status === 'pending').length,
                approved: this.logs.filter(l => l.status === 'approved' || l.status === 'granted').length,
                denied: this.logs.filter(l => l.status === 'denied').length
            };
        },
        
        get hasActiveFilters() {
            return this.filters.access_type || this.searchTerm;
        },
        
        filterLogs() {
            let filtered = [...this.logs];
            
            if (this.activeTab !== 'all') {
                filtered = filtered.filter(l => l.status === this.activeTab);
            }
            if (this.filters.access_type) {
                filtered = filtered.filter(l => l.access_type === this.filters.access_type);
            }
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(l => 
                    (l.person_name && l.person_name.toLowerCase().includes(term)) ||
                    (l.unit_number && l.unit_number.toLowerCase().includes(term))
                );
            }
            
            this.filteredLogs = filtered;
            this.sortLogs();
            this.updateTable();
            this.currentPage = 1;
        },
        
        clearFilters() {
            this.filters = { access_type: '', date_from: '', date_to: '' };
            this.searchTerm = '';
            this.filterLogs();
        },
        
        sortLogs() {
            this.filteredLogs.sort((a, b) => {
                let aVal = a[this.sortColumn];
                let bVal = b[this.sortColumn];
                if (this.sortColumn === 'access_time') {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.sortLogs();
            this.updateTable();
        },
        
        updateTable() {
            this.totalPages = Math.ceil(this.filteredLogs.length / this.entriesPerPage);
            const start = (this.currentPage - 1) * this.entriesPerPage;
            this.paginatedLogs = this.filteredLogs.slice(start, start + this.entriesPerPage);
        },
        
        get visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 5) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                if (current <= 3) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                } else if (current >= total - 2) {
                    for (let i = total - 4; i <= total; i++) pages.push(i);
                } else {
                    for (let i = current - 2; i <= current + 2; i++) pages.push(i);
                }
            }
            return pages;
        },
        
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.updateTable(); } },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.updateTable(); } },
        goToPage(page) { this.currentPage = page; this.updateTable(); },
        
        openQuickEntryModal() { if (window.securityQuickEntryModal) window.securityQuickEntryModal.openModal(); },
        openSecurityVisitorModal() { if (typeof openSecurityVisitorModal === 'function') openSecurityVisitorModal(); },
        openNewLogModal() { if (window.securityCrudModal) window.securityCrudModal.openModal(); },
        viewLog(id) { if (window.securityCrudModal) window.securityCrudModal.viewLog(id); },
        editLog(id) { if (window.securityCrudModal) window.securityCrudModal.editLog(id); },
        confirmDelete(id) { if (window.securityCrudModal) window.securityCrudModal.confirmDelete(id); },
        approveLog(id) { if (window.securityCrudModal) window.securityCrudModal.approveLog(id); },
        denyLog(id) { if (window.securityCrudModal) window.securityCrudModal.denyLog(id); }
    };
}

window.securityTable = securityTable;
</script>

<style>
[x-cloak] { display: none !important; }
</style>

@include('partials.modal.security-create-modal')
@include('partials.modal.security-quick-entry-modal')
@include('partials.modal.security-crud-modal')
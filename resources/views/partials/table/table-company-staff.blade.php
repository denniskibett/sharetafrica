{{-- resources/views/partials/table/table-company-staff.blade.php --}}
<div>
    <!-- Staff Table Header -->
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Staff Members</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Showing <span x-text="showingStart"></span> to <span x-text="showingEnd"></span> of 
                <span x-text="filteredStaff.length"></span> staff members
            </p>
        </div>
        <div class="flex gap-2">
            <input type="text" x-model="searchTerm" @input.debounce.300ms="filterStaff()" 
                placeholder="Search staff..." 
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <button @click="window.companyShowPage?.openAddStaffModal()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Staff
            </button>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-500">Loading staff...</span>
    </div>
    
    <!-- Table -->
    <div x-show="!loading" class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium cursor-pointer" @click="sortBy('name')">
                        <div class="flex items-center gap-1">
                            <span>Name</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'name' && sortDirection === 'asc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortColumn === 'name' && sortDirection === 'desc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium cursor-pointer" @click="sortBy('email')">
                        <div class="flex items-center gap-1">
                            <span>Email</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'email' && sortDirection === 'asc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortColumn === 'email' && sortDirection === 'desc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Phone</th>
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium cursor-pointer" @click="sortBy('role_name')">
                        <div class="flex items-center gap-1">
                            <span>Role</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'role_name' && sortDirection === 'asc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortColumn === 'role_name' && sortDirection === 'desc' ? 'text-blue-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Status</th>
                    <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Joined</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="staff in paginatedStaff" :key="staff.id">
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 font-medium text-sm" x-text="getInitials(staff.name)"></span>
                                </div>
                                <span class="font-medium text-gray-800 dark:text-white" x-text="staff.name"></span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400" x-text="staff.email"></td>
                        <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400" x-text="staff.phone || '-'"></td>
                        <td class="py-2.5 px-3">
                            <span :class="getRoleBadgeClass(staff.role)" class="px-2 py-1 text-xs rounded-full" x-text="staff.role_name || staff.role"></span>
                        </td>
                        <td class="py-2.5 px-3">
                            <span :class="staff.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'" class="px-2 py-1 text-xs rounded-full">
                                <span x-text="staff.is_active ? 'Active' : 'Inactive'"></span>
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-gray-500 dark:text-gray-400 text-sm" x-text="formatDate(staff.created_at)"></td>
                    </tr>
                </template>
                
                <tr x-show="filteredStaff.length === 0">
                    <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No staff members found</h3>
                        <p class="mt-1 text-sm" x-show="searchTerm">Try adjusting your search</p>
                        <p class="mt-1 text-sm" x-show="!searchTerm">Add staff members to this company</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div x-show="!loading && filteredStaff.length > 0" class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
        <div class="pb-3 sm:pb-0">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                Showing <span x-text="showingStart"></span> to <span x-text="showingEnd"></span> of <span x-text="filteredStaff.length"></span>
            </span>
        </div>
        <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
            <button @click="prevPage()" :disabled="currentPage === 1" 
                class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/></svg></span>
            </button>
            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            <ul class="hidden items-center gap-0.5 sm:flex">
                <template x-for="page in visiblePages" :key="page">
                    <li><a href="#" @click.prevent="goToPage(page)" :class="page === currentPage ? 'bg-blue-500 text-white' : 'hover:bg-blue-500 text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white'" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium" x-text="page"></a></li>
                </template>
            </ul>
            <button @click="nextPage()" :disabled="currentPage === totalPages" 
                class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/></svg></span>
            </button>
        </div>
    </div>
</div>

<script>
function companyStaffTable() {
    return {
        staff: [],
        filteredStaff: [],
        paginatedStaff: [],
        currentPage: 1,
        entriesPerPage: 10,
        searchTerm: '',
        sortColumn: 'name',
        sortDirection: 'asc',
        showingStart: 1,
        showingEnd: 10,
        totalPages: 1,
        loading: false,
        
        async loadStaff() {
            this.loading = true;
            try {
                const response = await fetch(`/admin/companies/${companyId}/users`, {
                    headers: { 
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Staff data received:', data);
                this.staff = data.users || [];
                
                // Update stats
                if (window.companyShowPage) {
                    window.companyShowPage.stats.totalStaff = this.staff.length;
                }
                this.filterStaff();
            } catch (error) {
                console.error('Error loading staff:', error);
                this.staff = [];
            } finally {
                this.loading = false;
            }
        },
        
        filterStaff() {
            let filtered = [...this.staff];
            if (this.searchTerm.trim()) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(s => 
                    s.name?.toLowerCase().includes(term) ||
                    s.email?.toLowerCase().includes(term)
                );
            }
            this.filteredStaff = filtered;
            this.sortStaff();
            this.updateTable();
            this.currentPage = 1;
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.sortStaff();
            this.updateTable();
        },
        
        sortStaff() {
            this.filteredStaff.sort((a, b) => {
                let aVal = a[this.sortColumn] || '';
                let bVal = b[this.sortColumn] || '';
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        updateTable() {
            this.totalPages = Math.ceil(this.filteredStaff.length / this.entriesPerPage);
            const startIndex = (this.currentPage - 1) * this.entriesPerPage;
            const endIndex = startIndex + this.entriesPerPage;
            this.paginatedStaff = this.filteredStaff.slice(startIndex, endIndex);
            this.showingStart = this.filteredStaff.length ? startIndex + 1 : 0;
            this.showingEnd = Math.min(endIndex, this.filteredStaff.length);
        },
        
        get visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 1) return [1];
            pages.push(1);
            let start = Math.max(2, current - 1);
            let end = Math.min(total - 1, current + 1);
            if (start > 2) pages.push('...');
            for (let i = start; i <= end; i++) {
                if (i > 1 && i < total) pages.push(i);
            }
            if (end < total - 1) pages.push('...');
            if (total > 1) pages.push(total);
            return pages;
        },
        
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updateTable();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updateTable();
            }
        },
        
        goToPage(page) {
            if (page !== '...') {
                this.currentPage = parseInt(page);
                this.updateTable();
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        
        getInitials(name) {
            if (!name) return 'U';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },
        
        getRoleBadgeClass(role) {
            const classes = {
                'admin': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                'super_admin': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'company_admin': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'property_manager': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'estate_manager': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'accountant': 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400',
                'meter_reader': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                'cleaning_staff': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'maintenance': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                'security': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'tenant': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'
            };
            return classes[role] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400';
        }
    };
}
</script>
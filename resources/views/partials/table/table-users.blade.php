{{-- resources/views/partials/table/table-users.blade.php --}}
<!-- Users Table - Pure Alpine.js Component -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="usersTable()" x-init="init()">
    <!-- Table Header -->
    <div class="flex flex-col justify-between gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row lg:items-center dark:border-gray-800">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Users Management</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage system users and their roles</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Role Filters -->
            <div class="hidden h-11 items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 lg:inline-flex dark:bg-gray-900">
                <button @click="filterRole = 'all'; currentPage = 1" :class="filterRole === 'all' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    All (<span x-text="roleCounts.all"></span>)
                </button>
                <button @click="filterRole = 'admin'; currentPage = 1" :class="filterRole === 'admin' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Admin (<span x-text="roleCounts.admin"></span>)
                </button>
                <button @click="filterRole = 'company_admin'; currentPage = 1" :class="filterRole === 'company_admin' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Company Admin (<span x-text="roleCounts.company_admin"></span>)
                </button>
                <button @click="filterRole = 'estate_manager'; currentPage = 1" :class="filterRole === 'estate_manager' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Estate Manager (<span x-text="roleCounts.estate_manager"></span>)
                </button>
                <button @click="filterRole = 'tenant'; currentPage = 1" :class="filterRole === 'tenant' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Tenant (<span x-text="roleCounts.tenant"></span>)
                </button>
            </div>

            <!-- Status Filter -->
            <div class="hidden h-11 items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 lg:inline-flex dark:bg-gray-900">
                <button @click="filterStatus = 'all'; currentPage = 1" :class="filterStatus === 'all' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    All Status
                </button>
                <button @click="filterStatus = 'active'; currentPage = 1" :class="filterStatus === 'active' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Active (<span x-text="statusCounts.active"></span>)
                </button>
                <button @click="filterStatus = 'inactive'; currentPage = 1" :class="filterStatus === 'inactive' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium">
                    Inactive (<span x-text="statusCounts.inactive"></span>)
                </button>
            </div>

            <!-- Search -->
            <div class="relative">
                <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                    </svg>
                </span>
                <input type="text" placeholder="Search by name, email or phone..." x-model="searchQuery" @input="currentPage = 1" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button @click="openCreateModal()" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Add User
                </button>
                <button @click="exportUsers()" class="border border-gray-300 shadow-theme-xs hover:bg-gray-50 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                        <path d="M10 1.66667V12.5M10 12.5L13.3333 9.16667M10 12.5L6.66667 9.16667M4.16667 15H15.8333M4.16667 18.3333H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
        <span class="ml-3 text-gray-500">Loading users...</span>
    </div>
    
    <!-- Table Content -->
    <div x-show="!loading" class="custom-scrollbar overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    <th class="p-4 whitespace-nowrap">
                        <div class="flex w-full cursor-pointer items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                    <span class="relative">
                                        <input type="checkbox" class="sr-only" @change="toggleSelectAll" :checked="isAllSelected"/>
                                        <span :class="isAllSelected ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                                            <span :class="isAllSelected ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                    <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('name')">
                        <div class="flex items-center gap-3">
                            <p>User</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'name' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'name' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('email')">
                        <div class="flex items-center gap-3">
                            <p>Email</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'email' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'email' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Role</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Company</th>
                    <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('created_at')">
                        <div class="flex items-center gap-3">
                            <p>Joined</p>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortBy === 'created_at' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/></svg>
                                <svg :class="sortBy === 'created_at' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/></svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Status</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-x divide-y divide-gray-200 dark:divide-gray-800">
                <template x-for="user in paginatedUsers" :key="user.id">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-4 whitespace-nowrap">
                            <label class="flex cursor-pointer items-center">
                                <span class="relative">
                                    <input type="checkbox" class="sr-only" :checked="selected.includes(user.id)" @change="toggleRow(user.id)"/>
                                    <span :class="selected.includes(user.id) ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                                        <span :class="selected.includes(user.id) ? '' : 'opacity-0'">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300" x-text="getInitials(user.name)"></span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="user.name"></span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="user.phone || 'No phone'"></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="user.email"></span>
                            <p class="text-xs text-gray-400" x-text="user.email_verified_at ? 'Verified' : 'Not verified'"></p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span :class="getRoleBadgeClass(user.role)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full" x-text="formatRole(user.role)"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="user.company_name || '-'"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(user.created_at)"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <button @click="toggleUserStatus(user)" :class="user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'" class="text-theme-xs rounded-full px-2 py-0.5 font-medium">
                                <span x-text="user.is_active ? 'Active' : 'Inactive'"></span>
                            </button>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                <div x-data="dropdown()" class="relative">
                                    <button @click="toggle" class="text-gray-500 dark:text-gray-400">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" class="shadow-theme-lg dark:bg-gray-dark absolute right-0 z-10 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-ref="dropdown">
                                        <button @click="viewUser(user.id)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">View</button>
                                        <button @click="openEditModal(user)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Edit</button>
                                        <button @click="resetPassword(user)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-yellow-500 hover:bg-yellow-50 hover:text-yellow-700 dark:text-yellow-400 dark:hover:bg-yellow-500/10">Reset Password</button>
                                        <button @click="deleteUser(user.id, user.name)" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10">Delete</button>
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
    <div x-show="!loading && filteredUsers.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No users found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new user.</p>
        <div class="mt-6">
            <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700">
                Add User
            </button>
        </div>
    </div>
    
    <!-- Pagination -->
    <div x-show="!loading && filteredUsers.length > 0" class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
        <div class="pb-3 sm:pb-0">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                Showing <span x-text="((currentPage - 1) * itemsPerPage) + (paginatedUsers.length ? 1 : 0)"></span>
                to <span x-text="((currentPage - 1) * itemsPerPage) + paginatedUsers.length"></span>
                of <span x-text="filteredUsers.length"></span>
            </span>
        </div>
        <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="previousPage" :disabled="currentPage === 1">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/></svg></span>
            </button>
            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            <ul class="hidden items-center gap-0.5 sm:flex">
                <template x-for="page in visiblePages" :key="page">
                    <li><a href="#" @click.prevent="goToPage(page)" :class="page === currentPage ? 'bg-brand-500 text-white' : 'hover:bg-brand-500 text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white'" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium" x-text="page"></a></li>
                </template>
            </ul>
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="nextPage" :disabled="currentPage === totalPages">
                <span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/></svg></span>
            </button>
        </div>
    </div>
</div>

<!-- Include Create/Edit User Modal -->
@include('partials.modal.user-create-modal')

<script>
const csrfTokenUsers = "{{ csrf_token() }}";

document.addEventListener('alpine:init', () => {
    // Users Table Component
    Alpine.data('usersTable', () => ({
        users: [],
        selected: [],
        sortBy: 'created_at',
        sortDirection: 'desc',
        currentPage: 1,
        itemsPerPage: 10,
        filterRole: 'all',
        filterStatus: 'all',
        searchQuery: '',
        loading: true,
        
        get roleCounts() {
            return {
                all: this.users.length,
                admin: this.users.filter(u => u.role === 'admin').length,
                company_admin: this.users.filter(u => u.role === 'company_admin').length,
                estate_manager: this.users.filter(u => u.role === 'estate_manager').length,
                tenant: this.users.filter(u => u.role === 'tenant').length
            };
        },
        
        get statusCounts() {
            return {
                active: this.users.filter(u => u.is_active).length,
                inactive: this.users.filter(u => !u.is_active).length
            };
        },
        
        get filteredUsers() {
            let filtered = this.users;
            
            if (this.filterRole !== 'all') {
                filtered = filtered.filter(u => u.role === this.filterRole);
            }
            
            if (this.filterStatus === 'active') {
                filtered = filtered.filter(u => u.is_active);
            } else if (this.filterStatus === 'inactive') {
                filtered = filtered.filter(u => !u.is_active);
            }
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(u => 
                    (u.name && u.name.toLowerCase().includes(query)) ||
                    (u.email && u.email.toLowerCase().includes(query)) ||
                    (u.phone && u.phone.toLowerCase().includes(query))
                );
            }
            
            return filtered;
        },
        
        get sortedUsers() {
            return this.filteredUsers.slice().sort((a, b) => {
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
                
                if (valA === null || valA === undefined) return 1;
                if (valB === null || valB === undefined) return -1;
                if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        get paginatedUsers() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.sortedUsers.slice(start, start + this.itemsPerPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
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
        
        get isAllSelected() {
            return this.paginatedUsers.length > 0 && this.paginatedUsers.every(u => this.selected.includes(u.id));
        },
        
        async init() {
            await this.loadUsers();
        },
        
        async loadUsers() {
            this.loading = true;
            try {
                const response = await fetch('/admin/users/data', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    const result = await response.json();
                    this.users = result.users || [];
                } else {
                    this.users = [];
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                this.users = [];
            } finally {
                this.loading = false;
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        
        formatRole(role) {
            const roleMap = {
                'admin': 'Admin',
                'company_admin': 'Company Admin',
                'estate_manager': 'Estate Manager',
                'tenant': 'Tenant'
            };
            return roleMap[role] || role;
        },
        
        getInitials(name) {
            if (!name) return 'U';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },
        
        getRoleBadgeClass(role) {
            const classes = {
                'admin': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                'company_admin': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'estate_manager': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'tenant': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
            };
            return classes[role] || 'bg-gray-100 text-gray-800';
        },
        
        sort(field) {
            if (this.sortBy === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortDirection = 'asc';
            }
        },
        
        toggleSelectAll() {
            if (this.isAllSelected) {
                this.selected = [];
            } else {
                this.selected = this.paginatedUsers.map(u => u.id);
            }
        },
        
        toggleRow(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
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
        
        viewUser(userId) {
            window.location.href = `/admin/users/${userId}`;
        },
        
        openCreateModal() {
            if (window.userCreateModal) {
                window.userCreateModal.openModal();
            }
        },
        
        openEditModal(user) {
            if (window.userCreateModal) {
                window.userCreateModal.openModal(user.id);
                setTimeout(() => {
                    if (window.userCreateModal) {
                        window.userCreateModal.form.name = user.name;
                        window.userCreateModal.form.email = user.email;
                        window.userCreateModal.form.phone = user.phone || '';
                        window.userCreateModal.form.role = user.role;
                        window.userCreateModal.form.company_id = user.company_id || '';
                        window.userCreateModal.isEditMode = true;
                        window.userCreateModal.userId = user.id;
                        window.userCreateModal.showPasswordField = false;
                    }
                }, 100);
            }
        },
        
        async toggleUserStatus(user) {
            const newStatus = !user.is_active;
            try {
                const response = await fetch(`/admin/users/${user.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfTokenUsers,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ...user, is_active: newStatus })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    user.is_active = newStatus;
                    await this.loadUsers();
                } else {
                    alert(result.message || 'Error updating user status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating user status');
            }
        },
        
        async resetPassword(user) {
            if (!confirm(`Reset password for ${user.name}? A new temporary password will be sent to their email.`)) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/users/${user.id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfTokenUsers,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || 'Password reset successfully. New password sent to user\'s email.');
                } else {
                    alert(result.message || 'Error resetting password');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error resetting password');
            }
        },
        
        async deleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to delete ${userName}? This action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfTokenUsers,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await this.loadUsers();
                    alert(result.message);
                } else {
                    alert(result.message || 'Error deleting user');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting user');
            }
        },
        
        exportUsers() {
            const headers = ['ID', 'Name', 'Email', 'Phone', 'Role', 'Company', 'Status', 'Joined Date'];
            const rows = this.filteredUsers.map(u => [
                u.id,
                u.name,
                u.email,
                u.phone || 'N/A',
                this.formatRole(u.role),
                u.company_name || 'N/A',
                u.is_active ? 'Active' : 'Inactive',
                this.formatDate(u.created_at)
            ]);
            
            const csv = [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `users-${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        }
    }));
    
    // Dropdown Component
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
.custom-scrollbar { scrollbar-width: thin; }
.custom-scrollbar::-webkit-scrollbar { height: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }
.dark .custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
</style>
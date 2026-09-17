    <!-- Tenants Table -->
    <div class="container mx-auto px-4 py-6" x-data="tenantTable()" x-init="init()">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <!-- Table Header with Search -->
            <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">All Tenants</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Showing <span x-text="showingStart"></span> to <span x-text="showingEnd"></span> of 
                        <span x-text="filteredTenants.length"></span> tenants
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center">
                        <label for="entriesPerPage" class="text-sm text-gray-500 dark:text-gray-400 mr-2 hidden sm:inline">Show:</label>
                        <div class="relative">
                            <select 
                                x-model="entriesPerPage" 
                                @change="updateTable()"
                                id="entriesPerPage" 
                                class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 pr-8"
                            >
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative flex-1 min-w-[150px]">
                        <input 
                            type="text" 
                            x-model="searchTerm"
                            @input.debounce.300ms="filterTenants()"
                            id="tenantSearch" 
                            placeholder="Search tenants..." 
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 pl-10"
                        >
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Add Tenant Button -->
                    <button 
                        @click="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-blue-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Tenant
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="min-w-full" id="tenantsTable">
                    <!-- Desktop table header -->
                    <thead class="hidden sm:table-header-group">
                        <tr class="border-gray-100 border-y dark:border-gray-800">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('name')">
                                <div class="flex items-center justify-between">
                                    <span>Tenant</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'name' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'name' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'name'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('email')">
                                <div class="flex items-center justify-between">
                                    <span>Email</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'email' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'email' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'email'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th> --}}
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('phone')">
                                <div class="flex items-center justify-between">
                                    <span>Phone</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'phone' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'phone' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'phone'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('current_unit')">
                                <div class="flex items-center justify-between">
                                    <span>Current Unit</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'current_unit' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'current_unit' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'current_unit'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('tenancies_count')">
                                <div class="flex items-center justify-between">
                                    <span>Tenancies</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'tenancies_count' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'tenancies_count' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'tenancies_count'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('created_at')">
                                <div class="flex items-center justify-between">
                                    <span>Joined</span>
                                    <span class="sort-icon text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="sortColumn === 'created_at' && sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            <path x-show="sortColumn === 'created_at' && sortDirection === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 15l-7-7-7 7" />
                                            <path x-show="sortColumn !== 'created_at'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    
                    <!-- Mobile table header -->
                    <thead class="sm:hidden">
                        <tr class="border-gray-100 border-y dark:border-gray-800">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tenant
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800" id="tenantsTableBody">
                        <template x-for="tenant in paginatedTenants" :key="tenant.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                <!-- Desktop cells -->
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <span class="text-purple-600 font-medium" x-text="tenant.name?.charAt(0)?.toUpperCase() || 'T'"></span>
                                        </div>
                                        <div>
                                            <a :href="`/tenants/${tenant.id}`" class="font-medium text-gray-800 text-sm dark:text-white/90 hover:text-blue-600">
                                                <span x-text="tenant.name"></span>
                                            </a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="tenant.email"></p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="tenant.email"></div>
                                </td>
                                
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <div x-text="tenant.phone || '-'"></div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <template x-if="tenant.current_unit">
                                        <div>
                                            <a :href="`/units/${tenant.current_unit.id}`" class="text-sm font-medium text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                <span x-text="tenant.current_unit.unit_number"></span>
                                            </a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="tenant.current_unit.estate_name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Since <span x-text="tenant.current_unit.move_in_date_formatted"></span>
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="!tenant.current_unit">
                                        <span class="text-sm text-gray-400">No active tenancy</span>
                                    </template>
                                </td>
                                
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="tenant.tenancies_count"></div>
                                </td>
                                
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="tenant.created_at_formatted"></div>
                                </td>
                                
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <!-- View -->
                                        <a :href="`/tenants/${tenant.id}`" class="text-blue-600 hover:text-blue-900 dark:text-blue-400" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <button @click="openEditModal(tenant)" class="text-green-600 hover:text-green-900 dark:text-green-400" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>

                                        <!-- Delete -->
                                        <button @click="confirmDelete(tenant)" class="text-red-600 hover:text-red-900 dark:text-red-400" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                
                                <!-- Mobile cells (simplified view) -->
                                <td class="px-6 py-4 sm:hidden">
                                    <div class="flex items-center gap-3">
                                        <div class="h-[40px] w-[40px] overflow-hidden rounded-md bg-purple-100 flex items-center justify-center">
                                            <span class="text-purple-600 font-medium" x-text="tenant.name?.charAt(0)?.toUpperCase() || 'T'"></span>
                                        </div>
                                        <div>
                                            <a :href="`/tenants/${tenant.id}`" class="font-medium text-gray-800 text-sm dark:text-white/90 hover:text-blue-600">
                                                <span x-text="tenant.name"></span>
                                            </a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="tenant.email"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <template x-if="tenant.current_unit">
                                                    <span class="text-xs text-blue-600 font-medium" x-text="tenant.current_unit.unit_number"></span>
                                                </template>
                                                <template x-if="!tenant.current_unit">
                                                    <span class="text-xs text-gray-400">No unit</span>
                                                </template>
                                                <span class="text-xs text-gray-500">•</span>
                                                <span class="text-xs text-gray-500" x-text="tenant.tenancies_count + ' tenancies'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 sm:hidden text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a :href="`/tenants/${tenant.id}`" class="text-blue-600 hover:text-blue-900 inline-block" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                        
                                        <button @click="openEditModal(tenant)" class="text-green-600 hover:text-green-900 inline-block" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        
                                        <button @click="confirmDelete(tenant)" class="text-red-600 hover:text-red-900 inline-block" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        
                        <tr x-show="filteredTenants.length === 0">
                            <td colspan="7" class="py-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tenants found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-show="searchTerm">
                                    Try adjusting your search or filter criteria
                                </p>
                                <div class="mt-4">
                                    <button 
                                        @click="openCreateModal()"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Tenant
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="flex flex-col items-center justify-between px-2 py-4 sm:flex-row sm:px-0">
                    <div class="hidden sm:flex">
                        <p class="text-sm text-gray-700 dark:text-gray-400">
                            Showing <span x-text="showingStart"></span> to <span x-text="showingEnd"></span> of 
                            <span x-text="filteredTenants.length"></span> results
                        </p>
                    </div>
                    <div class="flex-1 flex justify-between sm:justify-end">
                        <button 
                            @click="prevPage()" 
                            :disabled="currentPage === 1"
                            class="relative inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <div id="paginationNumbers" class="hidden sm:flex">
                            <template x-for="page in visiblePages" :key="page">
                                <button 
                                    @click="goToPage(page)"
                                    :class="{
                                        'relative inline-flex items-center px-4 py-2 text-sm font-medium': true,
                                        'bg-blue-600 text-white': currentPage === page,
                                        'text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700': currentPage !== page && page !== '...',
                                        'cursor-default': page === '...'
                                    }"
                                    x-text="page"
                                ></button>
                            </template>
                        </div>
                        <button 
                            @click="nextPage()" 
                            :disabled="currentPage === totalPages"
                            class="relative ml-3 inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Hidden data element for Alpine.js -->
<script type="application/json" id="tenants-data">
@json($tenantsData)
</script>

<!-- Single Create Tenant Modal -->
<div x-data="createTenantModal()" x-show="isModalOpen" x-cloak>
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isModalOpen = false"
            class="relative w-full max-w-[584px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- close btn -->
            <button
                @click="isModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    />
                </svg>
            </button>

            <form @submit.prevent="submitForm()">
                <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                    Add New Tenant
                </h4>

                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Email will be generated automatically from name and phone.
                        <br><span class="text-xs text-gray-500">Example: john.doe.7890@tenant.com</span>
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-5">
                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Full Name *
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            placeholder="John Doe"
                        />
                        <p x-show="errors.name" x-text="errors.name[0]" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Phone Number *
                        </label>
                        <input
                            type="tel"
                            x-model="form.phone"
                            required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            placeholder="0712345678"
                        />
                        <p x-show="errors.phone" x-text="errors.phone[0]" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Tenancy Section (Optional) -->
                    <div class="col-span-2 mt-2">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-4 w-4 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Assign to Unit (Optional)</h5>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Unit
                        </label>
                        <div class="relative">
                            <select
                                x-model="form.unit_id"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            >
                                <option value="">Select a unit (optional)</option>
                                <template x-for="unit in units" :key="unit.id">
                                    <option :value="unit.id" x-text="`${unit.unit_number} - ${unit.estate_name}`"></option>
                                </template>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            If assigned, tenancy will start on Jan 1, 2025
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end w-full gap-3 mt-6">
                    <button
                        @click="isModalOpen = false"
                        type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    >
                        Close
                    </button>
                    
                    <!-- Switch to Bulk Add -->
                    <button
                        type="button"
                        @click="switchToBulk()"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-green-600 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-green-800 dark:border-gray-700 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-white/[0.03] dark:hover:text-green-300 sm:w-auto"
                    >
                        Add Multiple
                    </button>
                    
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-600 shadow-theme-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    >
                        <span x-show="!loading">Create Tenant</span>
                        <span x-show="loading">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Create Tenant Modal -->
<div x-data="bulkCreateTenantModal()" x-show="isModalOpen" x-cloak>
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isModalOpen = false"
            class="relative w-full max-w-[900px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- close btn -->
            <button
                @click="isModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    />
                </svg>
            </button>

            <form @submit.prevent="submitForm()">
                <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                    Bulk Add Tenants
                </h4>

                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h5 class="mb-2 text-sm font-medium text-blue-700 dark:text-blue-300">Quick Add Multiple Tenants</h5>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Add multiple tenants at once. Only name and phone are required. You can assign each tenant to a different unit.
                        <br><span class="text-xs text-gray-500">Emails will be generated automatically. Example: john.doe.7890@tenant.com</span>
                        <br><span class="text-xs text-gray-500">Move-in date will be set to Jan 1, 2025 for all assigned units.</span>
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Table Header -->
                    <div class="grid grid-cols-12 gap-3 text-xs font-medium text-gray-500 dark:text-gray-400 px-2">
                        <div class="col-span-4">Name *</div>
                        <div class="col-span-3">Phone *</div>
                        <div class="col-span-4">Unit (Optional)</div>
                        <div class="col-span-1"></div>
                    </div>

                    <!-- Tenant Rows -->
                    <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                        <template x-for="(tenant, index) in form.tenants" :key="index">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <!-- Name -->
                                <div class="col-span-4">
                                    <input
                                        type="text"
                                        x-model="tenant.name"
                                        :required="index === 0"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        :placeholder="`Tenant ${index + 1} Name`"
                                    />
                                </div>
                                
                                <!-- Phone -->
                                <div class="col-span-3">
                                    <input
                                        type="tel"
                                        x-model="tenant.phone"
                                        :required="index === 0"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        :placeholder="`07XXXXXXX`"
                                    />
                                </div>
                                
                                <!-- Unit -->
                                <div class="col-span-4">
                                    <select
                                        x-model="tenant.unit_id"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    >
                                        <option value="">Select unit (optional)</option>
                                        <template x-for="unit in units" :key="unit.id">
                                            <option :value="unit.id" x-text="`${unit.unit_number} - ${unit.estate_name}`"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <!-- Remove Button -->
                                <div class="col-span-1 flex items-center justify-center">
                                    <button
                                        type="button"
                                        @click="removeTenant(index)"
                                        class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50"
                                        x-show="index > 0"
                                        title="Remove this tenant"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="space-x-3">
                            <button
                                type="button"
                                @click="addTenant()"
                                class="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Another Row
                            </button>
                            
                            <button
                                type="button"
                                @click="assignAllToUnit()"
                                class="inline-flex items-center gap-2 rounded-lg border border-green-300 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 shadow-theme-xs transition-colors hover:bg-green-100 hover:text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Assign All to Same Unit
                            </button>
                        </div>
                        
                        <button
                            type="button"
                            @click="switchToSingle()"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                        >
                            ← Back to Single Add
                        </button>
                    </div>
                    
                    <!-- Unit Selection for All -->
                    <div x-show="showBulkUnitAssign" class="mt-4 p-4 border border-green-200 dark:border-green-800 rounded-lg bg-green-50 dark:bg-green-900/10">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="h-4 w-4 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h5 class="text-sm font-medium text-green-700 dark:text-green-300">
                                    Assign All Tenants to Same Unit
                                </h5>
                            </div>
                            <button
                                type="button"
                                @click="showBulkUnitAssign = false"
                                class="text-green-600 hover:text-green-800"
                            >
                                Cancel
                            </button>
                        </div>
                        <select
                            x-model="bulkUnitId"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-green-300 bg-white px-4 py-2.5 text-sm text-green-800 shadow-theme-xs placeholder:text-green-400 focus:border-green-300 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-700 dark:bg-gray-900 dark:text-green-400 dark:placeholder:text-green-300"
                            @change="applyBulkUnit()"
                        >
                            <option value="">Select a unit for all tenants</option>
                            <template x-for="unit in units" :key="unit.id">
                                <option :value="unit.id" x-text="`${unit.unit_number} - ${unit.estate_name}`"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                            This will assign the selected unit to ALL tenants in the list above.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end w-full gap-3 mt-6">
                    <button
                        @click="isModalOpen = false"
                        type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-green-600 shadow-theme-xs hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    >
                        <span x-show="!loading">Create All Tenants (<span x-text="form.tenants.length"></span>)</span>
                        <span x-show="loading">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tenant Modal (Simplified) -->
<div x-data="editTenantModal()" x-show="isModalOpen" x-cloak>
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isModalOpen = false"
            class="relative w-full max-w-[584px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- close btn -->
            <button
                @click="isModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    />
                </svg>
            </button>

            <form @submit.prevent="submitForm()">
                <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                    Edit Tenant
                </h4>

                <div class="grid grid-cols-1 gap-x-6 gap-y-5">
                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Full Name *
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                        <p x-show="errors.name" x-text="errors.name[0]" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Email *
                        </label>
                        <input
                            type="email"
                            x-model="form.email"
                            required
                            @blur="checkEmail()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                        <p x-show="errors.email" x-text="errors.email[0]" class="mt-1 text-sm text-red-600"></p>
                        <p x-show="emailError" x-text="emailError" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Phone Number *
                        </label>
                        <input
                            type="tel"
                            x-model="form.phone"
                            required
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                        <p x-show="errors.phone" x-text="errors.phone[0]" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>

                <div class="flex items-center justify-end w-full gap-3 mt-6">
                    <button
                        @click="isModalOpen = false"
                        type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-600 shadow-theme-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    >
                        <span x-show="!loading">Update Tenant</span>
                        <span x-show="loading">Updating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Tenant Modal -->
<div x-data="deleteTenantModal()" x-show="isModalOpen" x-cloak>
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isModalOpen = false"
            class="relative w-full max-w-[584px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- close btn -->
            <button
                @click="isModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    />
                </svg>
            </button>

            <div class="text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                
                <h4 class="mb-2 text-lg font-medium text-gray-800 dark:text-white/90">
                    Delete Tenant
                </h4>
                
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete the tenant 
                    "<span x-text="tenant?.name" class="font-medium"></span>"? 
                </p>

                <div class="flex items-center justify-center gap-3">
                    <button
                        @click="isModalOpen = false"
                        type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteTenant()"
                        :disabled="loading"
                        type="button"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-600 shadow-theme-xs hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    >
                        <span x-show="!loading">Delete Tenant</span>
                        <span x-show="loading">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data element for Alpine.js -->
<script type="application/json" id="tenants-data">
@json($tenantsData)
</script>

<script type="application/json" id="vacant-units-data">
@json($vacantUnits)
</script>

<script>
function tenantTable() {
    return {
        // Data
        tenants: [],
        filteredTenants: [],
        paginatedTenants: [],
        currentPage: 1,
        entriesPerPage: 10,
        searchTerm: '',
        sortColumn: 'name',
        sortDirection: 'asc',
        showingStart: 1,
        showingEnd: 10,
        totalPages: 1,
        
        // Modal instances
        createModal: null,
        bulkCreateModal: null,
        editModal: null,
        deleteModal: null,
        
        init() {
            // Load tenants data
            const tenantsElement = document.getElementById('tenants-data');
            if (tenantsElement) {
                this.tenants = JSON.parse(tenantsElement.textContent);
            }
            
            this.filteredTenants = [...this.tenants];
            this.updateTable();
            
            // Initialize modals after DOM is ready
            this.$nextTick(() => {
                this.createModal = Alpine.$data(document.querySelector('[x-data="createTenantModal()"]'));
                this.bulkCreateModal = Alpine.$data(document.querySelector('[x-data="bulkCreateTenantModal()"]'));
                this.editModal = Alpine.$data(document.querySelector('[x-data="editTenantModal()"]'));
                this.deleteModal = Alpine.$data(document.querySelector('[x-data="deleteTenantModal()"]'));
            });
        },
        
        filterTenants() {
            if (!this.searchTerm.trim()) {
                this.filteredTenants = [...this.tenants];
            } else {
                const term = this.searchTerm.toLowerCase();
                this.filteredTenants = this.tenants.filter(tenant => {
                    return (
                        (tenant.name && tenant.name.toLowerCase().includes(term)) ||
                        (tenant.email && tenant.email.toLowerCase().includes(term)) ||
                        (tenant.phone && tenant.phone.includes(term)) ||
                        (tenant.phone2 && tenant.phone2.includes(term)) ||
                        (tenant.current_unit && tenant.current_unit.unit_number && 
                         tenant.current_unit.unit_number.toLowerCase().includes(term)) ||
                        (tenant.current_unit && tenant.current_unit.estate_name && 
                         tenant.current_unit.estate_name.toLowerCase().includes(term))
                    );
                });
            }
            
            this.sortTenants();
            this.updateTable();
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.sortTenants();
            this.updateTable();
        },
        
        sortTenants() {
            this.filteredTenants.sort((a, b) => {
                let aValue, bValue;
                
                // Handle current_unit sorting
                if (this.sortColumn === 'current_unit') {
                    aValue = a.current_unit?.unit_number || '';
                    bValue = b.current_unit?.unit_number || '';
                } else {
                    aValue = a[this.sortColumn]?.toString().toLowerCase() || '';
                    bValue = b[this.sortColumn]?.toString().toLowerCase() || '';
                }
                
                // Handle numeric sorting for tenancies_count
                if (this.sortColumn === 'tenancies_count') {
                    aValue = parseInt(aValue) || 0;
                    bValue = parseInt(bValue) || 0;
                    
                    if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                }
                
                // Handle date sorting
                if (this.sortColumn === 'created_at') {
                    aValue = a.created_at ? new Date(a.created_at) : new Date(0);
                    bValue = b.created_at ? new Date(b.created_at) : new Date(0);
                    
                    if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                }
                
                // Regular string sorting
                if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        updateTable() {
            this.totalPages = Math.ceil(this.filteredTenants.length / this.entriesPerPage);
            const startIndex = (this.currentPage - 1) * this.entriesPerPage;
            const endIndex = startIndex + this.entriesPerPage;
            
            this.paginatedTenants = this.filteredTenants.slice(startIndex, endIndex);
            this.showingStart = this.filteredTenants.length ? startIndex + 1 : 0;
            this.showingEnd = Math.min(endIndex, this.filteredTenants.length);
        },
        
        get visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            
            if (total <= 1) return [1];
            
            pages.push(1);
            
            let start = Math.max(2, current - 1);
            let end = Math.min(total - 1, current + 1);
            
            if (start > 2) {
                pages.push('...');
            }
            
            for (let i = start; i <= end; i++) {
                if (i > 1 && i < total) {
                    pages.push(i);
                }
            }
            
            if (end < total - 1) {
                pages.push('...');
            }
            
            if (total > 1) {
                pages.push(total);
            }
            
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
        
        openCreateModal() {
            if (this.createModal) {
                this.createModal.open();
            }
        },
        
        openBulkCreateModal() {
            if (this.bulkCreateModal) {
                this.bulkCreateModal.open();
            }
        },
        
        openEditModal(tenant) {
            if (this.editModal) {
                this.editModal.open(tenant);
            }
        },
        
        confirmDelete(tenant) {
            if (this.deleteModal) {
                this.deleteModal.open(tenant);
            }
        }
    };
}

function createTenantModal() {
    return {
        isModalOpen: false,
        units: [],
        form: {
            name: '',
            phone: '',
            unit_id: null
        },
        errors: {},
        loading: false,
        
        open() {
            this.resetForm();
            this.loadVacantUnits();
            this.isModalOpen = true;
        },
        
        resetForm() {
            this.form = {
                name: '',
                phone: '',
                unit_id: null
            };
            this.errors = {};
            this.loading = false;
        },
        
        loadVacantUnits() {
            const unitsElement = document.getElementById('vacant-units-data');
            if (unitsElement) {
                this.units = JSON.parse(unitsElement.textContent);
            }
        },
        
        switchToBulk() {
            this.isModalOpen = false;
            const bulkModal = Alpine.$data(document.querySelector('[x-data="bulkCreateTenantModal()"]'));
            if (bulkModal) {
                // Pre-populate bulk form with current single entry
                bulkModal.form.tenants = [{
                    name: this.form.name,
                    phone: this.form.phone
                }];
                bulkModal.open();
            }
        },
        
        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            try {
                const response = await fetch('/tenants', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.isModalOpen = false;
                    this.showNotification('Tenant created successfully! Email: ' + data.tenant?.user?.email, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.errors = data.errors || {};
                    this.showNotification(data.message || 'Failed to create tenant.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        showNotification(message, type = 'success') {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white shadow-lg z-99999 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z'}">
                        </path>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };
}

function bulkCreateTenantModal() {
    return {
        isModalOpen: false,
        units: [],
        form: {
            tenants: [
                { name: '', phone: '', unit_id: '' }
            ]
        },
        showBulkUnitAssign: false,
        bulkUnitId: '',
        errors: {},
        loading: false,
        
        open() {
            this.resetForm();
            this.loadVacantUnits();
            this.isModalOpen = true;
        },
        
        resetForm() {
            this.form = {
                tenants: [
                    { name: '', phone: '', unit_id: '' }
                ]
            };
            this.showBulkUnitAssign = false;
            this.bulkUnitId = '';
            this.errors = {};
            this.loading = false;
        },
        
        addTenant() {
            this.form.tenants.push({ name: '', phone: '', unit_id: '' });
        },
        
        removeTenant(index) {
            if (this.form.tenants.length > 1) {
                this.form.tenants.splice(index, 1);
            }
        },
        
        loadVacantUnits() {
            const unitsElement = document.getElementById('vacant-units-data');
            if (unitsElement) {
                this.units = JSON.parse(unitsElement.textContent);
            }
        },
        
        assignAllToUnit() {
            this.showBulkUnitAssign = true;
            this.bulkUnitId = '';
        },
        
        applyBulkUnit() {
            if (this.bulkUnitId) {
                // Apply the selected unit to all tenants
                this.form.tenants.forEach(tenant => {
                    tenant.unit_id = this.bulkUnitId;
                });
                this.showNotification(`All tenants assigned to selected unit`, 'success');
            }
        },
        
        switchToSingle() {
            this.isModalOpen = false;
            const singleModal = Alpine.$data(document.querySelector('[x-data="createTenantModal()"]'));
            if (singleModal && this.form.tenants.length > 0) {
                singleModal.form = {
                    name: this.form.tenants[0].name,
                    phone: this.form.tenants[0].phone,
                    unit_id: this.form.tenants[0].unit_id || null
                };
                singleModal.open();
            }
        },
        
        async submitForm() {
            // Validate at least one tenant has name and phone
            const validTenants = this.form.tenants.filter(t => t.name.trim() && t.phone.trim());
            
            if (validTenants.length === 0) {
                this.showNotification('Please add at least one tenant with name and phone.', 'error');
                return;
            }
            
            // Prepare data for submission
            const tenantsData = validTenants.map(tenant => ({
                name: tenant.name.trim(),
                phone: tenant.phone.trim(),
                unit_id: tenant.unit_id || null
            }));
            
            this.loading = true;
            this.errors = {};
            
            try {
                const response = await fetch('/tenants/bulk-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ tenants: tenantsData })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.isModalOpen = false;
                    
                    // Show success message with created emails
                    let successMessage = `${data.created?.length || 0} tenants created successfully!`;
                    
                    if (data.created && data.created.length > 0) {
                        // Group by unit assignment for better display
                        const withUnit = data.created.filter(t => t.unit).map(t => `• ${t.tenant}: ${t.email} → ${t.unit}`);
                        const withoutUnit = data.created.filter(t => !t.unit).map(t => `• ${t.tenant}: ${t.email} (no unit)`);
                        
                        successMessage += '\n\n';
                        
                        if (withUnit.length > 0) {
                            successMessage += 'With unit assignment:\n' + withUnit.join('\n');
                        }
                        
                        if (withoutUnit.length > 0) {
                            if (withUnit.length > 0) successMessage += '\n\n';
                            successMessage += 'Without unit assignment:\n' + withoutUnit.join('\n');
                        }
                    }
                    
                    this.showNotification(successMessage, 'success');
                    
                    // Show any errors if they occurred
                    if (data.errors && data.errors.length > 0) {
                        setTimeout(() => {
                            const errorMessages = data.errors.map(e => `Row ${e.index + 1} (${e.tenant}): ${e.error}`).join('\n');
                            this.showNotification(`Some tenants could not be created:\n${errorMessages}`, 'error');
                        }, 500);
                    }
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.errors = data.errors || {};
                    this.showNotification(data.message || 'Failed to create tenants.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        showNotification(message, type = 'success') {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white shadow-lg z-99999 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z'}">
                        </path>
                    </svg>
                    <span class="whitespace-pre-line">${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    };
}

function editTenantModal() {
    return {
        isModalOpen: false,
        tenant: null,
        form: {
            name: '',
            email: '',
            phone: ''
        },
        errors: {},
        loading: false,
        
        open(tenant) {
            this.tenant = tenant;
            this.form = {
                name: tenant.name || '',
                email: tenant.email || '',
                phone: tenant.phone || ''
            };
            this.errors = {};
            this.loading = false;
            this.isModalOpen = true;
        },
        
        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            try {
                const response = await fetch(`/tenants/${this.tenant.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.isModalOpen = false;
                    this.showNotification('Tenant updated successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.errors = data.errors || {};
                    this.showNotification(data.message || 'Failed to update tenant.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        showNotification(message, type = 'success') {
            alert(message);
        }
    };
}

function deleteTenantModal() {
    return {
        isModalOpen: false,
        tenant: null,
        loading: false,
        
        open(tenant) {
            this.tenant = tenant;
            this.loading = false;
            this.isModalOpen = true;
        },
        
        async deleteTenant() {
            this.loading = true;
            
            try {
                const response = await fetch(`/tenants/${this.tenant.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.isModalOpen = false;
                    this.showNotification('Tenant deleted successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotification(data.message || 'Failed to delete tenant.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        showNotification(message, type = 'success') {
            alert(message);
        }
    };
}

// Global functions to open modals
window.openCreateModal = function() {
    const modal = Alpine.$data(document.querySelector('[x-data="createTenantModal()"]'));
    if (modal) modal.open();
};

window.openBulkCreateModal = function() {
    const modal = Alpine.$data(document.querySelector('[x-data="bulkCreateTenantModal()"]'));
    if (modal) modal.open();
};
</script>

<style>
[x-cloak] { display: none !important; }
.modal-close-btn {
    backdrop-filter: blur(32px);
}
.z-99999 {
    z-index: 99999;
}
.z-999 {
    z-index: 999;
}
</style>
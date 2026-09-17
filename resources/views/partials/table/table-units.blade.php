<!-- Units Table with Tabs in Header -->
@php
    // Set default values if not provided
    $showEstateColumn = $showEstateColumn ?? true;
    $showUtilityColumns = $showUtilityColumns ?? true;
    $showTotalColumn = $showTotalColumn ?? true;
    $showEstateFilter = $showEstateFilter ?? true;
    $totalUnits = $totalUnits ?? 0;
    $occupiedCount = $occupiedCount ?? 0;
    $vacantCount = $vacantCount ?? 0;
@endphp

<div x-data="unitsTable({
    showEstateColumn: {{ $showEstateColumn ? 'true' : 'false' }},
    showUtilityColumns: {{ $showUtilityColumns ? 'true' : 'false' }},
    showTotalColumn: {{ $showTotalColumn ? 'true' : 'false' }},
    showEstateFilter: {{ $showEstateFilter ? 'true' : 'false' }},
    currentEstateId: {{ $currentEstate->id ?? 'null' }}
})" x-init="init()" x-cloak>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Table Header with Tabs - All in single line -->
        <div class="flex flex-nowrap items-center justify-between border-b border-gray-200 dark:border-gray-700 px-5 py-3 gap-3 overflow-x-auto">
            <!-- Tabs -->
            <div class="flex -mb-px space-x-4 flex-shrink-0">
                <button
                    @click="activeTab = 'all'; filterUnits()"
                    :class="activeTab === 'all' 
                        ? 'border-brand-500 text-brand-500 dark:text-brand-400 dark:border-brand-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="inline-flex items-center py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                >
                    All Units
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ $totalUnits ??0 }}
                    </span>
                </button>
                
                <button
                    @click="activeTab = 'occupied'; filterUnits()"
                    :class="activeTab === 'occupied' 
                        ? 'border-brand-500 text-brand-500 dark:text-brand-400 dark:border-brand-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="inline-flex items-center py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                >
                    Occupied
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        {{ $occupiedCount }}
                    </span>
                </button>
                
                <button
                    @click="activeTab = 'vacant'; filterUnits()"
                    :class="activeTab === 'vacant' 
                        ? 'border-brand-500 text-brand-500 dark:text-brand-400 dark:border-brand-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="inline-flex items-center py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                >
                    Vacant
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        {{ $vacantCount }}
                    </span>
                </button>
            </div>

            <!-- Filters and Controls - All in one line with flex-nowrap -->
            <div class="flex flex-nowrap items-center gap-2">
                <!-- Estate Filter (conditionally shown) -->
                @if($showEstateFilter && isset($estates) && count($estates) > 0)
                <div class="relative flex-shrink-0">
                    <select 
                        x-model="filters.estate_id"
                        @change="filterUnits()"
                        class="dark:bg-dark-900 h-9 w-[130px] appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 pr-7 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Estates</option>
                        @foreach($estates as $estate)
                            <option value="{{ $estate->id }}">{{ $estate->name }}</option>
                        @endforeach
                    </select>
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 7.293C5.68342 6.90258 6.31658 6.90258 6.707 7.293L10 10.586L13.293 7.293C13.6834 6.90258 14.3166 6.90258 14.707 7.293C15.0974 7.68342 15.0974 8.31658 14.707 8.707L10.707 12.707C10.3166 13.0974 9.68342 13.0974 9.293 12.707L5.293 8.707C4.90258 8.31658 4.90258 7.68342 5.293 7.293Z" fill=""/>
                        </svg>
                    </span>
                </div>
                @endif

                <!-- Unit Type Filter -->
                <div class="relative flex-shrink-0">
                    <select 
                        x-model="filters.unit_type"
                        @change="filterUnits()"
                        class="dark:bg-dark-900 h-9 w-[130px] appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 pr-7 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Types</option>
                        <option value="Studio">Studio</option>
                        <option value="Bedsitter">Bedsitter</option>
                        <option value="One Bedroom">1 Bedroom</option>
                        <option value="Two Bedroom">2 Bedroom</option>
                        <option value="Three Bedroom">3 Bedroom</option>
                        <option value="Apartment">Apartment</option>
                        <option value="House">House</option>
                        <option value="Office">Office</option>
                        <option value="Shop">Shop</option>
                    </select>
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 7.293C5.68342 6.90258 6.31658 6.90258 6.707 7.293L10 10.586L13.293 7.293C13.6834 6.90258 14.3166 6.90258 14.707 7.293C15.0974 7.68342 15.0974 8.31658 14.707 8.707L10.707 12.707C10.3166 13.0974 9.68342 13.0974 9.293 12.707L5.293 8.707C4.90258 8.31658 4.90258 7.68342 5.293 7.293Z" fill=""/>
                        </svg>
                    </span>
                </div>

                <!-- Rent Range Filter -->
                <div class="relative flex-shrink-0">
                    <select 
                        x-model="filters.rent_range"
                        @change="filterUnits()"
                        class="dark:bg-dark-900 h-9 w-[130px] appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 pr-7 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Rents</option>
                        <option value="0-10000">0 - 10k</option>
                        <option value="10001-20000">10k - 20k</option>
                        <option value="20001-30000">20k - 30k</option>
                        <option value="30001-50000">30k - 50k</option>
                        <option value="50001+">50k+</option>
                    </select>
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 7.293C5.68342 6.90258 6.31658 6.90258 6.707 7.293L10 10.586L13.293 7.293C13.6834 6.90258 14.3166 6.90258 14.707 7.293C15.0974 7.68342 15.0974 8.31658 14.707 8.707L10.707 12.707C10.3166 13.0974 9.68342 13.0974 9.293 12.707L5.293 8.707C4.90258 8.31658 4.90258 7.68342 5.293 7.293Z" fill=""/>
                        </svg>
                    </span>
                </div>

                <!-- Clear Filters Icon Button -->
                <button 
                    @click="clearFilters()"
                    x-show="hasActiveFilters"
                    class="flex-shrink-0 h-9 w-9 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors flex items-center justify-center"
                    title="Clear filters"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Search Bar -->
                <div class="relative flex-shrink-0">
                    <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        x-model="searchTerm"
                        @input.debounce.300ms="filterUnits()"
                        placeholder="Search..." 
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-[180px] rounded-lg border border-gray-300 bg-transparent py-1.5 pr-3 pl-8 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                </div>

                <!-- Entries Per Page -->
                <div class="relative flex-shrink-0">
                    <select 
                        x-model="entriesPerPage" 
                        @change="updateTable()"
                        class="dark:bg-dark-900 h-9 w-[70px] appearance-none rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 pr-6 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 7.293C5.68342 6.90258 6.31658 6.90258 6.707 7.293L10 10.586L13.293 7.293C13.6834 6.90258 14.3166 6.90258 14.707 7.293C15.0974 7.68342 15.0974 8.31658 14.707 8.707L10.707 12.707C10.3166 13.0974 9.68342 13.0974 9.293 12.707L5.293 8.707C4.90258 8.31658 4.90258 7.68342 5.293 7.293Z" fill=""/>
                        </svg>
                    </span>
                </div>

                <!-- Add Unit Button -->
                <button 
                    @click="window.unitCreateModal?.openModal({{ $currentEstate->id ?? 'null' }})"
                    class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex-shrink-0 inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Unit
                </button>
            </div>
        </div>

        <!-- Table Content with max-width and horizontal scroll -->
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1000px] table-auto">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400 cursor-pointer" @click="sortBy('unit_number')">
                            <div class="flex items-center gap-1">
                                <span>Unit #</span>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortColumn === 'unit_number' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortColumn === 'unit_number' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        
                        @if($showEstateColumn)
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Estate</th>
                        @endif
                        
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Type</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400 cursor-pointer" @click="sortBy('rent_amount')">
                            <div class="flex items-center gap-1">
                                <span>Rent</span>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortColumn === 'rent_amount' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortColumn === 'rent_amount' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        
                        @if($showUtilityColumns)
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Water</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Service</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Garbage</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Security</th>
                        @endif
                        
                        @if($showTotalColumn)
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400 cursor-pointer" @click="sortBy('total_monthly_charges')">
                            <div class="flex items-center gap-1">
                                <span>Total</span>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortColumn === 'total_monthly_charges' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortColumn === 'total_monthly_charges' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        @endif
                        
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="p-3 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Status</th>
                        <th class="p-3 text-right text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <template x-for="unit in paginatedUnits" :key="unit.id">
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="p-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 h-7 w-7 rounded-full flex items-center justify-center" 
                                         :class="getEstateColor(unit.estate_id)">
                                        <span class="text-white font-medium text-xs" x-text="getEstateInitials(unit.estate_name)"></span>
                                    </div>
                                    <a :href="`/units/${unit.id}`" class="font-medium text-gray-800 text-sm dark:text-white/90 hover:text-blue-600">
                                        <span x-text="unit.unit_number"></span>
                                    </a>
                                </div>
                            </td>
                            
                            @if($showEstateColumn)
                            <td class="p-3 whitespace-nowrap">
                                <span class="text-xs font-medium" :class="getEstateTextColor(unit.estate_id)" x-text="unit.estate_name"></span>
                            </td>
                            @endif
                            
                            <td class="p-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400" x-text="unit.unit_type"></td>
                            <td class="p-3 whitespace-nowrap text-xs font-medium text-gray-800 dark:text-white/90">
                                KES <span x-text="formatCurrency(unit.rent_amount)"></span>
                            </td>
                            
                            @if($showUtilityColumns)
                            <td class="p-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="formatCurrency(unit.water_charge || 0)"></span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="formatCurrency(unit.service_charge || 0)"></span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="formatCurrency(unit.garbage_charge || 0)"></span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="formatCurrency(unit.security_charge || 0)"></span>
                            </td>
                            @endif
                            
                            @if($showTotalColumn)
                            <td class="p-3 whitespace-nowrap">
                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                    KES <span x-text="formatCurrency(unit.total_monthly_charges || 0)"></span>
                                </div>
                            </td>
                            @endif
                            
                            <td class="p-3 whitespace-nowrap">
                                <template x-if="unit.active_tenancy && unit.active_tenancy.tenant">
                                    <div>
                                        <a :href="`/tenancies/${unit.active_tenancy.tenant_id}`" 
                                           class="text-xs font-medium text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                            <span x-text="unit.active_tenancy.tenant.name.split(' ').slice(0,2).join(' ')"></span>
                                        </a>
                                    </div>
                                </template>
                                <template x-if="!unit.active_tenancy || !unit.active_tenancy.tenant">
                                    <span class="text-xs text-gray-400">-</span>
                                </template>
                            </td>
                            <td class="p-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="unit.active_tenancy?.tenant?.phone || '-'"></span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" 
                                      :class="unit.status === 'occupied' 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                        : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'">
                                    <span x-text="unit.status.charAt(0).toUpperCase() + unit.status.slice(1)"></span>
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <div x-data="dropdown()" class="relative">
                                    <button
                                        @click="toggle"
                                        class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                        </svg>
                                    </button>
                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        class="shadow-theme-lg dark:bg-gray-dark absolute right-0 z-10 w-36 space-y-1 rounded-xl border border-gray-200 bg-white p-1.5 dark:border-gray-800"
                                        x-ref="dropdown"
                                        x-cloak
                                    >
                                        <a
                                            :href="`/units/${unit.id}`"
                                            class="text-theme-xs flex w-full rounded-lg px-2 py-1.5 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        <button
                                            @click="window.unitEditModal?.openModal(unit); open = false"
                                            class="text-theme-xs flex w-full rounded-lg px-2 py-1.5 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button
                                            @click="window.unitDeleteModal?.openModal(unit); open = false"
                                            class="text-theme-xs flex w-full rounded-lg px-2 py-1.5 text-left font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <template x-if="filteredUnits.length === 0">
                        <tr>
                            <td colspan="{{ ($showEstateColumn ? 1 : 0) + ($showUtilityColumns ? 4 : 0) + ($showTotalColumn ? 1 : 0) + 7 }}" class="p-8 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No units found</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="hasActiveFilters || searchTerm">
                                    Try adjusting your filters or search criteria
                                </p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination - Below table -->
        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-800">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing 
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="showingStart"></span>
                to 
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="showingEnd"></span>
                of 
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="filteredUnits.length"></span>
            </div>
            
            <div class="flex items-center gap-2">
                <button
                    @click="prevPage()"
                    :disabled="currentPage === 1"
                    class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="ml-1">Prev</span>
                </button>
                
                <div class="flex items-center gap-1">
                    <template x-for="page in visiblePages" :key="page">
                        <button
                            @click="goToPage(page)"
                            :class="page === currentPage 
                                ? 'bg-brand-500 text-white' 
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium transition-colors"
                            x-text="page"
                            x-show="page !== '...'"
                        ></button>
                        <span x-show="page === '...'" class="flex h-8 w-8 items-center justify-center text-sm text-gray-500">...</span>
                    </template>
                </div>
                
                <button
                    @click="nextPage()"
                    :disabled="currentPage === totalPages"
                    class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span class="mr-1">Next</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for Alpine.js -->
<script type="application/json" id="estate-units-data">
@json($unitsData ?? [])
</script>

<script>
function unitsTable(config = {}) {
    return {
        // Configuration
        showEstateColumn: config.showEstateColumn !== false,
        showUtilityColumns: config.showUtilityColumns !== false,
        showTotalColumn: config.showTotalColumn !== false,
        showEstateFilter: config.showEstateFilter !== false,
        currentEstateId: config.currentEstateId || null,
        
        // Data from controller
        units: [],
        filteredUnits: [],
        paginatedUnits: [],
        currentPage: 1,
        entriesPerPage: 10,
        searchTerm: '',
        filters: {
            estate_id: '',
            unit_type: '',
            rent_range: ''
        },
        sortColumn: 'unit_number',
        sortDirection: 'asc',
        showingStart: 1,
        showingEnd: 10,
        totalPages: 1,
        activeTab: 'all',
        
        init() {
            // Get units data from hidden element
            const unitsElement = document.getElementById('estate-units-data');
            if (unitsElement) {
                this.units = JSON.parse(unitsElement.textContent);
                console.log('Units loaded:', this.units.length);
            }
            
            // Listen for unit-updated events
            window.addEventListener('unit-updated', (e) => {
                const updatedUnit = e.detail;
                console.log('Unit updated event received:', updatedUnit);
                
                // Find and update the unit in the units array
                const index = this.units.findIndex(u => u.id === updatedUnit.id);
                
                if (index !== -1) {
                    // Update the unit with new data including utility fields
                    this.units[index] = {
                        ...this.units[index],
                        unit_number: updatedUnit.unit_number,
                        unit_type: updatedUnit.unit_type,
                        rent_amount: updatedUnit.rent_amount,
                        water_charge: updatedUnit.water_charge,
                        service_charge: updatedUnit.service_charge,
                        garbage_charge: updatedUnit.garbage_charge,
                        security_charge: updatedUnit.security_charge,
                        total_monthly_charges: updatedUnit.total_monthly_charges,
                        status: updatedUnit.status
                    };
                    
                    // Refresh the filtered units and table
                    this.filterUnits();
                    this.updateTable();
                    
                    console.log('Unit updated successfully in UI');
                } else {
                    console.warn('Unit not found in units array:', updatedUnit.id);
                }
            });
            
            this.filterUnits();
            this.updateTable();
        },
        
        get hasActiveFilters() {
            return this.filters.estate_id || this.filters.unit_type || this.filters.rent_range;
        },
        
        filterUnits() {
            let filtered = [...this.units];
            
            // Apply tab filter
            if (this.activeTab === 'occupied') {
                filtered = filtered.filter(unit => unit.status === 'occupied');
            } else if (this.activeTab === 'vacant') {
                filtered = filtered.filter(unit => unit.status === 'vacant');
            }
            
            // Apply estate filter (if shown)
            if (this.showEstateFilter && this.filters.estate_id) {
                filtered = filtered.filter(unit => unit.estate_id == this.filters.estate_id);
            }
            
            // Apply unit type filter
            if (this.filters.unit_type) {
                filtered = filtered.filter(unit => unit.unit_type === this.filters.unit_type);
            }
            
            // Apply rent range filter
            if (this.filters.rent_range) {
                const [min, max] = this.filters.rent_range.split('-').map(Number);
                filtered = filtered.filter(unit => {
                    const rent = parseFloat(unit.rent_amount);
                    if (this.filters.rent_range === '50001+') {
                        return rent >= 50001;
                    }
                    return rent >= min && rent <= max;
                });
            }
            
            // Apply search filter
            if (this.searchTerm.trim()) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(unit => {
                    return (
                        unit.unit_number?.toLowerCase().includes(term) ||
                        unit.unit_type?.toLowerCase().includes(term) ||
                        unit.estate_name?.toLowerCase().includes(term) ||
                        (unit.active_tenancy?.tenant?.name?.toLowerCase() || '').includes(term) ||
                        (unit.active_tenancy?.tenant?.phone?.toLowerCase() || '').includes(term)
                    );
                });
            }
            
            this.filteredUnits = filtered;
            this.sortUnits();
            this.updateTable();
            this.currentPage = 1;
        },
        
        clearFilters() {
            this.filters = {
                estate_id: '',
                unit_type: '',
                rent_range: ''
            };
            this.searchTerm = '';
            this.filterUnits();
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.sortUnits();
            this.updateTable();
        },
        
        sortUnits() {
            this.filteredUnits.sort((a, b) => {
                let aValue, bValue;
                
                if (this.sortColumn === 'rent_amount' || this.sortColumn === 'balance' || this.sortColumn === 'total_monthly_charges') {
                    aValue = parseFloat(a[this.sortColumn]) || 0;
                    bValue = parseFloat(b[this.sortColumn]) || 0;
                } else {
                    aValue = a[this.sortColumn]?.toString().toLowerCase() || '';
                    bValue = b[this.sortColumn]?.toString().toLowerCase() || '';
                }
                
                if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        updateTable() {
            this.totalPages = Math.ceil(this.filteredUnits.length / this.entriesPerPage);
            const startIndex = (this.currentPage - 1) * this.entriesPerPage;
            const endIndex = startIndex + this.entriesPerPage;
            
            this.paginatedUnits = this.filteredUnits.slice(startIndex, endIndex);
            this.showingStart = this.filteredUnits.length ? startIndex + 1 : 0;
            this.showingEnd = Math.min(endIndex, this.filteredUnits.length);
            
            // Reset to first page if current page is out of bounds
            if (this.currentPage > this.totalPages && this.totalPages > 0) {
                this.currentPage = 1;
                this.updateTable();
            }
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
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount || 0);
        },
        
        // Get first two letters of estate name for initials
        getEstateInitials(estateName) {
            if (!estateName) return 'UN';
            return estateName.substring(0, 2).toUpperCase();
        },
        
        // Get color class based on estate_id
        getEstateColor(estateId) {
            const colors = [
                'bg-blue-500',
                'bg-green-500',
                'bg-purple-500',
                'bg-pink-500',
                'bg-indigo-500',
                'bg-red-500',
                'bg-yellow-500',
                'bg-teal-500',
                'bg-orange-500',
                'bg-cyan-500'
            ];
            const index = (parseInt(estateId) - 1) % colors.length;
            return colors[index] || 'bg-gray-500';
        },
        
        // Get text color class based on estate_id
        getEstateTextColor(estateId) {
            const colors = [
                'text-blue-600 dark:text-blue-400',
                'text-green-600 dark:text-green-400',
                'text-purple-600 dark:text-purple-400',
                'text-pink-600 dark:text-pink-400',
                'text-indigo-600 dark:text-indigo-400',
                'text-red-600 dark:text-red-400',
                'text-yellow-600 dark:text-yellow-400',
                'text-teal-600 dark:text-teal-400',
                'text-orange-600 dark:text-orange-400',
                'text-cyan-600 dark:text-cyan-400'
            ];
            const index = (parseInt(estateId) - 1) % colors.length;
            return colors[index] || 'text-gray-600 dark:text-gray-400';
        }
    };
}

// Dropdown component
function dropdown() {
    return {
        open: false,
        toggle() {
            this.open = !this.open;
        }
    };
}

// Make functions available globally
window.unitsTable = unitsTable;
window.dropdown = dropdown;
</script>

<style>
[x-cloak] { display: none !important; }
</style>
<!-- resources/views/partials/modal/security-create-modal.blade.php -->

<!-- MAIN SECURITY VISITOR LOG MODAL (Full Modal with Estate→Unit→Tenant Selection) -->
<div id="securityVisitorModal" class="fixed inset-0 z-999999 hidden" style="isolation: isolate;" aria-labelledby="slideover-title" role="dialog" aria-modal="true">
    <!-- Backdrop with fade -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    
    <!-- Slideover Panel - slides in from right -->
    <div class="fixed inset-y-0 right-0 max-w-full flex">
        <div class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999" style="width: 48rem; max-width: calc(100% - 2rem);" id="securitySlideoverPanel">
            
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <svg class="inline w-5 h-5 mr-2 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Visitor & Security Logs
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Select estate, unit, and tenant to view visitor records and security logs
                        </p>
                    </div>
                    <button onclick="closeSecurityVisitorModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Selection Steps -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 text-center" id="step1Indicator">
                            <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-medium text-gray-600 dark:text-gray-300" id="step1Icon">1</div>
                            <p class="text-xs mt-1 text-gray-500">Select Estate</p>
                        </div>
                        <div class="w-12 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <div class="flex-1 text-center opacity-50" id="step2Indicator">
                            <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-medium text-gray-600 dark:text-gray-300" id="step2Icon">2</div>
                            <p class="text-xs mt-1 text-gray-500">Select Unit</p>
                        </div>
                        <div class="w-12 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <div class="flex-1 text-center opacity-50" id="step3Indicator">
                            <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-medium text-gray-600 dark:text-gray-300" id="step3Icon">3</div>
                            <p class="text-xs mt-1 text-gray-500">Select Tenant</p>
                        </div>
                    </div>
                </div>
                
                <!-- Dynamic Content Area -->
                <div id="securityModalContent" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Step 1: Estate Selection -->
                    <div id="stepEstate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Select Estate
                            </label>
                            <select id="securityEstateSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">-- Select an estate --</option>
                            </select>
                        </div>
                        <div class="text-center text-gray-500 dark:text-gray-400 py-8" id="estateLoadingIndicator" style="display: none;">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto mb-2"></div>
                            <p class="text-sm">Loading estates...</p>
                        </div>
                    </div>
                    
                    <!-- Step 2: Unit Selection -->
                    <div id="stepUnit" class="space-y-4" style="display: none;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Select Unit
                            </label>
                            <select id="securityUnitSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">-- Select a unit --</option>
                            </select>
                        </div>
                        <div id="unitInfoCard" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 hidden">
                            <p class="text-sm text-gray-600 dark:text-gray-400" id="unitInfoText"></p>
                        </div>
                        <div class="text-center text-gray-500 dark:text-gray-400 py-8" id="unitLoadingIndicator" style="display: none;">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto mb-2"></div>
                            <p class="text-sm">Loading units...</p>
                        </div>
                    </div>
                    
                    <!-- Step 3: Tenant Selection -->
                    <div id="stepTenant" class="space-y-4" style="display: none;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Select Tenant
                            </label>
                            <select id="securityTenantSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">-- Select a tenant --</option>
                            </select>
                        </div>
                        <div class="text-center text-gray-500 dark:text-gray-400 py-8" id="tenantLoadingIndicator" style="display: none;">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto mb-2"></div>
                            <p class="text-sm">Loading tenants...</p>
                        </div>
                    </div>
                    
                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;">
                        <!-- Tenant Info Card -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-800 dark:text-white" id="tenantNameDisplay">-</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        <span id="tenantUnitDisplay"></span>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="openAddVisitorModal()" class="px-3 py-1.5 text-sm bg-brand-500 text-white rounded-lg hover:bg-brand-600">
                                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        </svg>
                                        Add Visitor
                                    </button>
                                    <button onclick="openQuickEntryModal()" class="px-3 py-1.5 text-sm border border-brand-500 text-brand-600 rounded-lg hover:bg-brand-50 dark:border-brand-400 dark:text-brand-400">
                                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                        Quick Entry
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-brand-600" id="totalVisitors">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total Visitors</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-green-600" id="activeVisitors">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Active</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-blue-600" id="totalLogs">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Security Logs</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-yellow-600" id="pendingLogs">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
                            </div>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-800">
                            <div class="flex flex-wrap gap-2">
                                <button onclick="switchSecurityTab('visitors')" id="tabVisitorsBtn" class="px-4 py-2 text-sm font-medium border-b-2 border-brand-500 text-brand-600 dark:text-brand-400">
                                    Registered Visitors
                                </button>
                                <button onclick="switchSecurityTab('logs')" id="tabLogsBtn" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                    Security Logs
                                </button>
                                <button onclick="switchSecurityTab('oneTime')" id="tabOneTimeBtn" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                    Recent One-Time
                                </button>
                            </div>
                        </div>
                        
                        <!-- Visitors Table -->
                        <div id="visitorsTable" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visits</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="visitorsTableBody" class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Select a tenant to view visitors</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Security Logs Table -->
                        <div id="logsTable" class="overflow-x-auto" style="display: none;">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Access Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verified By</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody" class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Select a tenant to view security logs</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- One-Time Visitors Table -->
                        <div id="oneTimeTable" class="overflow-x-auto" style="display: none;">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Access Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="oneTimeTableBody" class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Select a tenant to view one-time visitors</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="border-t border-gray-200 dark:border-gray-800 p-4">
                    <div class="flex justify-between">
                        <div>
                            <button id="backButton" onclick="goBack()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" style="display: none;">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="closeSecurityVisitorModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QUICK ENTRY MODAL (Alpine.js) -->
<div x-data="securityQuickEntryModal" x-init="init()" x-cloak>
    <template x-if="isOpen">
        <div @click="closeModal()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"></div>
    </template>

    <div x-show="isOpen" 
         x-transition:enter="transition transform ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
         style="width: 38rem; max-width: calc(100% - 2rem);">
        <div class="p-6 lg:p-10">
            <button @click="closeModal()" class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 dark:bg-gray-800 sm:right-6 sm:top-6">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                </svg>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Quick Visitor Check-in</h4>

            <form @submit.prevent="quickCheckin">
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lookup By</label>
                    <select x-model="lookupBy" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="phone">Phone Number</option>
                        <option value="id_number">ID Number</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lookup Value</label>
                    <input type="text" x-model="lookupValue" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit</label>
                    <select x-model="unitId" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="">Select Unit</option>
                        @foreach($units ?? [] as $unit)
                        <option value="{{ $unit['id'] }}">{{ $unit['unit_number'] }} ({{ $unit['estate_name'] ?? 'No Estate' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Access Type</label>
                    <select x-model="accessType" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="entry">Entry</option>
                        <option value="exit">Exit</option>
                        <option value="delivery">Delivery</option>
                        <option value="guest">Guest</option>
                        <option value="contractor">Contractor</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Purpose (Optional)</label>
                    <input type="text" x-model="purpose" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" placeholder="Reason for visit">
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="closeModal()" class="px-4 py-3 border rounded-lg text-sm font-medium">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-3 bg-brand-500 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                        <span x-show="!isSubmitting">Check In</span>
                        <span x-show="isSubmitting">Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD VISITOR MODAL (Alpine.js) -->
<div x-data="securityAddVisitorModal" x-init="init()" x-cloak>
    <template x-if="isOpen">
        <div @click="closeModal()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"></div>
    </template>

    <div x-show="isOpen" 
         x-transition:enter="transition transform ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
         style="width: 38rem; max-width: calc(100% - 2rem);">
        <div class="p-6 lg:p-10">
            <button @click="closeModal()" class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 dark:bg-gray-800 sm:right-6 sm:top-6">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                </svg>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Register New Visitor</h4>

            <form @submit.prevent="registerVisitor">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">First Name *</label>
                        <input type="text" x-model="form.first_name" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Last Name</label>
                        <input type="text" x-model="form.last_name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone Number *</label>
                    <input type="text" x-model="form.phone" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                    <input type="email" x-model="form.email" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">ID Number</label>
                        <input type="text" x-model="form.id_number" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">ID Type</label>
                        <select x-model="form.id_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                            <option value="">Select</option>
                            <option value="national_id">National ID</option>
                            <option value="passport">Passport</option>
                            <option value="driving_license">Driving License</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Visitor Type *</label>
                    <select x-model="form.visitor_type" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" @change="onVisitorTypeChange">
                        <option value="one_time">One-time Visitor</option>
                        <option value="family">Family Member</option>
                        <option value="regular_guest">Regular Guest</option>
                        <option value="contractor">Contractor</option>
                        <option value="delivery">Delivery Personnel</option>
                        <option value="maintenance">Maintenance Staff</option>
                    </select>
                </div>

                <div x-show="showRelationship" class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Relationship</label>
                    <input type="text" x-model="form.relationship" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" placeholder="e.g., Brother, Friend, Colleague">
                </div>

                <div x-show="showCompany" class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company Name</label>
                    <input type="text" x-model="form.company" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Vehicle Registration (Optional)</label>
                    <input type="text" x-model="form.vehicle" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" placeholder="e.g., KCA 123A">
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Valid Until (Optional)</label>
                    <input type="date" x-model="form.valid_until" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                    <textarea x-model="form.notes" rows="3" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" placeholder="Additional notes..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-4 py-3 border rounded-lg text-sm font-medium">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-3 bg-brand-500 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                        <span x-show="!isSubmitting">Register Visitor</span>
                        <span x-show="isSubmitting">Registering...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==================== MAIN SECURITY VISITOR MODAL ====================
let securityState = {
    step: 1,
    estateId: null,
    unitId: null,
    tenantId: null,
    currentTab: 'visitors'
};

let visitorsList = [];
let logsList = [];
let oneTimeList = [];

function openSecurityVisitorModal() {
    const modal = document.getElementById('securityVisitorModal');
    const panel = document.getElementById('securitySlideoverPanel');
    
    if (modal && panel) {
        resetSecurityModal();
        modal.classList.remove('hidden');
        setTimeout(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        }, 10);
        document.body.style.overflow = 'hidden';
        loadEstates();
    }
}

function closeSecurityVisitorModal() {
    const modal = document.getElementById('securityVisitorModal');
    const panel = document.getElementById('securitySlideoverPanel');
    
    if (modal && panel) {
        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            resetSecurityModal();
        }, 300);
    }
}

function resetSecurityModal() {
    securityState = { step: 1, estateId: null, unitId: null, tenantId: null, currentTab: 'visitors' };
    document.getElementById('stepEstate').style.display = 'block';
    document.getElementById('stepUnit').style.display = 'none';
    document.getElementById('stepTenant').style.display = 'none';
    document.getElementById('resultsSection').style.display = 'none';
    document.getElementById('backButton').style.display = 'none';
    updateStepIndicators(1);
    
    const estateSelect = document.getElementById('securityEstateSelect');
    const unitSelect = document.getElementById('securityUnitSelect');
    const tenantSelect = document.getElementById('securityTenantSelect');
    if (estateSelect) estateSelect.innerHTML = '<option value="">-- Select an estate --</option>';
    if (unitSelect) unitSelect.innerHTML = '<option value="">-- Select a unit --</option>';
    if (tenantSelect) tenantSelect.innerHTML = '<option value="">-- Select a tenant --</option>';
}

function updateStepIndicators(step) {
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step${i}Indicator`);
        const icon = document.getElementById(`step${i}Icon`);
        if (indicator) {
            if (i < step) {
                indicator.classList.remove('opacity-50');
                if (icon) { icon.innerHTML = '✓'; icon.classList.add('bg-green-500', 'text-white'); icon.classList.remove('bg-gray-200', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-300'); }
            } else if (i === step) {
                indicator.classList.remove('opacity-50');
                if (icon) { icon.innerHTML = step.toString(); icon.classList.add('bg-brand-500', 'text-white'); icon.classList.remove('bg-gray-200', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-300', 'bg-green-500'); }
            } else {
                indicator.classList.add('opacity-50');
                if (icon) { icon.innerHTML = i.toString(); icon.classList.remove('bg-brand-500', 'text-white', 'bg-green-500'); icon.classList.add('bg-gray-200', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-300'); }
            }
        }
    }
}

function goBack() {
    if (securityState.step > 1) {
        securityState.step--;
        if (securityState.step === 1) {
            document.getElementById('stepUnit').style.display = 'none';
            document.getElementById('stepEstate').style.display = 'block';
            document.getElementById('backButton').style.display = 'none';
        } else if (securityState.step === 2) {
            document.getElementById('stepTenant').style.display = 'none';
            document.getElementById('stepUnit').style.display = 'block';
            document.getElementById('backButton').style.display = 'inline-flex';
        }
        document.getElementById('resultsSection').style.display = 'none';
        updateStepIndicators(securityState.step);
    }
}

async function loadEstates() {
    const loadingIndicator = document.getElementById('estateLoadingIndicator');
    const estateSelect = document.getElementById('securityEstateSelect');
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    
    try {
        const response = await fetch('/security/estates');
        const data = await response.json();
        if (data.success && data.estates) {
            estateSelect.innerHTML = '<option value="">-- Select an estate --</option>';
            data.estates.forEach(estate => { estateSelect.innerHTML += `<option value="${estate.id}">${estate.name}</option>`; });
            estateSelect.onchange = () => onEstateSelected();
        }
    } catch (error) { console.error('Error loading estates:', error); }
    finally { if (loadingIndicator) loadingIndicator.style.display = 'none'; }
}

async function onEstateSelected() {
    const estateSelect = document.getElementById('securityEstateSelect');
    const estateId = estateSelect.value;
    if (!estateId) return;
    
    securityState.estateId = parseInt(estateId);
    securityState.step = 2;
    document.getElementById('stepEstate').style.display = 'none';
    document.getElementById('stepUnit').style.display = 'block';
    document.getElementById('backButton').style.display = 'inline-flex';
    updateStepIndicators(2);
    await loadUnits(estateId);
}

async function loadUnits(estateId) {
    const loadingIndicator = document.getElementById('unitLoadingIndicator');
    const unitSelect = document.getElementById('securityUnitSelect');
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    unitSelect.innerHTML = '<option value="">-- Loading units --</option>';
    
    try {
        const response = await fetch(`/security/units?estate_id=${estateId}`);
        const data = await response.json();
        if (data.success && data.units) {
            unitSelect.innerHTML = '<option value="">-- Select a unit --</option>';
            data.units.forEach(unit => {
                const tenantInfo = unit.has_active_tenancy ? '✓ Occupied' : '○ Vacant';
                unitSelect.innerHTML += `<option value="${unit.id}" data-has-tenant="${unit.has_active_tenancy}">${unit.unit_number} - ${tenantInfo}</option>`;
            });
            unitSelect.onchange = () => onUnitSelected();
        }
    } catch (error) { console.error('Error loading units:', error); }
    finally { if (loadingIndicator) loadingIndicator.style.display = 'none'; }
}

async function onUnitSelected() {
    const unitSelect = document.getElementById('securityUnitSelect');
    const unitId = unitSelect.value;
    if (!unitId) return;
    
    securityState.unitId = parseInt(unitId);
    securityState.step = 3;
    document.getElementById('stepUnit').style.display = 'none';
    document.getElementById('stepTenant').style.display = 'block';
    updateStepIndicators(3);
    await loadTenants(unitId);
}

async function loadTenants(unitId) {
    const loadingIndicator = document.getElementById('tenantLoadingIndicator');
    const tenantSelect = document.getElementById('securityTenantSelect');
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    tenantSelect.innerHTML = '<option value="">-- Loading tenants --</option>';
    
    try {
        const response = await fetch(`/security/tenants?unit_id=${unitId}`);
        const data = await response.json();
        if (data.success && data.tenants) {
            tenantSelect.innerHTML = '<option value="">-- Select a tenant --</option>';
            if (data.tenants.length === 0) tenantSelect.innerHTML = '<option value="">-- No tenants found --</option>';
            else data.tenants.forEach(tenant => { tenantSelect.innerHTML += `<option value="${tenant.id}" data-status="${tenant.status}">${tenant.name} (${tenant.status})</option>`; });
            tenantSelect.onchange = () => onTenantSelected();
        }
    } catch (error) { console.error('Error loading tenants:', error); }
    finally { if (loadingIndicator) loadingIndicator.style.display = 'none'; }
}

async function onTenantSelected() {
    const tenantSelect = document.getElementById('securityTenantSelect');
    const tenantId = tenantSelect.value;
    if (!tenantId) return;
    
    securityState.tenantId = parseInt(tenantId);
    document.getElementById('stepTenant').style.display = 'none';
    document.getElementById('resultsSection').style.display = 'block';
    document.getElementById('backButton').style.display = 'inline-flex';
    await loadTenantData(tenantId);
}

async function loadTenantData(tenantId) {
    document.getElementById('visitorsTableBody').innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto mb-2"></div>Loading visitors...</td></tr>`;
    
    try {
        const visitorsResponse = await fetch(`/security/visitors?tenant_id=${tenantId}`);
        const visitorsData = await visitorsResponse.json();
        if (visitorsData.success) {
            document.getElementById('tenantNameDisplay').innerText = visitorsData.tenant?.name || '-';
            document.getElementById('tenantUnitDisplay').innerHTML = visitorsData.tenant?.unit ? `Unit: ${visitorsData.tenant.unit.unit_number}` : 'No active unit';
            document.getElementById('totalVisitors').innerText = visitorsData.stats?.total_visitors || 0;
            document.getElementById('activeVisitors').innerText = visitorsData.stats?.active_visitors || 0;
            visitorsList = visitorsData.visitors || [];
            oneTimeList = visitorsData.recent_one_time_visitors || [];
            renderVisitorsTable(visitorsList);
            renderOneTimeTable(oneTimeList);
        }
        
        const logsResponse = await fetch(`/security/logs-by-tenant?tenant_id=${tenantId}`);
        const logsData = await logsResponse.json();
        if (logsData.success) {
            document.getElementById('totalLogs').innerText = logsData.stats?.total_logs || 0;
            document.getElementById('pendingLogs').innerText = logsData.stats?.pending_logs || 0;
            logsList = logsData.logs || [];
            renderLogsTable(logsList);
        }
    } catch (error) { console.error('Error loading tenant data:', error); }
}

function renderVisitorsTable(visitors) {
    const tbody = document.getElementById('visitorsTableBody');
    if (!visitors || visitors.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No registered visitors found</td></tr>'; return; }
    
    tbody.innerHTML = visitors.map(visitor => `
        <tr>
            <td class="px-4 py-3"><div class="font-medium text-gray-800 dark:text-white">${escapeHtml(visitor.name)}</div>${visitor.id_number ? `<div class="text-xs text-gray-500">ID: ${visitor.id_number}</div>` : ''}</td>
            <td class="px-4 py-3"><span class="inline-flex px-2 py-1 text-xs rounded-full ${getVisitorTypeClass(visitor.visitor_type)}">${visitor.visitor_type_label}</span></td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${visitor.phone || '-'}</td>
            <td class="px-4 py-3">${visitor.is_blacklisted ? '<span class="text-red-600 text-xs font-medium">Blacklisted</span>' : visitor.is_active ? '<span class="text-green-600 text-xs font-medium">Active</span>' : '<span class="text-gray-500 text-xs">Inactive</span>'}${visitor.valid_until ? `<div class="text-xs text-gray-400">Valid until: ${new Date(visitor.valid_until).toLocaleDateString()}</div>` : ''}</td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${visitor.visit_count || 0}</td>
            <td class="px-4 py-3 text-right"><button onclick="quickEntryForVisitor(${visitor.id})" class="text-green-500 hover:text-green-600 text-sm">Quick Entry</button></td>
        </tr>
    `).join('');
}

function renderLogsTable(logs) {
    const tbody = document.getElementById('logsTableBody');
    if (!logs || logs.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No security logs found</td></tr>'; return; }
    
    tbody.innerHTML = logs.map(log => `
        <tr>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${log.access_time_formatted}</td>
            <td class="px-4 py-3"><div class="font-medium text-gray-800 dark:text-white">${escapeHtml(log.visitor_name)}</div>${log.visitor_phone ? `<div class="text-xs text-gray-500">${log.visitor_phone}</div>` : ''}</td>
            <td class="px-4 py-3"><span class="text-sm">${log.access_type}</span></td>
            <td class="px-4 py-3"><span class="inline-flex px-2 py-1 text-xs rounded-full ${getStatusClass(log.status)}">${log.status.charAt(0).toUpperCase() + log.status.slice(1)}</span></td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${log.purpose || '-'}</td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${log.verified_by}</td>
        </tr>
    `).join('');
}

function renderOneTimeTable(visitors) {
    const tbody = document.getElementById('oneTimeTableBody');
    if (!visitors || visitors.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No one-time visitors found</td></tr>'; return; }
    
    tbody.innerHTML = visitors.map(visitor => `
        <tr>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${visitor.access_time ? new Date(visitor.access_time).toLocaleString() : '-'}</td>
            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">${escapeHtml(visitor.name)}</td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${visitor.phone || '-'}</td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${visitor.vehicle || '-'}</td>
            <td class="px-4 py-3"><span class="text-sm">${visitor.access_type}</span></td>
            <td class="px-4 py-3"><span class="inline-flex px-2 py-1 text-xs rounded-full ${getStatusClass(visitor.status)}">${visitor.status.charAt(0).toUpperCase() + visitor.status.slice(1)}</span></td>
        </tr>
    `).join('');
}

function switchSecurityTab(tab) {
    securityState.currentTab = tab;
    const visitorsTable = document.getElementById('visitorsTable');
    const logsTable = document.getElementById('logsTable');
    const oneTimeTable = document.getElementById('oneTimeTable');
    const visitorsBtn = document.getElementById('tabVisitorsBtn');
    const logsBtn = document.getElementById('tabLogsBtn');
    const oneTimeBtn = document.getElementById('tabOneTimeBtn');
    
    visitorsTable.style.display = 'none';
    logsTable.style.display = 'none';
    oneTimeTable.style.display = 'none';
    visitorsBtn.className = 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400';
    logsBtn.className = 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400';
    oneTimeBtn.className = 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400';
    
    if (tab === 'visitors') { visitorsTable.style.display = 'block'; visitorsBtn.className = 'px-4 py-2 text-sm font-medium border-b-2 border-brand-500 text-brand-600 dark:text-brand-400'; }
    else if (tab === 'logs') { logsTable.style.display = 'block'; logsBtn.className = 'px-4 py-2 text-sm font-medium border-b-2 border-brand-500 text-brand-600 dark:text-brand-400'; }
    else if (tab === 'oneTime') { oneTimeTable.style.display = 'block'; oneTimeBtn.className = 'px-4 py-2 text-sm font-medium border-b-2 border-brand-500 text-brand-600 dark:text-brand-400'; }
}

function openQuickEntryModal() { if (window.securityQuickEntryModal) window.securityQuickEntryModal.openModal(); }
function openAddVisitorModal() { if (window.securityAddVisitorModal) window.securityAddVisitorModal.openModal(); }
function quickEntryForVisitor(visitorId) { alert(`Quick entry for visitor ID: ${visitorId} - To be implemented`); }

function getVisitorTypeClass(type) {
    const classes = { 'family': 'bg-pink-100 text-pink-800', 'employee': 'bg-blue-100 text-blue-800', 'contractor': 'bg-orange-100 text-orange-800', 'regular_guest': 'bg-purple-100 text-purple-800', 'delivery': 'bg-yellow-100 text-yellow-800', 'maintenance': 'bg-gray-100 text-gray-800', 'one_time': 'bg-green-100 text-green-800' };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getStatusClass(status) {
    const classes = { 'pending': 'bg-yellow-100 text-yellow-800', 'approved': 'bg-green-100 text-green-800', 'denied': 'bg-red-100 text-red-800', 'completed': 'bg-blue-100 text-blue-800' };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, function(m) { if (m === '&') return '&amp;'; if (m === '<') return '&lt;'; if (m === '>') return '&gt;'; return m; }); }

// ==================== QUICK ENTRY MODAL ====================
document.addEventListener('alpine:init', () => {
    Alpine.data('securityQuickEntryModal', () => ({
        isOpen: false, isSubmitting: false, lookupBy: 'phone', lookupValue: '', unitId: '', accessType: 'entry', purpose: '',
        init() { window.securityQuickEntryModal = this; },
        openModal() { this.isOpen = true; this.resetForm(); document.body.style.overflow = 'hidden'; },
        closeModal() { this.isOpen = false; document.body.style.overflow = ''; },
        resetForm() { this.lookupBy = 'phone'; this.lookupValue = ''; this.unitId = ''; this.accessType = 'entry'; this.purpose = ''; },
        async quickCheckin() {
            if (!this.lookupValue || !this.unitId) return;
            this.isSubmitting = true;
            try {
                const response = await fetch('/security/quick-entry', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                    body: JSON.stringify({ lookup_by: this.lookupBy, lookup_value: this.lookupValue, unit_id: this.unitId, access_type: this.accessType, purpose: this.purpose })
                });
                const data = await response.json();
                if (data.success) { alert(data.message); this.closeModal(); setTimeout(() => window.location.reload(), 1000); }
                else if (data.requires_registration) { alert('Visitor not found. Please register them first.'); this.closeModal(); if (window.securityAddVisitorModal) window.securityAddVisitorModal.openModal(); }
                else alert(data.message || 'Check-in failed');
            } catch (error) { console.error('Error:', error); alert('An error occurred'); }
            finally { this.isSubmitting = false; }
        }
    }));

    // ==================== ADD VISITOR MODAL ====================
    Alpine.data('securityAddVisitorModal', () => ({
        isOpen: false, isSubmitting: false, showRelationship: false, showCompany: false,
        form: { first_name: '', last_name: '', phone: '', email: '', id_number: '', id_type: '', visitor_type: 'one_time', relationship: '', company: '', vehicle: '', valid_until: '', notes: '' },
        init() { window.securityAddVisitorModal = this; },
        openModal() { this.isOpen = true; this.resetForm(); document.body.style.overflow = 'hidden'; },
        closeModal() { this.isOpen = false; document.body.style.overflow = ''; },
        resetForm() {
            this.form = { first_name: '', last_name: '', phone: '', email: '', id_number: '', id_type: '', visitor_type: 'one_time', relationship: '', company: '', vehicle: '', valid_until: '', notes: '' };
            this.showRelationship = false; this.showCompany = false;
        },
        onVisitorTypeChange() {
            this.showRelationship = this.form.visitor_type === 'family';
            this.showCompany = this.form.visitor_type === 'contractor';
        },
        async registerVisitor() {
            if (!this.form.first_name || !this.form.phone) { alert('Please fill in required fields'); return; }
            this.isSubmitting = true;
            try {
                const vehicles = this.form.vehicle ? [{ registration: this.form.vehicle, is_primary: true }] : [];
                const response = await fetch('/security/register-visitor', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                    body: JSON.stringify({ ...this.form, vehicles: vehicles })
                });
                const data = await response.json();
                if (data.success) { alert(data.message); this.closeModal(); if (securityState.tenantId) loadTenantData(securityState.tenantId); }
                else alert(data.message || 'Registration failed');
            } catch (error) { console.error('Error:', error); alert('An error occurred'); }
            finally { this.isSubmitting = false; }
        }
    }));
});
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
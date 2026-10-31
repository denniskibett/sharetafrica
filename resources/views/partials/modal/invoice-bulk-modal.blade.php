@php
    if (!isset($mappedActiveTenancies)) {
        $mappedActiveTenancies = App\Models\Tenancy::where('status', 'active')
            ->with('tenant.user', 'unit')
            ->get()
            ->map(function($tenancy) {
                return [
                    'id' => $tenancy->id,
                    'tenant_name' => $tenancy->tenant->user->name ?? 'Unknown',
                    'unit_number' => $tenancy->unit->unit_number ?? 'No Unit',
                ];
            });
    }
@endphp
<!-- BULK INVOICE SLIDEOVER MODAL (Bulk Create + Bulk Missing) -->
<div x-data="bulkInvoiceModal" x-init="init()" x-cloak>
  <!-- Backdrop -->
  <template x-if="isOpen">
    <div 
      @click="closeModal"
      class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    ></div>
  </template>

  <!-- Modal Content - Slides from Right -->
  <div x-show="isOpen" 
       x-transition:enter="transition transform ease-out duration-300"
       x-transition:enter-start="translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition transform ease-in duration-200"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="translate-x-full"
       class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999" style="width: 42rem; max-width: calc(100% - 2rem);">
    <div class="p-6 lg:p-8">
      <!-- close btn -->
      <button
        @click="closeModal"
        class="group absolute right-3 top-3 z-[99999] flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Bulk Invoice Management</h4>

      <!-- Tab Navigation -->
      <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
        <button 
          @click="activeTab = 'create'; resetCreateForm()" 
          :class="activeTab === 'create' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
          class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
          <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          Bulk Create
        </button>
        <button 
          @click="activeTab = 'missing'" 
          :class="activeTab === 'missing' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
          class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
          <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          Missing Invoices
        </button>
      </div>

      <!-- Form Errors -->
      <template x-if="formErrors.length > 0">
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
          <ul class="list-disc pl-5">
            <template x-for="error in formErrors" :key="error">
              <li x-text="error"></li>
            </template>
          </ul>
        </div>
      </template>

      <!-- ==================== TAB 1: BULK CREATE ==================== -->
      <div x-show="activeTab === 'create'">
        <form @submit.prevent="submitBulkCreate">
          @csrf
          
          <!-- Invoice Type -->
          <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Invoice Type *
            </label>
            <select
              x-model="createForm.invoice_type"
              @change="onInvoiceTypeChange()"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="monthly">Monthly</option>
              <option value="move_in">Move In</option>
              <option value="move_out">Move Out</option>
            </select>
          </div>

          <!-- Billing Month -->
          <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Billing Month *
            </label>
            <input
              type="month"
              x-model="createForm.billing_month"
              @change="onBillingMonthChange()"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>

          <!-- Target Selection -->
          <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Apply To *
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="createForm.apply_to === 'bulk' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="radio" x-model="createForm.apply_to" @click="onApplyToChange()" value="bulk" class="mr-3">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">All Tenancies</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">All active tenancies</p>
                </div>
              </label>
              
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="createForm.apply_to === 'single' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="radio" x-model="createForm.apply_to" @click="onApplyToChange()" value="single" class="mr-3">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Single Tenancy</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">One specific tenancy</p>
                </div>
              </label>
              
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="createForm.apply_to === 'multiple' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="radio" x-model="createForm.apply_to" @click="onApplyToChange()" value="multiple" class="mr-3">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Multiple Tenancies</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Select specific units</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Single Tenancy Selection -->
          <div class="mb-6" x-show="createForm.apply_to === 'single'">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Select Tenancy *
            </label>
            <select
              x-model="createForm.tenancy_id"
              @change="onTenancySelectionChange()"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">Select Tenancy</option>
              <template x-for="tenancy in activeTenanciesData" :key="tenancy.id">
                <option :value="tenancy.id" x-text="tenancy.tenant_name + ' - ' + tenancy.unit_number"></option>
              </template>
            </select>
          </div>

          <!-- Multiple Tenancy Selection -->
          <div class="mb-6" x-show="createForm.apply_to === 'multiple'">
            <div class="flex items-center justify-between mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                Select Tenancies *
              </label>
              <div class="flex gap-2">
                <button type="button" @click="selectAllTenancies" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                  Select All
                </button>
                <button type="button" @click="clearTenancySelection" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                  Clear All
                </button>
              </div>
            </div>
            
            <div class="text-sm mb-2" x-show="getSelectedTenancyCount() > 0">
              <span class="text-gray-600 dark:text-gray-400">Selected:</span>
              <span class="font-medium ml-2" x-text="getSelectedTenancyCount()"></span>
              <span class="text-gray-500 dark:text-gray-500 ml-1">tenancy(ies)</span>
            </div>
            
            <div class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto dark:border-gray-700">
              <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="tenancy in activeTenanciesData" :key="tenancy.id">
                  <label class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                    <input type="checkbox" 
                           x-model="createForm.selected_tenancies" 
                           :value="tenancy.id" 
                           @change="onTenancySelectionChange()"
                           class="mr-3 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600">
                    <div class="flex-1">
                      <p class="font-medium text-gray-700 dark:text-gray-300" x-text="tenancy.tenant_name"></p>
                      <p class="text-sm text-gray-500 dark:text-gray-400" x-text="'Unit: ' + tenancy.unit_number"></p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">active</span>
                  </label>
                </template>
              </div>
            </div>
          </div>

          <!-- ==================== UTILITY SELECTION SECTION ==================== -->
          <div class="mb-6" x-show="createForm.invoice_type === 'monthly' && getTenancyCount() > 0">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Select Items to Generate *
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.rent ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.rent" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">🏠 Rent</span>
              </label>
              
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.water ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.water" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">💧 Water</span>
              </label>
              
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.service ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.service" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">🔧 Service</span>
              </label>
              
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.garbage ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.garbage" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">🗑️ Garbage</span>
              </label>
              
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.security ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.security" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">🛡️ Security</span>
              </label>
              
              <label class="flex items-center p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                     :class="selectedUtilities.other ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="selectedUtilities.other" @change="updateSelectedItems()" class="mr-2 rounded border-gray-300 text-brand-500">
                <span class="text-sm">📋 Other</span>
              </label>
            </div>
            
            <!-- Refresh Button -->
            <div class="flex justify-end mb-2">
              <button type="button" @click="updateSelectedItems()" :disabled="isLoadingUtilities" class="text-xs text-brand-500 hover:text-brand-600 flex items-center gap-1">
                <svg x-show="!isLoadingUtilities" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span x-text="isLoadingUtilities ? 'Loading...' : '↻ Refresh Utility Charges'"></span>
              </button>
            </div>
          </div>

          <!-- Check Existing Invoices Button -->
          <div class="mb-6" x-show="createForm.invoice_type === 'monthly' && createForm.items.length > 0 && createForm.items.some(i => i.amount > 0)">
            <button type="button" 
                    @click="checkExistingInvoices"
                    :disabled="isCheckingInvoices"
                    class="w-full py-2.5 px-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
              <span x-show="!isCheckingInvoices">🔍 Check Existing Invoices for Selected Month</span>
              <span x-show="isCheckingInvoices">Checking...</span>
            </button>
            
            <!-- Check Results - FIXED with null safety -->
            <template x-if="checkResults">
              <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h5 class="text-sm font-medium text-blue-800 dark:text-blue-300">Invoice Check Results</h5>
                    <div class="mt-1 grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <span class="text-blue-700 dark:text-blue-400">Already have invoices:</span>
                        <span class="ml-2 font-medium" x-text="checkResults?.existing_count || 0"></span>
                      </div>
                      <div>
                        <span class="text-green-700 dark:text-green-400">Will create for:</span>
                        <span class="ml-2 font-medium" x-text="checkResults?.remaining_count || 0"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Invoice Items Preview Section -->
          <div class="mb-6" x-show="createForm.invoice_type === 'monthly' && createForm.items.length > 0">
            <div class="flex items-center justify-between mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                Invoice Items Preview (<span x-text="createForm.items.length"></span> items)
              </label>
              <button type="button" @click="addManualItem" class="text-xs text-brand-500 hover:text-brand-600 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Manual Item
              </button>
            </div>
            
            <!-- Summary -->
            <div class="mb-6 rounded-lg bg-gray-50 dark:bg-gray-800 p-4" x-show="summaryTotals.total > 0">
              <h5 class="font-medium text-gray-800 dark:text-white/90 mb-2">Summary</h5>
              <div class="space-y-1 text-sm">
                <p class="text-gray-600 dark:text-gray-400" x-show="selectedUtilities.rent && summaryTotals.rent > 0">
                  Rent Total: <span class="font-medium">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.rent.toFixed(2)"></span></span>
                </p>
                <p class="text-gray-600 dark:text-gray-400" x-show="selectedUtilities.water && summaryTotals.water > 0">
                  Water Total: <span class="font-medium">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.water.toFixed(2)"></span></span>
                </p>
                <p class="text-gray-600 dark:text-gray-400" x-show="selectedUtilities.service && summaryTotals.service > 0">
                  Service Total: <span class="font-medium">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.service.toFixed(2)"></span></span>
                </p>
                <p class="text-gray-600 dark:text-gray-400" x-show="selectedUtilities.garbage && summaryTotals.garbage > 0">
                  Garbage Total: <span class="font-medium">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.garbage.toFixed(2)"></span></span>
                </p>
                <p class="text-gray-600 dark:text-gray-400" x-show="selectedUtilities.security && summaryTotals.security > 0">
                  Security Total: <span class="font-medium">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.security.toFixed(2)"></span></span>
                </p>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                  <p class="text-gray-800 dark:text-white/90 font-medium">
                    Grand Total: <span class="text-brand-600">{{ \App\Helpers\SystemHelper::currencySymbol() }}<span x-text="summaryTotals.total.toFixed(2)"></span></span>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">Across <span x-text="summaryTotals.tenancy_count"></span> tenancies | <span x-text="createForm.items.length"></span> items</p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end w-full gap-3 mt-6">
              <button @click="closeModal" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">Cancel</button>
              <button type="submit" :disabled="isLoading || !createFormValid || createForm.items.filter(i => i.amount > 0).length === 0" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
                <span x-show="!isLoading">Create Invoices</span>
                <span x-show="isLoading">Creating...</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- ==================== TAB 2: MISSING INVOICES ==================== -->
      <div x-show="activeTab === 'missing'">
        <div class="mb-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Select tenancies to generate missing invoices for</p>
            </div>
            <div class="flex gap-2">
              <button type="button" @click="selectAllMissingTenancies" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Select All</button>
              <button type="button" @click="clearMissingTenancySelection" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Clear All</button>
            </div>
          </div>

          <div class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto dark:border-gray-700 mb-6">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <template x-for="tenancy in activeTenanciesData" :key="tenancy.id">
                <label class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                  <input type="checkbox" 
                         x-model="missingForm.selected_tenancies" 
                         :value="tenancy.id" 
                         @change="loadMissingMonthsForTenancy(tenancy.id)" 
                         class="mr-3 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600">
                  <div class="flex-1">
                    <p class="font-medium text-gray-700 dark:text-gray-300" x-text="tenancy.tenant_name"></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="'Unit: ' + tenancy.unit_number"></p>
                  </div>
                  <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">active</span>
                </label>
              </template>
            </div>
          </div>

          <div x-show="missingForm.selected_tenancies.length > 0 && missingMonthsData.length > 0" class="mt-4">
            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Missing Months</h5>
            <div class="space-y-4 max-h-96 overflow-y-auto">
              <template x-for="tenancy in missingMonthsData" :key="tenancy.id">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <div class="flex justify-between items-center mb-3">
                    <span class="font-medium text-gray-800 dark:text-white/90" x-text="tenancy.tenant_name + ' - ' + tenancy.unit_number"></span>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400" x-text="tenancy.missing_months.length + ' missing month(s)'"></span>
                  </div>
                  <div class="space-y-2">
                    <template x-for="month in tenancy.missing_months" :key="month.value">
                      <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                        <div class="flex items-center gap-3">
                          <input type="checkbox" x-model="tenancy.selected_months" :value="month.value" class="rounded border-gray-300">
                          <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="month.label"></span>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <div x-show="missingForm.selected_tenancies.length > 0 && missingMonthsData.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No missing months found for selected tenancies.
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button @click="closeModal" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">Cancel</button>
          <button @click="generateMissingInvoices" :disabled="missingGenerating || missingForm.selected_tenancies.length === 0" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-yellow-500 shadow-theme-xs hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
            <span x-show="!missingGenerating">Generate Missing Invoices</span>
            <span x-show="missingGenerating">Generating...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('bulkInvoiceModal', () => ({
    isOpen: false,
    activeTab: 'create',
    formErrors: [],
    isLoading: false,
    isCheckingInvoices: false,
    missingGenerating: false,
    isLoadingUtilities: false,
    checkResults: null,
    activeTenanciesData: @json($mappedActiveTenancies),
    showWaterSection: false,
    utilityCache: {},
    
    // Utility selection
    selectedUtilities: {
      rent: true,
      water: true,
      service: true,
      garbage: true,
      security: true,
      other: false
    },
    
    // Bulk Create Form
    createForm: {
      invoice_type: 'monthly',
      billing_month: '',
      apply_to: 'bulk',
      tenancy_id: '',
      selected_tenancies: [],
      items: []
    },
    nextItemId: 1,
    
    // Summary totals
    summaryTotals: {
      rent: 0,
      water: 0,
      service: 0,
      garbage: 0,
      security: 0,
      total: 0,
      tenancy_count: 0
    },
    
    // Missing Invoices Form
    missingForm: {
      selected_tenancies: []
    },
    missingMonthsData: [],
    
    init() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, '0');
      this.createForm.billing_month = `${year}-${month}`;
      window.bulkInvoiceModal = this;
      this.updateSelectedItems();
    },
    
    resetCreateForm() {
      this.createForm.items = [];
      this.nextItemId = 1;
      this.checkResults = null;
      this.summaryTotals = { rent: 0, water: 0, service: 0, garbage: 0, security: 0, total: 0, tenancy_count: 0 };
      this.utilityCache = {};
      if (this.createForm.invoice_type === 'monthly') {
        this.updateSelectedItems();
      }
    },
    
    get createFormValid() {
      const basicValid = this.createForm.invoice_type && this.createForm.billing_month;
      const hasItemsWithAmount = this.createForm.items.some(item => item.amount && parseFloat(item.amount) > 0);
      
      if (this.createForm.apply_to === 'single') return basicValid && hasItemsWithAmount && this.createForm.tenancy_id;
      if (this.createForm.apply_to === 'multiple') return basicValid && hasItemsWithAmount && this.createForm.selected_tenancies.length > 0;
      return basicValid && hasItemsWithAmount;
    },
    
    formatNumber(value) { 
      return parseFloat(value || 0).toFixed(2); 
    },
    
    formatMonth(monthString) { 
      if (!monthString) return ''; 
      const [year, month] = monthString.split('-'); 
      return new Date(year, month - 1).toLocaleDateString('en-US', { year: 'numeric', month: 'long' }); 
    },
    
    getTenancyCount() {
      if (this.createForm.apply_to === 'bulk') return this.activeTenanciesData.length;
      if (this.createForm.apply_to === 'single') return this.createForm.tenancy_id ? 1 : 0;
      return this.createForm.selected_tenancies.length;
    },
    
    getSelectedTenancyCount() { 
      return this.createForm.selected_tenancies.length; 
    },
    
    getSelectedTenancyIds() {
      if (this.createForm.apply_to === 'bulk') {
        return this.activeTenanciesData.map(t => t.id);
      } else if (this.createForm.apply_to === 'single') {
        return this.createForm.tenancy_id ? [this.createForm.tenancy_id] : [];
      } else {
        return this.createForm.selected_tenancies;
      }
    },
    
    async fetchTenancyUtilityData(tenancyId) {
      if (this.utilityCache[tenancyId]) {
        return this.utilityCache[tenancyId];
      }
      
      try {
        const response = await fetch(`/tenancies/${tenancyId}/invoice-data`);
        const data = await response.json();
        
        if (data.success) {
          const utilityData = {
            rent_amount: parseFloat(data.rent_amount) || 0,
            service_charge: parseFloat(data.service_charge) || 0,
            garbage_charge: parseFloat(data.garbage_charge) || 0,
            security_charge: parseFloat(data.security_charge) || 0,
            has_water: data.has_water_config || false,
            water_rate: parseFloat(data.water_rate) || 0,
            water_source: data.water_source,
            previous_reading: parseFloat(data.previous_reading) || 0,
            current_reading: parseFloat(data.current_reading) || 0,
          };
          
          if (utilityData.has_water) {
            if (utilityData.water_source === 'unit') {
              utilityData.water_charge = utilityData.water_rate;
            } else {
              const consumption = Math.max(0, utilityData.current_reading - utilityData.previous_reading);
              utilityData.water_charge = consumption * utilityData.water_rate;
              utilityData.water_consumption = consumption;
            }
          } else {
            utilityData.water_charge = 0;
          }
          
          this.utilityCache[tenancyId] = utilityData;
          return utilityData;
        }
      } catch (error) {
        console.error('Error fetching utility data:', error);
        return null;
      }
    },
    
    async updateSelectedItems() {
      const tenancyIds = this.getSelectedTenancyIds();
      
      if (tenancyIds.length === 0) {
        this.createForm.items = [];
        this.summaryTotals = { rent: 0, water: 0, service: 0, garbage: 0, security: 0, total: 0, tenancy_count: 0 };
        return;
      }
      
      this.isLoadingUtilities = true;
      
      try {
        const allUtilityData = [];
        for (const tenancyId of tenancyIds) {
          const utilityData = await this.fetchTenancyUtilityData(tenancyId);
          if (utilityData) {
            const tenancyInfo = this.activeTenanciesData.find(t => t.id === tenancyId);
            allUtilityData.push({
              tenancy_id: tenancyId,
              tenant_name: tenancyInfo?.tenant_name || 'Unknown',
              unit_number: tenancyInfo?.unit_number || 'Unknown',
              ...utilityData
            });
          }
        }
        
        if (allUtilityData.length > 0) {
          this.generateItemsFromAllTenancies(allUtilityData);
        }
      } catch (error) {
        console.error('Error updating selected items:', error);
      } finally {
        this.isLoadingUtilities = false;
      }
    },
    
    generateItemsFromAllTenancies(allUtilityData) {
      const newItems = [];
      const monthLabel = this.formatMonth(this.createForm.billing_month);
      
      let rentTotal = 0;
      let waterTotal = 0;
      let serviceTotal = 0;
      let garbageTotal = 0;
      let securityTotal = 0;
      
      for (const data of allUtilityData) {
        const unitLabel = `${data.unit_number} (${data.tenant_name})`;
        
        if (this.selectedUtilities.rent && data.rent_amount > 0) {
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'rent',
            amount: data.rent_amount,
            description: `Monthly Rent for ${unitLabel} - ${monthLabel}`,
            is_auto: true
          });
          rentTotal += data.rent_amount;
        }
        
        if (this.selectedUtilities.service && data.service_charge > 0) {
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'service',
            amount: data.service_charge,
            description: `Service Charge for ${unitLabel} - ${monthLabel}`,
            is_auto: true
          });
          serviceTotal += data.service_charge;
        }
        
        if (this.selectedUtilities.garbage && data.garbage_charge > 0) {
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'garbage',
            amount: data.garbage_charge,
            description: `Garbage Collection for ${unitLabel} - ${monthLabel}`,
            is_auto: true
          });
          garbageTotal += data.garbage_charge;
        }
        
        if (this.selectedUtilities.security && data.security_charge > 0) {
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'security',
            amount: data.security_charge,
            description: `Security Service for ${unitLabel} - ${monthLabel}`,
            is_auto: true
          });
          securityTotal += data.security_charge;
        }
        
        if (this.selectedUtilities.water && data.water_charge > 0) {
          let waterDescription = `Water Charges for ${unitLabel} - ${monthLabel}`;
          let metadata = null;
          
          if (data.water_consumption && data.water_consumption > 0) {
            waterDescription += ` (Usage: ${data.water_consumption.toFixed(2)} m³ @ ${data.water_rate}/m³)`;
            metadata = {
              consumption: data.water_consumption,
              rate: data.water_rate,
              previous_reading: data.previous_reading,
              current_reading: data.current_reading,
              unit_number: data.unit_number
            };
          } else if (data.water_source === 'unit') {
            waterDescription += ` (Flat rate)`;
          }
          
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'water',
            amount: data.water_charge,
            description: waterDescription,
            is_auto: true,
            metadata: metadata
          });
          waterTotal += data.water_charge;
        }
      }
      
      if (this.selectedUtilities.other) {
        for (const data of allUtilityData) {
          newItems.push({
            id: this.nextItemId++,
            tenancy_id: data.tenancy_id,
            unit_number: data.unit_number,
            item_type: 'other',
            amount: 0,
            description: `Other Charges for ${data.unit_number} (${data.tenant_name}) - ${monthLabel}`,
            is_auto: false
          });
        }
      }
      
      this.createForm.items = newItems;
      
      this.summaryTotals = {
        rent: rentTotal,
        water: waterTotal,
        service: serviceTotal,
        garbage: garbageTotal,
        security: securityTotal,
        total: rentTotal + waterTotal + serviceTotal + garbageTotal + securityTotal,
        tenancy_count: allUtilityData.length
      };
    },
    
    updateItemTotal() {
      const rentTotal = this.createForm.items.filter(i => i.item_type === 'rent').reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
      const waterTotal = this.createForm.items.filter(i => i.item_type === 'water').reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
      const serviceTotal = this.createForm.items.filter(i => i.item_type === 'service').reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
      const garbageTotal = this.createForm.items.filter(i => i.item_type === 'garbage').reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
      const securityTotal = this.createForm.items.filter(i => i.item_type === 'security').reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
      
      this.summaryTotals.rent = rentTotal;
      this.summaryTotals.water = waterTotal;
      this.summaryTotals.service = serviceTotal;
      this.summaryTotals.garbage = garbageTotal;
      this.summaryTotals.security = securityTotal;
      this.summaryTotals.total = rentTotal + waterTotal + serviceTotal + garbageTotal + securityTotal;
    },
    
    addManualItem() {
      this.createForm.items.push({
        id: this.nextItemId++,
        item_type: 'other',
        amount: 0,
        description: '',
        is_auto: false
      });
      this.resetCheckResults();
    },
    
    removeItem(index) {
      this.createForm.items.splice(index, 1);
      this.updateItemTotal();
      this.resetCheckResults();
    },
    
    onInvoiceTypeChange() {
      this.createForm.items = [];
      this.nextItemId = 1;
      this.resetCheckResults();
      this.updateSelectedItems();
    },
    
    onBillingMonthChange() {
      this.resetCheckResults();
      this.updateSelectedItems();
    },
    
    onApplyToChange() {
      this.resetCheckResults();
      this.utilityCache = {};
      this.updateSelectedItems();
    },
    
    onTenancySelectionChange() {
      this.resetCheckResults();
      this.utilityCache = {};
      this.updateSelectedItems();
    },
    
    selectAllTenancies() {
      this.createForm.selected_tenancies = this.activeTenanciesData.map(t => t.id);
      this.resetCheckResults();
      this.utilityCache = {};
      this.updateSelectedItems();
    },
    
    clearTenancySelection() {
      this.createForm.selected_tenancies = [];
      this.resetCheckResults();
      this.utilityCache = {};
      this.updateSelectedItems();
    },
    
    resetCheckResults() {
      this.checkResults = null;
    },
    
    async checkExistingInvoices() {
      if (!this.createForm.invoice_type || !this.createForm.billing_month) {
        alert('Please select invoice type and billing month first');
        return;
      }
      
      let tenancyIds = this.getSelectedTenancyIds();
      
      if (tenancyIds.length === 0) {
        alert('Please select tenancies to check');
        return;
      }
      
      this.isCheckingInvoices = true;
      try {
        const response = await fetch('{{ route("invoices.check.existing") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            tenancy_ids: tenancyIds,
            invoice_type: this.createForm.invoice_type,
            billing_month: this.createForm.billing_month
          })
        });
        const result = await response.json();
        if (result.success) {
          this.checkResults = result;
        } else {
          alert('Error checking existing invoices: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error checking existing invoices:', error);
        alert('An error occurred while checking existing invoices');
      } finally {
        this.isCheckingInvoices = false;
      }
    },
    
    async submitBulkCreate() {
      if (!this.createFormValid) {
        alert('Please fill in all required fields and ensure at least one item has an amount');
        return;
      }
      
      const tenancyIds = [...new Set(this.createForm.items.filter(item => item.tenancy_id).map(item => item.tenancy_id))];
      
      let finalTenancyIds = tenancyIds;
      if (this.checkResults && this.checkResults.remaining_tenancy_ids) {
        finalTenancyIds = tenancyIds.filter(id => this.checkResults.remaining_tenancy_ids.includes(id));
      }
      
      if (finalTenancyIds.length === 0) {
        alert('No tenancies to create invoices for (all have existing invoices)');
        return;
      }
      
      this.isLoading = true;
      let successCount = 0;
      let errorCount = 0;
      const errors = [];
      
      try {
        const itemsByTenancy = {};
        for (const item of this.createForm.items) {
          if (!item.tenancy_id) continue;
          if (!itemsByTenancy[item.tenancy_id]) {
            itemsByTenancy[item.tenancy_id] = [];
          }
          if (item.amount && parseFloat(item.amount) > 0) {
            itemsByTenancy[item.tenancy_id].push(item);
          }
        }
        
        for (const tenancyId of finalTenancyIds) {
          const items = itemsByTenancy[tenancyId] || [];
          if (items.length === 0) continue;
          
          const totalAmount = items.reduce((sum, item) => sum + parseFloat(item.amount), 0);
          
          const response = await fetch(`/tenancies/${tenancyId}/invoices`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              billing_month: this.createForm.billing_month,
              items: items.map(item => ({
                item_type: item.item_type,
                description: item.description,
                amount: parseFloat(item.amount),
                metadata: item.metadata
              }))
            })
          });
          
          const result = await response.json();
          if (result.success) {
            successCount++;
          } else {
            errorCount++;
            errors.push(`Tenancy ${tenancyId}: ${result.message}`);
          }
        }
        
        const message = `Created ${successCount} invoice(s) successfully!`;
        if (errorCount > 0) {
          alert(`${message}\nFailed: ${errorCount}\n${errors.join('\n')}`);
        } else {
          alert(message);
        }
        
        this.closeModal();
        setTimeout(() => window.location.reload(), 1500);
      } catch (error) {
        console.error('Error creating bulk invoices:', error);
        alert('An error occurred while creating invoices: ' + error.message);
      } finally {
        this.isLoading = false;
      }
    },
    
    // Missing Invoices Methods
    async loadMissingMonthsForTenancy(tenancyId) {
      if (!this.missingForm.selected_tenancies.includes(tenancyId)) {
        this.missingMonthsData = this.missingMonthsData.filter(t => t.id !== tenancyId);
        return;
      }
      
      try {
        const tenancyInfo = this.activeTenanciesData.find(t => t.id === tenancyId);
        const response = await fetch(`/tenancies/${tenancyId}/billing-history`);
        const data = await response.json();
        
        if (data.success) {
          const existingData = this.missingMonthsData.find(t => t.id === tenancyId);
          const missingMonths = (data.missing_months || []).map(m => ({ 
            value: m, 
            label: this.formatMonth(m) 
          }));
          
          if (existingData) {
            existingData.missing_months = missingMonths;
            existingData.selected_months = [];
          } else {
            this.missingMonthsData.push({ 
              id: tenancyId, 
              tenant_name: tenancyInfo?.tenant_name || 'Unknown',
              unit_number: tenancyInfo?.unit_number || '',
              missing_months: missingMonths, 
              selected_months: [], 
              water_readings: {} 
            });
          }
        }
      } catch (error) { 
        console.error('Error loading missing months:', error); 
      }
    },
    
    selectAllMissingTenancies() { 
      this.missingForm.selected_tenancies = this.activeTenanciesData.map(t => t.id);
      this.activeTenanciesData.forEach(t => this.loadMissingMonthsForTenancy(t.id));
    },
    
    clearMissingTenancySelection() { 
      this.missingForm.selected_tenancies = []; 
      this.missingMonthsData = []; 
    },
    
    updateWaterReading(tenancyId, month) {
      const tenancy = this.missingMonthsData.find(t => t.id === tenancyId);
      if (tenancy && tenancy.water_readings && tenancy.water_readings[month]) {
        const reading = tenancy.water_readings[month];
        reading.consumption = Math.max(0, reading.current - (reading.previous || 0));
        reading.charge = reading.consumption * 10;
      }
    },
    
    async generateMissingInvoices() {
      this.missingGenerating = true;
      try {
        const invoicesToGenerate = [];
        for (const tenancy of this.missingMonthsData) {
          if (tenancy.selected_months && tenancy.selected_months.length > 0) {
            invoicesToGenerate.push({ 
              tenancy_id: tenancy.id, 
              months: tenancy.selected_months 
            });
          }
        }
        
        if (invoicesToGenerate.length === 0) { 
          alert('No months selected'); 
          this.missingGenerating = false; 
          return; 
        }
        
        alert(`Generating invoices for ${invoicesToGenerate.length} tenancies...`);
        this.closeModal();
        setTimeout(() => window.location.reload(), 2000);
      } catch (error) { 
        console.error('Error generating missing invoices:', error); 
        alert('An error occurred');
      } finally { 
        this.missingGenerating = false; 
      }
    },
    
    openModal(tab = 'create') { 
      this.isOpen = true; 
      this.activeTab = tab;
      if (tab === 'create') {
        this.resetCreateForm();
      } else {
        this.missingForm.selected_tenancies = [];
        this.missingMonthsData = [];
      }
      document.body.style.overflow = 'hidden'; 
    },
    
    closeModal() { 
      this.isOpen = false; 
      this.formErrors = []; 
      document.body.style.overflow = ''; 
    }
  }));
});
</script>
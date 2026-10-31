<!-- CREATE INVOICE SLIDEOVER MODAL -->
<div x-data="invoiceCreateModal" x-init="init()" class="relative z-99999">
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
       x-cloak
       class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-99999"
       style="width: 42rem; max-width: calc(100% - 2rem);">
    
    <!-- Close Button -->
    <button
      @click="closeModal"
      class="group fixed right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
    >
      <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
      </svg>
    </button>

    <div class="p-6 lg:p-8">
      <form @submit.prevent="submitForm">
        @csrf
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
          Generate Invoice
        </h4>

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

        <!-- Tenancy Info Card -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg" x-show="tenancyData">
          <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Tenancy Information</h5>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <span class="text-gray-500 dark:text-gray-400">Tenant:</span>
              <span class="font-medium text-gray-800 dark:text-white/90 ml-2" x-text="tenancyData?.tenant_name"></span>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Unit:</span>
              <span class="font-medium text-gray-800 dark:text-white/90 ml-2" x-text="tenancyData?.unit_number"></span>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Estate:</span>
              <span class="font-medium text-gray-800 dark:text-white/90 ml-2" x-text="tenancyData?.estate_name"></span>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Move-in Date:</span>
              <span class="font-medium text-gray-800 dark:text-white/90 ml-2" x-text="tenancyData?.move_in_date_formatted"></span>
            </div>
          </div>
        </div>

        <!-- Utility Charges Section - NEW DROPDOWN BASED -->
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
          <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-400">Add Utility Charges</h5>
            <span class="text-xs text-blue-600 dark:text-blue-300">Select a utility to auto-populate</span>
          </div>
          
          <!-- Utility Dropdown -->
          <div class="mb-3">
            <select 
              x-model="selectedUtility"
              @change="addUtilityToInvoice"
              class="dark:bg-dark-900 w-full rounded-lg border border-blue-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-500 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-blue-700 dark:bg-gray-900 dark:text-white/90"
            >
              <option value="">-- Select Utility to Add --</option>
              <option x-show="standardCharges.rent > 0" value="rent">Rent (KES <span x-text="formatNumber(standardCharges.rent)"></span>)</option>
              <option x-show="standardCharges.service > 0" value="service">Service Charge (KES <span x-text="formatNumber(standardCharges.service)"></span>)</option>
              <option x-show="standardCharges.garbage > 0" value="garbage">Garbage Collection (KES <span x-text="formatNumber(standardCharges.garbage)"></span>)</option>
              <option x-show="standardCharges.security > 0" value="security">Security Service (KES <span x-text="formatNumber(standardCharges.security)"></span>)</option>
            </select>
          </div>

          <!-- Quick Add Buttons for available utilities -->
          <div class="flex flex-wrap gap-2">
            <template x-for="(amount, type) in standardCharges" :key="type">
              <button 
                x-show="amount > 0 && !isUtilityInInvoice(type)"
                @click="addSpecificUtility(type)"
                type="button"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:border-blue-400 transition-colors"
                x-text="getUtilityLabel(type) + ': KES ' + formatNumber(amount)"
              ></button>
            </template>
          </div>
          
          <!-- Added Utilities Summary -->
          <div x-show="addedUtilities.length > 0" class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
            <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-2">Added Utilities (System Generated):</p>
            <div class="space-y-1">
              <template x-for="util in addedUtilities" :key="util.type">
                <div class="flex items-center justify-between text-sm bg-white dark:bg-gray-700/50 rounded px-3 py-1.5">
                  <span class="text-gray-700 dark:text-gray-300" x-text="getUtilityLabel(util.type)"></span>
                  <div class="flex items-center gap-3">
                    <span class="font-medium text-gray-800 dark:text-white/90">KES <span x-text="formatNumber(util.amount)"></span></span>
                    <button 
                      @click="removeUtility(util.type)"
                      type="button"
                      class="text-red-500 hover:text-red-700 text-xs"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Missing Invoices Modal Trigger -->
        <div x-show="missingMonthsFiltered.length > 0" class="mb-4">
          <button
            type="button"
            @click="openMissingInvoicesModal"
            class="w-full flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors"
          >
            <div class="flex items-center gap-3">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <div class="text-left">
                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-400">Missing Invoices Detected</p>
                <p class="text-xs text-yellow-700 dark:text-yellow-500">
                  <span x-text="missingMonthsFiltered.length"></span> month(s) without invoices. Click to generate.
                </p>
              </div>
            </div>
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>

        <!-- Duplicate Invoice Alert -->
        <div x-show="hasDuplicateInvoices" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <svg class="w-6 h-6 text-red-600 dark:text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <h5 class="text-sm font-semibold text-red-800 dark:text-red-400">Duplicate Invoices Detected</h5>
                <p class="text-sm text-red-700 dark:text-red-500 mt-1">
                  Multiple invoices exist for the same billing month.
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                  <template x-for="(invoices, month) in duplicateMonths" :key="month">
                    <button
                      @click="openDuplicateResolution(month, invoices)"
                      type="button"
                      class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/50 dark:text-red-400"
                    >
                      Fix <span x-text="formatMonthDisplay(month)"></span> (<span x-text="invoices.length"></span> duplicates)
                    </button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Next Billing Month Info -->
        <div x-show="nextBillingMonth && !forceGenerateMode && missingMonthsFiltered.length === 0 && !allInvoicesGenerated" class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-amber-800 dark:text-amber-400">Next Billing Month</p>
              <p class="text-lg font-bold text-amber-900 dark:text-amber-300" x-text="nextBillingMonthFormatted"></p>
              <p class="text-xs text-amber-700 dark:text-amber-500 mt-1">Regular monthly invoices are generated sequentially</p>
            </div>
            <button
              type="button"
              @click="enableForceGenerate"
              class="px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-100 rounded-lg hover:bg-amber-200 dark:text-amber-300 dark:bg-amber-900/50 dark:hover:bg-amber-900"
            >
              Generate Anyway
            </button>
          </div>
        </div>

        <!-- All Invoices Generated State -->
        <div x-show="allInvoicesGenerated && !forceGenerateMode && missingMonthsFiltered.length === 0 && nextBillingMonth" 
             class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
          <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-green-600 dark:text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
              <p class="text-sm font-semibold text-green-800 dark:text-green-400">All Invoices Generated</p>
              <p class="text-sm text-green-700 dark:text-green-500">
                All invoices up to <span x-text="formatMonthDisplay(currentMonth)"></span> have been generated.
              </p>
              <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                Next invoice will be available on <span x-text="nextBillingMonthFormatted"></span>
              </p>
            </div>
          </div>
        </div>

        <!-- Force Generate Warning -->
        <div x-show="forceGenerateMode" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-red-800 dark:text-red-400">⚠️ Force Generate Mode</p>
              <p class="text-xs text-red-700 dark:text-red-500 mt-1">You're generating an invoice for a month that's not the next sequential month.</p>
            </div>
            <button
              type="button"
              @click="disableForceGenerate"
              class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Force Generate Reason -->
        <div x-show="forceGenerateMode" class="mb-4">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Reason for Force Generation *
          </label>
          <textarea
            x-model="forceGenerateReason"
            rows="2"
            placeholder="Please explain why you're generating an invoice outside the normal billing cycle..."
            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          ></textarea>
        </div>

        <!-- Billing Month -->
        <div class="mb-4">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Billing Month *
          </label>
          <input
            type="month"
            x-model="form.billing_month"
            @change="checkMonthValidity"
            :class="{'border-red-500 focus:border-red-500': monthWarning}"
            :disabled="allInvoicesGenerated && !forceGenerateMode"
            required
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:opacity-50 disabled:cursor-not-allowed"
          />
          <p x-show="monthWarning" class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="monthWarning"></p>
        </div>

        <!-- Invoice Items (Manual Items) -->
        <div class="mb-4">
          <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
              Manual Invoice Items
            </label>
            <button
              type="button"
              @click="addItem"
              :disabled="allInvoicesGenerated && !forceGenerateMode"
              class="text-sm text-brand-500 hover:text-brand-600 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Item
            </button>
          </div>

          <div class="space-y-3">
            <template x-for="(item, index) in form.items" :key="index">
              <div class="grid grid-cols-12 gap-2 items-start">
                <div class="col-span-5">
                  <input
                    type="text"
                    x-model="item.description"
                    placeholder="Description"
                    :disabled="allInvoicesGenerated && !forceGenerateMode || isSystemItem(item)"
                    :class="isSystemItem(item) ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:opacity-50 disabled:cursor-not-allowed"
                  />
                </div>
                <div class="col-span-3">
                  <select
                    x-model="item.item_type"
                    :disabled="allInvoicesGenerated && !forceGenerateMode || isSystemItem(item)"
                    :class="isSystemItem(item) ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <option value="rent">Rent</option>
                    <option value="water">Water</option>
                    <option value="service">Service Charge</option>
                    <option value="garbage">Garbage</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="col-span-3">
                  <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 dark:text-gray-400">KES</span>
                    </div>
                    <input
                      type="number"
                      step="0.01"
                      x-model="item.amount"
                      placeholder="0.00"
                      :disabled="allInvoicesGenerated && !forceGenerateMode || isSystemItem(item)"
                      :class="isSystemItem(item) ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''"
                      class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-12 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                  </div>
                </div>
                <div class="col-span-1">
                  <button
                    type="button"
                    @click="removeItem(index)"
                    x-show="!isSystemItem(item)"
                    :disabled="allInvoicesGenerated && !forceGenerateMode"
                    class="text-red-600 hover:text-red-800 disabled:opacity-50"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Smart Water Reading Section -->
        <div x-show="showWaterSection && form.billing_month && !allInvoicesGenerated" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
          <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90">Water Meter Reading</h5>
            <span class="text-xs px-2 py-1 rounded-full" 
                  :class="waterSource === 'estate' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'"
                  x-text="waterSource === 'estate' ? 'Estate Rate' : 'Unit Rate'"></span>
          </div>
          
          <!-- Water Reading Inputs -->
          <div class="space-y-4">
            <!-- Previous Reading -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Previous Reading (units)</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <input
                  type="text"
                  :value="formatNumber(previousReading) + ' units'"
                  disabled
                  class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-gray-100 dark:bg-gray-800 px-4 py-2.5 pl-10 text-sm text-gray-600 dark:text-gray-400 cursor-not-allowed"
                />
              </div>
            </div>
            
            <!-- Current Reading -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Current Reading (units) *</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                </div>
                <input
                  type="number"
                  step="0.01"
                  x-model="currentReadingInput"
                  @input="updateWaterCalculation"
                  placeholder="Enter current meter reading"
                  class="dark:bg-dark-900 w-full rounded-lg border border-blue-300 bg-transparent px-4 py-2.5 pl-10 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-500 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-blue-700 dark:bg-gray-900 dark:text-white/90"
                />
              </div>
            </div>
            
            <!-- Water Rate -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Water Rate</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input
                  type="text"
                  :value="formatNumber(waterRate) + ' / unit'"
                  disabled
                  class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-gray-100 dark:bg-gray-800 px-4 py-2.5 pl-16 text-sm text-gray-600 dark:text-gray-400 cursor-not-allowed"
                />
              </div>
            </div>
            
            <!-- Consumption Summary -->
            <div class="pt-3 border-t border-blue-200 dark:border-blue-800">
              <div class="grid grid-cols-2 gap-4 mb-3">
                <div class="text-center p-2 bg-white dark:bg-gray-800 rounded-lg">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Consumption</p>
                  <p class="text-lg font-bold text-blue-600" x-text="formatNumber(consumption) + ' units'"></p>
                </div>
                <div class="text-center p-2 bg-white dark:bg-gray-800 rounded-lg">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Water Charge</p>
                  <p class="text-lg font-bold text-green-600">KES <span x-text="formatNumber(calculatedWaterCharge)"></span></p>
                </div>
              </div>
              
              <button
                type="button"
                @click="addWaterItemToInvoice"
                :disabled="consumption <= 0 || (allInvoicesGenerated && !forceGenerateMode)"
                class="w-full py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Water Charge to Invoice
              </button>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount:</span>
            <span class="text-lg font-semibold text-brand-600 dark:text-brand-400">
              KES <span x-text="formatCurrency(getTotalAmount())"></span>
            </span>
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button
            @click="closeModal"
            type="button"
            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !form.billing_month || getTotalAmount() <= 0 || (allInvoicesGenerated && !forceGenerateMode)"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">Create Invoice</span>
            <span x-show="loading">Creating...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Missing Invoices Modal -->
  <div x-show="showMissingInvoicesModal" x-cloak class="fixed inset-0 z-99999 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black/50" @click="showMissingInvoicesModal = false"></div>
      
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto z-99999">
        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Missing Invoices</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Select months to generate invoices (Water readings must be sequential)
          </p>
          <button @click="showMissingInvoicesModal = false" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <div class="mb-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Month</th>
                  <th x-show="standardCharges.rent > 0" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Rent</th>
                  <th x-show="standardCharges.service > 0" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Service</th>
                  <th x-show="standardCharges.garbage > 0" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Garbage</th>
                  <th x-show="standardCharges.security > 0" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Security</th>
                  <th x-show="showWaterSection" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Water</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                    <input type="checkbox" x-model="selectAllMissing" @change="toggleSelectAllMissing" class="rounded border-gray-300">
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="(month, idx) in missingMonthsFiltered" :key="month.value">
                  <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white" x-text="month.label"></td>
                    <td x-show="standardCharges.rent > 0" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">KES <span x-text="formatNumber(standardCharges.rent)"></span></td>
                    <td x-show="standardCharges.service > 0" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">KES <span x-text="formatNumber(standardCharges.service)"></span></td>
                    <td x-show="standardCharges.garbage > 0" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">KES <span x-text="formatNumber(standardCharges.garbage)"></span></td>
                    <td x-show="standardCharges.security > 0" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">KES <span x-text="formatNumber(standardCharges.security)"></span></td>
                    <td x-show="showWaterSection" class="px-4 py-3 text-sm">
                      <div class="space-y-2">
                        <input 
                          type="number" 
                          step="0.01" 
                          x-model="waterReadings[month.value].current" 
                          @change="updateWaterReadingForMonth(month.value, idx)"
                          placeholder="Current reading"
                          class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:bg-gray-800"
                        />
                        <div class="text-xs text-gray-500">
                          Previous: <span x-text="formatNumber(waterReadings[month.value]?.previous || 0)"></span><br>
                          Consumption: <span x-text="formatNumber(waterReadings[month.value]?.consumption || 0)"></span> units<br>
                          Charge: KES <span x-text="formatNumber(waterReadings[month.value]?.charge || 0)"></span>
                        </div>
                        <div x-show="waterReadings[month.value]?.warning" class="text-xs text-red-600" x-text="waterReadings[month.value]?.warning"></div>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <input type="checkbox" x-model="selectedMissingMonths" :value="month.value" class="rounded border-gray-300">
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          
          <div x-show="waterSequentialWarning" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-sm text-yellow-800 dark:text-yellow-400">
              ⚠️ Water readings must be sequential. Each month's reading must be greater than or equal to the previous month's reading.
            </p>
          </div>
        </div>
        
        <div class="sticky bottom-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end gap-3">
          <button @click="showMissingInvoicesModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            Cancel
          </button>
          <button @click="generateSelectedMissingMonths" :disabled="bulkGenerating || selectedMissingMonths.length === 0 || hasWaterSequentialError" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!bulkGenerating">Generate Selected (<span x-text="selectedMissingMonths.length"></span>)</span>
            <span x-show="bulkGenerating">Generating...</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Duplicate Invoice Resolution Modal -->
  <div x-show="showDuplicateResolution" x-cloak class="fixed inset-0 z-99999 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black/50" @click="showDuplicateResolution = false"></div>
      
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-2xl w-full z-99999">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">Duplicate Invoices Found</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Multiple invoices exist for <span x-text="duplicateMonthFormatted"></span>
          </p>
          <button @click="showDuplicateResolution = false" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
            Please select which invoice to keep. The selected invoice will be kept, and others will be deleted along with their items and payments.
          </p>
          
          <div class="space-y-3">
            <template x-for="invoice in duplicateInvoicesList" :key="invoice.id">
              <div class="border rounded-lg p-4 cursor-pointer transition-all"
                   :class="selectedDuplicateInvoice === invoice.id ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                   @click="selectedDuplicateInvoice = invoice.id">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <input type="radio" :checked="selectedDuplicateInvoice === invoice.id" class="w-4 h-4 text-brand-500">
                      <span class="font-medium text-gray-800 dark:text-white/90">Invoice #<span x-text="invoice.id"></span></span>
                      <span class="px-2 py-0.5 text-xs rounded-full"
                            :class="invoice.status === 'paid' ? 'bg-green-100 text-green-800' : invoice.status === 'partial' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'"
                            x-text="invoice.status"></span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      Total: KES <span x-text="formatNumber(invoice.total_amount)"></span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      Created: <span x-text="invoice.created_at_formatted"></span>
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-500">Items (<span x-text="invoice.items?.length || 0"></span>):</p>
                    <ul class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                      <template x-for="item in invoice.items" :key="item.id">
                        <li>• <span x-text="item.description"></span>: KES <span x-text="formatNumber(item.amount)"></span></li>
                      </template>
                    </ul>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
        
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end gap-3">
          <button @click="showDuplicateResolution = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            Cancel
          </button>
          <button @click="resolveDuplicates" :disabled="!selectedDuplicateInvoice || resolvingDuplicates" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
            <span x-show="!resolvingDuplicates">Keep Selected, Delete Others</span>
            <span x-show="resolvingDuplicates">Processing...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('invoiceCreateModal', () => ({
    isOpen: false,
    tenancyId: null,
    tenancyData: null,
    form: {
      billing_month: new Date().toISOString().slice(0, 7),
      items: []
    },
    formErrors: [],
    loading: false,
    existingInvoices: [],
    nextBillingMonth: null,
    nextBillingMonthFormatted: null,
    forceGenerateMode: false,
    forceGenerateReason: '',
    monthWarning: null,
    allInvoicesGenerated: false,
    currentMonth: new Date().toISOString().slice(0, 7),
    
    // Standard charges
    standardCharges: {
      rent: 0,
      service: 0,
      garbage: 0,
      security: 0
    },
    
    // Added utilities tracking
    addedUtilities: [],
    selectedUtility: '',
    
    // Missing months
    missingMonthsFiltered: [],
    selectedMissingMonths: [],
    selectAllMissing: false,
    bulkGenerating: false,
    showMissingInvoicesModal: false,
    waterSequentialWarning: false,
    hasWaterSequentialError: false,
    
    // Water data
    showWaterSection: false,
    waterSource: 'estate',
    previousReading: 0,
    currentReadingInput: 0,
    waterRate: 0,
    consumption: 0,
    calculatedWaterCharge: 0,
    waterReadings: {},
    
    // Duplicates
    hasDuplicateInvoices: false,
    duplicateMonths: {},
    showDuplicateResolution: false,
    duplicateMonth: null,
    duplicateMonthFormatted: null,
    duplicateInvoicesList: [],
    selectedDuplicateInvoice: null,
    resolvingDuplicates: false,
    
    init() {
      window.invoiceCreateModal = this;
    },
    
    formatNumber(value) {
      return parseFloat(value || 0).toFixed(2);
    },
    
    formatMonthDisplay(month) {
      if (!month) return '';
      const date = new Date(month + '-01');
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    },
    
    get hasAnyStandardCharges() {
      return this.standardCharges.rent > 0 || 
             this.standardCharges.service > 0 || 
             this.standardCharges.garbage > 0 || 
             this.standardCharges.security > 0 ||
             (this.showWaterSection && this.waterRate > 0);
    },
    
    getUtilityLabel(type) {
      const labels = {
        rent: 'Rent',
        service: 'Service Charge',
        garbage: 'Garbage Collection',
        security: 'Security Service'
      };
      return labels[type] || type;
    },
    
    isUtilityInInvoice(type) {
      return this.form.items.some(item => item.item_type === type && item._isSystem === true);
    },
    
    isSystemItem(item) {
      return item._isSystem === true;
    },
    
    addUtilityToInvoice() {
      if (!this.selectedUtility) return;
      this.addSpecificUtility(this.selectedUtility);
      this.selectedUtility = '';
    },
    
    addSpecificUtility(type) {
      const amount = this.standardCharges[type];
      if (!amount || amount <= 0) return;
      if (this.isUtilityInInvoice(type)) return;
      
      const label = this.getUtilityLabel(type);
      
      this.form.items.push({
        description: label,
        item_type: type,
        amount: amount,
        _isSystem: true,
        _isReadOnly: true
      });
      
      // Track added utilities
      if (!this.addedUtilities.some(u => u.type === type)) {
        this.addedUtilities.push({ type, amount });
      }
      
      // Remove from quick add buttons
      this.$nextTick(() => {
        this.formErrors = [];
      });
    },
    
    removeUtility(type) {
      this.form.items = this.form.items.filter(item => item.item_type !== type || !item._isSystem);
      this.addedUtilities = this.addedUtilities.filter(u => u.type !== type);
    },
    
    async openModal(tenancyId) {
      this.tenancyId = tenancyId;
      this.isOpen = true;
      this.resetForm();
      this.forceGenerateMode = false;
      this.forceGenerateReason = '';
      this.monthWarning = null;
      this.selectedMissingMonths = [];
      this.selectAllMissing = false;
      this.allInvoicesGenerated = false;
      this.addedUtilities = [];
      
      await this.fetchTenancyData();
      await this.checkExistingInvoices();
      await this.fetchBillingHistory();
      await this.determineNextBillingMonth();
      
      document.body.style.overflow = 'hidden';
    },
    
    async fetchTenancyData() {
      try {
        const response = await fetch(`/tenancies/${this.tenancyId}/invoice-data`);
        const data = await response.json();
        
        if (response.ok) {
          this.tenancyData = data.tenancy;
          
          if (this.tenancyData.move_in_date) {
            const moveInDate = new Date(this.tenancyData.move_in_date);
            this.tenancyData.move_in_date_formatted = moveInDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
          }
          
          this.standardCharges = {
            rent: parseFloat(data.rent_amount) || 0,
            service: parseFloat(data.service_charge) || 0,
            garbage: parseFloat(data.garbage_charge) || 0,
            security: parseFloat(data.security_charge) || 0
          };
          
          if (data.has_water_config) {
            this.showWaterSection = true;
            this.waterSource = data.water_source;
            this.previousReading = parseFloat(data.previous_reading) || 0;
            this.currentReadingInput = parseFloat(data.current_reading) || 0;
            this.waterRate = parseFloat(data.water_rate) || 0;
            this.updateWaterCalculation();
          }
        }
      } catch (error) {
        console.error('Error fetching tenancy data:', error);
      }
    },
    
    async checkExistingInvoices() {
      try {
        const response = await fetch(`/tenancies/${this.tenancyId}/invoices`);
        const data = await response.json();
        
        if (response.ok && data.invoices) {
          this.existingInvoices = data.invoices;
        }
      } catch (error) {
        console.error('Error fetching existing invoices:', error);
        this.existingInvoices = [];
      }
    },
    
    async fetchBillingHistory() {
      try {
        const response = await fetch(`/tenancies/${this.tenancyId}/billing-history`);
        const data = await response.json();
        
        if (data.success) {
          const currentYearMonth = new Date().toISOString().slice(0, 7);
          
          this.missingMonthsFiltered = (data.missing_months || [])
            .filter(m => m !== currentYearMonth)
            .map(month => ({
              value: month,
              label: this.formatMonthDisplay(month)
            }));
          
          this.missingMonthsFiltered.sort((a, b) => a.value.localeCompare(b.value));
          
          this.hasDuplicateInvoices = Object.keys(data.duplicate_months || {}).length > 0;
          this.duplicateMonths = data.duplicate_months || {};
          
          if (this.showWaterSection && this.missingMonthsFiltered.length > 0) {
            let lastReading = this.previousReading;
            
            const lastWaterInvoice = this.existingInvoices
              .filter(inv => inv.items && inv.items.some(i => i.item_type === 'water'))
              .sort((a, b) => new Date(b.billing_month) - new Date(a.billing_month))[0];
            
            if (lastWaterInvoice) {
              const lastWaterItem = lastWaterInvoice.items.find(i => i.item_type === 'water');
              if (lastWaterItem && lastWaterItem.metadata) {
                const metadata = typeof lastWaterItem.metadata === 'string' ? JSON.parse(lastWaterItem.metadata) : lastWaterItem.metadata;
                lastReading = metadata.current_reading || metadata.currentReading || lastReading;
              }
            }
            
            this.missingMonthsFiltered.forEach((month, index) => {
              const estimatedConsumption = 10;
              const currentReadingForMonth = lastReading + estimatedConsumption;
              const charge = estimatedConsumption * this.waterRate;
              
              this.waterReadings[month.value] = {
                previous: lastReading,
                current: currentReadingForMonth,
                consumption: estimatedConsumption,
                charge: charge,
                warning: null
              };
              
              lastReading = currentReadingForMonth;
            });
            
            this.validateWaterSequential();
          }
        }
      } catch (error) {
        console.error('Error fetching billing history:', error);
      }
    },
    
    validateWaterSequential() {
      this.waterSequentialWarning = false;
      this.hasWaterSequentialError = false;
      
      const sortedMonths = [...this.missingMonthsFiltered].sort((a, b) => a.value.localeCompare(b.value));
      
      for (let i = 0; i < sortedMonths.length; i++) {
        const month = sortedMonths[i];
        const reading = this.waterReadings[month.value];
        
        if (reading && reading.current > 0) {
          if (i > 0) {
            const prevMonth = sortedMonths[i - 1];
            const prevReading = this.waterReadings[prevMonth.value];
            if (prevReading && reading.current < prevReading.current) {
              reading.warning = `Current reading (${reading.current}) cannot be less than previous month's reading (${prevReading.current})`;
              this.waterSequentialWarning = true;
              this.hasWaterSequentialError = true;
            } else {
              reading.warning = null;
            }
          }
          
          const previousReadingForMonth = i === 0 ? this.previousReading : this.waterReadings[sortedMonths[i - 1]?.value]?.current || reading.previous;
          reading.consumption = Math.max(0, reading.current - previousReadingForMonth);
          reading.charge = reading.consumption * this.waterRate;
          reading.previous = previousReadingForMonth;
        }
      }
    },
    
    updateWaterReadingForMonth(monthValue, index) {
      const reading = this.waterReadings[monthValue];
      if (reading && reading.current > 0) {
        let previousReadingForMonth = this.previousReading;
        
        if (index > 0) {
          const prevMonth = this.missingMonthsFiltered[index - 1];
          const prevReading = this.waterReadings[prevMonth.value];
          if (prevReading && prevReading.current > 0) {
            previousReadingForMonth = prevReading.current;
          }
        }
        
        reading.previous = previousReadingForMonth;
        reading.consumption = Math.max(0, reading.current - previousReadingForMonth);
        reading.charge = reading.consumption * this.waterRate;
        
        if (index < this.missingMonthsFiltered.length - 1) {
          const nextMonth = this.missingMonthsFiltered[index + 1];
          const nextReading = this.waterReadings[nextMonth.value];
          if (nextReading && nextReading.current > 0 && nextReading.current < reading.current) {
            nextReading.warning = `Current reading (${nextReading.current}) cannot be less than previous month's reading (${reading.current})`;
            this.waterSequentialWarning = true;
            this.hasWaterSequentialError = true;
          } else if (nextReading) {
            nextReading.warning = null;
            nextReading.previous = reading.current;
            nextReading.consumption = Math.max(0, nextReading.current - reading.current);
            nextReading.charge = nextReading.consumption * this.waterRate;
          }
        }
        
        if (index > 0) {
          const prevMonth = this.missingMonthsFiltered[index - 1];
          const prevReading = this.waterReadings[prevMonth.value];
          if (prevReading && reading.current < prevReading.current) {
            reading.warning = `Current reading (${reading.current}) cannot be less than previous month's reading (${prevReading.current})`;
            this.waterSequentialWarning = true;
            this.hasWaterSequentialError = true;
          } else {
            reading.warning = null;
            this.waterSequentialWarning = false;
            this.hasWaterSequentialError = false;
          }
        }
        
        this.waterReadings = { ...this.waterReadings };
      }
    },
    
    openMissingInvoicesModal() {
      this.validateWaterSequential();
      this.showMissingInvoicesModal = true;
    },
    
    toggleSelectAllMissing() {
      if (this.selectAllMissing) {
        this.selectedMissingMonths = this.missingMonthsFiltered.map(m => m.value);
      } else {
        this.selectedMissingMonths = [];
      }
    },
    
    async generateSelectedMissingMonths() {
      if (this.selectedMissingMonths.length === 0) return;
      
      this.validateWaterSequential();
      if (this.hasWaterSequentialError) {
        this.formErrors = ['Please fix water reading sequential errors before generating.'];
        return;
      }
      
      this.bulkGenerating = true;
      
      try {
        const selectedMonthsData = this.missingMonthsFiltered.filter(m => 
          this.selectedMissingMonths.includes(m.value)
        );
        
        const monthsWithItems = selectedMonthsData.map(month => {
          const items = [];
          
          if (this.standardCharges.rent > 0) {
            items.push({
              description: 'Monthly Rent',
              item_type: 'rent',
              amount: this.standardCharges.rent
            });
          }
          
          if (this.standardCharges.service > 0) {
            items.push({
              description: 'Service Charge',
              item_type: 'service',
              amount: this.standardCharges.service
            });
          }
          
          if (this.standardCharges.garbage > 0) {
            items.push({
              description: 'Garbage Collection',
              item_type: 'garbage',
              amount: this.standardCharges.garbage
            });
          }
          
          if (this.standardCharges.security > 0) {
            items.push({
              description: 'Security Service',
              item_type: 'security',
              amount: this.standardCharges.security
            });
          }
          
          const waterReading = this.waterReadings[month.value];
          if (waterReading && waterReading.current > 0 && waterReading.consumption > 0) {
            items.push({
              description: `Water Consumption (${waterReading.consumption.toFixed(2)} units @ KES ${this.waterRate.toFixed(2)}/unit)`,
              item_type: 'water',
              amount: waterReading.charge,
              metadata: {
                previous_reading: waterReading.previous,
                current_reading: waterReading.current,
                consumption: waterReading.consumption,
                rate: this.waterRate
              }
            });
          }
          
          return { month: month.value, items: items };
        });
        
        const response = await fetch(`/tenancies/${this.tenancyId}/invoices/bulk-missing`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ months_data: monthsWithItems })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.showMissingInvoicesModal = false;
          this.selectedMissingMonths = [];
          this.selectAllMissing = false;
          
          if (window.successModal) {
            window.successModal.show('Success!', `Generated ${data.generated_count} invoices successfully.`);
          } else {
            alert(`Success! Generated ${data.generated_count} invoices.`);
          }
          
          setTimeout(() => window.location.reload(), 2000);
        } else {
          alert(data.message || 'Failed to generate missing invoices');
        }
      } catch (error) {
        console.error('Error generating missing invoices:', error);
        alert('Error generating invoices');
      } finally {
        this.bulkGenerating = false;
      }
    },
    
    async determineNextBillingMonth() {
      const billingMonths = this.existingInvoices
        .filter(inv => inv.billing_month && inv.invoice_type === 'monthly')
        .map(inv => inv.billing_month.slice(0, 7));
      
      billingMonths.sort();
      
      let expectedStartMonth = null;
      if (this.tenancyData?.move_in_date) {
        const moveInDate = new Date(this.tenancyData.move_in_date);
        const moveInDay = moveInDate.getDate();
        expectedStartMonth = moveInDate.toISOString().slice(0, 7);
        if (moveInDay > 15) {
          const nextMonth = new Date(moveInDate);
          nextMonth.setMonth(nextMonth.getMonth() + 1);
          expectedStartMonth = nextMonth.toISOString().slice(0, 7);
        }
      } else {
        expectedStartMonth = new Date().toISOString().slice(0, 7);
      }
      
      const currentMonthDate = new Date();
      currentMonthDate.setDate(1);
      let checkDate = new Date(expectedStartMonth + '-01');
      let foundGap = null;
      
      while (checkDate <= currentMonthDate) {
        const yearMonth = checkDate.toISOString().slice(0, 7);
        if (!billingMonths.includes(yearMonth)) {
          foundGap = yearMonth;
          break;
        }
        checkDate.setMonth(checkDate.getMonth() + 1);
      }
      
      if (this.missingMonthsFiltered.length > 0 || foundGap) {
        this.nextBillingMonth = null;
        this.nextBillingMonthFormatted = null;
        this.allInvoicesGenerated = false;
        return;
      }
      
      if (billingMonths.length > 0) {
        const latestMonth = billingMonths[billingMonths.length - 1];
        const latestDate = new Date(latestMonth + '-01');
        const nextDate = new Date(latestDate);
        nextDate.setMonth(nextDate.getMonth() + 1);
        const nextMonth = nextDate.toISOString().slice(0, 7);
        
        const nextMonthDate = new Date(nextMonth + '-01');
        
        if (nextMonthDate > currentMonthDate) {
          this.allInvoicesGenerated = true;
          this.nextBillingMonth = nextMonth;
          this.nextBillingMonthFormatted = this.formatMonthDisplay(nextMonth);
          return;
        }
        
        this.allInvoicesGenerated = false;
        this.nextBillingMonth = nextMonth;
        this.nextBillingMonthFormatted = this.formatMonthDisplay(nextMonth);
        this.form.billing_month = nextMonth;
      } else {
        this.allInvoicesGenerated = false;
        this.nextBillingMonth = expectedStartMonth;
        this.nextBillingMonthFormatted = this.formatMonthDisplay(expectedStartMonth);
        this.form.billing_month = expectedStartMonth;
      }
    },
    
    updateWaterCalculation() {
      const current = parseFloat(this.currentReadingInput) || 0;
      this.consumption = Math.max(0, current - this.previousReading);
      this.calculatedWaterCharge = this.consumption * this.waterRate;
    },
    
    checkMonthValidity() {
      const selectedMonth = this.form.billing_month;
      
      if (!selectedMonth) {
        this.monthWarning = null;
        return;
      }
      
      const existingForMonth = this.existingInvoices.some(inv => 
        inv.billing_month && inv.billing_month.slice(0, 7) === selectedMonth && inv.invoice_type === 'monthly'
      );
      
      if (existingForMonth) {
        this.monthWarning = `An invoice already exists for ${this.formatMonthDisplay(selectedMonth)}. Please edit the existing invoice.`;
        return;
      }
      
      if (this.nextBillingMonth && selectedMonth !== this.nextBillingMonth && !this.forceGenerateMode && this.missingMonthsFiltered.length === 0 && !this.allInvoicesGenerated) {
        this.monthWarning = `Warning: ${this.formatMonthDisplay(selectedMonth)} is not the next sequential billing month. The next expected month is ${this.nextBillingMonthFormatted}. Click "Generate Anyway" if you want to proceed.`;
      } else {
        this.monthWarning = null;
      }
    },
    
    enableForceGenerate() {
      this.forceGenerateMode = true;
      this.monthWarning = null;
    },
    
    disableForceGenerate() {
      this.forceGenerateMode = false;
      this.forceGenerateReason = '';
      if (this.nextBillingMonth && !this.allInvoicesGenerated) {
        this.form.billing_month = this.nextBillingMonth;
      }
      this.checkMonthValidity();
    },
    
    addWaterItemToInvoice() {
      const waterCharge = parseFloat(this.calculatedWaterCharge) || 0;
      const consumptionNum = parseFloat(this.consumption) || 0;
      const waterRateNum = parseFloat(this.waterRate) || 0;
      
      if (waterCharge > 0 && consumptionNum > 0) {
        this.form.items = this.form.items.filter(item => item.item_type !== 'water' || !item._isSystem);
        
        const newItem = {
          description: `Water Consumption (${consumptionNum.toFixed(2)} units @ KES ${waterRateNum.toFixed(2)}/unit)`,
          item_type: 'water',
          amount: waterCharge,
          _isSystem: true,
          _isReadOnly: true,
          metadata: {
            previous_reading: this.previousReading,
            current_reading: this.currentReadingInput,
            consumption: consumptionNum,
            rate: waterRateNum
          }
        };
        
        this.form.items = [...this.form.items, newItem];
      }
    },
    
    openDuplicateResolution(month, invoices) {
      this.duplicateMonth = month;
      this.duplicateMonthFormatted = this.formatMonthDisplay(month);
      this.duplicateInvoicesList = invoices.map(inv => ({
        ...inv,
        items: inv.items || [],
        created_at_formatted: inv.created_at ? new Date(inv.created_at).toLocaleDateString() : 'Unknown'
      }));
      this.selectedDuplicateInvoice = null;
      this.showDuplicateResolution = true;
    },
    
    async resolveDuplicates() {
      if (!this.selectedDuplicateInvoice) return;
      
      this.resolvingDuplicates = true;
      
      try {
        const response = await fetch('/invoices/resolve-duplicates', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({
            month: this.duplicateMonth,
            tenancy_id: this.tenancyId,
            keep_invoice_id: this.selectedDuplicateInvoice
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.showDuplicateResolution = false;
          if (window.successModal) {
            window.successModal.show('Success', data.message);
          }
          await this.checkExistingInvoices();
          await this.fetchBillingHistory();
          await this.determineNextBillingMonth();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          this.formErrors = [data.message];
        }
      } catch (error) {
        console.error('Error resolving duplicates:', error);
        this.formErrors = ['Failed to resolve duplicate invoices'];
      } finally {
        this.resolvingDuplicates = false;
      }
    },
    
    addItem() {
      this.form.items.push({ 
        description: '', 
        item_type: 'other', 
        amount: '',
        _isSystem: false,
        _isReadOnly: false
      });
    },
    
    removeItem(index) {
      const item = this.form.items[index];
      if (item && item._isSystem) {
        this.addedUtilities = this.addedUtilities.filter(u => u.type !== item.item_type);
      }
      if (this.form.items.length > 0) {
        this.form.items.splice(index, 1);
      }
    },
    
    getTotalAmount() {
      return this.form.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    },
    
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount || 0);
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.form.billing_month) {
        this.formErrors.push('Please select billing month');
      }
      
      if (this.form.items.length === 0) {
        this.formErrors.push('Please add at least one invoice item');
      }
      
      const existingForMonth = this.existingInvoices.some(inv => 
        inv.billing_month && inv.billing_month.slice(0, 7) === this.form.billing_month && inv.invoice_type === 'monthly'
      );
      
      if (existingForMonth) {
        this.formErrors.push(`An invoice already exists for ${this.formatMonthDisplay(this.form.billing_month)}. Please edit the existing invoice.`);
      }
      
      if (this.nextBillingMonth && this.form.billing_month !== this.nextBillingMonth && !this.forceGenerateMode && this.missingMonthsFiltered.length === 0 && !this.allInvoicesGenerated) {
        this.formErrors.push(`Please generate invoice for the next sequential month: ${this.nextBillingMonthFormatted}. Use "Generate Anyway" to override.`);
      }
      
      if (this.forceGenerateMode && (!this.forceGenerateReason || this.forceGenerateReason.trim().length < 5)) {
        this.formErrors.push('Please provide a reason for force generating this invoice (minimum 5 characters)');
      }
      
      this.form.items.forEach((item, index) => {
        if (!item._isSystem) {
          if (!item.description || !item.description.trim()) {
            this.formErrors.push(`Item ${index + 1}: Description is required`);
          }
          if (!item.amount || parseFloat(item.amount) <= 0) {
            this.formErrors.push(`Item ${index + 1}: Please enter a valid amount`);
          }
        }
      });
      
      return this.formErrors.length === 0;
    },
    
    async submitForm() {
      if (!this.validateForm()) {
        const modalContent = document.querySelector('.overflow-y-auto');
        if (modalContent) {
          modalContent.scrollTop = 0;
        }
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(`/tenancies/${this.tenancyId}/invoices`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            billing_month: this.form.billing_month,
            items: this.form.items,
            force_generate: this.forceGenerateMode,
            force_reason: this.forceGenerateReason
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.closeModal();
          
          if (window.successModal) {
            const message = this.forceGenerateMode 
              ? `Invoice force generated for ${this.formatMonthDisplay(this.form.billing_month)}` 
              : data.message || 'Invoice created successfully';
            window.successModal.show('Success!', message);
          } else {
            alert('Invoice created successfully!');
          }
          
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          if (data.errors) {
            this.formErrors = Object.values(data.errors).flat();
          } else {
            this.formErrors = [data.message || 'Failed to create invoice'];
          }
          
          const modalContent = document.querySelector('.overflow-y-auto');
          if (modalContent) {
            modalContent.scrollTop = 0;
          }
        }
      } catch (error) {
        console.error('Error:', error);
        this.formErrors = ['An error occurred. Please try again.'];
      } finally {
        this.loading = false;
      }
    },
    
    closeModal() {
      this.isOpen = false;
      this.tenancyId = null;
      this.tenancyData = null;
      this.formErrors = [];
      this.loading = false;
      this.showWaterSection = false;
      this.existingInvoices = [];
      this.nextBillingMonth = null;
      this.nextBillingMonthFormatted = null;
      this.forceGenerateMode = false;
      this.forceGenerateReason = '';
      this.monthWarning = null;
      this.missingMonthsFiltered = [];
      this.selectedMissingMonths = [];
      this.showMissingInvoicesModal = false;
      this.showDuplicateResolution = false;
      this.waterReadings = {};
      this.allInvoicesGenerated = false;
      this.addedUtilities = [];
      this.form.items = [];
      document.body.style.overflow = '';
    },
    
    resetForm() {
      this.form = {
        billing_month: new Date().toISOString().slice(0, 7),
        items: []
      };
      this.formErrors = [];
      this.loading = false;
      this.currentReadingInput = 0;
      this.consumption = 0;
      this.calculatedWaterCharge = 0;
      this.addedUtilities = [];
    }
  }));
});
</script>
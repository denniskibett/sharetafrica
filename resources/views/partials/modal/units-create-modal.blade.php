<!-- CREATE UNIT SLIDEOVER MODAL with Mass Add Support, Utility Charges, and Advanced Batch Generation -->
<div x-data="unitCreateModal" x-init="init()">
  <!-- Backdrop with 50% opacity and frost effect -->
  <template x-if="isOpen">
    <div 
      @click="closeModal()"
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
       class="fixed top-0 right-0 h-full w-full sm:max-w-xl md:max-w-2xl lg:max-w-4xl bg-white dark:bg-gray-900 shadow-2xl z-99999 overflow-y-auto">
    <div class="p-6 lg:p-8">
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C6.65237 16.9318 6.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <form @submit.prevent="submitForm()" x-ref="createUnitForm" novalidate>
        <div class="flex items-center justify-between mb-6">
          <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">
            Add New Unit(s)
          </h4>
          
          <!-- Mode Toggle -->
          <div class="flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
            <button
              type="button"
              @click="mode = 'single'"
              :class="mode === 'single' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400'"
              class="rounded-md px-4 py-2 text-sm font-medium transition-all"
            >
              Single Unit
            </button>
            <button
              type="button"
              @click="mode = 'mass'"
              :class="mode === 'mass' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400'"
              class="rounded-md px-4 py-2 text-sm font-medium transition-all"
            >
              Mass Add
            </button>
            <button
              type="button"
              @click="mode = 'batch'"
              :class="mode === 'batch' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400'"
              class="rounded-md px-4 py-2 text-sm font-medium transition-all"
            >
              Batch Generate
            </button>
          </div>
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

        <!-- ==================== SINGLE UNIT MODE ==================== -->
        <div x-show="mode === 'single'" x-cloak>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <!-- Estate -->
            <div x-show="!preSelectedEstateId">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate *</label>
              <select 
                x-model="formData.estate_id"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              >
                <option value="">Select Estate</option>
                @foreach($estates ?? [] as $estate)
                  <option value="{{ $estate->id }}">{{ $estate->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Unit Number -->
            <div :class="preSelectedEstateId ? 'md:col-span-2' : ''">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Number *</label>
              <input 
                x-model="formData.unit_number"
                type="text"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                placeholder="e.g., BLK-A-101, HAR-001"
              />
            </div>

            <!-- Unit Type -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Type *</label>
              <select x-model="formData.unit_type" @change="updateBedsFromUnitType" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Select Type</option>
                <option value="Studio">Studio (0 Bed)</option>
                <option value="Bedsitter">Bedsitter (0 Bed)</option>
                <option value="One Bedroom">One Bedroom (1 Bed)</option>
                <option value="Two Bedroom">Two Bedroom (2 Beds)</option>
                <option value="Three Bedroom">Three Bedroom (3 Beds)</option>
                <option value="Apartment">Apartment</option>
                <option value="House">House</option>
                <option value="Office">Office</option>
                <option value="Shop">Shop</option>
              </select>
            </div>

            <!-- Property Category -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Property Category</label>
              <select x-model="formData.property_category" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="showhouse">Showhouse</option>
                <option value="office">Office</option>
                <option value="retail">Retail</option>
                <option value="industrial">Industrial</option>
              </select>
            </div>

            <!-- Rent Amount -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Monthly Rent *</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.rent_amount" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <!-- Utility Charges Row -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Water Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.water_charge" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Service Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.service_charge" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Garbage Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.garbage_charge" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.security_charge" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <!-- Additional Fields -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ownership Type</label>
              <select x-model="formData.ownership_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="tenant">Tenant</option>
                <option value="homeowner">Homeowner</option>
                <option value="company">Company</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Furnishing Status</label>
              <select x-model="formData.furnishing_status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="unfurnished">Unfurnished</option>
                <option value="furnished">Furnished</option>
                <option value="semi_furnished">Semi-Furnished</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Stay Type</label>
              <select x-model="formData.stay_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="long_stay">Long Stay</option>
                <option value="short_stay">Short Stay</option>
                <option value="bnb">BNB</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Water Billing Type</label>
              <select x-model="formData.water_billing_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="flat">Flat Rate</option>
                <option value="consumption">Consumption Based</option>
              </select>
            </div>

            <!-- BNB Specific Fields -->
            <template x-if="formData.stay_type === 'bnb'">
              <>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nightly Rate (KES)</label>
                  <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 dark:text-gray-400">KES</span>
                    </div>
                    <input x-model="formData.bnb_nightly_rate" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cleaning Fee (KES)</label>
                  <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 dark:text-gray-400">KES</span>
                    </div>
                    <input x-model="formData.bnb_cleaning_fee" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Min Stay Days</label>
                  <input x-model="formData.min_stay_days" type="number" min="1" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="1"/>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Max Stay Days</label>
                  <input x-model="formData.max_stay_days" type="number" min="1" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="30"/>
                </div>
              </>
            </template>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Deposit (KES)</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.security_deposit_amount" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Commission Rate (%)</label>
              <input x-model="formData.commission_rate" type="number" step="0.01" min="0" max="100" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
            </div>

            <!-- Status -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
              <select x-model="formData.status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="vacant">Vacant</option>
                <option value="occupied">Occupied</option>
                <option value="available">Available</option>
              </select>
            </div>

            <!-- Water Reading Fields -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Previous Water Reading</label>
              <input x-model="formData.previous_water_reading" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom Water Rate (KES)</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 dark:text-gray-400">KES</span>
                </div>
                <input x-model="formData.custom_water_rate" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00"/>
              </div>
            </div>

            <div class="col-span-1 md:col-span-2">
              <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Monthly Charges Summary</h5>
                <div class="grid grid-cols-2 gap-2 text-sm">
                  <div class="text-gray-600 dark:text-gray-400">Rent:</div>
                  <div class="text-right font-medium text-gray-800 dark:text-gray-200">KES <span x-text="parseFloat(formData.rent_amount || 0).toFixed(2)"></span></div>
                  <div class="text-gray-600 dark:text-gray-400">Water:</div>
                  <div class="text-right font-medium text-gray-800 dark:text-gray-200">KES <span x-text="parseFloat(formData.water_charge || 0).toFixed(2)"></span></div>
                  <div class="text-gray-600 dark:text-gray-400">Service:</div>
                  <div class="text-right font-medium text-gray-800 dark:text-gray-200">KES <span x-text="parseFloat(formData.service_charge || 0).toFixed(2)"></span></div>
                  <div class="text-gray-600 dark:text-gray-400">Garbage:</div>
                  <div class="text-right font-medium text-gray-800 dark:text-gray-200">KES <span x-text="parseFloat(formData.garbage_charge || 0).toFixed(2)"></span></div>
                  <div class="text-gray-600 dark:text-gray-400">Security:</div>
                  <div class="text-right font-medium text-gray-800 dark:text-gray-200">KES <span x-text="parseFloat(formData.security_charge || 0).toFixed(2)"></span></div>
                  <div class="border-t border-gray-300 dark:border-gray-700 mt-2 pt-2 font-semibold text-gray-800 dark:text-gray-200">Total Monthly:</div>
                  <div class="border-t border-gray-300 dark:border-gray-700 mt-2 pt-2 text-right font-semibold text-blue-600 dark:text-blue-400">KES <span x-text="(parseFloat(formData.rent_amount || 0) + parseFloat(formData.water_charge || 0) + parseFloat(formData.service_charge || 0) + parseFloat(formData.garbage_charge || 0) + parseFloat(formData.security_charge || 0)).toFixed(2)"></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== MASS ADD MODE ==================== -->
        <div x-show="mode === 'mass'" x-cloak>
          <div class="mb-6 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
            <p class="text-sm text-blue-700 dark:text-blue-300"><strong>Tip:</strong> Add multiple units manually. Each unit will share the same settings.</p>
          </div>

          <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Units to Add *</label>
              <button type="button" @click="addUnitField()" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">+ Add Another Unit</button>
            </div>
            <div class="space-y-3">
              <template x-for="(unit, index) in units" :key="index">
                <div class="flex items-center gap-3">
                  <div class="flex-1">
                    <input type="text" x-model="unit.unit_number" placeholder="Unit number (e.g., 101, A1, BLK-A-201)" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
                  </div>
                  <button type="button" @click="removeUnitField(index)" :disabled="units.length === 1" class="text-red-600 hover:text-red-800 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>
              </template>
            </div>
          </div>

          <!-- Common Fields for Mass Add -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div x-show="!preSelectedEstateId">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate *</label>
              <select x-model="commonFields.estate_id" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Select Estate</option>
                @foreach($estates ?? [] as $estate)
                  <option value="{{ $estate->id }}">{{ $estate->name }}</option>
                @endforeach
              </select>
            </div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Type *</label><select x-model="commonFields.unit_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="">Select Type</option><option value="Studio">Studio</option><option value="Bedsitter">Bedsitter</option><option value="One Bedroom">One Bedroom</option><option value="Two Bedroom">Two Bedroom</option><option value="Three Bedroom">Three Bedroom</option><option value="Apartment">Apartment</option><option value="House">House</option><option value="Office">Office</option><option value="Shop">Shop</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Property Category</label><select x-model="commonFields.property_category" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="residential">Residential</option><option value="commercial">Commercial</option><option value="showhouse">Showhouse</option><option value="office">Office</option><option value="retail">Retail</option><option value="industrial">Industrial</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Monthly Rent *</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.rent_amount" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Water Charge</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.water_charge" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Service Charge</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.service_charge" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Garbage Charge</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.garbage_charge" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Charge</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.security_charge" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label><select x-model="commonFields.status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="vacant">Vacant</option><option value="occupied">Occupied</option><option value="available">Available</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ownership Type</label><select x-model="commonFields.ownership_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="tenant">Tenant</option><option value="homeowner">Homeowner</option><option value="company">Company</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Furnishing Status</label><select x-model="commonFields.furnishing_status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="unfurnished">Unfurnished</option><option value="furnished">Furnished</option><option value="semi_furnished">Semi-Furnished</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Stay Type</label><select x-model="commonFields.stay_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="long_stay">Long Stay</option><option value="short_stay">Short Stay</option><option value="bnb">BNB</option><option value="mixed">Mixed</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Deposit (KES)</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div><input type="number" x-model="commonFields.security_deposit_amount" placeholder="0.00" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/></div></div>

            <div class="col-span-1 md:col-span-2">
              <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Summary</h5>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  <p>Will create <span x-text="units.length"></span> units</p>
                  <ul class="mt-2 space-y-1">
                    <li>Type: <span x-text="commonFields.unit_type || 'Not selected'" class="font-medium"></span></li>
                    <li>Rent: KES <span x-text="parseFloat(commonFields.rent_amount || 0).toFixed(2)" class="font-medium"></span></li>
                    <li class="pt-2 font-semibold text-gray-800 dark:text-gray-200">Total Monthly: KES <span x-text="(parseFloat(commonFields.rent_amount || 0) + parseFloat(commonFields.water_charge || 0) + parseFloat(commonFields.service_charge || 0) + parseFloat(commonFields.garbage_charge || 0) + parseFloat(commonFields.security_charge || 0)).toFixed(2)"></span></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== BATCH GENERATE MODE ==================== -->
        <div x-show="mode === 'batch'" x-cloak>
          <div class="mb-6 p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
            <p class="text-sm text-green-700 dark:text-green-300">
              <strong>Batch Generator:</strong> Automatically generate units with sequential numbering. Great for apartment blocks, floors, or multi-building estates.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <!-- Estate -->
            <div x-show="!preSelectedEstateId" class="col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate *</label>
              <select x-model="batchConfig.estate_id" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Select Estate</option>
                @foreach($estates ?? [] as $estate)
                  <option value="{{ $estate->id }}">{{ $estate->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Naming Pattern -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Naming Pattern</label>
              <select x-model="batchConfig.namingPattern" @change="generateBatchPreview" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="simple">Simple (1, 2, 3...)</option>
                <option value="prefix">With Prefix (BLK-001, BLK-002...)</option>
                <option value="floor_suffix">Floor + Suffix (1A, 1B, 2A, 2B...)</option>
                <option value="custom">Custom Format</option>
              </select>
            </div>

            <!-- Prefix (shown when prefix pattern selected) -->
            <div x-show="batchConfig.namingPattern === 'prefix'">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Prefix</label>
              <input type="text" x-model="batchConfig.prefix" @input="generateBatchPreview" placeholder="e.g., BLK-A, HAR, TOWER-1" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
            </div>

            <!-- Zero Padding Option for Prefix Mode -->
            <div x-show="batchConfig.namingPattern === 'prefix'">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Zero Padding (Digits)</label>
              <select x-model="batchConfig.prefixPadding" @change="generateBatchPreview" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="auto">Auto (based on max number)</option>
                <option value="1">No padding (1, 2, 3...)</option>
                <option value="2">2 digits (01, 02, 03...)</option>
                <option value="3">3 digits (001, 002, 003...)</option>
                <option value="4">4 digits (0001, 0002...)</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Auto will add leading zeros so all numbers have the same length (e.g., 001, 002... up to 999)</p>
            </div>

            <!-- Suffix (when floor_suffix pattern selected) -->
            <div x-show="batchConfig.namingPattern === 'floor_suffix'">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Suffix Letters</label>
              <div class="flex flex-wrap gap-2">
                <label class="flex items-center"><input type="checkbox" value="A" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> A</label>
                <label class="flex items-center"><input type="checkbox" value="B" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> B</label>
                <label class="flex items-center"><input type="checkbox" value="C" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> C</label>
                <label class="flex items-center"><input type="checkbox" value="D" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> D</label>
                <label class="flex items-center"><input type="checkbox" value="E" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> E</label>
                <label class="flex items-center"><input type="checkbox" value="F" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> F</label>
                <label class="flex items-center"><input type="checkbox" value="G" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> G</label>
                <label class="flex items-center"><input type="checkbox" value="H" x-model="batchConfig.suffixLetters" @change="generateBatchPreview" class="mr-1"> H</label>
              </div>
              <p class="text-xs text-gray-500 mt-1">Units will be named like: 1A, 1B, 2A, 2B, etc.</p>
            </div>

            <!-- Floor Number Zero Padding (for floor_suffix mode) -->
            <div x-show="batchConfig.namingPattern === 'floor_suffix'">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Floor Number Padding</label>
              <select x-model="batchConfig.floorPadding" @change="generateBatchPreview" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="1">No padding (1, 2, 3...)</option>
                <option value="2">2 digits (01, 02, 03...)</option>
                <option value="3">3 digits (001, 002, 003...)</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Applies to floor numbers only (e.g., 01A, 01B, 02A...)</p>
            </div>

            <!-- Custom Format Template -->
            <div x-show="batchConfig.namingPattern === 'custom'" class="col-span-1 md:col-span-2">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom Format Template</label>
              <input type="text" x-model="batchConfig.customTemplate" @input="generateBatchPreview" placeholder="e.g., {prefix}-{floor:02d}{letter} or TOWER-{unit:03d}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              <p class="text-xs text-gray-500 mt-1">Use {prefix}, {floor}, {unit}, {letter}. Add :02d for zero-padding (e.g., {floor:02d} = 01, {unit:03d} = 001).</p>
            </div>

            <!-- Custom Prefix Field (for custom template) -->
            <div x-show="batchConfig.namingPattern === 'custom'">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom Prefix Value</label>
              <input type="text" x-model="batchConfig.prefix" @input="generateBatchPreview" placeholder="e.g., TOWER, BLK-A" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
            </div>

            <!-- Number of Floors -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Number of Floors</label>
              <input type="number" x-model="batchConfig.numberOfFloors" @input="generateBatchPreview" min="1" max="50" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
            </div>

            <!-- Units Per Floor -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Units Per Floor</label>
              <input type="number" x-model="batchConfig.unitsPerFloor" @input="generateBatchPreview" min="1" max="50" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
            </div>

            <!-- Starting Unit Number -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Starting Unit Number</label>
              <input type="number" x-model="batchConfig.startingUnit" @input="generateBatchPreview" min="1" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
            </div>

            <!-- Unit Type (with bed mapping) -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Type *</label>
              <select x-model="batchConfig.unit_type" @change="updateBatchBeds" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Select Type</option>
                <option value="Studio">Studio (0 Bed)</option>
                <option value="Bedsitter">Bedsitter (0 Bed)</option>
                <option value="One Bedroom">One Bedroom (1 Bed)</option>
                <option value="Two Bedroom">Two Bedroom (2 Beds)</option>
                <option value="Three Bedroom">Three Bedroom (3 Beds)</option>
                <option value="Apartment">Apartment</option>
                <option value="House">House</option>
                <option value="Office">Office</option>
                <option value="Shop">Shop</option>
              </select>
            </div>

            <!-- Bedrooms -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bedrooms</label>
              <input type="number" x-model="batchConfig.bedrooms" readonly class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-400" readonly/>
              <p class="text-xs text-gray-500 mt-1">Auto-detected from unit type</p>
            </div>

            <!-- Rent and Charges -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Monthly Rent *</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div>
                <input type="number" x-model="batchConfig.rent_amount" @input="generateBatchPreview" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Water Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div>
                <input type="number" x-model="batchConfig.water_charge" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Service Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div>
                <input type="number" x-model="batchConfig.service_charge" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Garbage Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div>
                <input type="number" x-model="batchConfig.garbage_charge" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Charge</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">KES</span></div>
                <input type="number" x-model="batchConfig.security_charge" min="0" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
              </div>
            </div>

            <!-- Other Fields -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
              <select x-model="batchConfig.status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="vacant">Vacant</option>
                <option value="occupied">Occupied</option>
                <option value="available">Available</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Property Category</label>
              <select x-model="batchConfig.property_category" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="showhouse">Showhouse</option>
                <option value="office">Office</option>
                <option value="retail">Retail</option>
                <option value="industrial">Industrial</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ownership Type</label>
              <select x-model="batchConfig.ownership_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="tenant">Tenant</option>
                <option value="homeowner">Homeowner</option>
                <option value="company">Company</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Furnishing Status</label>
              <select x-model="batchConfig.furnishing_status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="unfurnished">Unfurnished</option>
                <option value="furnished">Furnished</option>
                <option value="semi_furnished">Semi-Furnished</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Stay Type</label>
              <select x-model="batchConfig.stay_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="long_stay">Long Stay</option>
                <option value="short_stay">Short Stay</option>
                <option value="bnb">BNB</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>

            <!-- Preview Section -->
            <div class="col-span-1 md:col-span-2 mt-4">
              <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Preview: Units to be Created</h5>
                <div class="max-h-40 overflow-y-auto">
                  <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2 text-sm">
                    <template x-for="(unitName, idx) in batchPreview" :key="idx">
                      <div class="bg-white dark:bg-gray-700 rounded px-2 py-1 text-center text-xs">
                        <span x-text="unitName"></span>
                      </div>
                    </template>
                  </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">
                  Total units to create: <span class="font-bold" x-text="batchPreview.length"></span>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  Total Monthly per unit: KES <span x-text="(parseFloat(batchConfig.rent_amount || 0) + parseFloat(batchConfig.water_charge || 0) + parseFloat(batchConfig.service_charge || 0) + parseFloat(batchConfig.garbage_charge || 0) + parseFloat(batchConfig.security_charge || 0)).toFixed(2)"></span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end w-full gap-3 mt-8">
          <button @click="closeModal()" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">Cancel</button>
          <button type="submit" :disabled="loading" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
            <span x-show="!loading">
              <span x-show="mode === 'single'">Save Unit</span>
              <span x-show="mode === 'mass'">Create <span x-text="units.length"></span> Units</span>
              <span x-show="mode === 'batch'">Generate <span x-text="batchPreview.length"></span> Units</span>
            </span>
            <span x-show="loading">Processing...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('unitCreateModal', () => ({
    isOpen: false,
    mode: 'single',
    preSelectedEstateId: null,
    
    formData: { /* ... same as before ... */ },
    units: [{ unit_number: '' }],
    commonFields: { /* ... same as before ... */ },
    
    // Batch generation config
    batchConfig: {
      estate_id: '',
      namingPattern: 'simple',
      prefix: '',
      prefixPadding: 'auto',
      floorPadding: '2', // Changed default to 2 for 01, 02 format
      suffixLetters: ['A', 'B', 'C', 'D'],
      customTemplate: '',
      numberOfFloors: 1,
      unitsPerFloor: 4,
      startingUnit: 1,
      unit_type: '',
      bedrooms: 0,
      rent_amount: '',
      water_charge: '',
      service_charge: '',
      garbage_charge: '',
      security_charge: '',
      status: 'vacant',
      property_category: 'residential',
      ownership_type: 'tenant',
      furnishing_status: 'unfurnished',
      stay_type: 'long_stay',
      security_deposit_amount: ''
    },
    
    formErrors: [],
    loading: false,
    batchPreview: [],
    
    init() {
      window.unitCreateModal = this;
    },
    
    // Helper: Format number with ALWAYS 2 digits for numbers 1-9 (01, 02, etc.)
    formatNumber(num, padding, maxNumber = null) {
      // For auto padding with max number
      if (padding === 'auto' && maxNumber !== null) {
        const maxDigits = String(maxNumber).length;
        // Ensure at least 2 digits if max number < 10
        const minDigits = maxNumber < 10 ? 2 : maxDigits;
        return String(num).padStart(minDigits, '0');
      }
      
      let paddingLength = parseInt(padding);
      
      // Special case: If padding is '1' but number < 10, still use 2 digits
      if (paddingLength === 1 && num < 10) {
        return String(num).padStart(2, '0');
      }
      
      // Default to at least 2 digits for single-digit numbers
      if (isNaN(paddingLength) || paddingLength <= 1) {
        return num < 10 ? String(num).padStart(2, '0') : String(num);
      }
      
      return String(num).padStart(paddingLength, '0');
    },
    
    // Helper: Get max unit number for auto padding
    getMaxUnitNumber() {
      const totalUnits = this.batchConfig.numberOfFloors * this.batchConfig.unitsPerFloor;
      return this.batchConfig.startingUnit + totalUnits - 1;
    },
    
    // Generate unit names for batch preview
    generateBatchPreview() {
      const config = this.batchConfig;
      const units = [];
      const totalUnits = config.numberOfFloors * config.unitsPerFloor;
      const maxUnitNumber = this.getMaxUnitNumber();
      
      if (config.namingPattern === 'simple') {
        for (let i = 0; i < totalUnits; i++) {
          const num = config.startingUnit + i;
          // Always ensure 2 digits for numbers 1-9
          units.push(num < 10 ? `${num}`.padStart(2, '0') : `${num}`);
        }
      } 
      else if (config.namingPattern === 'prefix') {
        const prefix = config.prefix || 'UNIT';
        for (let i = 0; i < totalUnits; i++) {
          const num = config.startingUnit + i;
          const formattedNum = this.formatNumber(num, config.prefixPadding, maxUnitNumber);
          units.push(`${prefix}${formattedNum}`);
        }
      }
      else if (config.namingPattern === 'floor_suffix') {
        const letters = config.suffixLetters.length > 0 ? config.suffixLetters : ['A', 'B', 'C', 'D'];
        let counter = config.startingUnit;
        for (let floor = 1; floor <= config.numberOfFloors; floor++) {
          // Floor numbers always formatted with at least 2 digits (01, 02, etc.)
          const formattedFloor = this.formatNumber(floor, config.floorPadding, config.numberOfFloors);
          for (let unitIdx = 0; unitIdx < config.unitsPerFloor; unitIdx++) {
            const letter = letters[unitIdx % letters.length];
            units.push(`${formattedFloor}${letter}`);
            counter++;
          }
        }
      }
      else if (config.namingPattern === 'custom') {
        let template = config.customTemplate || '{prefix}{unit:02d}'; // Changed default to 02d
        let counter = config.startingUnit;
        for (let floor = 1; floor <= config.numberOfFloors; floor++) {
          for (let unitNum = 1; unitNum <= config.unitsPerFloor; unitNum++) {
            let name = template;
            
            // Replace {prefix}
            name = name.replace(/\{prefix\}/g, config.prefix || '');
            
            // Replace {floor} with optional padding (always at least 2 digits)
            name = name.replace(/\{floor(?::(\d+)([a-z]+))?\}/g, (match, padding) => {
              if (padding) {
                return this.formatNumber(floor, padding, config.numberOfFloors);
              }
              // Default: ensure at least 2 digits
              return floor < 10 ? String(floor).padStart(2, '0') : floor;
            });
            
            // Replace {unit} with optional padding (always at least 2 digits)
            name = name.replace(/\{unit(?::(\d+)([a-z]+))?\}/g, (match, padding) => {
              let num = counter;
              if (padding) {
                return this.formatNumber(num, padding, maxUnitNumber);
              }
              // Default: ensure at least 2 digits
              return num < 10 ? String(num).padStart(2, '0') : num;
            });
            
            // Replace {letter}
            const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            name = name.replace(/\{letter(?::(\d+))?\}/g, (match) => {
              const letterIndex = (counter - 1) % letters.length;
              return letters[letterIndex];
            });
            
            units.push(name);
            counter++;
          }
        }
      }
      
      this.batchPreview = units;
    },
    
    updateBatchBeds() {
      const typeMap = {
        'Studio': 0,
        'Bedsitter': 0,
        'One Bedroom': 1,
        'Two Bedroom': 2,
        'Three Bedroom': 3,
        'Apartment': 2,
        'House': 3,
        'Office': 0,
        'Shop': 0
      };
      this.batchConfig.bedrooms = typeMap[this.batchConfig.unit_type] || 0;
      this.generateBatchPreview();
    },
    
    // Get the actual units to submit in batch mode
    getBatchUnits() {
      const config = this.batchConfig;
      const units = [];
      const totalUnits = config.numberOfFloors * config.unitsPerFloor;
      const maxUnitNumber = this.getMaxUnitNumber();
      
      if (config.namingPattern === 'simple') {
        for (let i = 0; i < totalUnits; i++) {
          const num = config.startingUnit + i;
          // Always ensure 2 digits for numbers 1-9
          units.push({ unit_number: num < 10 ? `${num}`.padStart(2, '0') : `${num}` });
        }
      } 
      else if (config.namingPattern === 'prefix') {
        const prefix = config.prefix || 'UNIT';
        for (let i = 0; i < totalUnits; i++) {
          const num = config.startingUnit + i;
          const formattedNum = this.formatNumber(num, config.prefixPadding, maxUnitNumber);
          units.push({ unit_number: `${prefix}${formattedNum}` });
        }
      }
      else if (config.namingPattern === 'floor_suffix') {
        const letters = config.suffixLetters.length > 0 ? config.suffixLetters : ['A', 'B', 'C', 'D'];
        for (let floor = 1; floor <= config.numberOfFloors; floor++) {
          // Floor numbers always formatted with at least 2 digits
          const formattedFloor = this.formatNumber(floor, config.floorPadding, config.numberOfFloors);
          for (let unitIdx = 0; unitIdx < config.unitsPerFloor; unitIdx++) {
            const letter = letters[unitIdx % letters.length];
            units.push({ unit_number: `${formattedFloor}${letter}` });
          }
        }
      }
      else if (config.namingPattern === 'custom') {
        let template = config.customTemplate || '{prefix}{unit:02d}';
        let counter = config.startingUnit;
        for (let floor = 1; floor <= config.numberOfFloors; floor++) {
          for (let unitNum = 1; unitNum <= config.unitsPerFloor; unitNum++) {
            let name = template;
            
            name = name.replace(/\{prefix\}/g, config.prefix || '');
            name = name.replace(/\{floor(?::(\d+)([a-z]+))?\}/g, (match, padding) => {
              if (padding) return this.formatNumber(floor, padding, config.numberOfFloors);
              // Default: ensure at least 2 digits
              return floor < 10 ? String(floor).padStart(2, '0') : floor;
            });
            name = name.replace(/\{unit(?::(\d+)([a-z]+))?\}/g, (match, padding) => {
              let num = counter;
              if (padding) return this.formatNumber(num, padding, maxUnitNumber);
              // Default: ensure at least 2 digits
              return num < 10 ? String(num).padStart(2, '0') : num;
            });
            
            const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            name = name.replace(/\{letter(?::(\d+))?\}/g, (match) => {
              const letterIndex = (counter - 1) % letters.length;
              return letters[letterIndex];
            });
            
            units.push({ unit_number: name });
            counter++;
          }
        }
      }
      
      return units;
    },
    
    openModal(estateId = null) {
      this.preSelectedEstateId = estateId;
      this.resetForm();
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
      
      if (estateId) {
        this.formData.estate_id = estateId;
        this.commonFields.estate_id = estateId;
        this.batchConfig.estate_id = estateId;
      }
      
      this.generateBatchPreview();
    },
    
    closeModal() { 
      this.isOpen = false;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    resetForm() { 
      this.mode = 'single';
      this.formData = {
        estate_id: this.preSelectedEstateId || '',
        unit_number: '',
        unit_type: '',
        rent_amount: '',
        water_charge: '',
        service_charge: '',
        garbage_charge: '',
        security_charge: '',
        status: 'vacant',
        ownership_type: 'tenant',
        furnishing_status: 'unfurnished',
        stay_type: 'long_stay',
        property_category: 'residential',
        is_active: 1,
        previous_water_reading: '',
        current_water_reading: '',
        last_reading_date: '',
        custom_water_rate: '',
        water_billing_type: 'consumption',
        min_stay_days: '',
        max_stay_days: '',
        bnb_cleaning_fee: '',
        bnb_nightly_rate: '',
        security_deposit_amount: '',
        commission_rate: ''
      };
      this.units = [{ unit_number: '' }];
      this.commonFields = {
        estate_id: this.preSelectedEstateId || '',
        unit_type: '',
        rent_amount: '',
        water_charge: '',
        service_charge: '',
        garbage_charge: '',
        security_charge: '',
        status: 'vacant',
        ownership_type: 'tenant',
        furnishing_status: 'unfurnished',
        stay_type: 'long_stay',
        property_category: 'residential',
        security_deposit_amount: ''
      };
      this.batchConfig = {
        estate_id: this.preSelectedEstateId || '',
        namingPattern: 'simple',
        prefix: '',
        prefixPadding: 'auto',
        floorPadding: '2', // Default to 2-digit floors
        suffixLetters: ['A', 'B', 'C', 'D'],
        customTemplate: '',
        numberOfFloors: 1,
        unitsPerFloor: 4,
        startingUnit: 1,
        unit_type: '',
        bedrooms: 0,
        rent_amount: '',
        water_charge: '',
        service_charge: '',
        garbage_charge: '',
        security_charge: '',
        status: 'vacant',
        property_category: 'residential',
        ownership_type: 'tenant',
        furnishing_status: 'unfurnished',
        stay_type: 'long_stay',
        security_deposit_amount: ''
      };
      this.formErrors = [];
      this.generateBatchPreview();
    },
    
    addUnitField() {
      this.units.push({ unit_number: '' });
    },
    
    removeUnitField(index) {
      if (this.units.length > 1) {
        this.units.splice(index, 1);
      }
    },
    
    validateSingleForm() {
      const errors = [];
      if (!this.preSelectedEstateId && !this.formData.estate_id) errors.push('Please select an estate');
      if (!this.formData.unit_number?.trim()) errors.push('Please enter a unit number');
      if (!this.formData.unit_type) errors.push('Please select a unit type');
      if (!this.formData.rent_amount || parseFloat(this.formData.rent_amount) <= 0) errors.push('Please enter a valid rent amount greater than 0');
      return errors;
    },
    
    validateMassForm() {
      const errors = [];
      if (!this.preSelectedEstateId && !this.commonFields.estate_id) errors.push('Please select an estate');
      if (this.units.some(u => !u.unit_number?.trim())) errors.push('Please fill in all unit numbers');
      if (!this.commonFields.unit_type) errors.push('Please select a unit type');
      if (!this.commonFields.rent_amount || parseFloat(this.commonFields.rent_amount) <= 0) errors.push('Please enter a valid rent amount greater than 0');
      return errors;
    },
    
    validateBatchForm() {
      const errors = [];
      if (!this.preSelectedEstateId && !this.batchConfig.estate_id) errors.push('Please select an estate');
      if (!this.batchConfig.unit_type) errors.push('Please select a unit type');
      if (!this.batchConfig.rent_amount || parseFloat(this.batchConfig.rent_amount) <= 0) errors.push('Please enter a valid rent amount');
      if (this.batchConfig.numberOfFloors < 1) errors.push('Number of floors must be at least 1');
      if (this.batchConfig.unitsPerFloor < 1) errors.push('Units per floor must be at least 1');
      if (this.batchConfig.namingPattern === 'custom' && !this.batchConfig.customTemplate.trim()) errors.push('Please provide a custom format template');
      if (this.batchConfig.namingPattern === 'prefix' && !this.batchConfig.prefix.trim()) errors.push('Please provide a prefix');
      return errors;
    },
    
    async submitForm() {
      this.loading = true;
      this.formErrors = [];
      
      try {
        let unitsToSubmit = [];
        let commonData = {};
        
        if (this.mode === 'single') {
          const errors = this.validateSingleForm();
          if (errors.length > 0) {
            this.formErrors = errors;
            this.loading = false;
            document.querySelector('.overflow-y-auto')?.scrollTo(0, 0);
            return;
          }
          
          const response = await fetch('/units', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify(this.formData)
          });
          
          const data = await response.json();
          
          if (response.ok) {
            this.closeModal();
            if (window.successModal) {
              window.successModal.show('Unit Created', `Unit ${this.formData.unit_number} has been created successfully.`);
            } else {
              alert(`Success: Unit ${this.formData.unit_number} created successfully.`);
            }
            setTimeout(() => window.location.reload(), 1500);
          } else {
            if (data.errors) {
              this.formErrors = Object.values(data.errors).flat();
            } else {
              this.formErrors = [data.message || 'Failed to create unit'];
            }
            document.querySelector('.overflow-y-auto')?.scrollTo(0, 0);
          }
          this.loading = false;
          return;
        }
        
        else if (this.mode === 'mass') {
          const errors = this.validateMassForm();
          if (errors.length > 0) {
            this.formErrors = errors;
            this.loading = false;
            document.querySelector('.overflow-y-auto')?.scrollTo(0, 0);
            return;
          }
          
          unitsToSubmit = this.units;
          commonData = { ...this.commonFields };
        }
        
        else if (this.mode === 'batch') {
          const errors = this.validateBatchForm();
          if (errors.length > 0) {
            this.formErrors = errors;
            this.loading = false;
            document.querySelector('.overflow-y-auto')?.scrollTo(0, 0);
            return;
          }
          
          unitsToSubmit = this.getBatchUnits();
          commonData = {
            estate_id: this.batchConfig.estate_id,
            unit_type: this.batchConfig.unit_type,
            rent_amount: this.batchConfig.rent_amount,
            water_charge: this.batchConfig.water_charge,
            service_charge: this.batchConfig.service_charge,
            garbage_charge: this.batchConfig.garbage_charge,
            security_charge: this.batchConfig.security_charge,
            status: this.batchConfig.status,
            property_category: this.batchConfig.property_category,
            ownership_type: this.batchConfig.ownership_type,
            furnishing_status: this.batchConfig.furnishing_status,
            stay_type: this.batchConfig.stay_type,
            security_deposit_amount: this.batchConfig.security_deposit_amount
          };
        }
        
        // Submit multiple units (mass or batch)
        let created = 0;
        let failed = [];
        let failedDetails = [];
        
        for (let unit of unitsToSubmit) {
          try {
            const response = await fetch('/units', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                estate_id: commonData.estate_id,
                unit_number: unit.unit_number.trim(),
                unit_type: commonData.unit_type,
                rent_amount: commonData.rent_amount,
                water_charge: commonData.water_charge || 0,
                service_charge: commonData.service_charge || 0,
                garbage_charge: commonData.garbage_charge || 0,
                security_charge: commonData.security_charge || 0,
                status: commonData.status || 'vacant',
                ownership_type: commonData.ownership_type || 'tenant',
                furnishing_status: commonData.furnishing_status || 'unfurnished',
                stay_type: commonData.stay_type || 'long_stay',
                property_category: commonData.property_category || 'residential',
                security_deposit_amount: commonData.security_deposit_amount || 0
              })
            });
            
            if (response.ok) {
              created++;
            } else {
              const data = await response.json();
              failed.push(unit.unit_number);
              failedDetails.push(`${unit.unit_number}: ${data.message || 'Unknown error'}`);
            }
          } catch (error) {
            failed.push(unit.unit_number);
            failedDetails.push(`${unit.unit_number}: ${error.message}`);
          }
        }
        
        this.closeModal();
        
        if (created > 0) {
          let message = `Successfully created ${created} unit(s).`;
          if (failed.length > 0) message += ` Failed to create ${failed.length} unit(s).`;
          
          if (window.successModal) {
            window.successModal.show('Units Created', message);
          } else {
            alert(`Success: ${message}`);
          }
          
          if (failedDetails.length > 0 && window.errorModal) {
            setTimeout(() => {
              window.errorModal.show('Partial Success', `Some units failed to create:`, failedDetails);
            }, 2000);
          }
        } else if (failedDetails.length > 0) {
          if (window.errorModal) {
            window.errorModal.show('Error', 'Failed to create units', failedDetails);
          } else {
            alert(`Error: Failed to create units\n\n${failedDetails.join('\n')}`);
          }
        }
        
        if (created > 0) {
          setTimeout(() => window.location.reload(), 1500);
        }
        
        this.loading = false;
        
      } catch (error) {
        console.error('Error:', error);
        if (window.errorModal) {
          window.errorModal.show('Error', 'An unexpected error occurred. Please try again.');
        } else {
          this.formErrors = ['An unexpected error occurred. Please try again.'];
        }
        this.loading = false;
      }
    }
  }));
});
</script>
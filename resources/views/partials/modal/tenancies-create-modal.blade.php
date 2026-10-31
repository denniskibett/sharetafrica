<!-- CREATE/EDIT TENANCY SLIDEOVER MODAL -->
<div x-data="tenancyCreateModal" x-init="init()">
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
      class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
      style="width: 42rem; max-width: calc(100% - 2rem);">
    <div class="p-6 lg:p-10">
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <form @submit.prevent="submitForm">
        @csrf
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="isEditMode ? 'Edit Tenancy' : 'Add New Tenancy'"></h4>

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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <!-- Tenant Selection -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Tenant *
            </label>
            <div class="space-y-3">
              <select
                x-model="form.tenant_selection"
                @change="onTenantSelectionChange"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              >
                <option value="">Select Tenant</option>
                <option value="existing">Existing Tenant</option>
                <option value="new">Create New Tenant</option>
              </select>
              
              <!-- Existing Tenant Selection -->
              <div x-show="form.tenant_selection === 'existing'" x-cloak>
                <select
                  x-model="form.tenant_id"
                  @change="loadTenantSummary()"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
                  <option value="">Select Existing Tenant</option>
                  @foreach($availableUsers ?? [] as $user)
                    <option value="{{ $user['tenant_id'] }}" data-balance="{{ $user['outstanding_balance'] ?? 0 }}" data-paid="{{ $user['total_paid'] ?? 0 }}" data-active="{{ $user['active_tenancies'] ?? 0 }}">
                      {{ $user['name'] }} ({{ $user['phone'] ?? 'No Phone' }})
                      @if(isset($user['has_ended_tenancy']) && $user['has_ended_tenancy'])
                        - Previous Tenant
                      @endif
                      @if(($user['outstanding_balance'] ?? 0) > 0)
                        - Balance: KES {{ number_format($user['outstanding_balance'], 2) }}
                      @endif
                    </option>
                  @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Showing tenants who don't have an active tenancy
                </p>
              </div>

              <!-- New Tenant Fields -->
              <div x-show="form.tenant_selection === 'new'" class="space-y-3" x-cloak>
                <input
                  type="text"
                  x-model="form.new_tenant_name"
                  placeholder="Tenant Full Name *"
                  :required="form.tenant_selection === 'new'"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
                <input
                  type="text"
                  x-model="form.new_tenant_phone"
                  placeholder="Phone Number *"
                  :required="form.tenant_selection === 'new'"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
                <input
                  type="email"
                  x-model="form.new_tenant_email"
                  placeholder="Email (Optional)"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
              </div>
            </div>
          </div>

          <!-- Tenant Financial Summary (for existing tenants) -->
          <div x-show="form.tenant_selection === 'existing' && form.tenant_id && tenantSummary" class="col-span-2" x-cloak>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
              <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tenant Financial Summary</h5>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                  <span class="text-gray-500 dark:text-gray-400">Outstanding Balance:</span>
                  <p class="font-semibold" :class="(tenantSummary?.outstanding_balance || 0) > 0 ? 'text-red-600' : 'text-green-600'">
                    KES <span x-text="formatNumber(tenantSummary?.outstanding_balance || 0)"></span>
                  </p>
                </div>
                <div>
                  <span class="text-gray-500 dark:text-gray-400">Total Paid:</span>
                  <p class="font-semibold text-green-600">KES <span x-text="formatNumber(tenantSummary?.total_paid || 0)"></span></p>
                </div>
                <div>
                  <span class="text-gray-500 dark:text-gray-400">Total Invoiced:</span>
                  <p class="font-semibold text-blue-600">KES <span x-text="formatNumber(tenantSummary?.total_invoiced || 0)"></span></p>
                </div>
                <div>
                  <span class="text-gray-500 dark:text-gray-400">Active Tenancies:</span>
                  <p class="font-semibold text-gray-800 dark:text-white/90" x-text="tenantSummary?.active_tenancies || 0"></p>
                </div>
              </div>
              <div x-show="tenantSummary?.recent_invoices && tenantSummary.recent_invoices.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recent Invoices</p>
                <div class="space-y-1">
                  <template x-for="inv in (tenantSummary?.recent_invoices || [])" :key="inv.id">
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-600 dark:text-gray-400">#<span x-text="inv.id"></span></span>
                      <span class="text-gray-800 dark:text-white/90">KES <span x-text="formatNumber(inv.amount)"></span></span>
                      <span class="text-gray-500" x-text="inv.status"></span>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- Unit Selection -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Unit *
            </label>
            <select
              x-model="form.unit_id"
              @change="onUnitChange"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">Select Unit</option>
              @foreach($vacantUnits ?? [] as $unit)
                <option value="{{ $unit['id'] }}" data-rent="{{ $unit['rent_amount'] ?? 0 }}" data-water="{{ $unit['water_charge'] ?? 0 }}" data-service="{{ $unit['service_charge'] ?? 0 }}" data-garbage="{{ $unit['garbage_charge'] ?? 0 }}" data-security="{{ $unit['security_charge'] ?? 0 }}" data-maintenance="{{ $unit['maintenance_count'] ?? 0 }}">
                  {{ $unit['unit_number'] }} - {{ $unit['estate_name'] ?? 'No Estate' }} ({{ $unit['unit_type'] }})
                </option>
              @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Only vacant units are shown
            </p>
          </div>

          <!-- Unit Details Summary -->
          <div x-show="form.unit_id && selectedUnit" class="col-span-2" x-cloak>
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
              <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Unit Details</h5>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Rent:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.rent_amount || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Water:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.water_charge || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Service:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.service_charge || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Garbage:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.garbage_charge || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Security:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.security_charge || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Total Monthly:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300">KES <span x-text="formatNumber(selectedUnit?.total_monthly_payment || 0)"></span></span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-400">Open Maintenance:</span>
                  <span class="font-semibold text-blue-800 dark:text-blue-300" x-text="selectedUnit?.maintenance_count || 0"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Move-in Date -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Move-in Date *
            </label>
            <input
              type="date"
              x-model="form.move_in_date"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>

          <!-- Move-out Date (for edit) -->
          <div class="col-span-1" x-show="isEditMode">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Move-out Date
            </label>
            <input
              type="date"
              x-model="form.move_out_date"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>

          <!-- Status (for edit) -->
          <div class="col-span-1" x-show="isEditMode">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Status
            </label>
            <select
              x-model="form.status"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="active">Active</option>
              <option value="ended">Ended</option>
            </select>
          </div>

          <!-- Deposit Amount -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Security Deposit (KES)
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">KES</span>
              <input
                type="number"
                x-model="form.deposit_amount"
                step="100"
                min="0"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-12 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              />
            </div>
            <p class="mt-1 text-xs text-gray-500">Default: 1 month rent (KES <span x-text="formatNumber(form.rent_amount)"></span>)</p>
          </div>

          <!-- Lease Agreement -->
          <div class="col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-400">
                Lease Agreement
              </label>
              <button 
                type="button" 
                @click="generateLeaseAgreement()" 
                :disabled="!form.unit_id || !form.tenant_name"
                class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                📄 <span x-text="leaseAgreementGenerated ? 'View Agreement' : 'Generate Agreement'"></span>
              </button>
            </div>
            <div x-show="leaseAgreementGenerated" class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <p class="text-sm text-green-700 dark:text-green-300">
                ✅ Lease agreement generated. <a href="#" @click.prevent="viewLeaseAgreement()" class="underline font-medium">View Document</a>
              </p>
            </div>
            <div x-show="!leaseAgreementGenerated && form.unit_id" class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
              <p class="text-sm text-yellow-700 dark:text-yellow-300">
                ⚠️ Click "Generate Agreement" to create the lease document.
              </p>
            </div>
          </div>

          <!-- Initial Charges Summary -->
          <div class="col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Initial Charges</h5>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-sm">Security Deposit</span>
                  <p class="text-xs text-gray-500">Refundable at end of tenancy</p>
                </div>
                <span class="font-semibold text-blue-600">KES <span x-text="formatNumber(form.deposit_amount || 0)"></span></span>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-sm">First Month Rent</span>
                  <p class="text-xs text-gray-500">Due on move-in</p>
                </div>
                <span class="font-semibold text-green-600">KES <span x-text="formatNumber(form.rent_amount || 0)"></span></span>
              </div>
              <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Total Initial Payment</span>
                <span class="font-bold text-blue-700 dark:text-blue-400">KES <span x-text="formatNumber(totalInitialCharges)"></span></span>
              </div>
            </div>
          </div>

          <!-- Generate Invoice Option -->
          <div class="col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-2">Generate Invoice:</label>
            <div class="flex flex-wrap gap-4">
              <label class="flex items-center gap-2">
                <input type="radio" x-model="form.generate_invoice" value="yes" class="form-radio text-brand-500">
                <span class="text-sm">Yes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" x-model="form.generate_invoice" value="draft" class="form-radio text-yellow-500">
                <span class="text-sm">Save as Draft</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" x-model="form.generate_invoice" value="no" class="form-radio text-gray-500">
                <span class="text-sm">No</span>
              </label>
            </div>
          </div>

          <!-- Notes -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Notes (Optional)
            </label>
            <textarea
              x-model="form.notes"
              rows="2"
              class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="Any additional notes about this tenancy..."
            ></textarea>
          </div>
        </div>

        <!-- Summary -->
        <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg" x-show="form.unit_id && form.move_in_date && (form.tenant_id || form.new_tenant_name)">
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <strong>Summary:</strong> Creating tenancy for 
            <span x-text="getTenantSummary()"></span>
            in unit <span x-text="getUnitSummary()"></span>
            starting <span x-text="form.move_in_date"></span>
          </p>
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
            :disabled="loading || !isFormValid"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading" x-text="isEditMode ? 'Update Tenancy' : 'Create Tenancy'"></span>
            <span x-show="loading">Processing...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('tenancyCreateModal', () => ({
    isOpen: false,
    isEditMode: false,
    editId: null,
    form: {
      tenant_selection: '',
      tenant_id: '',
      tenant_name: '',
      tenant_phone: '',
      tenant_email: '',
      new_tenant_name: '',
      new_tenant_phone: '',
      new_tenant_email: '',
      unit_id: '',
      move_in_date: '',
      move_out_date: '',
      status: 'active',
      deposit_amount: 0,
      rent_amount: 0,
      notes: '',
      generate_invoice: 'yes',
      lease_terms: '',
    },
    formErrors: [],
    loading: false,
    units: @json($vacantUnits ?? []),
    selectedUnit: null,
    tenantSummary: null,
    leaseAgreementGenerated: false,
    leaseAgreementData: null,
    
    init() {
      window.tenancyCreateModal = this;
      const today = new Date().toISOString().split('T')[0];
      this.form.move_in_date = today;
      
      // Set default deposit to rent amount when unit changes
      this.$watch('form.rent_amount', (value) => {
        if (!this.form.deposit_amount || this.form.deposit_amount == 0) {
          this.form.deposit_amount = value || 0;
        }
      });
    },
    
    openModal(editData = null) {
      this.isOpen = true;
      this.resetForm();
      this.formErrors = [];
      
      if (editData) {
        this.isEditMode = true;
        this.editId = editData.id;
        this.form.tenant_id = editData.tenant_id;
        this.form.tenant_name = editData.tenant_name || '';
        this.form.tenant_phone = editData.tenant_phone || '';
        this.form.tenant_email = editData.tenant_email || '';
        this.form.unit_id = editData.unit_id;
        this.form.move_in_date = editData.move_in_date;
        this.form.move_out_date = editData.move_out_date || '';
        this.form.status = editData.status || 'active';
        this.form.deposit_amount = editData.deposit_amount || 0;
        this.form.rent_amount = editData.rent_amount || 0;
        this.form.notes = editData.notes || '';
        this.form.generate_invoice = 'no';
        this.form.tenant_selection = 'existing';
        
        // Find and set the selected unit
        this.selectedUnit = this.units.find(u => u.id == this.form.unit_id);
        if (this.selectedUnit) {
          this.form.rent_amount = this.selectedUnit.rent_amount || 0;
        }
        this.loadTenantSummary();
      } else {
        this.isEditMode = false;
        this.editId = null;
        this.selectedUnit = null;
        this.tenantSummary = null;
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.formErrors = [];
      this.loading = false;
      this.leaseAgreementGenerated = false;
      document.body.style.overflow = '';
    },
    
    resetForm() {
      const today = new Date().toISOString().split('T')[0];
      this.form = {
        tenant_selection: '',
        tenant_id: '',
        tenant_name: '',
        tenant_phone: '',
        tenant_email: '',
        new_tenant_name: '',
        new_tenant_phone: '',
        new_tenant_email: '',
        unit_id: '',
        move_in_date: today,
        move_out_date: '',
        status: 'active',
        deposit_amount: 0,
        rent_amount: 0,
        notes: '',
        generate_invoice: 'yes',
        lease_terms: '',
      };
      this.selectedUnit = null;
      this.tenantSummary = null;
      this.leaseAgreementGenerated = false;
      this.leaseAgreementData = null;
    },
    
    get isFormValid() {
      if (!this.form.tenant_selection) return false;
      
      if (this.form.tenant_selection === 'existing' && !this.form.tenant_id) return false;
      
      if (this.form.tenant_selection === 'new') {
        if (!this.form.new_tenant_name || !this.form.new_tenant_name.trim()) return false;
        if (!this.form.new_tenant_phone || !this.form.new_tenant_phone.trim()) return false;
      }
      
      if (!this.form.unit_id) return false;
      if (!this.form.move_in_date) return false;
      
      return true;
    },
    
    get totalInitialCharges() {
      const deposit = parseFloat(this.form.deposit_amount) || 0;
      const rent = parseFloat(this.form.rent_amount) || 0;
      return deposit + rent;
    },
    
    onTenantSelectionChange() {
      if (this.form.tenant_selection !== 'existing') {
        this.form.tenant_id = '';
        this.tenantSummary = null;
      }
    },
    
    async loadTenantSummary() {
      if (!this.form.tenant_id) {
        this.tenantSummary = null;
        return;
      }
      
      try {
        const response = await fetch(`/tenancies/tenant-summary/${this.form.tenant_id}`);
        const data = await response.json();
        if (data.success) {
          this.tenantSummary = data.tenant;
        }
      } catch (error) {
        console.error('Error loading tenant summary:', error);
        this.tenantSummary = null;
      }
    },
    
    onUnitChange() {
      this.selectedUnit = this.units.find(u => u.id == this.form.unit_id);
      if (this.selectedUnit) {
        this.form.rent_amount = this.selectedUnit.rent_amount || 0;
        if (!this.isEditMode) {
          this.form.deposit_amount = this.selectedUnit.rent_amount || 0;
        }
      } else {
        this.form.rent_amount = 0;
      }
      this.leaseAgreementGenerated = false;
    },
    
    async generateLeaseAgreement() {
      if (!this.form.unit_id) {
        alert('Please select a unit first');
        return;
      }
      
      const tenantName = this.form.tenant_selection === 'existing' 
        ? this.form.tenant_name 
        : this.form.new_tenant_name;
      
      if (!tenantName) {
        alert('Please provide tenant information first');
        return;
      }
      
      this.loading = true;
      
      try {
        this.leaseAgreementGenerated = true;
        this.leaseAgreementData = {
          tenant_name: tenantName,
          unit_number: this.selectedUnit?.unit_number || '',
          rent_amount: this.form.rent_amount,
          deposit_amount: this.form.deposit_amount,
          start_date: this.form.move_in_date,
        };
        
        this.showNotification('Lease agreement generated successfully!', 'success');
      } catch (error) {
        console.error('Error generating lease agreement:', error);
        this.showNotification('Error generating lease agreement', 'error');
      } finally {
        this.loading = false;
      }
    },
    
    viewLeaseAgreement() {
      if (this.leaseAgreementData) {
        alert('Lease Agreement:\n\n' + 
          'Tenant: ' + this.leaseAgreementData.tenant_name + '\n' +
          'Unit: ' + this.leaseAgreementData.unit_number + '\n' +
          'Rent: KES ' + this.leaseAgreementData.rent_amount + '\n' +
          'Deposit: KES ' + this.leaseAgreementData.deposit_amount + '\n' +
          'Start Date: ' + this.leaseAgreementData.start_date
        );
      }
    },
    
    getTenantSummary() {
      if (this.form.tenant_selection === 'existing' && this.form.tenant_name) {
        return this.form.tenant_name;
      } else if (this.form.tenant_selection === 'new' && this.form.new_tenant_name) {
        return this.form.new_tenant_name;
      }
      return 'tenant';
    },
    
    getUnitSummary() {
      if (this.selectedUnit) {
        return this.selectedUnit.unit_number;
      }
      return 'selected unit';
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.form.tenant_selection) {
        this.formErrors.push('Please select tenant option');
      } else if (this.form.tenant_selection === 'existing' && !this.form.tenant_id) {
        this.formErrors.push('Please select an existing tenant');
      } else if (this.form.tenant_selection === 'new') {
        if (!this.form.new_tenant_name || !this.form.new_tenant_name.trim()) {
          this.formErrors.push('Please enter tenant name');
        }
        if (!this.form.new_tenant_phone || !this.form.new_tenant_phone.trim()) {
          this.formErrors.push('Please enter tenant phone');
        }
        if (this.form.new_tenant_email && !this.isValidEmail(this.form.new_tenant_email)) {
          this.formErrors.push('Please enter a valid email address');
        }
      }
      
      if (!this.form.unit_id) {
        this.formErrors.push('Please select a unit');
      }
      
      if (!this.form.move_in_date) {
        this.formErrors.push('Please select move-in date');
      }
      
      if (this.form.move_out_date && new Date(this.form.move_out_date) < new Date(this.form.move_in_date)) {
        this.formErrors.push('Move-out date cannot be before move-in date');
      }
      
      return this.formErrors.length === 0;
    },
    
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    showNotification(message, type = 'success') {
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} transition-all duration-300 transform translate-y-0`;
      notification.textContent = message;
      document.body.appendChild(notification);
      setTimeout(() => {
        notification.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => notification.remove(), 300);
      }, 3000);
    },
    
    formatNumber(value) {
      if (value === undefined || value === null) return '0.00';
      return parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    
    async submitForm() {
      if (!this.validateForm()) {
        const modalContent = this.$el.closest('.overflow-y-auto');
        if (modalContent) modalContent.scrollTop = 0;
        return;
      }
      
      this.loading = true;
      
      try {
        const postData = {
          unit_id: this.form.unit_id,
          move_in_date: this.form.move_in_date,
          notes: this.form.notes,
          deposit_amount: this.form.deposit_amount,
          generate_invoice: this.form.generate_invoice,
          lease_terms: this.form.lease_terms,
        };
        
        if (this.isEditMode) {
          postData.move_out_date = this.form.move_out_date;
          postData.status = this.form.status;
        }
        
        if (this.form.tenant_selection === 'existing') {
          postData.tenant_id = this.form.tenant_id;
        } else {
          postData.new_tenant_name = this.form.new_tenant_name;
          postData.new_tenant_phone = this.form.new_tenant_phone;
          if (this.form.new_tenant_email) {
            postData.new_tenant_email = this.form.new_tenant_email;
          }
        }
        
        const url = this.isEditMode 
          ? `/tenancies/${this.editId}` 
          : '{{ route("tenancies.store") }}';
        const method = this.isEditMode ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify(postData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.closeModal();
          
          if (window.successModal) {
            window.successModal.show(
              'Success!', 
              data.message || (this.isEditMode ? 'Tenancy updated successfully!' : 'Tenancy created successfully!')
            );
          }
          
          setTimeout(() => window.location.reload(), 1500);
        } else {
          this.formErrors = [data.message || 'Failed to save tenancy'];
          const modalContent = this.$el.closest('.overflow-y-auto');
          if (modalContent) modalContent.scrollTop = 0;
        }
      } catch (error) {
        console.error('Error:', error);
        this.formErrors = ['An error occurred. Please try again.'];
      } finally {
        this.loading = false;
      }
    }
  }));
});
</script>
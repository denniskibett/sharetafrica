<!-- EDIT UNIT SLIDEOVER MODAL with Utility Charges -->
<div x-data="unitEditModal" x-init="init()">
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
       style="width: 38rem; max-width: calc(100% - 2rem);">
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

      <form @submit.prevent="submitForm()" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT">
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
          Edit Unit
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <!-- Unit Number -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Number *</label>
            <input 
              x-model="formData.unit_number"
              type="text"
              name="unit_number"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="e.g., HAR-101"
            />
          </div>

          <!-- Unit Type -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit Type *</label>
            <select 
              x-model="formData.unit_type"
              name="unit_type"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">Select Type</option>
              <option value="Studio">Studio</option>
              <option value="Bedsitter">Bedsitter</option>
              <option value="One Bedroom">One Bedroom</option>
              <option value="Two Bedroom">Two Bedroom</option>
              <option value="Three Bedroom">Three Bedroom</option>
              <option value="Apartment">Apartment</option>
              <option value="House">House</option>
              <option value="Office">Office</option>
              <option value="Shop">Shop</option>
            </select>
          </div>

          <!-- Rent Amount -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Monthly Rent *</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">KES</span>
              </div>
              <input 
                x-model="formData.rent_amount"
                type="number"
                step="0.01"
                min="0"
                name="rent_amount"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Water Charge -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Water Charge (KES)</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">KES</span>
              </div>
              <input 
                x-model="formData.water_charge"
                type="number"
                step="0.01"
                min="0"
                name="water_charge"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Service Charge -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Service Charge (KES)</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">KES</span>
              </div>
              <input 
                x-model="formData.service_charge"
                type="number"
                step="0.01"
                min="0"
                name="service_charge"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Garbage Charge -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Garbage Charge (KES)</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">KES</span>
              </div>
              <input 
                x-model="formData.garbage_charge"
                type="number"
                step="0.01"
                min="0"
                name="garbage_charge"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Security Charge -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Security Charge (KES)</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">KES</span>
              </div>
              <input 
                x-model="formData.security_charge"
                type="number"
                step="0.01"
                min="0"
                name="security_charge"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
            <select 
              x-model="formData.status"
              name="status"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="vacant">Vacant</option>
              <option value="occupied">Occupied</option>
            </select>
          </div>

          <!-- Estate (Read-only) -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate</label>
            <input 
              type="text"
              :value="currentUnit?.estate_name"
              readonly
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
            />
          </div>

          <!-- Total Summary Section -->
          <div class="col-span-1 md:col-span-2">
            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
              <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Monthly Charges Summary</h5>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="text-gray-600 dark:text-gray-400">Rent:</div>
                <div class="text-right font-medium text-gray-800 dark:text-gray-200">
                  KES <span x-text="parseFloat(formData.rent_amount || 0).toFixed(2)"></span>
                </div>
                
                <div class="text-gray-600 dark:text-gray-400">Water:</div>
                <div class="text-right font-medium text-gray-800 dark:text-gray-200">
                  KES <span x-text="parseFloat(formData.water_charge || 0).toFixed(2)"></span>
                </div>
                
                <div class="text-gray-600 dark:text-gray-400">Service Charge:</div>
                <div class="text-right font-medium text-gray-800 dark:text-gray-200">
                  KES <span x-text="parseFloat(formData.service_charge || 0).toFixed(2)"></span>
                </div>
                
                <div class="text-gray-600 dark:text-gray-400">Garbage:</div>
                <div class="text-right font-medium text-gray-800 dark:text-gray-200">
                  KES <span x-text="parseFloat(formData.garbage_charge || 0).toFixed(2)"></span>
                </div>
                
                <div class="text-gray-600 dark:text-gray-400">Security:</div>
                <div class="text-right font-medium text-gray-800 dark:text-gray-200">
                  KES <span x-text="parseFloat(formData.security_charge || 0).toFixed(2)"></span>
                </div>
                
                <div class="border-t border-gray-300 dark:border-gray-700 mt-2 pt-2 font-semibold text-gray-800 dark:text-gray-200">Total Monthly:</div>
                <div class="border-t border-gray-300 dark:border-gray-700 mt-2 pt-2 text-right font-semibold text-blue-600 dark:text-blue-400">
                  KES <span x-text="(parseFloat(formData.rent_amount || 0) + parseFloat(formData.water_charge || 0) + parseFloat(formData.service_charge || 0) + parseFloat(formData.garbage_charge || 0) + parseFloat(formData.security_charge || 0)).toFixed(2)"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button
            @click="closeModal()"
            type="button"
            :disabled="loading"
            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">Update Unit</span>
            <span x-show="loading">Updating...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('unitEditModal', () => ({
    isOpen: false,
    currentUnit: null,
    formData: {
      unit_number: '',
      unit_type: '',
      rent_amount: '',
      water_charge: '',
      service_charge: '',
      garbage_charge: '',
      security_charge: '',
      status: 'vacant'
    },
    formErrors: [],
    loading: false,
    
    init() {
      window.unitEditModal = this;
    },
    
    openModal(unit) {
      this.currentUnit = unit;
      this.isOpen = true;
      this.formErrors = [];
      this.loading = false;
      
      if (unit) {
        this.formData = {
          unit_number: unit.unit_number || '',
          unit_type: unit.unit_type || '',
          rent_amount: unit.rent_amount || '',
          water_charge: unit.water_charge || '',
          service_charge: unit.service_charge || '',
          garbage_charge: unit.garbage_charge || '',
          security_charge: unit.security_charge || '',
          status: unit.status || 'vacant'
        };
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentUnit = null;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    validateForm() {
      const errors = [];
      
      if (!this.formData.unit_number || this.formData.unit_number.trim() === '') {
        errors.push('Please enter a unit number');
      }
      
      if (!this.formData.unit_type) {
        errors.push('Please select a unit type');
      }
      
      if (!this.formData.rent_amount || parseFloat(this.formData.rent_amount) <= 0) {
        errors.push('Please enter a valid rent amount greater than 0');
      }
      
      // Validate utilities (optional, but must be numeric if provided)
      if (this.formData.water_charge && isNaN(parseFloat(this.formData.water_charge))) {
        errors.push('Water charge must be a valid number');
      }
      
      if (this.formData.service_charge && isNaN(parseFloat(this.formData.service_charge))) {
        errors.push('Service charge must be a valid number');
      }
      
      if (this.formData.garbage_charge && isNaN(parseFloat(this.formData.garbage_charge))) {
        errors.push('Garbage charge must be a valid number');
      }
      
      if (this.formData.security_charge && isNaN(parseFloat(this.formData.security_charge))) {
        errors.push('Security charge must be a valid number');
      }
      
      return errors;
    },
    
    async submitForm() {
      this.loading = true;
      this.formErrors = [];
      
      // Validate form
      const errors = this.validateForm();
      if (errors.length > 0) {
        this.formErrors = errors;
        this.loading = false;
        const modalContent = document.querySelector('.overflow-y-auto');
        if (modalContent) modalContent.scrollTop = 0;
        return;
      }
      
      try {
        const response = await fetch(`/units/${this.currentUnit.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            unit_number: this.formData.unit_number,
            unit_type: this.formData.unit_type,
            rent_amount: this.formData.rent_amount,
            water_charge: this.formData.water_charge || 0,
            service_charge: this.formData.service_charge || 0,
            garbage_charge: this.formData.garbage_charge || 0,
            security_charge: this.formData.security_charge || 0,
            status: this.formData.status
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          // Calculate total for event data
          const total = parseFloat(this.formData.rent_amount || 0) +
                       parseFloat(this.formData.water_charge || 0) +
                       parseFloat(this.formData.service_charge || 0) +
                       parseFloat(this.formData.garbage_charge || 0) +
                       parseFloat(this.formData.security_charge || 0);
          
          // Dispatch event to update the table
          window.dispatchEvent(new CustomEvent('unit-updated', {
            detail: {
              id: this.currentUnit.id,
              unit_number: this.formData.unit_number,
              unit_type: this.formData.unit_type,
              rent_amount: parseFloat(this.formData.rent_amount),
              water_charge: parseFloat(this.formData.water_charge || 0),
              service_charge: parseFloat(this.formData.service_charge || 0),
              garbage_charge: parseFloat(this.formData.garbage_charge || 0),
              security_charge: parseFloat(this.formData.security_charge || 0),
              total_monthly_charges: total,
              status: this.formData.status
            }
          }));
          
          this.closeModal();
          
          if (window.successModal) {
            window.successModal.show('Unit Updated', `Unit ${this.formData.unit_number} has been updated successfully.`);
          } else {
            alert(`Success: Unit ${this.formData.unit_number} updated successfully.`);
          }
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat();
            this.formErrors = errorMessages;
            if (window.errorModal) {
              window.errorModal.show('Validation Error', 'Please check the following errors:', errorMessages);
            }
          } else {
            this.formErrors = [data.message || 'Failed to update unit'];
            if (window.errorModal) {
              window.errorModal.show('Error', data.message || 'Failed to update unit');
            }
          }
          const modalContent = document.querySelector('.overflow-y-auto');
          if (modalContent) modalContent.scrollTop = 0;
        }
      } catch (error) {
        console.error('Error:', error);
        this.formErrors = ['An unexpected error occurred. Please try again.'];
        if (window.errorModal) {
          window.errorModal.show('Error', 'An unexpected error occurred. Please try again.');
        }
      } finally {
        this.loading = false;
      }
    }
  }));
});
</script>
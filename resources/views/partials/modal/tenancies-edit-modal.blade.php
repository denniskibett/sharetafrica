<!-- EDIT TENANCY SLIDEOVER MODAL -->
<div x-data="tenancyEditModal" x-init="init()">
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

      <form @submit.prevent="submitForm">
        @csrf
        @method('PUT')
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
          Edit Tenancy
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
          <!-- Tenant Info (Read-only) -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Tenant
            </label>
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
              <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                <span class="text-purple-600 font-medium" x-text="currentTenancy?.tenant_name?.charAt(0)?.toUpperCase() || 'T'"></span>
              </div>
              <div>
                <p class="font-medium text-gray-800 text-sm dark:text-white/90" x-text="currentTenancy?.tenant_name || 'Unknown'"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="currentTenancy?.tenant_phone || '-'"></p>
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
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">Select Unit</option>
              @foreach($allUnits ?? [] as $unit)
                <option value="{{ $unit['id'] }}" 
                        {{-- Mark current unit as selected --}}
                        @if(isset($unit['status']) && $unit['status'] === 'occupied' && $unit['id'] != ($currentTenancy?->unit_id ?? ''))
                        disabled class="text-gray-400"
                        @endif
                >
                  {{ $unit['unit_number'] }} - {{ $unit['estate_name'] ?? 'No Estate' }} ({{ $unit['unit_type'] }})
                  @if($unit['status'] === 'occupied' && $unit['id'] != ($currentTenancy?->unit_id ?? ''))
                    - Already Occupied
                  @endif
                </option>
              @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              <span x-show="form.unit_id != currentTenancy?.unit_id" class="text-yellow-600">Changing unit will mark the previous unit as vacant.</span>
            </p>
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

          <!-- Move-out Date -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Move-out Date
            </label>
            <div class="flex gap-2">
              <input
                type="date"
                x-model="form.move_out_date"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              />
              <button
                type="button"
                @click="terminateToday"
                class="whitespace-nowrap px-3 py-2.5 text-sm font-medium text-white rounded-lg bg-red-600 hover:bg-red-700"
              >
                Today
              </button>
            </div>
          </div>

          <!-- Status -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Status *
            </label>
            <select
              x-model="form.status"
              @change="onStatusChange"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="active">Active</option>
              <option value="ended">Ended</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Notes
            </label>
            <textarea
              x-model="form.notes"
              rows="3"
              class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="Add any notes about this tenancy..."
            ></textarea>
          </div>
        </div>

        <!-- Summary of changes -->
        <div class="mt-4 p-3 bg-blue-50 rounded-lg dark:bg-blue-900/20" x-show="hasChanges">
          <p class="text-sm text-blue-700 dark:text-blue-300">
            <strong>Changes will:</strong>
            <span x-show="form.status === 'ended'">Mark tenancy as ended and set unit to vacant.</span>
            <span x-show="form.status === 'active' && form.unit_id != currentTenancy?.unit_id">Move tenant to new unit and mark old unit as vacant.</span>
            <span x-show="form.status === 'active' && form.unit_id == currentTenancy?.unit_id && form.move_out_date">Update move-out date while keeping tenancy active.</span>
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
            :disabled="loading || !form.unit_id || !form.move_in_date"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">Update Tenancy</span>
            <span x-show="loading">Updating...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('tenancyEditModal', () => ({
    isOpen: false,
    currentTenancy: null,
    form: {
      unit_id: '',
      move_in_date: '',
      move_out_date: '',
      status: 'active',
      notes: ''
    },
    formErrors: [],
    loading: false,
    
    init() {
      window.tenancyEditModal = this;
    },
    
    openModal(tenancy) {
      this.currentTenancy = tenancy;
      this.isOpen = true;
      this.formErrors = [];
      this.loading = false;
      
      if (tenancy) {
        this.form = {
          unit_id: String(tenancy.unit_id || ''),
          move_in_date: this.formatDateForInput(tenancy.move_in_date),
          move_out_date: tenancy.move_out_date ? this.formatDateForInput(tenancy.move_out_date) : '',
          status: tenancy.status || 'active',
          notes: tenancy.notes || ''
        };
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentTenancy = null;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    get hasChanges() {
      if (!this.currentTenancy) return false;
      return (
        this.form.unit_id !== String(this.currentTenancy.unit_id) ||
        this.form.move_in_date !== this.formatDateForInput(this.currentTenancy.move_in_date) ||
        this.form.move_out_date !== (this.currentTenancy.move_out_date ? this.formatDateForInput(this.currentTenancy.move_out_date) : '') ||
        this.form.status !== this.currentTenancy.status
      );
    },
    
    formatDateForInput(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toISOString().split('T')[0];
    },
    
    terminateToday() {
      const today = new Date().toISOString().split('T')[0];
      this.form.move_out_date = today;
      this.form.status = 'ended';
    },
    
    onStatusChange() {
      if (this.form.status === 'ended' && !this.form.move_out_date) {
        // Auto-set move-out date to today if ending and no date set
        this.form.move_out_date = new Date().toISOString().split('T')[0];
      }
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.form.unit_id) {
        this.formErrors.push('Please select a unit');
      }
      
      if (!this.form.move_in_date) {
        this.formErrors.push('Please select move-in date');
      }
      
      if (this.form.status === 'ended' && !this.form.move_out_date) {
        this.formErrors.push('Please set move-out date when ending tenancy');
      }
      
      if (this.form.move_out_date && this.form.move_out_date < this.form.move_in_date) {
        this.formErrors.push('Move-out date cannot be before move-in date');
      }
      
      return this.formErrors.length === 0;
    },
    
    async submitForm() {
      if (!this.validateForm()) {
        // Scroll to top to show errors
        const modalContent = this.$el.closest('.overflow-y-auto');
        if (modalContent) {
          modalContent.scrollTop = 0;
        }
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(`/tenancies/${this.currentTenancy.id}`, {
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
          this.closeModal();
          
          // Show success message
          if (window.successModal) {
            window.successModal.show(
              'Success!', 
              data.message || 'Tenancy updated successfully'
            );
          }
          
          // Reload after a short delay
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          this.formErrors = [data.message || 'Failed to update tenancy'];
          
          // Scroll to show errors
          const modalContent = this.$el.closest('.overflow-y-auto');
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
    }
  }));
});
</script>
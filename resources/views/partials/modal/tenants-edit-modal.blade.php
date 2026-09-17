<!-- EDIT TENANT SLIDEOVER MODAL -->
<div x-data="tenantEditModal" x-init="init()">
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
          Edit Tenant
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
          <!-- Full Name -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Full Name *
            </label>
            <input
              type="text"
              x-model="form.name"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="John Doe"
            />
          </div>

          <!-- Email -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Email *
            </label>
            <input
              type="email"
              x-model="form.email"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="john@example.com"
            />
          </div>

          <!-- Phone -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Phone Number *
            </label>
            <input
              type="tel"
              x-model="form.phone"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="0712345678"
            />
          </div>

          <!-- Secondary Phone -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Secondary Phone
            </label>
            <input
              type="tel"
              x-model="form.phone2"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="0723456789"
            />
          </div>

          <!-- ID Number -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              ID Number
            </label>
            <input
              type="text"
              x-model="form.id_number"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="12345678"
            />
          </div>

          <!-- Emergency Contact -->
          <div class="col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Emergency Contact
            </label>
            <input
              type="text"
              x-model="form.emergency_contact"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="Jane Doe - 0712345678"
            />
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
              placeholder="Any additional notes about this tenant..."
            ></textarea>
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
            :disabled="loading || !form.name || !form.email || !form.phone"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">Update Tenant</span>
            <span x-show="loading">Updating...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('tenantEditModal', () => ({
    isOpen: false,
    currentTenant: null,
    form: {
      name: '',
      email: '',
      phone: '',
      phone2: '',
      id_number: '',
      emergency_contact: '',
      notes: ''
    },
    formErrors: [],
    loading: false,
    
    init() {
      window.tenantEditModal = this;
    },
    
    openModal(tenant) {
      this.currentTenant = tenant;
      this.isOpen = true;
      this.formErrors = [];
      this.loading = false;
      
      if (tenant) {
        this.form = {
          name: tenant.name || '',
          email: tenant.email || '',
          phone: tenant.phone || '',
          phone2: tenant.phone2 || '',
          id_number: tenant.id_number || '',
          emergency_contact: tenant.emergency_contact || '',
          notes: tenant.notes || ''
        };
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentTenant = null;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.form.name || !this.form.name.trim()) {
        this.formErrors.push('Please enter tenant name');
      }
      
      if (!this.form.email || !this.form.email.trim()) {
        this.formErrors.push('Please enter email');
      } else if (!this.isValidEmail(this.form.email)) {
        this.formErrors.push('Please enter a valid email address');
      }
      
      if (!this.form.phone || !this.form.phone.trim()) {
        this.formErrors.push('Please enter phone number');
      }
      
      return this.formErrors.length === 0;
    },
    
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    async submitForm() {
      if (!this.validateForm()) {
        const modalContent = this.$el.closest('.overflow-y-auto');
        if (modalContent) {
          modalContent.scrollTop = 0;
        }
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(`/tenants/${this.currentTenant.id}`, {
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
          
          if (window.successModal) {
            window.successModal.show(
              'Success!', 
              data.message || 'Tenant updated successfully'
            );
          }
          
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          this.formErrors = [data.message || 'Failed to update tenant'];
          
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
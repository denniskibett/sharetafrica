<!-- CREATE TENANT SLIDEOVER MODAL -->
<div x-data="tenantCreateModal" x-init="init()">
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
        <div class="flex items-center justify-between mb-6">
          <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">
            Add New Tenant
          </h4>
          
          <!-- Mode Toggle -->
          <div class="flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
            <button
              type="button"
              @click="mode = 'new'"
              :class="mode === 'new' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400'"
              class="rounded-md px-4 py-2 text-sm font-medium transition-all"
            >
              New User
            </button>
            <button
              type="button"
              @click="mode = 'existing'; loadExistingUsers()"
              :class="mode === 'existing' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400'"
              class="rounded-md px-4 py-2 text-sm font-medium transition-all"
            >
              Existing User
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

        <!-- NEW USER MODE -->
        <div x-show="mode === 'new'" x-cloak>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <!-- Full Name -->
            <div class="col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Full Name *
              </label>
              <input
                type="text"
                x-model="form.name"
                {{-- required --}}
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="John Doe"
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
                {{-- required --}}
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0712345678"
              />
            </div>

            <!-- Email -->
            <div class="col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email Address *
              </label>
              <input
                type="email"
                x-model="form.email"
                {{-- required --}}
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="john@example.com"
              />
              <p class="mt-1 text-xs text-gray-500">Password will be <strong class="font-mono">00000000</strong></p>
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
          </div>
        </div>

        <!-- EXISTING USER MODE -->
        <div x-show="mode === 'existing'" x-cloak>
          <div class="grid grid-cols-1 gap-x-6 gap-y-5">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Select Existing User *
              </label>
              <div class="relative">
                <select 
                  x-model="selectedUserId"
                  @change="onUserSelect()"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
                  <option value="">-- Select a user --</option>
                  <template x-for="user in existingUsers" :key="user.id">
                    <option :value="user.id" x-text="`${user.name} - ${user.email} (${user.phone || 'No phone'})`"></option>
                  </template>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">Showing users without tenant records</p>
            </div>

            <!-- Selected User Info -->
            <div x-show="selectedUserId" class="mt-4 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
              <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selected User</h5>
              <div class="space-y-1">
                <p class="text-sm"><span class="font-medium">Name:</span> <span x-text="selectedUserData?.name || ''"></span></p>
                <p class="text-sm"><span class="font-medium">Email:</span> <span x-text="selectedUserData?.email || ''"></span></p>
                <p class="text-sm"><span class="font-medium">Phone:</span> <span x-text="selectedUserData?.phone || 'N/A'"></span></p>
              </div>
            </div>

            <!-- Additional Info for Existing User -->
            <div x-show="selectedUserId" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 mt-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">ID Number (Optional)</label>
                <input type="text" x-model="form.id_number"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs"
                  placeholder="ID Number" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Emergency Contact</label>
                <input type="text" x-model="form.emergency_contact"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs"
                  placeholder="Emergency Contact" />
              </div>
            </div>
          </div>
        </div>

        <!-- Unit Assignment (Common) -->
        <div class="mt-6">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Assign Unit (Optional)
          </label>
          <select
            x-model="form.unit_id"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          >
            <option value="">Select Unit (Optional)</option>
            @foreach($vacantUnits ?? [] as $unit)
              <option value="{{ $unit['id'] }}">
                {{ $unit['unit_number'] }} - {{ $unit['estate_name'] }} (KES {{ number_format($unit['rent_amount'] ?? 0, 2) }})
              </option>
            @endforeach
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Only vacant units are shown. If selected, tenant will be moved in immediately.
          </p>
        </div>

        <!-- Notes -->
        <div class="mt-4">
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

        <!-- Summary -->
        <div class="mt-4 p-3 bg-blue-50 rounded-lg dark:bg-blue-900/20" x-show="mode === 'new' ? form.name : selectedUserId">
          <p class="text-sm text-blue-700 dark:text-blue-300">
            <strong>Summary:</strong> 
            <span x-show="mode === 'new'">Creating new user account for <span x-text="form.name"></span></span>
            <span x-show="mode === 'existing' && selectedUserId">Adding tenant record for <span x-text="selectedUserData?.name"></span></span>
            <span x-show="form.unit_id"> and assigning to selected unit</span>
            <span x-show="!form.unit_id"> without unit assignment</span>.
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
            :disabled="loading || (mode === 'new' ? (!form.name || !form.phone || !form.email) : !selectedUserId)"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">Create Tenant</span>
            <span x-show="loading">Creating...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('tenantCreateModal', () => ({
    // Mode: 'new' or 'existing'
    mode: 'new',
    
    // Form data
    form: {
      name: '',
      email: '',
      phone: '',
      phone2: '',
      id_number: '',
      emergency_contact: '',
      unit_id: '',
      notes: ''
    },
    
    // Existing user data
    existingUsers: @json($existingUsersWithoutTenant ?? []),
    selectedUserId: '',
    selectedUserData: null,
    
    // UI state
    isOpen: false,
    formErrors: [],
    loading: false,
    
    init() {
      window.tenantCreateModal = this;
    },
    
    openModal() {
      this.isOpen = true;
      this.resetForm();
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    resetForm() {
      this.mode = 'new';
      this.form = {
        name: '',
        email: '',
        phone: '',
        phone2: '',
        id_number: '',
        emergency_contact: '',
        unit_id: '',
        notes: ''
      };
      this.selectedUserId = '';
      this.selectedUserData = null;
      this.formErrors = [];
      this.loading = false;
    },
    
    loadExistingUsers() {
      this.selectedUserId = '';
      this.selectedUserData = null;
      this.form.id_number = '';
      this.form.emergency_contact = '';
    },
    
    onUserSelect() {
      if (this.selectedUserId) {
        this.selectedUserData = this.existingUsers.find(u => u.id == this.selectedUserId);
      } else {
        this.selectedUserData = null;
      }
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (this.mode === 'new') {
        if (!this.form.name || !this.form.name.trim()) {
          this.formErrors.push('Please enter tenant name');
        }
        
        if (!this.form.email || !this.form.email.trim()) {
          this.formErrors.push('Please enter email address');
        } else if (!this.isValidEmail(this.form.email)) {
          this.formErrors.push('Please enter a valid email address');
        }
        
        if (!this.form.phone || !this.form.phone.trim()) {
          this.formErrors.push('Please enter phone number');
        }
      } else {
        if (!this.selectedUserId) {
          this.formErrors.push('Please select an existing user');
        }
      }
      
      return this.formErrors.length === 0;
    },
    
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
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
      
      let payload = {};
      
      if (this.mode === 'new') {
        payload = {
          mode: 'new',
          name: this.form.name,
          email: this.form.email,
          phone: this.form.phone,
          phone2: this.form.phone2,
          id_number: this.form.id_number,
          emergency_contact: this.form.emergency_contact,
          unit_id: this.form.unit_id,
          notes: this.form.notes
        };
      } else {
        payload = {
          mode: 'existing',
          user_id: this.selectedUserId,
          id_number: this.form.id_number,
          emergency_contact: this.form.emergency_contact,
          unit_id: this.form.unit_id,
          notes: this.form.notes
        };
      }
      
      try {
        const response = await fetch('{{ route("tenants.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.closeModal();
          
          if (window.successModal) {
            window.successModal.show(
              'Success!', 
              data.message || 'Tenant created successfully'
            );
          }
          
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          this.formErrors = [data.message || 'Failed to create tenant'];
          
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
    }
  }));
});
</script>
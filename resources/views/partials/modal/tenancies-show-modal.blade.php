<!-- SHOW TENANCY CENTERED MODAL -->
<div x-data="tenancyShowModal" x-init="init()">
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

  <!-- Modal Content - Centered -->
  <div x-show="isOpen" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       x-cloak
       class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
       style="width: 38rem; max-width: calc(100% - 2rem);">
    <div 
      @click.outside="closeModal()"
      class="relative w-full max-w-2xl rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10 max-h-[90vh] overflow-y-auto"
    >
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <div>
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
          Tenancy Details
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <!-- Tenant -->
          <div class="md:col-span-2">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Tenant</p>
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                <span class="text-purple-600 font-bold text-lg" x-text="currentTenancy?.tenant_name?.charAt(0)?.toUpperCase() || 'T'"></span>
              </div>
              <div>
                <p class="font-medium text-gray-800 dark:text-white/90" x-text="currentTenancy?.tenant_name || 'Unknown'"></p>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="currentTenancy?.tenant_phone || '-'"></p>
              </div>
            </div>
          </div>

          <!-- Unit -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Unit</p>
            <div class="flex items-center gap-2">
              <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="text-blue-600 font-medium text-sm" x-text="currentTenancy?.unit_number?.charAt(0)?.toUpperCase() || 'U'"></span>
              </div>
              <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentTenancy?.unit_number || '-'"></p>
            </div>
          </div>

          <!-- Estate -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Estate</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentTenancy?.estate_name || '-'"></p>
          </div>

          <!-- Move-in Date -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Move-in Date</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="formatDate(currentTenancy?.move_in_date)"></p>
          </div>

          <!-- Move-out Date -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Move-out Date</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="formatDate(currentTenancy?.move_out_date) || '-'"></p>
          </div>

          <!-- Duration -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Duration</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="calculateDuration(currentTenancy)"></p>
          </div>

          <!-- Status -->
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Status</p>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" 
                  :class="currentTenancy?.status === 'active' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
              <span x-text="currentTenancy?.status ? currentTenancy.status.charAt(0).toUpperCase() + currentTenancy.status.slice(1) : 'Unknown'"></span>
            </span>
          </div>

          <!-- Created At -->
          <div x-show="currentTenancy?.created_at">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Created</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="formatDate(currentTenancy?.created_at)"></p>
          </div>

          <!-- Updated At -->
          <div x-show="currentTenancy?.updated_at">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Updated</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="formatDate(currentTenancy?.updated_at)"></p>
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button
            @click="closeModal()"
            type="button"
            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
          >
            Close
          </button>
          <button
            @click="closeModal(); window.tenancyEditModal?.openModal(currentTenancy)"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-yellow-500 shadow-theme-xs hover:bg-yellow-600 sm:w-auto"
          >
            Edit
          </button>
          <a :href="`/tenancies/${currentTenancy?.id}`" 
             class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-500 shadow-theme-xs hover:bg-blue-600 sm:w-auto">
            Full Details
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('tenancyShowModal', () => ({
    isOpen: false,
    currentTenancy: null,
    
    init() {
      window.tenancyShowModal = this;
    },
    
    openModal(tenancy) {
      this.currentTenancy = tenancy;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentTenancy = null;
      document.body.style.overflow = '';
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    calculateDuration(tenancy) {
      if (!tenancy?.move_in_date) return 'N/A';
      
      const moveIn = new Date(tenancy.move_in_date);
      const moveOut = tenancy.move_out_date ? new Date(tenancy.move_out_date) : new Date();
      
      const diffTime = Math.abs(moveOut - moveIn);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays < 30) {
        return `${diffDays} days`;
      } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        return `${months} month${months > 1 ? 's' : ''}`;
      } else {
        const years = Math.floor(diffDays / 365);
        const remainingMonths = Math.floor((diffDays % 365) / 30);
        if (remainingMonths > 0) {
          return `${years} year${years > 1 ? 's' : ''} ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`;
        }
        return `${years} year${years > 1 ? 's' : ''}`;
      }
    }
  }));
});
</script>
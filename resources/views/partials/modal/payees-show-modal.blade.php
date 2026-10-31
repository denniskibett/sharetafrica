<!-- SHOW PAYEE CENTERED MODAL -->
<div x-data="payeeShowModal" x-init="init()">
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
          Payee Details
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <div class="md:col-span-2">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Name</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentPayee?.name || 'N/A'"></p>
          </div>
          
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Type</p>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium" 
                  :class="{
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': currentPayee?.type === 'staff',
                    'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400': currentPayee?.type === 'vendor',
                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': currentPayee?.type === 'utility'
                  }">
              <span x-text="currentPayee?.type ? currentPayee.type.charAt(0).toUpperCase() + currentPayee.type.slice(1) : 'N/A'"></span>
            </span>
          </div>
          
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Phone</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentPayee?.phone || '-'"></p>
          </div>
          
          <div class="md:col-span-2">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Email</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentPayee?.email || '-'"></p>
          </div>
          
          <div>
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Expenses Count</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="currentPayee?.expenses_count || 0"></p>
          </div>
          
          <div x-show="currentPayee?.created_at">
            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Created At</p>
            <p class="text-sm text-gray-800 dark:text-white/90" x-text="formatDate(currentPayee?.created_at)"></p>
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
            @click="closeModal(); window.payeeEditModal?.openModal(currentPayee)"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-yellow-500 shadow-theme-xs hover:bg-yellow-600 sm:w-auto"
          >
            Edit
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('payeeShowModal', () => ({
    isOpen: false,
    currentPayee: null,
    
    init() {
      window.payeeShowModal = this;
    },
    
    openModal(payee) {
      this.currentPayee = payee;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentPayee = null;
      document.body.style.overflow = '';
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }));
});
</script>
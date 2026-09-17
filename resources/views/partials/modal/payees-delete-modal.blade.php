<!-- DELETE PAYEE MODAL -->
<div x-data="payeeDeleteModal" x-init="init()">
  <template x-if="isOpen">
    <div @click="closeModal()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0">
    </div>
  </template>

  <div x-show="isOpen" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       x-cloak
       class="fixed inset-0 flex items-center justify-center p-5 z-99999">
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/20">
        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
      </div>
      
      <h3 class="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90">Confirm Deletion</h3>
      <p class="mb-6 text-sm text-center text-gray-600 dark:text-gray-400" x-text="message"></p>
      
      <template x-if="errorMessage">
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
          <p class="text-sm text-red-600 dark:text-red-400" x-text="errorMessage"></p>
        </div>
      </template>
      
      <div class="flex items-center justify-center gap-3">
        <button @click="closeModal()" :disabled="loading" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Cancel</button>
        <button @click="confirmDelete()" :disabled="loading" class="flex w-full justify-center rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
          <span x-show="!loading">Delete</span>
          <span x-show="loading" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Deleting...
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('payeeDeleteModal', () => ({
    isOpen: false,
    loading: false,
    payeeToDelete: null,
    message: '',
    errorMessage: '',
    
    init() {
      window.payeeDeleteModal = this;
    },
    
    openModal(payee) {
      this.payeeToDelete = payee;
      this.message = `Are you sure you want to delete payee "${payee.name}"? This action cannot be undone.`;
      this.errorMessage = '';
      this.loading = false;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.payeeToDelete = null;
      this.errorMessage = '';
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    async confirmDelete() {
      if (!this.payeeToDelete) return;
      
      this.loading = true;
      this.errorMessage = '';
      
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(`/payees/${this.payeeToDelete.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (response.ok && data.success) {
          this.closeModal();
          window.location.reload();
        } else {
          this.errorMessage = data.message || 'Failed to delete payee. Please try again.';
        }
      } catch (error) {
        console.error('Error:', error);
        this.errorMessage = 'Network error. Please check your connection and try again.';
      } finally {
        this.loading = false;
      }
    }
  }));
});
</script>
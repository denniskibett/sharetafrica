<!-- DELETE CONFIRMATION CENTERED MODAL -->
<div x-data="expenseDeleteModal" x-init="init()">
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
       class="fixed inset-0 flex items-center justify-center p-5 z-99999">
    <div 
      class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8"
    >
      <!-- Alert Icon -->
      <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/20">
        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
      </div>
      
      <h3 class="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90">Confirm Deletion</h3>
      <p class="mb-6 text-sm text-center text-gray-600 dark:text-gray-400" x-text="message"></p>
      
      <div class="flex items-center justify-center gap-3">
        <button
          @click="closeModal()"
          class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
        >
          Cancel
        </button>
        <button
          @click="confirmDelete()"
          class="flex w-full justify-center rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('expenseDeleteModal', () => ({
    isOpen: false,
    expenseToDelete: null,
    message: '',
    
    init() {
      window.expenseDeleteModal = this;
    },
    
    openModal(expense) {
      this.expenseToDelete = expense;
      this.message = `Are you sure you want to delete this expense of ${this.formatCurrency(expense.amount)}? This action cannot be undone.`;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.expenseToDelete = null;
      document.body.style.overflow = '';
    },
    
    confirmDelete() {
      if (this.expenseToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/expenses/${this.expenseToDelete.id}`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        
        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
      }
    },
    
    formatCurrency(amount) {
      const symbol = "{{ SystemHelper::currencySymbol() }} ";
      if (!amount) return symbol + "0.00";
      return symbol + parseFloat(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }));
});
</script>
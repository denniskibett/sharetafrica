<!-- DELETE INVOICE CONFIRMATION MODAL -->
<div x-data="invoiceDeleteModal" x-init="init()" x-cloak>
  <!-- Backdrop -->
  <template x-if="isOpen">
    <div 
      @click="closeModal"
      class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-[99999]"
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
       class="fixed inset-0 flex items-center justify-center z-[99999] p-4">
    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <svg class="h-6 w-6 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Delete Invoice</h3>
        </div>
        <button
          @click="closeModal"
          class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <div class="mb-4">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Are you sure you want to delete this invoice?
          </p>
          <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Invoice #:</span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="invoiceData?.id"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Tenant:</span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="invoiceData?.tenant_name"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Unit:</span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="invoiceData?.unit_number"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Billing Month:</span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="invoiceData?.billing_month_formatted"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Total Amount:</span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="'KES ' + formatNumber(invoiceData?.total_amount)"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                <span class="inline-flex px-2 py-0.5 text-xs rounded-full" 
                      :class="getStatusClass(invoiceData?.status)"
                      x-text="formatStatus(invoiceData?.status)"></span>
              </div>
              <div x-show="invoiceData?.items?.length > 0" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Items (<span x-text="invoiceData?.items?.length"></span>):</p>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                  <template x-for="item in invoiceData?.items" :key="item.id">
                    <li class="flex justify-between">
                      <span x-text="item.description"></span>
                      <span x-text="'KES ' + formatNumber(item.amount)"></span>
                    </li>
                  </template>
                </ul>
              </div>
            </div>
          </div>
          <p class="mt-4 text-sm text-red-600 dark:text-red-400">
            ⚠️ This action cannot be undone. All associated items and payments will also be deleted.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="closeModal"
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="confirmDelete"
          :disabled="isDeleting"
          class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span x-show="!isDeleting">Delete Invoice</span>
          <span x-show="isDeleting">Deleting...</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('invoiceDeleteModal', () => ({
    isOpen: false,
    invoiceData: null,
    isDeleting: false,
    
    init() {
      window.invoiceDeleteModal = this;
    },
    
    formatNumber(value) {
      return parseFloat(value || 0).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    
    formatStatus(status) {
      if (!status) return '';
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    
    getStatusClass(status) {
      const classes = {
        'paid': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'unpaid': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'partial': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-800/50 dark:text-gray-400'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },
    
    openModal(invoice) {
      this.invoiceData = invoice;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.invoiceData = null;
      this.isDeleting = false;
      document.body.style.overflow = '';
    },
    
    async confirmDelete() {
      if (!this.invoiceData) return;
      
      this.isDeleting = true;
      
      try {
        const response = await fetch(`/invoices/${this.invoiceData.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.closeModal();
          
          if (window.successModal) {
            window.successModal.show('Success', 'Invoice deleted successfully');
          } else {
            alert('Invoice deleted successfully');
          }
          
          setTimeout(() => window.location.reload(), 1500);
        } else {
          alert(data.message || 'Failed to delete invoice');
        }
      } catch (error) {
        console.error('Error deleting invoice:', error);
        alert('An error occurred while deleting the invoice');
      } finally {
        this.isDeleting = false;
      }
    }
  }));
});
</script>
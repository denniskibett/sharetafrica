<!-- EDIT PAYMENT SLIDEOVER MODAL -->
<div x-data="paymentEditModal" x-init="init()">
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

      <form :action="`/payments/${currentPayment?.id}`" method="POST" @submit="validateForm">
        @csrf @method('PUT')
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
          Edit Payment
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
          <!-- Tenant Payer Selection -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant (Payer) *</label>
            <select 
              x-model="formData.tenancy_id"
              name="tenancy_id"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">Select Tenant</option>
              @foreach($users as $user)
                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
              @endforeach
            </select>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Invoice</label>
            <select 
              x-model="formData.invoice_id"
              name="invoice_id"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="">N/A</option>
              @foreach($invoices as $invoice)
                <option value="{{ $invoice['id'] }}">{{ $invoice['label'] }}</option>
              @endforeach
            </select>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount *</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 dark:text-gray-400">$</span>
              </div>
              <input 
                x-model="formData.amount"
                @blur="formatAmount()"
                type="number"
                step="0.01"
                name="amount"
                required
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-8 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                placeholder="0.00"
              />
            </div>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Method *</label>
            <select 
              x-model="formData.payment_method"
              name="payment_method"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >
              <option value="mpesa">Mpesa</option>
              <option value="bank">Bank</option>
              <option value="cash">Cash</option>
            </select>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transaction ID</label>
            <input 
              x-model="formData.transaction_id"
              name="transaction_id"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>
          
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transaction Message</label>
            <textarea 
              x-model="formData.transaction_message"
              name="transaction_message"
              rows="2"
              class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            ></textarea>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Paid To</label>
            <input 
              x-model="formData.paid_to"
              name="paid_to"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payer Name</label>
            <input 
              x-model="formData.payer_name"
              name="payer_name"
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Date *</label>
            <input 
              x-model="formData.payment_datetime"
              @change="updatePaymentMonth()"
              type="datetime-local"
              name="payment_datetime"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Month</label>
            <input 
              x-model="formData.payment_month"
              name="payment_month"
              readonly
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button
            @click="closeModal()"
            type="button"
            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto"
          >
            Update Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('paymentEditModal', () => ({
    isOpen: false,
    currentPayment: null,
    formData: {
      tenancy_id: '',
      invoice_id: '',
      amount: '',
      payment_method: 'mpesa',
      transaction_id: '',
      transaction_message: '',
      paid_to: '',
      payer_name: '',
      payment_datetime: '',
      payment_month: ''
    },
    formErrors: [],
    
    init() {
      window.paymentEditModal = this;
    },
    
    openModal(payment) {
      this.currentPayment = payment;
      this.isOpen = true;
      this.formErrors = [];
      
      if (payment) {
        this.formData = {
          tenancy_id: payment.tenancy_id || '',
          invoice_id: payment.invoice_id || '',
          amount: payment.amount || '',
          payment_method: payment.payment_method || 'mpesa',
          transaction_id: payment.transaction_id || '',
          transaction_message: payment.transaction_message || '',
          paid_to: payment.paid_to || '',
          payer_name: payment.payer_name || '',
          payment_datetime: this.formatDateForInput(payment.payment_datetime),
          payment_month: payment.payment_month || ''
        };
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.currentPayment = null;
      this.formErrors = [];
      document.body.style.overflow = '';
    },
    
    formatDateForInput(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toISOString().slice(0, 16);
    },
    
    updatePaymentMonth() {
      if (this.formData.payment_datetime) {
        try {
          const date = new Date(this.formData.payment_datetime);
          if (!isNaN(date.getTime())) {
            this.formData.payment_month = date.toLocaleDateString('en-US', { 
              month: 'long', 
              year: 'numeric' 
            });
          }
        } catch (e) {
          console.error('Error updating payment month:', e);
        }
      }
    },
    
    formatAmount() {
      if (this.formData.amount) {
        const value = parseFloat(this.formData.amount);
        if (!isNaN(value)) {
          this.formData.amount = value.toFixed(2);
        } else {
          this.formData.amount = '';
        }
      }
    },
    
    validateForm(event) {
      this.formErrors = [];
      
      if (!this.formData.tenancy_id) {
        this.formErrors.push('Please select a tenant');
      }
      
      if (!this.formData.amount || parseFloat(this.formData.amount) <= 0) {
        this.formErrors.push('Please enter a valid amount greater than 0');
      }
      
      if (!this.formData.payment_datetime) {
        this.formErrors.push('Please select a payment date');
      }
      
      if (!this.formData.payment_method) {
        this.formErrors.push('Please select a payment method');
      }
      
      if (this.formErrors.length > 0) {
        event.preventDefault();
        this.$el.scrollTop = 0;
      }
    }
  }));
});
</script>
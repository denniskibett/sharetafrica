<!-- CREATE PAYMENT SLIDEOVER MODAL -->
<div x-data="paymentCreateModal" x-init="init()">
  <!-- Backdrop -->
  <template x-if="isOpen">
    <div 
      @click="closeModal"
      class="fixed inset-0 bg-gray-700/30 backdrop-blur-md backdrop-saturate-150 transition-opacity z-99999"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    ></div>
  </template>

  <!-- Slideover Modal Content - Right Side -->
  <div x-show="isOpen" 
       x-transition:enter="transition ease-out duration-300 transform"
       x-transition:enter-start="translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200 transform"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="translate-x-full"
       x-cloak
       class="fixed inset-y-0 right-0 z-99999 w-full max-w-2xl bg-white shadow-xl dark:bg-gray-900 overflow-y-auto">
    
    <!-- Header -->
    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
      <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">
        Record Payment
      </h4>
      <button
        @click="closeModal"
        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700"
      >
        <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"/>
        </svg>
      </button>
    </div>

    <!-- Form Content -->
    <form @submit.prevent="submitForm" class="p-6 lg:p-8">
      @csrf

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

      <!-- M-Pesa Status (shown during STK Push) -->
      <div x-show="mpesaStatus.show" class="mb-6 rounded-lg p-4 text-sm" :class="mpesaStatus.class">
        <div class="flex items-start">
          <div class="flex-shrink-0" x-html="mpesaStatus.icon"></div>
          <div class="ml-3">
            <p class="font-medium" x-text="mpesaStatus.title"></p>
            <p class="text-sm" x-text="mpesaStatus.message"></p>
            <div x-show="mpesaStatus.progress" class="mt-2">
              <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div class="h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + mpesaStatus.progress + '%'"
                     :class="mpesaStatus.progressClass"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tenancy Details Section -->
      <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
        <h5 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tenancy Details</h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400">Tenant Name:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenancyDetails.tenant_name || '-'"></p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Unit Number:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenancyDetails.unit_number || '-'"></p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Estate:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenancyDetails.estate_name || '-'"></p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Invoice Total:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="formatCurrency(tenancyDetails.total_amount)"></p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Outstanding Balance:</span>
            <p class="font-medium text-red-600 dark:text-red-400" x-text="formatCurrency(tenancyDetails.outstanding_balance)"></p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Billing Month:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="tenancyDetails.billing_month || '-'"></p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        <!-- Invoice Selection -->
        <div class="col-span-2">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Select Invoice *
          </label>
          <select
            x-model="form.invoice_id"
            @change="updateTenancyDetails()"
            required
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          >
            <option value="">Select Invoice</option>
            @foreach($invoices ?? [] as $invoiceItem)
              <option value="{{ $invoiceItem['id'] }}" data-tenant-name="{{ $invoiceItem['payer_name'] ?? 'N/A' }}" data-total-amount="{{ $invoiceItem['total_amount'] ?? 0 }}">
                {{ $invoiceItem['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Amount -->
        <div class="col-span-1">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Payment Amount *
          </label>
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <span class="text-gray-500 dark:text-gray-400">{{ \App\Helpers\SystemHelper::currencySymbol() }}</span>
            </div>
            <input
              type="number"
              step="0.01"
              x-model="form.amount"
              @input="updateOutstandingBalance()"
              required
              class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-12 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="0.00"
            />
          </div>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Outstanding after payment: <span x-text="formatCurrency(outstandingAfterPayment)" class="font-medium"></span>
          </p>
        </div>

        <!-- Payment Method -->
        <div class="col-span-1">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Payment Method *
          </label>
          <select
            x-model="form.payment_method"
            @change="handlePaymentMethodChange()"
            required
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          >
            <option value="mpesa">💳 M-Pesa Paybill (STK Push)</option>
            <option value="cash">💰 Cash</option>
            <option value="bank">🏦 Bank Transfer</option>
          </select>
        </div>

        <!-- M-Pesa Phone Number (shown only for mpesa) -->
        <div class="col-span-1" x-show="form.payment_method === 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            📱 M-Pesa Phone Number *
          </label>
          <input
            type="text"
            x-model="form.mpesa_phone"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="2547XXXXXXXX"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Enter the Safaricom phone number registered with M-Pesa
          </p>
        </div>

        <!-- M-Pesa Instructions -->
        <div class="col-span-2" x-show="form.payment_method === 'mpesa'">
          <div class="rounded-lg bg-blue-50 p-3 text-sm text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
            <strong>📌 How M-Pesa Paybill works:</strong>
            <ul class="mt-1 list-disc pl-5">
              <li>You will receive an STK Push pop-up on your phone</li>
              <li>Enter your M-Pesa PIN to confirm</li>
              <li>Payment will be confirmed automatically</li>
              <li>You will receive a confirmation SMS</li>
            </ul>
          </div>
        </div>

        <!-- Transaction ID (hidden for M-Pesa) -->
        <div class="col-span-1" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Transaction ID
          </label>
          <input
            type="text"
            x-model="form.transaction_id"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="e.g., AC12XYZ"
          />
        </div>

        <!-- Payment Date (hidden for M-Pesa) -->
        <div class="col-span-1" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Payment Date *
          </label>
          <input
            type="datetime-local"
            x-model="form.payment_datetime"
            :required="form.payment_method !== 'mpesa'"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          />
        </div>

        <!-- Payment Month (hidden for M-Pesa) -->
        <div class="col-span-1" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Payment Month *
          </label>
          <input
            type="month"
            x-model="form.payment_month"
            :required="form.payment_method !== 'mpesa'"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
          />
        </div>

        <!-- Paid To (hidden for M-Pesa) -->
        <div class="col-span-1" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Paid To
          </label>
          <input
            type="text"
            x-model="form.paid_to"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="Receiver name"
          />
        </div>

        <!-- Payer Name (hidden for M-Pesa) -->
        <div class="col-span-1" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Payer Name
          </label>
          <input
            type="text"
            x-model="form.payer_name"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="Sender name"
          />
        </div>

        <!-- Transaction Message (hidden for M-Pesa) -->
        <div class="col-span-2" x-show="form.payment_method !== 'mpesa'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Transaction Message
          </label>
          <textarea
            x-model="form.transaction_message"
            rows="3"
            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="Copy and paste M-Pesa message here..."
          ></textarea>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="sticky bottom-0 mt-8 flex items-center justify-end gap-3 border-t border-gray-200 bg-white pt-4 dark:border-gray-700 dark:bg-gray-900">
        <button
          @click="closeModal"
          type="button"
          class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="loading || !form.invoice_id || !form.amount || !form.payment_method || (form.payment_method === 'mpesa' && !form.mpesa_phone)"
          class="flex justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs disabled:opacity-50 disabled:cursor-not-allowed"
          :class="form.payment_method === 'mpesa' ? 'bg-green-600 hover:bg-green-700' : 'bg-brand-500 hover:bg-brand-600'"
        >
          <span x-show="!loading" x-text="form.payment_method === 'mpesa' ? '💳 Pay with M-Pesa' : 'Record Payment'"></span>
          <span x-show="loading">Processing...</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('paymentCreateModal', () => ({
    isOpen: false,
    invoiceData: null,
    tenancyId: null,
    tenancyDetails: {
      tenant_name: '',
      unit_number: '',
      estate_name: '',
      total_amount: 0,
      outstanding_balance: 0,
      billing_month: ''
    },
    form: {
      invoice_id: '',
      amount: '',
      payment_method: 'mpesa',
      mpesa_phone: '',
      transaction_id: '',
      transaction_message: '',
      paid_to: '',
      payer_name: '',
      payment_datetime: new Date().toISOString().slice(0, 16),
      payment_month: new Date().toISOString().slice(0, 7)
    },
    formErrors: [],
    loading: false,
    
    // M-Pesa status tracking
    mpesaStatus: {
      show: false,
      title: '',
      message: '',
      icon: '',
      class: '',
      progress: 0,
      progressClass: '',
      checkoutId: null,
      interval: null,
      attempts: 0,
      maxAttempts: 30
    },
    
    init() {
      window.paymentCreateModal = this;
    },
    
    openModal(data) {
      this.invoiceData = data;
      this.tenancyId = data.tenancy_id;
      this.isOpen = true;
      this.resetForm();
      
      if (data && data.id) {
        this.form.invoice_id = data.id;
        if (data.tenant_name) {
          this.form.payer_name = data.tenant_name;
        }
        if (data.total_amount) {
          this.form.amount = data.total_amount;
        }
        if (data.phone) {
          this.form.mpesa_phone = data.phone;
        }
        
        this.tenancyDetails = {
          tenant_name: data.tenant_name || '',
          unit_number: data.unit_number || '',
          estate_name: data.estate_name || '',
          total_amount: data.total_amount || 0,
          outstanding_balance: data.outstanding_balance || data.total_amount || 0,
          billing_month: data.billing_month || ''
        };
      }
      
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.invoiceData = null;
      this.tenancyId = null;
      this.formErrors = [];
      this.loading = false;
      this.resetMpesaStatus();
      document.body.style.overflow = '';
    },
    
    resetForm() {
      this.form = {
        invoice_id: '',
        amount: '',
        payment_method: 'mpesa',
        mpesa_phone: '',
        transaction_id: '',
        transaction_message: '',
        paid_to: '',
        payer_name: '',
        payment_datetime: new Date().toISOString().slice(0, 16),
        payment_month: new Date().toISOString().slice(0, 7)
      };
      this.formErrors = [];
      this.loading = false;
      this.tenancyDetails = {
        tenant_name: '',
        unit_number: '',
        estate_name: '',
        total_amount: 0,
        outstanding_balance: 0,
        billing_month: ''
      };
      this.resetMpesaStatus();
    },
    
    resetMpesaStatus() {
      if (this.mpesaStatus.interval) {
        clearInterval(this.mpesaStatus.interval);
        this.mpesaStatus.interval = null;
      }
      this.mpesaStatus.show = false;
      this.mpesaStatus.title = '';
      this.mpesaStatus.message = '';
      this.mpesaStatus.icon = '';
      this.mpesaStatus.class = '';
      this.mpesaStatus.progress = 0;
      this.mpesaStatus.progressClass = '';
      this.mpesaStatus.checkoutId = null;
      this.mpesaStatus.attempts = 0;
    },
    
    handlePaymentMethodChange() {
      this.resetMpesaStatus();
    },
    
    updateTenancyDetails() {
      if (this.form.invoice_id) {
        fetch(`/invoices/${this.form.invoice_id}/details`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            this.tenancyDetails = {
              tenant_name: data.tenant_name || '',
              unit_number: data.unit_number || '',
              estate_name: data.estate_name || '',
              total_amount: data.total_amount || 0,
              outstanding_balance: data.outstanding_balance || data.total_amount || 0,
              billing_month: data.billing_month_formatted || ''
            };
            if (!this.form.payer_name || this.form.payer_name === '') {
              this.form.payer_name = this.tenancyDetails.tenant_name;
            }
            if (!this.form.mpesa_phone && data.phone) {
              this.form.mpesa_phone = data.phone;
            }
          }
        })
        .catch(error => console.error('Error fetching invoice details:', error));
      } else {
        this.resetTenancyDetails();
      }
    },
    
    resetTenancyDetails() {
      this.tenancyDetails = {
        tenant_name: '',
        unit_number: '',
        estate_name: '',
        total_amount: 0,
        outstanding_balance: 0,
        billing_month: ''
      };
    },
    
    updateOutstandingBalance() {
      const paymentAmount = parseFloat(this.form.amount) || 0;
      this.tenancyDetails.outstanding_balance = Math.max(0, this.tenancyDetails.total_amount - paymentAmount);
    },
    
    get outstandingAfterPayment() {
      const total = this.tenancyDetails.total_amount || 0;
      const payment = parseFloat(this.form.amount) || 0;
      return Math.max(0, total - payment);
    },
    
    formatCurrency(amount) {
      const symbol = "{{ \App\Helpers\SystemHelper::currencySymbol() }}";
      if (!amount && amount !== 0) return symbol + " 0.00";
      return symbol + " " + parseFloat(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.form.invoice_id) {
        this.formErrors.push('Please select an invoice');
      }
      
      if (!this.form.amount || parseFloat(this.form.amount) <= 0) {
        this.formErrors.push('Please enter a valid amount');
      }
      
      if (parseFloat(this.form.amount) > this.tenancyDetails.total_amount) {
        this.formErrors.push(`Payment amount (${this.formatCurrency(this.form.amount)}) exceeds invoice total (${this.formatCurrency(this.tenancyDetails.total_amount)})`);
      }
      
      if (!this.form.payment_method) {
        this.formErrors.push('Please select payment method');
      }
      
      if (this.form.payment_method === 'mpesa') {
        const phone = this.form.mpesa_phone.replace(/[^0-9]/g, '');
        if (phone.length < 10 || !phone.startsWith('254')) {
          this.formErrors.push('Please enter a valid M-Pesa phone number (format: 2547XXXXXXXX)');
        }
      } else {
        if (!this.form.payment_datetime) {
          this.formErrors.push('Please select payment date');
        }
        if (!this.form.payment_month) {
          this.formErrors.push('Please select payment month');
        }
      }
      
      return this.formErrors.length === 0;
    },
    
    async submitForm() {
      if (!this.validateForm()) {
        const modalContent = document.querySelector('.fixed.inset-y-0.right-0');
        if (modalContent) {
          modalContent.scrollTop = 0;
        }
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(`/payments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            tenancy_id: this.tenancyId,
            invoice_id: this.form.invoice_id,
            amount: this.form.amount,
            payment_method: this.form.payment_method === 'mpesa' ? 'mpesa_paybill' : this.form.payment_method,
            mpesa_phone: this.form.mpesa_phone,
            transaction_id: this.form.transaction_id,
            transaction_message: this.form.transaction_message,
            paid_to: this.form.paid_to,
            payer_name: this.form.payer_name,
            payment_datetime: this.form.payment_datetime,
            payment_month: this.form.payment_month
          })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
          if (data.is_mpesa) {
            this.showMpesaStatus(data.checkout_request_id);
          } else {
            this.closeModal();
            // ✅ NO BROWSER ALERT - Using alert modal
            window.alertModal.showSuccess('Payment Successful', data.message || 'Payment recorded successfully!');
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          }
        } else if (data.requires_confirmation) {
          this.loading = false;
          // ✅ NO BROWSER CONFIRM - Using alert modal
          this.showInsufficientBalanceConfirmation(data);
        } else {
          // ✅ NO BROWSER ALERT - Using alert modal for errors
          if (data.message) {
            window.alertModal.showError('Payment Failed', data.message);
          }
          this.formErrors = [data.message || 'Failed to record payment'];
          const modalContent = document.querySelector('.fixed.inset-y-0.right-0');
          if (modalContent) {
            modalContent.scrollTop = 0;
          }
          this.loading = false;
        }
      } catch (error) {
        console.error('Error:', error);
        // ✅ NO BROWSER ALERT - Using alert modal
        window.alertModal.showError('Error', 'An error occurred. Please try again.');
        this.formErrors = ['An error occurred. Please try again.'];
        this.loading = false;
      }
    },
    
    showInsufficientBalanceConfirmation(data) {
      const currency = data.data?.currency || 'KSh ';
      const formattedBalance = data.data?.formatted_balance || currency + '0.00';
      const formattedAmount = data.data?.formatted_amount || currency + '0.00';
      const tenantName = data.data?.tenant_name || 'Tenant';
      
      // ✅ NO BROWSER CONFIRM - Using alert modal
      window.alertModal.show(
        'warning',
        '⚠️ Insufficient Wallet Balance',
        `Payment amount (${formattedAmount}) exceeds wallet balance (${formattedBalance}). ${tenantName} will need to top up their wallet. Continue?`,
        [],
        {
          showCancelButton: true,
          confirmButtonText: 'Yes, Continue',
          cancelButtonText: 'Cancel',
          onConfirm: () => {
            this.proceedWithPayment();
          },
          onCancel: () => {
            this.loading = false;
          }
        }
      );
    },
    
    async proceedWithPayment() {
      this.loading = true;
      
      try {
        const response = await fetch(`/payments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            tenancy_id: this.tenancyId,
            invoice_id: this.form.invoice_id,
            amount: this.form.amount,
            payment_method: this.form.payment_method === 'mpesa' ? 'mpesa_paybill' : this.form.payment_method,
            mpesa_phone: this.form.mpesa_phone,
            transaction_id: this.form.transaction_id,
            transaction_message: this.form.transaction_message,
            paid_to: this.form.paid_to,
            payer_name: this.form.payer_name,
            payment_datetime: this.form.payment_datetime,
            payment_month: this.form.payment_month,
            confirm_insufficient_balance: true
          })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
          if (data.is_mpesa) {
            this.showMpesaStatus(data.checkout_request_id);
          } else {
            this.closeModal();
            // ✅ NO BROWSER ALERT - Using alert modal
            window.alertModal.showSuccess('Payment Successful', data.message || 'Payment recorded successfully!');
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          }
        } else {
          // ✅ NO BROWSER ALERT - Using alert modal
          if (data.message) {
            window.alertModal.showError('Payment Failed', data.message);
          }
          this.formErrors = [data.message || 'Failed to record payment'];
          this.loading = false;
        }
      } catch (error) {
        console.error('Error:', error);
        // ✅ NO BROWSER ALERT - Using alert modal
        window.alertModal.showError('Error', 'An error occurred. Please try again.');
        this.formErrors = ['An error occurred. Please try again.'];
        this.loading = false;
      }
    },
    
    showMpesaStatus(checkoutId) {
      this.mpesaStatus.show = true;
      this.mpesaStatus.checkoutId = checkoutId;
      this.mpesaStatus.title = '📱 STK Push Sent!';
      this.mpesaStatus.message = `Please check your phone (${this.form.mpesa_phone}) and enter your M-Pesa PIN.`;
      this.mpesaStatus.icon = `<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
      this.mpesaStatus.class = 'bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
      this.mpesaStatus.progress = 0;
      this.mpesaStatus.progressClass = 'bg-blue-600';
      this.mpesaStatus.attempts = 0;
      
      this.loading = false;
      this.startPolling();
    },
    
    startPolling() {
      if (this.mpesaStatus.interval) {
        clearInterval(this.mpesaStatus.interval);
      }
      
      this.mpesaStatus.interval = setInterval(() => {
        this.mpesaStatus.attempts++;
        this.mpesaStatus.progress = Math.min((this.mpesaStatus.attempts / this.mpesaStatus.maxAttempts) * 100, 95);
        
        fetch(`/payments/mpesa/status?checkout_request_id=${this.mpesaStatus.checkoutId}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const status = data.status || 'pending';
            
            if (status === '0' || status === 'completed' || status === 'success') {
              clearInterval(this.mpesaStatus.interval);
              this.mpesaStatus.interval = null;
              this.mpesaStatus.progress = 100;
              this.mpesaStatus.progressClass = 'bg-green-600';
              this.mpesaStatus.title = '✅ Payment Successful!';
              this.mpesaStatus.message = `Your payment has been confirmed. Receipt: ${data.data?.ReceiptNumber || 'N/A'}`;
              this.mpesaStatus.icon = `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
              this.mpesaStatus.class = 'bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-400';
              
              setTimeout(() => {
                this.closeModal();
                window.location.reload();
              }, 3000);
              
            } else if (status === '1' || status === 'failed' || status === 'error') {
              clearInterval(this.mpesaStatus.interval);
              this.mpesaStatus.interval = null;
              this.mpesaStatus.title = '❌ Payment Failed';
              this.mpesaStatus.message = data.message || 'Transaction failed. Please try again.';
              this.mpesaStatus.icon = `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
              this.mpesaStatus.class = 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-400';
              this.loading = false;
            }
          }
        })
        .catch(() => {});
        
        if (this.mpesaStatus.attempts >= this.mpesaStatus.maxAttempts) {
          clearInterval(this.mpesaStatus.interval);
          this.mpesaStatus.interval = null;
          this.mpesaStatus.title = '⏳ Payment Still Processing';
          this.mpesaStatus.message = 'Please check your M-Pesa app for status.';
          this.mpesaStatus.icon = `<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
          this.mpesaStatus.class = 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        }
      }, 5000);
    }
  }));
});
</script>
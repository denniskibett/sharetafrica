<!-- PAYMENT MODAL (Create & Edit combined) - ACCOUNTANT DIRECT PAYMENT -->
<div x-data="paymentModal" x-init="init()">
  <!-- Backdrop -->
  <template x-if="isOpen">
    <div 
      @click="closeModal"
      class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"
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
       class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
       style="width: 42rem; max-width: calc(100% - 2rem);">    
    
    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
      <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="isEditMode ? 'Edit Payment' : 'Record Payment'"></h4>
      <button
        @click="closeModal"
        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700"
      >
        <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C6.65237 16.9318 6.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"/>
        </svg>
      </button>
    </div>

    <form @submit.prevent="submitForm" class="p-6 lg:p-8">
      @csrf
      <input type="hidden" name="_method" x-model="formMethod">

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

      <template x-if="successMessage">
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400">
          <p x-text="successMessage"></p>
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

      <!-- Invoice Summary (when opened from invoice) -->
      <div x-show="preSelectedInvoice" class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <h5 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-400">📄 Invoice Details</h5>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <span class="text-gray-600 dark:text-gray-400">Invoice #:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="preSelectedInvoice?.invoice_number"></p>
          </div>
          <div>
            <span class="text-gray-600 dark:text-gray-400">Tenant:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="preSelectedInvoice?.tenant_name"></p>
          </div>
          <div>
            <span class="text-gray-600 dark:text-gray-400">Unit:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="preSelectedInvoice?.unit_number"></p>
          </div>
          <div>
            <span class="text-gray-600 dark:text-gray-400">Billing Month:</span>
            <p class="font-medium text-gray-800 dark:text-white/90" x-text="preSelectedInvoice?.billing_month_formatted"></p>
          </div>
          <div class="col-span-2">
            <span class="text-gray-600 dark:text-gray-400">Remaining Amount:</span>
            <p class="font-semibold text-brand-600 dark:text-brand-400" x-text="formatCurrency(preSelectedInvoice?.remaining_amount)"></p>
          </div>
        </div>
        
        <!-- Invoice Items Breakdown -->
        <div x-show="preSelectedInvoice?.items?.length > 0" class="mt-3 pt-2 border-t border-blue-200 dark:border-blue-800">
          <p class="text-xs font-semibold text-blue-800 dark:text-blue-400 mb-2">Invoice Items:</p>
          <div class="space-y-1">
            <template x-for="item in preSelectedInvoice?.items" :key="item.id">
              <div class="flex justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400" x-text="item.description"></span>
                <span class="font-medium text-gray-800 dark:text-white/90" x-text="formatCurrency(item.amount)"></span>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Tenant Selection (hidden when invoice pre-selected) -->
      <div class="mb-5" x-show="!preSelectedInvoice">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Select Tenant *</label>
        <select
          x-model="form.tenant_id"
          @change="loadTenantInvoices(); loadWalletBalance()"
          :required="!preSelectedInvoice"
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
        >
          <option value="">Select Tenant</option>
          @foreach($tenants ?? [] as $tenant)
            <option value="{{ $tenant['id'] }}" data-unit="{{ $tenant['unit_number'] }}">{{ $tenant['name'] }} ({{ $tenant['unit_number'] ?? 'No Unit' }})</option>
          @endforeach
        </select>
      </div>

      <!-- Wallet Balance Display - Shows Bavix Wallet Balance -->
      <div x-show="walletBalance !== null && !walletLoading" class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-blue-700 dark:text-blue-400">💰 Wallet Balance (Bavix)</span>
            <p class="text-lg font-bold text-blue-800 dark:text-blue-300" x-text="formatCurrency(walletBalance)"></p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
              <span x-show="walletBalance > 0">Available for payment</span>
              <span x-show="walletBalance === 0">No funds available</span>
            </p>
          </div>
          <div class="text-right">
            <button 
              @click="useWalletBalance()" 
              class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-800/50 dark:hover:bg-blue-800 transition-colors"
              x-show="walletBalance > 0 && selectedInvoiceRemaining > 0"
            >
              Use Balance
            </button>
            <p class="text-xs text-gray-500 mt-1" x-show="walletBalance > 0 && selectedInvoiceRemaining > 0">
              Will pay KES <span x-text="formatNumber(Math.min(walletBalance, selectedInvoiceRemaining))"></span>
            </p>
          </div>
        </div>
        <div class="mt-2 text-xs text-blue-600 dark:text-blue-400">
          <span x-show="walletBalance >= selectedInvoiceRemaining && selectedInvoiceRemaining > 0">✅ Sufficient balance to pay invoice in full</span>
          <span x-show="walletBalance > 0 && walletBalance < selectedInvoiceRemaining">⚠️ Partial balance - <span x-text="formatCurrency(selectedInvoiceRemaining - walletBalance)"></span> additional required</span>
          <span x-show="walletBalance === 0">ℹ️ No wallet balance available - manual payment required</span>
          <span x-show="selectedInvoiceRemaining === 0">✅ Invoice is fully paid</span>
        </div>
      </div>

      <!-- Loading State for Wallet -->
      <div x-show="walletLoading" class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-500 border-t-transparent"></div>
          <span class="text-sm text-gray-500">Loading wallet balance...</span>
        </div>
      </div>

      <!-- Invoice Selection (hidden when invoice pre-selected) -->
      <div class="mb-5" x-show="!preSelectedInvoice && tenantInvoices.length > 0">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Select Invoice to Pay *</label>
        <select
          x-model="form.invoice_id"
          @change="selectInvoiceForPayment(); loadWalletBalance()"
          :required="!preSelectedInvoice"
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
        >
          <option value="">Select Invoice</option>
          <template x-for="invoice in tenantInvoices" :key="invoice.id">
            <option :value="invoice.id" x-text="invoice.invoice_number + ' - ' + formatCurrency(invoice.remaining_amount) + ' (' + invoice.status + ')'"></option>
          </template>
        </select>
      </div>

      <!-- Hidden inputs for pre-selected invoice values -->
      <template x-if="preSelectedInvoice">
        <input type="hidden" x-model="form.tenant_id">
      </template>
      
      <template x-if="preSelectedInvoice">
        <input type="hidden" x-model="form.invoice_id">
      </template>

      <div class="mb-5" x-show="!preSelectedInvoice && tenantInvoices.length === 0 && form.tenant_id">
        <div class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
          No pending invoices found for this tenant.
        </div>
      </div>

      <!-- Amount -->
      <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Amount *</label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <span class="text-gray-500 dark:text-gray-400">{{ \App\Helpers\SystemHelper::currencySymbol() }}</span>
          </div>
          <input
            type="number"
            step="0.01"
            x-model="form.amount"
            min="0.01"
            required
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-12 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            placeholder="0.00"
          />
        </div>
        <div class="flex justify-between mt-1">
          <p class="text-xs text-gray-500">
            Invoice due: <span x-text="formatCurrency(selectedInvoiceRemaining)"></span>
            <span x-show="parseFloat(form.amount) > selectedInvoiceRemaining" class="text-green-600 ml-2">
              Excess will be added to wallet
            </span>
          </p>
          <div class="flex items-center gap-2">
            <button 
              @click="setAmountByPercentage(100)" 
              class="text-xs px-2 py-0.5 rounded bg-brand-100 text-brand-700 hover:bg-brand-200 dark:bg-brand-900/30 dark:text-brand-400 dark:hover:bg-brand-900/50 transition-colors"
              x-show="selectedInvoiceRemaining > 0"
            >
              Full
            </button>
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Method *</label>
        <select
          x-model="form.payment_method"
          @change="handlePaymentMethodChange()"
          required
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
        >
          <option value="cash">💰 Cash</option>
          <option value="bank_transfer">🏦 Bank Transfer</option>
          <option value="mpesa_paybill">📱 M-Pesa Paybill (STK Push)</option>
          <option value="manual_topup">📝 Manual Top-up</option>
        </select>
      </div>

      <!-- M-Pesa Phone Field (shown only when mpesa_paybill is selected) -->
      <div x-show="form.payment_method === 'mpesa_paybill'" class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
          📱 M-Pesa Phone Number *
        </label>
        <input
          type="text"
          x-model="form.mpesa_phone"
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
          placeholder="2547XXXXXXXX"
        />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Enter the Safaricom phone number registered with M-Pesa
        </p>
      </div>

      <!-- M-Pesa Instructions -->
      <div x-show="form.payment_method === 'mpesa_paybill'" class="mb-5">
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

      <!-- External Reference -->
      <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Reference / Receipt Number</label>
        <input
          type="text"
          x-model="form.external_reference"
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
          placeholder="Receipt number, M-Pesa code, or Bank transaction ID"
        />
      </div>

      <!-- Payment Date -->
      <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Date *</label>
        <input
          type="datetime-local"
          x-model="form.payment_datetime"
          required
          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
        />
      </div>

      <!-- Payment Month - Hidden field always sent -->
      <input type="hidden" x-model="form.payment_month">

      <!-- Notes -->
      <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes (Optional)</label>
        <textarea
          x-model="form.notes"
          rows="3"
          class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
          placeholder="Additional notes about this payment..."
        ></textarea>
      </div>

      <!-- Summary -->
      <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Payment Summary</h5>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Total Payment:</span>
            <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="formatCurrency(form.amount)"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Invoice Amount Due:</span>
            <span class="text-gray-800 dark:text-white/90" x-text="formatCurrency(selectedInvoiceRemaining)"></span>
          </div>
          
          <!-- Show excess calculation -->
          <template x-if="parseFloat(form.amount) > selectedInvoiceRemaining">
            <div class="flex justify-between text-green-600">
              <span>Excess (Added to Wallet):</span>
              <span class="font-semibold" x-text="formatCurrency(parseFloat(form.amount) - selectedInvoiceRemaining)"></span>
            </div>
          </template>
          
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
            <span class="text-gray-800 dark:text-white/90" x-text="getPaymentMethodLabel(form.payment_method)"></span>
          </div>
          <div class="flex justify-between" x-show="form.external_reference">
            <span class="text-gray-600 dark:text-gray-400">Reference:</span>
            <span class="text-gray-800 dark:text-white/90 font-mono text-xs" x-text="form.external_reference"></span>
          </div>
          
          <!-- Wallet Impact Summary -->
          <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
            <div class="flex justify-between">
              <span class="text-gray-700 dark:text-gray-300">Invoice Status After:</span>
              <span x-show="selectedInvoiceRemaining <= parseFloat(form.amount)" class="text-green-600">Fully Paid</span>
              <span x-show="selectedInvoiceRemaining > parseFloat(form.amount)" class="text-yellow-600">Partially Paid</span>
              <span x-show="selectedInvoiceRemaining === 0" class="text-green-600">Already Paid</span>
            </div>
            <div class="flex justify-between mt-1" x-show="parseFloat(form.amount) > selectedInvoiceRemaining && selectedInvoiceRemaining > 0">
              <span class="text-gray-700 dark:text-gray-300">Excess to Wallet:</span>
              <span class="text-green-600" x-text="formatCurrency(parseFloat(form.amount) - selectedInvoiceRemaining)"></span>
            </div>
            <div class="flex justify-between mt-1" x-show="walletBalance !== null">
              <span class="text-gray-700 dark:text-gray-300">Wallet Balance After:</span>
              <span class="font-semibold" :class="walletBalance - parseFloat(form.amount) >= 0 ? 'text-green-600' : 'text-red-600'">
                <span x-text="formatCurrency(walletBalance - parseFloat(form.amount))"></span>
                <span x-show="walletBalance - parseFloat(form.amount) < 0" class="text-xs text-red-500 ml-1">(Insufficient)</span>
                <span x-show="parseFloat(form.amount) > selectedInvoiceRemaining && selectedInvoiceRemaining > 0" class="text-green-500 ml-1">(+ excess added)</span>
              </span>
            </div>
          </div>
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
          :disabled="loading || !isFormValid || (form.payment_method === 'mpesa_paybill' && !form.mpesa_phone)"
          class="flex justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs disabled:opacity-50 disabled:cursor-not-allowed"
          :class="form.payment_method === 'mpesa_paybill' ? 'bg-green-600 hover:bg-green-700' : 'bg-brand-500 hover:bg-brand-600'"
        >
          <span x-show="!loading" x-text="form.payment_method === 'mpesa_paybill' ? '💳 Pay with M-Pesa' : 'Process Payment'"></span>
          <span x-show="loading">Processing...</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('paymentModal', () => ({
    isOpen: false,
    isEditMode: false,
    isTenant: {{ auth()->user()->hasRole('tenant') ? 'true' : 'false' }},
    currentPaymentId: null,
    preSelectedInvoice: null,
    selectedInvoiceData: null,
    tenantInvoices: [],
    walletBalance: null,
    walletLoading: false,
    form: {
      tenant_id: '',
      invoice_id: '',
      amount: '',
      payment_method: 'cash',
      mpesa_phone: '',
      external_reference: '',
      payment_datetime: new Date().toISOString().slice(0, 16),
      payment_month: new Date().toISOString().slice(0, 7),
      notes: ''
    },
    formMethod: 'POST',
    formErrors: [],
    successMessage: '',
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
    
    get maxAmount() {
      return this.selectedInvoiceData?.remaining_amount || 0;
    },
    
    get selectedInvoiceRemaining() {
      return this.selectedInvoiceData?.remaining_amount || 0;
    },
    
    get isFormValid() {
      if (!this.preSelectedInvoice) {
        if (!this.form.tenant_id) return false;
        if (!this.form.invoice_id) return false;
      }
      if (!this.form.amount || parseFloat(this.form.amount) <= 0) return false;
      if (!this.form.payment_datetime) return false;
      if (!this.form.payment_method) return false;
      if (this.form.payment_method === 'mpesa_paybill' && !this.form.mpesa_phone) return false;
      return true;
    },
    
    init() {
      window.paymentCreateModal = this;
      console.log('Payment modal initialized');
      
      window.dispatchEvent(new CustomEvent('paymentModalReady'));
      
      window.addEventListener('open-payment-modal', (event) => {
        console.log('Received open-payment-modal event:', event.detail);
        if (event.detail && event.detail.invoice) {
          this.openPaymentModalForInvoice(event.detail.invoice);
        }
      });
      
      document.addEventListener('open-payment-modal', (event) => {
        console.log('Received document open-payment-modal event:', event.detail);
        if (event.detail && event.detail.invoice) {
          this.openPaymentModalForInvoice(event.detail.invoice);
        }
      });
    },
    
    openCreateModal() {
      this.isEditMode = false;
      this.currentPaymentId = null;
      this.preSelectedInvoice = null;
      this.selectedInvoiceData = null;
      this.formMethod = 'POST';
      this.walletBalance = null;
      this.resetForm();
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    openPaymentModalForInvoice(invoice) {
      console.log('=== openPaymentModalForInvoice called ===');
      console.log('Received invoice data:', invoice);
      
      if (!invoice || !invoice.id) {
        console.error('Invalid invoice data:', invoice);
        alert('Could not process payment: Invalid invoice data');
        return;
      }
      
      this.isEditMode = false;
      this.currentPaymentId = null;
      this.preSelectedInvoice = invoice;
      this.selectedInvoiceData = invoice;
      this.formMethod = 'POST';
      
      this.form.tenant_id = invoice.tenant_id;
      this.form.invoice_id = invoice.id;
      this.form.amount = invoice.remaining_amount || invoice.total_amount || 0;
      this.form.payment_datetime = new Date().toISOString().slice(0, 16);
      this.form.payment_month = new Date().toISOString().slice(0, 7);
      this.form.external_reference = '';
      this.form.notes = '';
      this.form.payment_method = 'cash';
      this.form.mpesa_phone = invoice.phone || '';
      
      console.log('Form populated:', this.form);
      
      // Load wallet balance for the tenant from Bavix
      if (invoice.tenant_id) {
        this.loadWalletBalance(invoice.tenant_id);
      }
      
      if (invoice.id) {
        this.fetchInvoiceDetails(invoice.id);
      }
      
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },

    async fetchInvoiceDetails(invoiceId) {
      console.log('Fetching invoice details for ID:', invoiceId);
      
      if (!invoiceId) {
        console.error('No invoice ID provided for fetching details');
        return;
      }
      
      try {
        const response = await fetch(`/invoices/${invoiceId}/details`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        console.log('Fetch response status:', response.status);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('Invoice details response:', data);
        
        if (data.success) {
          this.selectedInvoiceData = data.invoice;
          this.form.tenant_id = data.invoice.tenant_id;
          this.form.invoice_id = data.invoice.id;
          this.form.amount = data.invoice.remaining_amount;
          
          if (data.invoice.billing_month) {
            this.form.payment_month = data.invoice.billing_month.slice(0, 7);
          }
          
          if (this.preSelectedInvoice) {
            this.preSelectedInvoice = {
              ...this.preSelectedInvoice,
              ...data.invoice
            };
          }
          
          if (data.invoice.phone) {
            this.form.mpesa_phone = data.invoice.phone;
          }
          
          // Load wallet balance for the tenant from Bavix
          if (data.invoice.tenant_id) {
            this.loadWalletBalance(data.invoice.tenant_id);
          }
        } else {
          console.warn('Failed to fetch invoice details:', data.error);
        }
      } catch (error) {
        console.error('Error fetching invoice details:', error);
        this.formErrors = ['Could not load invoice details. Please try again.'];
      }
    },
    
    openEditModal(payment) {
      this.isEditMode = true;
      this.currentPaymentId = payment.id;
      this.formMethod = 'PUT';
      this.form = {
        tenant_id: payment.tenant_id || '',
        invoice_id: payment.invoice_id || '',
        amount: payment.amount || '',
        payment_method: payment.payment_method || 'cash',
        mpesa_phone: '',
        external_reference: payment.external_reference || '',
        payment_datetime: payment.payment_datetime || payment.created_at ? new Date(payment.created_at).toISOString().slice(0, 16) : new Date().toISOString().slice(0, 16),
        payment_month: payment.billing_month ? payment.billing_month.slice(0, 7) : new Date().toISOString().slice(0, 7),
        notes: payment.meta?.notes || ''
      };
      this.walletBalance = null;
      if (payment.tenant_id) {
        this.loadWalletBalance(payment.tenant_id);
      }
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.isEditMode = false;
      this.currentPaymentId = null;
      this.preSelectedInvoice = null;
      this.selectedInvoiceData = null;
      this.formErrors = [];
      this.successMessage = '';
      this.loading = false;
      this.walletBalance = null;
      this.resetMpesaStatus();
      document.body.style.overflow = '';
    },
    
    resetForm() {
      this.form = {
        tenant_id: '',
        invoice_id: '',
        amount: '',
        payment_method: 'cash',
        mpesa_phone: '',
        external_reference: '',
        payment_datetime: new Date().toISOString().slice(0, 16),
        payment_month: new Date().toISOString().slice(0, 7),
        notes: ''
      };
      this.tenantInvoices = [];
      this.selectedInvoiceData = null;
      this.formErrors = [];
      this.successMessage = '';
      this.walletBalance = null;
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
    
    // ================================================================
    // WALLET BALANCE METHODS - Using Bavix
    // ================================================================
    
    async loadWalletBalance(tenantId = null) {
      const id = tenantId || this.form.tenant_id;
      if (!id) {
        this.walletBalance = null;
        return;
      }
      
      this.walletLoading = true;
      
      try {
        // Call the Bavix wallet balance endpoint with tenant_id
        const response = await fetch(`/api/wallet/balance?tenant_id=${id}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        const data = await response.json();
        console.log('Wallet balance response:', data);
        
        if (data.success) {
          this.walletBalance = data.balance || 0;
          console.log('Wallet balance loaded from Bavix:', this.walletBalance);
        } else {
          this.walletBalance = 0;
          console.warn('Failed to load wallet balance:', data.error);
        }
      } catch (error) {
        console.error('Error loading wallet balance:', error);
        this.walletBalance = 0;
      } finally {
        this.walletLoading = false;
      }
    },
    
    useWalletBalance() {
      if (this.walletBalance === null || this.walletBalance <= 0) {
        return;
      }
      
      // Set the amount to the minimum of wallet balance and invoice remaining
      const amount = Math.min(this.walletBalance, this.selectedInvoiceRemaining);
      this.form.amount = amount > 0 ? amount.toFixed(2) : '';
      
      // Show feedback
      if (this.walletBalance >= this.selectedInvoiceRemaining) {
        this.showToast('success', '✅ Using wallet balance to pay invoice in full');
      } else if (this.walletBalance > 0) {
        this.showToast('info', `💰 Using KES ${this.formatNumber(this.walletBalance)} from wallet balance. Additional KES ${this.formatNumber(this.selectedInvoiceRemaining - this.walletBalance)} required.`);
      }
    },
    
    setAmountByPercentage(percentage) {
      if (!this.selectedInvoiceRemaining || this.selectedInvoiceRemaining <= 0) {
        return;
      }
      
      const amount = (this.selectedInvoiceRemaining * percentage) / 100;
      this.form.amount = amount > 0 ? amount.toFixed(2) : '';
    },
    
    showToast(type, message) {
      const toast = document.createElement('div');
      const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
      };
      toast.className = `fixed bottom-4 right-4 z-50 rounded-lg px-4 py-3 text-white text-sm shadow-lg transition-all duration-500 transform translate-y-0 ${colors[type] || 'bg-gray-700'}`;
      toast.innerText = message;
      document.body.appendChild(toast);
      setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-8');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    },
    
    // ================================================================
    // END WALLET BALANCE METHODS
    // ================================================================
    
    async loadTenantInvoices() {
      if (!this.form.tenant_id) {
        this.tenantInvoices = [];
        return;
      }
      
      try {
        const response = await fetch(`/payments/tenant/${this.form.tenant_id}/invoices`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        const data = await response.json();
        if (data.success) {
          this.tenantInvoices = data.invoices;
        }
      } catch (error) {
        console.error('Error loading tenant invoices:', error);
      }
    },
    
    selectInvoiceForPayment() {
      const selected = this.tenantInvoices.find(inv => inv.id == this.form.invoice_id);
      if (selected) {
        this.selectedInvoiceData = selected;
        this.form.amount = selected.remaining_amount;
        if (selected.billing_month) {
          this.form.payment_month = selected.billing_month.slice(0, 7);
        }
        // Reload wallet balance for the selected invoice's tenant
        if (selected.tenant_id) {
          this.loadWalletBalance(selected.tenant_id);
        }
      }
    },
    
    getPaymentMethodLabel(method) {
      const labels = {
        'cash': 'Cash',
        'bank_transfer': 'Bank Transfer',
        'mpesa_paybill': 'M-Pesa Paybill (STK Push)',
        'manual_topup': 'Manual Top-up'
      };
      return labels[method] || method;
    },
    
    formatCurrency(amount) {
      const symbol = "{{ \App\Helpers\SystemHelper::currencySymbol() ?? 'KES ' }}";
      if (!amount && amount !== 0) return symbol + "0.00";
      return symbol + parseFloat(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    
    formatNumber(value) {
      if (!value && value !== 0) return '0.00';
      return parseFloat(value).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
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
              
              // Trigger background refresh
              this.triggerRefresh();
              
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
    },
    
    // Background refresh function
    triggerRefresh() {
      // Dispatch events to refresh wallet balance
      const refreshEvent = new CustomEvent('wallet-refresh', {
        detail: { source: 'payment_modal' }
      });
      window.dispatchEvent(refreshEvent);
      
      // Dispatch event to refresh invoices table
      const invoiceRefreshEvent = new CustomEvent('invoice-refresh', {
        detail: { source: 'payment_modal' }
      });
      window.dispatchEvent(invoiceRefreshEvent);
      
      // Dispatch event to refresh dashboard cards
      const dashboardRefreshEvent = new CustomEvent('dashboard-refresh', {
        detail: { source: 'payment_modal' }
      });
      window.dispatchEvent(dashboardRefreshEvent);
      
      // Also dispatch specific wallet-updated event
      const walletUpdateEvent = new CustomEvent('wallet-updated', {
        detail: { 
          new_balance: this.walletBalance || 0,
          source: 'payment_modal'
        }
      });
      window.dispatchEvent(walletUpdateEvent);
      
      // Close modal after a short delay
      setTimeout(() => {
        this.closeModal();
      }, 1500);
    },
    
    async submitForm() {
      this.formErrors = [];
      this.successMessage = '';
      
      const tenantId = this.preSelectedInvoice?.tenant_id || this.form.tenant_id;
      const invoiceId = this.preSelectedInvoice?.id || this.form.invoice_id;
      
      if (!tenantId) {
        this.formErrors.push('Please select a tenant');
        return;
      }
      
      if (!invoiceId) {
        this.formErrors.push('Please select an invoice to pay');
        return;
      }
      
      if (!this.form.amount || parseFloat(this.form.amount) <= 0) {
        this.formErrors.push('Please enter a valid amount');
        return;
      }
      
      if (this.form.payment_method === 'mpesa_paybill') {
        const phone = this.form.mpesa_phone?.replace(/[^0-9]/g, '');
        if (!phone || phone.length < 10 || !phone.startsWith('254')) {
          this.formErrors.push('Please enter a valid M-Pesa phone number (format: 2547XXXXXXXX)');
          return;
        }
      }
      
      // Check if amount exceeds wallet balance (warning only)
      if (this.walletBalance !== null && parseFloat(this.form.amount) > this.walletBalance) {
        const proceed = await new Promise((resolve) => {
          window.alertModal.show(
            'warning',
            '⚠️ Insufficient Wallet Balance',
            `Payment amount (${this.formatCurrency(this.form.amount)}) exceeds wallet balance (${this.formatCurrency(this.walletBalance)}). The tenant will need to top up their wallet. Continue?`,
            [],
            {
              showCancelButton: true,
              confirmButtonText: 'Yes, Continue',
              cancelButtonText: 'Cancel',
              onConfirm: () => resolve(true),
              onCancel: () => resolve(false)
            }
          );
        });
        
        if (!proceed) {
          return;
        }
      }

      if (parseFloat(this.form.amount) > this.selectedInvoiceRemaining) {
        const excess = parseFloat(this.form.amount) - this.selectedInvoiceRemaining;
        if (excess > 0) {
          const proceed = await new Promise((resolve) => {
            window.alertModal.show(
              'info',
              '⚠️ Excess Payment',
              `Amount exceeds invoice due by ${this.formatCurrency(excess)}. This excess will be added to the tenant's wallet balance. Continue?`,
              [],
              {
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel',
                onConfirm: () => resolve(true),
                onCancel: () => resolve(false)
              }
            );
          });
          
          if (!proceed) {
            return;
          }
        }
      }
      
      if (!this.form.payment_datetime) {
        this.formErrors.push('Please select payment date');
        return;
      }
      
      if (this.form.payment_method === 'cash' && !this.form.payment_month) {
        this.formErrors.push('Please select payment month');
        return;
      }
      
      if (!this.form.payment_method) {
        this.formErrors.push('Please select payment method');
        return;
      }
      
      this.loading = true;
      
      try {
        let url, method, body;
        
        if (this.isEditMode) {
          url = `/payments/${this.currentPaymentId}`;
          method = 'PUT';
          body = JSON.stringify({
            status: this.form.status,
            is_reconciled: this.form.status === 'completed' ? 1 : 0,
            notes: this.form.notes
          });
        } else {
          url = '/payments';
          method = 'POST';
          body = JSON.stringify({
            tenant_id: tenantId,
            invoice_id: invoiceId,
            amount: parseFloat(this.form.amount),
            payment_method: this.form.payment_method === 'mpesa_paybill' ? 'mpesa_paybill' : this.form.payment_method,
            mpesa_phone: this.form.mpesa_phone,
            external_reference: this.form.external_reference,
            payment_datetime: this.form.payment_datetime,
            payment_month: this.form.payment_month,
            notes: this.form.notes,
            wallet_balance_at_payment: this.walletBalance
          });
        }
        
        console.log('Sending payment request:', { url, method, body });
        
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
          },
          body: body
        });
        
        const data = await response.json();
        console.log('Payment response:', data);
        
        if (response.ok && data.success) {
          if (data.is_mpesa) {
            this.showMpesaStatus(data.checkout_request_id);
          } else {
            const amountPaid = data.data?.amount_paid_to_invoice || parseFloat(this.form.amount);
            const amountToWallet = data.data?.amount_added_to_wallet || 0;
            const newBalance = data.data?.wallet_balance || 0;
            
            let successMsg = '';
            if (amountToWallet > 0 && amountPaid > 0) {
              successMsg = `Payment successful! KES ${this.formatNumber(amountPaid)} paid to invoice, KES ${this.formatNumber(amountToWallet)} added to wallet. New balance: KES ${this.formatNumber(newBalance)}`;
            } else if (amountPaid > 0) {
              successMsg = `Payment successful! KES ${this.formatNumber(amountPaid)} paid to invoice.`;
            } else {
              successMsg = `KES ${this.formatNumber(amountToWallet)} added to wallet balance. New balance: KES ${this.formatNumber(newBalance)}`;
            }
            
            this.successMessage = successMsg;
            
            // Update wallet balance in the modal from Bavix
            if (newBalance !== undefined) {
              this.walletBalance = newBalance;
            }
            
            const paymentEvent = new CustomEvent('payment-success', {
              detail: {
                invoice_id: invoiceId,
                tenant_id: tenantId,
                amount_paid: amountPaid,
                amount_to_wallet: amountToWallet,
                payment_id: data.data?.payment_id || data.data?.id,
                wallet_balance: newBalance,
                new_status: data.data?.invoice_status || 'paid'
              }
            });
            
            window.dispatchEvent(paymentEvent);
            document.dispatchEvent(paymentEvent);
            
            setTimeout(() => {
              this.closeModal();
              this.showToast('success', successMsg);
            }, 1500);
          }
        } else {
          this.formErrors = [data.message || data.error || 'Failed to process payment'];
          this.loading = false;
        }
      } catch (error) {
        console.error('Error:', error);
        this.formErrors = ['An error occurred. Please try again.'];
        this.loading = false;
      }
    }
  }));
});
</script>
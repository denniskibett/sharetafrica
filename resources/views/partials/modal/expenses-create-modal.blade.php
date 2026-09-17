<!-- CREATE EXPENSE SLIDEOVER MODAL -->
<div x-data="expenseCreateModal" x-init="init()">
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
      <button @click="closeModal()" class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <form @submit.prevent="submitForm()">
        @csrf
        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Add New Expense</h4>

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
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estate *</label>
            <select x-model="formData.estate_id" name="estate_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
              <option value="">Select Estate</option>
              @foreach($estates ?? [] as $estate)
                <option value="{{ $estate->id }}">{{ $estate->name }}</option>
              @endforeach
            </select>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payee *</label>
            <select x-model="formData.payee_id" name="payee_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
              <option value="">Select Payee</option>
              @foreach($payees ?? [] as $payee)
                <option value="{{ $payee->id }}">{{ $payee->name }}</option>
              @endforeach
            </select>
          </div>
          
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category *</label>
            <select x-model="formData.expense_category_id" name="expense_category_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
              <option value="">Select Category</option>
              @foreach($categories ?? [] as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount *</label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 dark:text-gray-400">$</span></div>
              <input x-model="formData.amount" type="number" step="0.01" name="amount" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-8 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="0.00" />
            </div>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Date *</label>
            <input x-model="formData.expense_date" type="date" name="expense_date" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
            <select x-model="formData.status" name="status" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
            <textarea x-model="formData.description" name="description" rows="3" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Enter expense description..."></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button @click="closeModal()" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">Cancel</button>
          <button type="submit" :disabled="loading" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
            <span x-show="!loading">Save Expense</span>
            <span x-show="loading" class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Saving...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('expenseCreateModal', () => ({
    isOpen: false,
    loading: false,
    formData: {
      estate_id: '',
      payee_id: '',
      expense_category_id: '',
      amount: '',
      expense_date: '',
      status: 'pending',
      description: ''
    },
    formErrors: [],
    
    init() {
      window.expenseCreateModal = this;
      const today = new Date().toISOString().split('T')[0];
      this.formData.expense_date = today;
    },
    
    openModal() {
      this.isOpen = true;
      this.resetForm();
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.formErrors = [];
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    resetForm() {
      const today = new Date().toISOString().split('T')[0];
      this.formData = {
        estate_id: '',
        payee_id: '',
        expense_category_id: '',
        amount: '',
        expense_date: today,
        status: 'pending',
        description: ''
      };
      this.formErrors = [];
    },
    
    validateForm() {
      this.formErrors = [];
      
      if (!this.formData.estate_id) this.formErrors.push('Please select an estate');
      if (!this.formData.payee_id) this.formErrors.push('Please select a payee');
      if (!this.formData.expense_category_id) this.formErrors.push('Please select a category');
      if (!this.formData.amount || parseFloat(this.formData.amount) <= 0) this.formErrors.push('Please enter a valid amount greater than 0');
      if (!this.formData.expense_date) this.formErrors.push('Please select a date');
      
      return this.formErrors.length === 0;
    },

    async submitForm() {
      if (!this.validateForm()) {
        const modalContent = document.querySelector('.overflow-y-auto');
        if (modalContent) modalContent.scrollTop = 0;
        return;
      }

      this.loading = true;
      this.formErrors = [];
      
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
          this.formErrors = ['CSRF token not found. Please refresh the page.'];
          this.loading = false;
          return;
        }

        const formData = new FormData();
        formData.append('estate_id', this.formData.estate_id);
        formData.append('payee_id', this.formData.payee_id);
        formData.append('expense_category_id', this.formData.expense_category_id);
        formData.append('amount', this.formData.amount);
        formData.append('expense_date', this.formData.expense_date);
        formData.append('status', this.formData.status);
        formData.append('description', this.formData.description || '');

        const response = await fetch('/expenses', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
          this.closeModal();
          setTimeout(() => window.location.reload(), 500);
        } else {
          if (data.errors) this.formErrors = Object.values(data.errors).flat();
          else this.formErrors = [data.message || 'Failed to create expense. Please try again.'];
          const modalContent = document.querySelector('.overflow-y-auto');
          if (modalContent) modalContent.scrollTop = 0;
        }
      } catch (error) {
        console.error('Error:', error);
        this.formErrors = ['Network error. Please check your connection and try again.'];
      } finally {
        this.loading = false;
      }
    }
  }));
});
</script>
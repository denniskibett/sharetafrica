<!-- SUCCESS ALERT CENTERED MODAL -->
<div x-data="successModal" x-init="init()">
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
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <div class="text-center">
        <div class="relative flex items-center justify-center z-1 mb-7">
          <svg class="fill-green-50 dark:fill-green-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" fill="" fill-opacity="" />
          </svg>
          <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
            <svg class="fill-green-600 dark:fill-green-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.9375 19.0004C5.9375 11.7854 11.7864 5.93652 19.0014 5.93652C26.2164 5.93652 32.0653 11.7854 32.0653 19.0004C32.0653 26.2154 26.2164 32.0643 19.0014 32.0643C11.7864 32.0643 5.9375 26.2154 5.9375 19.0004ZM19.0014 2.93652C10.1296 2.93652 2.9375 10.1286 2.9375 19.0004C2.9375 27.8723 10.1296 35.0643 19.0014 35.0643C27.8733 35.0643 35.0653 27.8723 35.0653 19.0004C35.0653 10.1286 27.8733 2.93652 19.0014 2.93652ZM24.7855 17.0575C25.3713 16.4717 25.3713 15.522 24.7855 14.9362C24.1997 14.3504 23.25 14.3504 22.6642 14.9362L17.7177 19.8827L15.3387 17.5037C14.7529 16.9179 13.8031 16.9179 13.2173 17.5037C12.6316 18.0894 12.6316 19.0392 13.2173 19.625L16.657 23.0647C16.9383 23.346 17.3199 23.504 17.7177 23.504C18.1155 23.504 18.4971 23.346 18.7784 23.0647L24.7855 17.0575Z" fill="" />
            </svg>
          </span>
        </div>

        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-title-sm" x-text="title"></h4>
        
        <!-- Simple Message -->
        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400" x-show="!hasDetails()" x-text="message"></p>
        
        <!-- Detailed Message with List -->
        <div x-show="hasDetails()" class="text-left">
          <p class="text-sm leading-6 text-gray-500 dark:text-gray-400 mb-3" x-text="message"></p>
          
          <!-- Success Items List -->
          <div x-show="successItems.length > 0" class="mb-3">
            <p class="text-xs font-semibold text-green-600 dark:text-green-400 mb-2">✓ Successfully processed:</p>
            <ul class="space-y-1">
              <template x-for="item in successItems" :key="item">
                <li class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                  <svg class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span x-text="item"></span>
                </li>
              </template>
            </ul>
          </div>
          
          <!-- Failed Items List -->
          <div x-show="failedItems.length > 0">
            <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-2">✗ Failed to process:</p>
            <ul class="space-y-1">
              <template x-for="item in failedItems" :key="item">
                <li class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                  <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span x-text="item"></span>
                </li>
              </template>
            </ul>
          </div>
        </div>

        <div class="flex items-center justify-center w-full gap-3 mt-7">
          <button
            @click="confirm()"
            type="button"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-green-600 shadow-theme-xs hover:bg-green-700 sm:w-auto"
          >
            <span x-text="buttonText"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('successModal', () => ({
    isOpen: false,
    title: 'Success!',
    message: 'Operation completed successfully.',
    successItems: [],
    failedItems: [],
    buttonText: 'Okay, Got It',
    onConfirmCallback: null,
    
    init() {
      window.successModal = this;
    },
    
    show(title, message, options = {}) {
      this.title = title;
      this.message = message;
      this.successItems = options.successItems || [];
      this.failedItems = options.failedItems || [];
      this.buttonText = options.buttonText || 'Okay, Got It';
      this.onConfirmCallback = options.onConfirm || null;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    // Simple success message
    simple(title, message) {
      this.show(title, message, {
        successItems: [],
        failedItems: [],
        buttonText: 'Okay, Got It',
        onConfirm: null
      });
    },
    
    // Success with list of items
    successWithList(title, message, successItems) {
      this.show(title, message, {
        successItems: successItems,
        failedItems: [],
        buttonText: 'Okay, Got It',
        onConfirm: null
      });
    },
    
    // Partial success (some succeeded, some failed)
    partialSuccess(title, message, successItems, failedItems) {
      this.show(title, message, {
        successItems: successItems,
        failedItems: failedItems,
        buttonText: 'Okay, Got It',
        onConfirm: null
      });
    },
    
    hasDetails() {
      return this.successItems.length > 0 || this.failedItems.length > 0;
    },
    
    confirm() {
      if (this.onConfirmCallback && typeof this.onConfirmCallback === 'function') {
        this.onConfirmCallback();
      }
      this.closeModal();
    },
    
    closeModal() {
      this.isOpen = false;
      this.title = 'Success!';
      this.message = 'Operation completed successfully.';
      this.successItems = [];
      this.failedItems = [];
      this.buttonText = 'Okay, Got It';
      this.onConfirmCallback = null;
      document.body.style.overflow = '';
    }
  }));
});
</script>
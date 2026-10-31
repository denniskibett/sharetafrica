<!-- REUSABLE ALERT MODAL -->
<div x-data="alertModal" x-init="init()">
  <!-- Backdrop with 50% opacity and frost effect -->
  <template x-if="isOpen">
    <div 
      @click="closeModal()"
      class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-999999"
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
       class="fixed inset-0 flex items-center justify-center p-5 z-999999">
    <div 
      class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8"
    >
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-999999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C6.43342 16.9318 6.43342 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <div class="text-center">
        <!-- Dynamic Icon based on type -->
        <div class="relative flex items-center justify-center z-1 mb-7">
          <!-- Error Icon -->
          <template x-if="type === 'error'">
            <>
              <svg class="fill-red-50 dark:fill-red-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" />
              </svg>
              <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
                <svg class="fill-red-600 dark:fill-red-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M19 5.9375C11.7854 5.9375 5.9375 11.7864 5.9375 19.0014C5.9375 26.2164 11.7854 32.0653 19.0004 32.0653C26.2154 32.0653 32.0643 26.2164 32.0643 19.0014C32.0643 11.7864 26.2154 5.9375 19.0004 5.9375H19ZM19 2.9375C10.1286 2.9375 2.9375 10.1296 2.9375 19.0014C2.9375 27.8733 10.1286 35.0653 19.0004 35.0653C27.8723 35.0653 35.0643 27.8733 35.0643 19.0014C35.0643 10.1296 27.8723 2.9375 19.0004 2.9375H19ZM24.7855 22.6642C25.3713 23.25 25.3713 24.1997 24.7855 24.7855C24.1997 25.3713 23.25 25.3713 22.6642 24.7855L19.0004 21.1217L15.3366 24.7855C14.7508 25.3713 13.8011 25.3713 13.2153 24.7855C12.6295 24.1997 12.6295 23.25 13.2153 22.6642L16.8791 19.0004L13.2153 15.3366C12.6295 14.7508 12.6295 13.8011 13.2153 13.2153C13.8011 12.6295 14.7508 12.6295 15.3366 13.2153L19.0004 16.8791L22.6642 13.2153C23.25 12.6295 24.1997 12.6295 24.7855 13.2153C25.3713 13.8011 25.3713 14.7508 24.7855 15.3366L21.1217 19.0004L24.7855 22.6642Z" />
                </svg>
              </span>
            </>
          </template>

          <!-- Success Icon -->
          <template x-if="type === 'success'">
            <>
              <svg class="fill-green-50 dark:fill-green-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" />
              </svg>
              <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
                <svg class="fill-green-600 dark:fill-green-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M19 5.9375C11.7854 5.9375 5.9375 11.7864 5.9375 19.0014C5.9375 26.2164 11.7854 32.0653 19.0004 32.0653C26.2154 32.0653 32.0643 26.2164 32.0643 19.0014C32.0643 11.7864 26.2154 5.9375 19.0004 5.9375H19ZM19 2.9375C10.1286 2.9375 2.9375 10.1296 2.9375 19.0014C2.9375 27.8733 10.1286 35.0653 19.0004 35.0653C27.8723 35.0653 35.0643 27.8733 35.0643 19.0014C35.0643 10.1296 27.8723 2.9375 19.0004 2.9375H19ZM26.1238 15.3184C26.7096 15.9042 26.7096 16.8539 26.1238 17.4397L18.5421 25.0214C17.9563 25.6072 17.0066 25.6072 16.4208 25.0214L11.8762 20.4768C11.2904 19.891 11.2904 18.9413 11.8762 18.3555C12.462 17.7697 13.4117 17.7697 13.9975 18.3555L17.4814 21.8394L24.0025 15.3184C24.5883 14.7326 25.538 14.7326 26.1238 15.3184Z" />
                </svg>
              </span>
            </>
          </template>

          <!-- Warning Icon -->
          <template x-if="type === 'warning'">
            <>
              <svg class="fill-yellow-50 dark:fill-yellow-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" />
              </svg>
              <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
                <svg class="fill-yellow-600 dark:fill-yellow-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M19 5.9375C11.7854 5.9375 5.9375 11.7864 5.9375 19.0014C5.9375 26.2164 11.7854 32.0653 19.0004 32.0653C26.2154 32.0653 32.0643 26.2164 32.0643 19.0014C32.0643 11.7864 26.2154 5.9375 19.0004 5.9375H19ZM19 2.9375C10.1286 2.9375 2.9375 10.1296 2.9375 19.0014C2.9375 27.8733 10.1286 35.0653 19.0004 35.0653C27.8723 35.0653 35.0643 27.8733 35.0643 19.0014C35.0643 10.1296 27.8723 2.9375 19.0004 2.9375H19ZM19 26.1777C20.1164 26.1777 21.0219 25.2722 21.0219 24.1558C21.0219 23.0394 20.1164 22.1339 19 22.1339C17.8836 22.1339 16.9781 23.0394 16.9781 24.1558C16.9781 25.2722 17.8836 26.1777 19 26.1777ZM17.7998 19.0893C17.708 19.7997 18.319 20.4108 19.0294 20.4108C19.7398 20.4108 20.3509 19.7997 20.2591 19.0893L19.9536 16.7359C19.8618 16.0255 19.1571 15.5 18.4467 15.5C17.7363 15.5 17.0316 16.0255 16.9398 16.7359L17.7998 19.0893Z" />
                </svg>
              </span>
            </>
          </template>

          <!-- Info Icon -->
          <template x-if="type === 'info'">
            <>
              <svg class="fill-blue-50 dark:fill-blue-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" />
              </svg>
              <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
                <svg class="fill-blue-600 dark:fill-blue-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M19 5.9375C11.7854 5.9375 5.9375 11.7864 5.9375 19.0014C5.9375 26.2164 11.7854 32.0653 19.0004 32.0653C26.2154 32.0653 32.0643 26.2164 32.0643 19.0014C32.0643 11.7864 26.2154 5.9375 19.0004 5.9375H19ZM19 2.9375C10.1286 2.9375 2.9375 10.1296 2.9375 19.0014C2.9375 27.8733 10.1286 35.0653 19.0004 35.0653C27.8723 35.0653 35.0643 27.8733 35.0643 19.0014C35.0643 10.1296 27.8723 2.9375 19.0004 2.9375H19ZM19 26.1777C20.1164 26.1777 21.0219 25.2722 21.0219 24.1558C21.0219 23.0394 20.1164 22.1339 19 22.1339C17.8836 22.1339 16.9781 23.0394 16.9781 24.1558C16.9781 25.2722 17.8836 26.1777 19 26.1777ZM20.4108 14.7369C20.4108 15.8533 19.5053 16.7588 18.3889 16.7588C17.2725 16.7588 16.367 15.8533 16.367 14.7369C16.367 13.6205 17.2725 12.715 18.3889 12.715C19.5053 12.715 20.4108 13.6205 20.4108 14.7369Z" />
                </svg>
              </span>
            </>
          </template>
        </div>

        <h4 class="mb-2 text-2xl font-semibold" 
            :class="{
              'text-gray-800 dark:text-white/90': type === 'info',
              'text-red-600 dark:text-red-500': type === 'error',
              'text-green-600 dark:text-green-500': type === 'success',
              'text-yellow-600 dark:text-yellow-500': type === 'warning'
            }" 
            x-text="title">
        </h4>
        
        <div class="text-sm leading-6 text-gray-500 dark:text-gray-400">
          <p x-text="message"></p>
          <div x-show="errors.length > 0" class="mt-3 text-left">
            <p class="font-medium mb-2">Details:</p>
            <ul class="list-disc pl-5 space-y-1">
              <template x-for="error in errors" :key="error">
                <li x-text="error"></li>
              </template>
            </ul>
          </div>
        </div>

        <div class="flex items-center justify-center w-full gap-3 mt-7">
          <!-- Cancel Button (shown only when showCancelButton is true) -->
          <button
            x-show="showCancelButton"
            @click="cancel()"
            type="button"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            <span x-text="cancelButtonText"></span>
          </button>

          <!-- Confirm/Continue Button (shown only when showCancelButton is true) -->
          <button
            x-show="showCancelButton"
            @click="confirm()"
            type="button"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg shadow-theme-xs sm:w-auto"
            :class="{
              'bg-red-600 hover:bg-red-700': type === 'error',
              'bg-green-600 hover:bg-green-700': type === 'success',
              'bg-yellow-600 hover:bg-yellow-700': type === 'warning',
              'bg-blue-600 hover:bg-blue-700': type === 'info'
            }"
          >
            <span x-text="confirmButtonText"></span>
          </button>

          <!-- Normal Close Button (shown when showCancelButton is false) -->
          <button
            x-show="!showCancelButton"
            @click="closeModal()"
            type="button"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg shadow-theme-xs sm:w-auto"
            :class="{
              'bg-red-600 hover:bg-red-700': type === 'error',
              'bg-green-600 hover:bg-green-700': type === 'success',
              'bg-yellow-600 hover:bg-yellow-700': type === 'warning',
              'bg-blue-600 hover:bg-blue-700': type === 'info'
            }"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('alertModal', () => ({
    isOpen: false,
    type: 'error', // 'error', 'success', 'warning', 'info'
    title: 'Error!',
    message: 'An error occurred.',
    errors: [],
    showCancelButton: false,
    confirmButtonText: 'Continue',
    cancelButtonText: 'Cancel',
    onConfirm: null,
    onCancel: null,
    
    init() {
      window.alertModal = this;
    },
    
    show(type, title, message, errors = [], options = {}) {
      this.type = type || 'error';
      this.title = title || this.getDefaultTitle(type);
      this.message = message || this.getDefaultMessage(type);
      this.errors = Array.isArray(errors) ? errors : (errors ? [errors] : []);
      this.showCancelButton = options.showCancelButton || false;
      this.confirmButtonText = options.confirmButtonText || 'Continue';
      this.cancelButtonText = options.cancelButtonText || 'Cancel';
      this.onConfirm = options.onConfirm || null;
      this.onCancel = options.onCancel || null;
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    getDefaultTitle(type) {
      const titles = {
        'error': 'Error!',
        'success': 'Success!',
        'warning': 'Warning!',
        'info': 'Information'
      };
      return titles[type] || titles.error;
    },
    
    getDefaultMessage(type) {
      const messages = {
        'error': 'An error occurred.',
        'success': 'Operation completed successfully.',
        'warning': 'Please check your input.',
        'info': 'Please note:'
      };
      return messages[type] || messages.error;
    },
    
    closeModal() {
      this.isOpen = false;
      this.errors = [];
      this.showCancelButton = false;
      this.onConfirm = null;
      this.onCancel = null;
      document.body.style.overflow = '';
    },
    
    confirm() {
      const callback = this.onConfirm;
      this.closeModal();
      if (typeof callback === 'function') {
        callback();
      }
    },
    
    cancel() {
      const callback = this.onCancel;
      this.closeModal();
      if (typeof callback === 'function') {
        callback();
      }
    },
    
    // Convenience methods for different alert types
    showError(title, message, errors = []) {
      this.show('error', title, message, errors);
    },
    
    showSuccess(title, message, errors = []) {
      this.show('success', title, message, errors);
    },
    
    showWarning(title, message, errors = [], options = {}) {
      this.show('warning', title, message, errors, options);
    },
    
    showInfo(title, message, errors = []) {
      this.show('info', title, message, errors);
    }
  }));
});
</script>
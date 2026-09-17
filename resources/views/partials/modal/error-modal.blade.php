<!-- ERROR ALERT CENTERED MODAL -->
<div x-data="errorModal" x-init="init()">
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
          <svg class="fill-red-50 dark:fill-red-500/15" width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M34.364 6.85053C38.6205 -2.28351 51.3795 -2.28351 55.636 6.85053C58.0129 11.951 63.5594 14.6722 68.9556 13.3853C78.6192 11.0807 86.5743 21.2433 82.2185 30.3287C79.7862 35.402 81.1561 41.5165 85.5082 45.0122C93.3019 51.2725 90.4628 63.9451 80.7747 66.1403C75.3648 67.3661 71.5265 72.2695 71.5572 77.9156C71.6123 88.0265 60.1169 93.6664 52.3918 87.3184C48.0781 83.7737 41.9219 83.7737 37.6082 87.3184C29.8831 93.6664 18.3877 88.0266 18.4428 77.9156C18.4735 72.2695 14.6352 67.3661 9.22531 66.1403C-0.462787 63.9451 -3.30193 51.2725 4.49185 45.0122C8.84391 41.5165 10.2138 35.402 7.78151 30.3287C3.42572 21.2433 11.3808 11.0807 21.0444 13.3853C26.4406 14.6722 31.9871 11.951 34.364 6.85053Z" fill="" fill-opacity="" />
          </svg>
          <span class="absolute -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2">
            <svg class="fill-red-600 dark:fill-red-500" width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M19 5.9375C11.7854 5.9375 5.9375 11.7864 5.9375 19.0014C5.9375 26.2164 11.7854 32.0653 19.0004 32.0653C26.2154 32.0653 32.0643 26.2164 32.0643 19.0014C32.0643 11.7864 26.2154 5.9375 19.0004 5.9375H19ZM19 2.9375C10.1286 2.9375 2.9375 10.1296 2.9375 19.0014C2.9375 27.8733 10.1286 35.0653 19.0004 35.0653C27.8723 35.0653 35.0643 27.8733 35.0643 19.0014C35.0643 10.1296 27.8723 2.9375 19.0004 2.9375H19ZM24.7855 22.6642C25.3713 23.25 25.3713 24.1997 24.7855 24.7855C24.1997 25.3713 23.25 25.3713 22.6642 24.7855L19.0004 21.1217L15.3366 24.7855C14.7508 25.3713 13.8011 25.3713 13.2153 24.7855C12.6295 24.1997 12.6295 23.25 13.2153 22.6642L16.8791 19.0004L13.2153 15.3366C12.6295 14.7508 12.6295 13.8011 13.2153 13.2153C13.8011 12.6295 14.7508 12.6295 15.3366 13.2153L19.0004 16.8791L22.6642 13.2153C23.25 12.6295 24.1997 12.6295 24.7855 13.2153C25.3713 13.8011 25.3713 14.7508 24.7855 15.3366L21.1217 19.0004L24.7855 22.6642Z" fill=""/>
            </svg>
          </span>
        </div>

        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-title-sm" x-text="title"></h4>
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
          <button
            @click="closeModal()"
            type="button"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-600 shadow-theme-xs hover:bg-red-700 sm:w-auto"
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
  Alpine.data('errorModal', () => ({
    isOpen: false,
    title: 'Error!',
    message: 'An error occurred.',
    errors: [],
    
    init() {
      window.errorModal = this;
    },
    
    show(title, message, errors = []) {
      this.title = title;
      this.message = message;
      this.errors = Array.isArray(errors) ? errors : [errors];
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.errors = [];
      document.body.style.overflow = '';
    }
  }));
});
</script>
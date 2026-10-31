<!-- WATER METER READING MODAL -->
<div x-data="unitWaterReadingModal" x-init="init()" x-cloak>
  <!-- Backdrop -->
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

  <!-- Modal Content -->
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
      <div class="p-6 lg:p-8">
      <!-- close btn -->
      <button
        @click="closeModal()"
        class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
      >
        <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
        </svg>
      </button>

      <div class="mb-6">
        <h4 class="text-lg font-medium text-gray-800 dark:text-white/90 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
          Record Water Meter Reading
        </h4>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update the water meter reading for this unit</p>
      </div>

      <!-- Unit Info Card -->
      <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">Unit Number:</span>
          <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="unit?.unit_number"></span>
        </div>
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">Estate:</span>
          <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="unit?.estate_name"></span>
        </div>
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">Current Tenant:</span>
          <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="unit?.active_tenancy?.tenant?.name || 'Vacant'"></span>
        </div>
        <div class="border-t border-blue-200 dark:border-blue-800 my-3 pt-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Previous Reading:</span>
            <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="previousReading.toFixed(2)"></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">Last Reading Date:</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white" x-text="lastReadingDate || 'No previous reading'"></span>
          </div>
          <div class="flex items-center justify-between mt-2" x-show="estimatedConsumption > 0">
            <span class="text-sm text-gray-600 dark:text-gray-400">Estimated Consumption:</span>
            <span class="text-sm font-medium text-blue-600 dark:text-blue-400" x-text="estimatedConsumption.toFixed(2) + ' units'"></span>
          </div>
        </div>
      </div>

      <form @submit.prevent="submitReading()">
        <!-- Current Reading -->
        <div class="mb-6">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Current Meter Reading *
          </label>
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <input 
              x-model="currentReading"
              type="number"
              step="0.01"
              min="0"
              required
              class="dark:bg-dark-900 h-12 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              placeholder="Enter current meter reading"
            />
          </div>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter the current water meter reading as shown on the meter</p>
        </div>

        <!-- Preview Section (shows calculated charge) -->
        <div x-show="currentReading && currentReading > previousReading" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
          <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Preview</p>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Units Consumed:</span>
              <span class="font-semibold text-gray-800 dark:text-white" x-text="calculateConsumption().toFixed(2)"></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Water Rate:</span>
              <span class="font-semibold text-gray-800 dark:text-white">KES <span x-text="waterRate.toFixed(2)"></span> per unit</span>
            </div>
            <div class="border-t border-green-200 dark:border-green-800 pt-2 mt-2">
              <div class="flex justify-between">
                <span class="font-semibold text-gray-800 dark:text-white">Estimated Water Charge:</span>
                <span class="font-bold text-green-600 dark:text-green-400">KES <span x-text="calculateEstimatedCharge().toFixed(2)"></span></span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This amount will be added to the next invoice</p>
            </div>
          </div>
        </div>

        <!-- Warning for negative consumption -->
        <div x-show="currentReading && currentReading <= previousReading && currentReading > 0" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <p class="text-sm text-yellow-800 dark:text-yellow-300">Current reading must be greater than previous reading</p>
          </div>
        </div>

        <!-- Note Field (Optional) -->
        <div class="mb-6">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Notes (Optional)
          </label>
          <textarea 
            x-model="notes"
            rows="3"
            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            placeholder="Any notes about the reading (e.g., meter replaced, estimated reading, etc.)"
          ></textarea>
        </div>

        <div class="flex items-center justify-end w-full gap-3 mt-6">
          <button
            @click="closeModal()"
            type="button"
            :disabled="loading"
            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || (currentReading && currentReading <= previousReading)"
            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-600 shadow-theme-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
          >
            <span x-show="!loading">
              <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Save Reading
            </span>
            <span x-show="loading">Saving...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('unitWaterReadingModal', () => ({
    isOpen: false,
    unit: null,
    previousReading: 0,
    lastReadingDate: null,
    currentReading: '',
    notes: '',
    waterRate: 0,
    loading: false,
    
    init() {
      window.unitWaterReadingModal = this;
    },
    
    openModal(unit, waterRate = 0) {
      this.unit = unit;
      this.waterRate = waterRate;
      this.previousReading = parseFloat(unit.current_water_reading) || parseFloat(unit.previous_water_reading) || 0;
      this.lastReadingDate = unit.last_reading_date || null;
      this.currentReading = '';
      this.notes = '';
      this.isOpen = true;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.isOpen = false;
      this.loading = false;
      document.body.style.overflow = '';
    },
    
    calculateConsumption() {
      const current = parseFloat(this.currentReading) || 0;
      const previous = this.previousReading || 0;
      return Math.max(0, current - previous);
    },
    
    calculateEstimatedCharge() {
      const consumption = this.calculateConsumption();
      return consumption * (this.waterRate || 0);
    },
    
    get estimatedConsumption() {
      return this.calculateConsumption();
    },
    
    async submitReading() {
      this.loading = true;
      
      try {
        const response = await fetch(`/units/${this.unit.id}/water-reading`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            current_water_reading: this.currentReading,
            notes: this.notes
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.closeModal();
          
          if (window.successModal) {
            const consumption = data.consumption || 0;
            const charge = data.estimated_charge || 0;
            window.successModal.show(
              'Water Reading Recorded', 
              `Reading saved successfully!\n\nUnits consumed: ${consumption.toFixed(2)}\nEstimated charge: KES ${charge.toFixed(2)}\n\nThis amount will be added to the next invoice.`
            );
          } else {
            alert('Water meter reading recorded successfully!');
          }
          
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else {
          if (window.errorModal) {
            window.errorModal.show('Error', data.message || 'Failed to save reading');
          } else {
            alert(data.message || 'Failed to save reading');
          }
        }
      } catch (error) {
        console.error('Error:', error);
        if (window.errorModal) {
          window.errorModal.show('Error', 'An unexpected error occurred. Please try again.');
        } else {
          alert('An error occurred. Please try again.');
        }
      } finally {
        this.loading = false;
      }
    }
  }));
});
</script>
<!-- SECURITY QUICK ENTRY MODAL -->
<div x-data="securityQuickEntryModal" x-init="init()" x-cloak>
    <template x-if="isOpen">
        <div @click="closeModal()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"></div>
    </template>

    <div x-show="isOpen" 
         x-transition:enter="transition transform ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999"
         style="width: 38rem; max-width: calc(100% - 2rem);">
        <div class="p-6 lg:p-10">
            <button @click="closeModal()" class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 dark:bg-gray-800 sm:right-6 sm:top-6">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                </svg>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">Quick Visitor Check-in</h4>

            <form @submit.prevent="quickCheckin">
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lookup By</label>
                    <select x-model="lookupBy" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="phone">Phone Number</option>
                        <option value="id_number">ID Number</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lookup Value</label>
                    <input type="text" x-model="lookupValue" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit</label>
                    <select x-model="unitId" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="">Select Unit</option>
                        @foreach($units ?? [] as $unit)
                        <option value="{{ $unit['id'] }}">{{ $unit['unit_number'] }} ({{ $unit['estate_name'] ?? 'No Estate' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Access Type</label>
                    <select x-model="accessType" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm">
                        <option value="entry">Entry</option>
                        <option value="exit">Exit</option>
                        <option value="delivery">Delivery</option>
                        <option value="guest">Guest</option>
                        <option value="contractor">Contractor</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Purpose (Optional)</label>
                    <input type="text" x-model="purpose" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm" placeholder="Reason for visit">
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="closeModal()" class="px-4 py-3 border rounded-lg text-sm font-medium">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-3 bg-brand-500 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                        <span x-show="!isSubmitting">Check In</span>
                        <span x-show="isSubmitting">Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('securityQuickEntryModal', () => ({
        isOpen: false,
        isSubmitting: false,
        lookupBy: 'phone',
        lookupValue: '',
        unitId: '',
        accessType: 'entry',
        purpose: '',
        
        init() {
            window.securityQuickEntryModal = this;
        },
        
        openModal() {
            this.isOpen = true;
            this.resetForm();
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },
        
        resetForm() {
            this.lookupBy = 'phone';
            this.lookupValue = '';
            this.unitId = '';
            this.accessType = 'entry';
            this.purpose = '';
        },
        
        async quickCheckin() {
            if (!this.lookupValue || !this.unitId) {
                alert('Please enter lookup value and select a unit');
                return;
            }
            
            this.isSubmitting = true;
            
            try {
                const response = await fetch('/security/quick-entry', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        lookup_by: this.lookupBy,
                        lookup_value: this.lookupValue,
                        unit_id: this.unitId,
                        access_type: this.accessType,
                        purpose: this.purpose
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    this.closeModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else if (data.requires_registration) {
                    alert('Visitor not found. Please register them first.');
                    this.closeModal();
                    if (window.securityAddVisitorModal) {
                        window.securityAddVisitorModal.openModal();
                    }
                } else {
                    alert(data.message || 'Check-in failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>
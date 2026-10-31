<!-- resources/views/partials/modal/security-crud-modal.blade.php -->
<div x-data="securityCrudModal" x-init="init()" x-cloak>
    <!-- Create/Edit Slide-over Modal -->
    <div x-show="showFormModal" 
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-0 z-[99999] overflow-hidden">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="closeFormModal()"></div>
        
        <div class="absolute inset-y-0 right-0 max-w-full flex">
            <div class="w-screen max-w-md">
                <div class="h-full flex flex-col bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
                    <div class="px-4 py-6 bg-gradient-to-r from-brand-500 to-brand-600 sm:px-6">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-white">
                                <span x-text="form.id ? 'Edit Access Log' : 'Create New Access Log'"></span>
                            </h2>
                            <button @click="closeFormModal()" class="rounded-md text-white hover:text-gray-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm" class="flex-1 flex flex-col">
                        <div class="flex-1 px-4 py-6 sm:px-6">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit *</label>
                                    <select x-model="form.unit_id" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                                        <option value="">Select Unit</option>
                                        @foreach($units ?? [] as $unit)
                                            {{-- <option value="{{ $unit['id'] ?? '' }}">{{ $unit['unit_number'] ?? 'N/A' }} - {{ $unit['estate'] ?? 'No Estate' }}</option> --}}
                                            <option value="{{ $unit['id'] }}">{{ $unit['unit_number'] }} - {{ $unit['estate_name'] ?? '' }}</option>

                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Person Name *</label>
                                    <input type="text" x-model="form.person_name" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700" placeholder="Full name">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Access Type *</label>
                                    <select x-model="form.access_type" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                                        <option value="entry">Entry</option>
                                        <option value="exit">Exit</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="guest">Guest</option>
                                        <option value="contractor">Contractor</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                                    <select x-model="form.status" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                                        <option value="granted">Granted</option>
                                        <option value="denied">Denied</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date & Time *</label>
                                    <input type="datetime-local" x-model="form.datetime" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Verified By</label>
                                    <input type="text" x-model="form.verified_by" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700" placeholder="Who verified this access?">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                                    <textarea x-model="form.notes" rows="4" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700" placeholder="Additional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="closeFormModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg dark:bg-gray-700 dark:text-gray-300">Cancel</button>
                                <button type="submit" :disabled="submitting" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg disabled:opacity-50">
                                    <span x-show="!submitting" x-text="form.id ? 'Update' : 'Create'"></span>
                                    <span x-show="submitting">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div x-show="showViewModal" x-transition class="fixed inset-0 z-[99999] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeViewModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-6">
                <h3 class="text-lg font-medium mb-4">Access Log Details</h3>
                <div class="space-y-3">
                    <div><p class="text-xs text-gray-500">Date & Time</p><p class="text-sm" x-text="viewData.datetime_formatted"></p></div>
                    <div><p class="text-xs text-gray-500">Unit</p><p class="text-sm" x-text="viewData.unit_number"></p></div>
                    <div><p class="text-xs text-gray-500">Person Name</p><p class="text-sm" x-text="viewData.person_name"></p></div>
                    <div><p class="text-xs text-gray-500">Access Type</p><p class="text-sm" x-text="viewData.access_type"></p></div>
                    <div><p class="text-xs text-gray-500">Status</p><p class="text-sm" x-text="viewData.status"></p></div>
                    <div><p class="text-xs text-gray-500">Verified By</p><p class="text-sm" x-text="viewData.verified_by || 'System'"></p></div>
                    <div><p class="text-xs text-gray-500">Notes</p><p class="text-sm" x-text="viewData.notes || '-'"></p></div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="closeViewModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('securityCrudModal', () => ({
        showFormModal: false,
        showViewModal: false,
        submitting: false,
        form: { id: null, unit_id: '', person_name: '', access_type: 'entry', status: 'pending', datetime: '', verified_by: '', notes: '' },
        viewData: {},
        
        init() { window.securityCrudModal = this; },
        
        openModal() { this.resetForm(); this.showFormModal = true; document.body.style.overflow = 'hidden'; },
        closeFormModal() { this.showFormModal = false; document.body.style.overflow = ''; },
        closeViewModal() { this.showViewModal = false; document.body.style.overflow = ''; },
        
        resetForm() {
            this.form = { id: null, unit_id: '', person_name: '', access_type: 'entry', status: 'pending', datetime: new Date().toISOString().slice(0, 16), verified_by: '', notes: '' };
        },
        
        editLog(logId) { 
            // TODO: Fetch and populate form
            alert('Edit log ' + logId); 
            this.openModal(); 
        },
        
        viewLog(logId) { 
            // TODO: Fetch and show view
            this.showViewModal = true; 
            document.body.style.overflow = 'hidden'; 
        },
        
        confirmDelete(logId) { 
            if (confirm('Delete this log?')) alert('Delete log ' + logId); 
        },
        
        approveLog(logId) { 
            if (confirm('Approve this access?')) alert('Approve log ' + logId); 
        },
        
        denyLog(logId) { 
            if (confirm('Deny this access?')) alert('Deny log ' + logId); 
        },
        
        async submitForm() {
            this.submitting = true;
            await new Promise(r => setTimeout(r, 500));
            alert(this.form.id ? 'Log updated' : 'Log created');
            this.submitting = false;
            this.closeFormModal();
            location.reload();
        }
    }));
});
</script>
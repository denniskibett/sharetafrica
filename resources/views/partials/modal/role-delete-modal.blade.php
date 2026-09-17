<!-- ROLE DELETE MODAL -->
<div x-data="roleDeleteModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <!-- Frosty Background Overlay -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

    <!-- Modal Panel -->
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-md">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-5 sticky top-0 z-50 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">Delete Role</h3>
                                <p class="text-sm text-red-200">This action cannot be undone</p>
                            </div>
                        </div>
                        <button @click="closeModal()" class="text-white/80 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-8">
                    <div class="text-center">
                        <div class="mx-auto h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                            <svg class="h-10 w-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">
                            Are you sure you want to delete this role?
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            You are about to delete the role <strong class="text-gray-700 dark:text-gray-300" x-text="roleName"></strong>.
                        </p>
                        
                        <!-- Warning: Users assigned -->
                        <div x-show="userCount > 0" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/30 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">
                                        This role has <strong x-text="userCount"></strong> assigned user(s)
                                    </p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Please reassign these users to another role before deleting.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Protected role warning -->
                        <div x-show="isProtected" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <p class="text-sm font-medium text-red-800 dark:text-red-400">
                                    This role is protected and cannot be deleted.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-100 dark:bg-gray-800/80 px-6 py-4 flex justify-end gap-3 sticky bottom-0 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button type="button" @click="closeModal()" 
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition font-medium">
                        Cancel
                    </button>
                    <button @click="confirmDelete()" 
                        x-show="!isProtected && userCount === 0"
                        :disabled="deleting"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center gap-2">
                        <span x-show="!deleting" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Role
                        </span>
                        <span x-show="deleting" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                    <span x-show="isProtected || userCount > 0" class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-show="isProtected">Cannot delete protected role</span>
                        <span x-show="!isProtected && userCount > 0">Reassign users first</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roleDeleteModal', () => ({
        showModal: false,
        deleting: false,
        roleId: null,
        roleName: '',
        userCount: 0,
        isProtected: false,
        
        init() {
            window.roleDeleteModal = this;
        },
        
        openModal(role) {
            this.roleId = role.id;
            this.roleName = role.name.replace('_', ' ').toTitleCase();
            this.userCount = role.users_count || 0;
            this.isProtected = role.is_protected || false;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            this.deleting = false;
            document.body.style.overflow = '';
        },
        
        confirmDelete() {
            if (this.isProtected) {
                window.alertModal.showWarning(
                    'Protected Role',
                    'This role is protected and cannot be deleted.'
                );
                return;
            }
            
            if (this.userCount > 0) {
                window.alertModal.showWarning(
                    'Users Assigned',
                    'Cannot delete a role with assigned users. Please reassign users first.'
                );
                return;
            }
            
            // Show confirmation with the alert modal
            window.alertModal.showWarning(
                'Confirm Deletion',
                `Are you sure you want to delete the role "${this.roleName}"? This action cannot be undone.`,
                [],
                {
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    onConfirm: () => {
                        this.performDelete();
                    }
                }
            );
        },
        
        async performDelete() {
            this.deleting = true;
            
            try {
                const response = await fetch('/roles/' + this.roleId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.closeModal();
                    window.alertModal.showSuccess('Role Deleted', result.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    window.alertModal.showError('Deletion Failed', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                window.alertModal.showError('System Error', 'An unexpected error occurred.');
            } finally {
                this.deleting = false;
            }
        }
    }));
});
</script>
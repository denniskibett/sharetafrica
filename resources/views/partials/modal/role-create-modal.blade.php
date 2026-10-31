<!-- ROLE FORM MODAL (Create & Edit) -->
<div x-data="roleFormModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <!-- Frosty Background Overlay -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

    <!-- Modal Panel -->
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-lg">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                
                <!-- Header - Dynamic based on mode -->
                <div class="px-6 py-5 sticky top-0 z-50 flex-shrink-0"
                    :class="isEditMode ? 'bg-gradient-to-r from-blue-600 to-indigo-700' : 'bg-gradient-to-r from-blue-600 to-indigo-700'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!isEditMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    <path x-show="isEditMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    <span x-text="isEditMode ? 'Edit Role' : 'Create New Role'"></span>
                                </h3>
                                <p class="text-sm text-blue-200" x-text="isEditMode ? 'Update role details and permissions' : 'Add a new role with custom permissions'"></p>
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
                <form @submit.prevent="submitForm()" class="flex-1 flex flex-col overflow-hidden">
                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                        <!-- Role ID (hidden, for edit mode) -->
                        <input type="hidden" x-model="roleId">
                        
                        <!-- Role Name -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Role Name *
                            </label>
                            <input type="text" 
                                x-model="form.name"
                                required
                                placeholder="e.g., property_manager"
                                :disabled="isProtected"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:opacity-50 disabled:cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="isProtected">Protected roles cannot be renamed</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="!isProtected">Use underscores for multi-word roles (e.g., property_manager)</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Description
                            </label>
                            <textarea x-model="form.description" 
                                rows="3"
                                placeholder="Describe the role's purpose..."
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
                        </div>

                        <!-- Permissions -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Assign Permissions
                            </label>
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 max-h-80 overflow-y-auto">
                                <!-- Loading State -->
                                <div x-show="loadingPermissions" class="flex items-center justify-center py-8">
                                    <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                
                                <!-- Permissions List -->
                                <div x-show="!loadingPermissions">
                                    <template x-for="(perms, group) in permissionsByGroup" :key="group">
                                        <div class="mb-4 last:mb-0">
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2" 
                                                x-text="group.toTitleCase() + ' Permissions'">
                                            </h4>
                                            <div class="space-y-1.5">
                                                <template x-for="permission in perms" :key="permission.id">
                                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/30 transition cursor-pointer">
                                                        <input type="checkbox" 
                                                            x-model="form.permissions"
                                                            :value="permission.id"
                                                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="permission.name"></span>
                                                        <span class="text-xs text-gray-400 ml-auto" x-text="permission.group"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="Object.keys(permissionsByGroup).length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                        No permissions available. Please create permissions first.
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="form.permissions.length"></span> permission(s) selected
                            </p>
                        </div>

                        <!-- Protected Role Notice (Edit Mode) -->
                        <div x-show="isEditMode && isProtected" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/30 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">
                                        Protected Role
                                    </p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        This is a system role. The name cannot be changed, but you can modify permissions.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- User Count (Edit Mode) -->
                        <div x-show="isEditMode && userCount > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-400">
                                        <span x-text="userCount"></span> user(s) have this role
                                    </p>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        Updating permissions will affect all users with this role.
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
                        <button type="submit" :disabled="saving || !form.name"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center gap-2">
                            <span x-show="!saving" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!isEditMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    <path x-show="isEditMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span x-text="isEditMode ? 'Update Role' : 'Create Role'"></span>
                            </span>
                            <span x-show="saving" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isEditMode ? 'Updating...' : 'Creating...'"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roleFormModal', () => ({
        showModal: false,
        saving: false,
        loadingPermissions: false,
        isEditMode: false,
        isProtected: false,
        roleId: null,
        roleName: '',
        userCount: 0,
        form: {
            name: '',
            description: '',
            permissions: []
        },
        permissionsByGroup: {},
        allPermissions: [],
        
        init() {
            window.roleFormModal = this;
            this.loadPermissions();
        },
        
        async loadPermissions() {
            this.loadingPermissions = true;
            try {
                // Try to get permissions from the page data first
                const permissions = @json($permissions ?? []);
                if (Object.keys(permissions).length > 0) {
                    this.permissionsByGroup = permissions;
                    // Flatten for easier access
                    this.allPermissions = [];
                    for (const [group, perms] of Object.entries(permissions)) {
                        for (const perm of perms) {
                            this.allPermissions.push({
                                ...perm,
                                group: group
                            });
                        }
                    }
                } else {
                    // Fetch from API if not available
                    const response = await fetch('/admin/roles/permissions', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.permissionsByGroup = data.permissions || {};
                        this.allPermissions = data.all || [];
                    }
                }
            } catch (error) {
                console.error('Error loading permissions:', error);
                // Don't show error modal here as it might be confusing
            } finally {
                this.loadingPermissions = false;
            }
        },
        
        openCreateModal() {
            this.isEditMode = false;
            this.isProtected = false;
            this.roleId = null;
            this.roleName = '';
            this.userCount = 0;
            this.form = {
                name: '',
                description: '',
                permissions: []
            };
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        openEditModal(role) {
            this.isEditMode = true;
            this.roleId = role.id;
            this.roleName = role.name.replace('_', ' ').toTitleCase();
            this.isProtected = role.is_protected || false;
            this.userCount = role.users_count || 0;
            this.form = {
                name: role.name,
                description: role.description || '',
                permissions: role.permissions || []
            };
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            this.saving = false;
            document.body.style.overflow = '';
        },
        
        validateForm() {
            if (!this.form.name || this.form.name.trim() === '') {
                window.alertModal.showWarning(
                    'Validation Error',
                    'Please enter a role name.'
                );
                return false;
            }
            
            // Check if name contains spaces (should use underscores)
            if (this.form.name.includes(' ')) {
                window.alertModal.showWarning(
                    'Invalid Role Name',
                    'Role name should not contain spaces. Use underscores instead (e.g., property_manager).'
                );
                return false;
            }
            
            // Check if name is lowercase
            if (this.form.name !== this.form.name.toLowerCase()) {
                window.alertModal.showWarning(
                    'Invalid Role Name',
                    'Role name should be lowercase. Use underscores for multi-word roles.'
                );
                return false;
            }
            
            return true;
        },
        
        async submitForm() {
            if (!this.validateForm()) {
                return;
            }
            
            this.saving = true;
            
            try {
                const url = this.isEditMode ? `/roles/${this.roleId}` : '/roles';
                const method = this.isEditMode ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.closeModal();
                    
                    const title = this.isEditMode ? 'Role Updated' : 'Role Created';
                    const message = result.message || (this.isEditMode ? 'Role updated successfully!' : 'Role created successfully!');
                    
                    window.alertModal.showSuccess(title, message);
                    
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    window.alertModal.showError(
                        this.isEditMode ? 'Update Failed' : 'Creation Failed',
                        result.message || 'An error occurred. Please try again.'
                    );
                }
            } catch (error) {
                console.error('Error:', error);
                window.alertModal.showError(
                    'System Error',
                    'An unexpected error occurred. Please try again.'
                );
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
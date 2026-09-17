{{-- resources/views/partials/modal/companies-users-modal.blade.php --}}
<div x-data="companiesUsersModal()" x-init="init()" x-cloak>
    <div x-show="showModal" class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-90" @click="closeModal"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex">
            <div class="relative w-screen max-w-2xl">
                <div class="h-full flex flex-col bg-white shadow-xl overflow-y-auto dark:bg-gray-800">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-700 sticky top-0 z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-white" x-text="'Manage Users - ' + companyName"></h2>
                                <p class="text-sm text-purple-100 mt-1">Add and manage company staff members</p>
                            </div>
                            <button @click="closeModal" class="text-white hover:text-gray-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1">
                        <!-- User Slots Info -->
                        <div class="mx-6 mt-6 p-4 bg-gray-50 rounded-lg dark:bg-gray-700/50" x-show="availableUserSlots !== null">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Available User Slots:</span>
                                <span class="text-2xl font-bold" :class="availableUserSlots > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" x-text="availableUserSlots"></span>
                            </div>
                            <div class="flex justify-between items-center mt-2 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Current Users:</span>
                                <span class="font-medium text-gray-900 dark:text-white" x-text="users.length"></span>
                            </div>
                            <div x-show="availableUserSlots !== null && availableUserSlots <= 0 && users.length > 0" class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-xs dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400">
                                ⚠️ This company has reached its maximum user limit. Please upgrade their subscription to add more users.
                            </div>
                        </div>

                        <!-- Add New User Form -->
                        <div class="mx-6 mt-6 p-5 border rounded-lg bg-gray-50 dark:bg-gray-700/50 dark:border-gray-600">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Add New User
                            </h4>
                            <form @submit.prevent="addUser">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                                        <input type="text" x-model="newUser.first_name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                        <input type="text" x-model="newUser.last_name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" required>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                        <input type="email" x-model="newUser.email" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                        <input type="text" x-model="newUser.phone" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
                                        <select x-model="newUser.role_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" required>
                                            <option value="">Select Role</option>
                                            <template x-for="role in availableRoles" :key="role.id">
                                                <option :value="role.id" x-text="role.display_name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Temporary Password *</label>
                                        <input type="password" x-model="newUser.password" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm" required>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">User should change this after first login</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition" :disabled="addingUser">
                                        <span x-show="!addingUser">Add User to Company</span>
                                        <span x-show="addingUser">Adding...</span>
                                    </button>
                                </div>
                                <p x-show="userError" class="text-red-500 text-sm mt-2" x-text="userError"></p>
                            </form>
                        </div>

                        <!-- Users List -->
                        <div class="mx-6 my-6">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Company Staff (<span x-text="users.length"></span>)
                            </h4>
                            
                            <!-- Loading state for users -->
                            <div x-show="loadingUsers" class="text-center py-8">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mx-auto"></div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Loading users...</p>
                            </div>
                            
                            <!-- Users List -->
                            <div x-show="!loadingUsers" class="space-y-2">
                                <template x-for="user in users" :key="user.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition dark:bg-gray-700/50 dark:hover:bg-gray-700">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                                    <span class="text-purple-600 dark:text-purple-400 text-sm font-medium" x-text="user.full_name ? user.full_name.charAt(0) : '?'"></span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white" x-text="user.full_name"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="user.email"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full" :class="user.role_badge" x-text="user.role_name"></span>
                                            <button @click="removeUser(user.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Remove from company">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="users.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    No users in this company yet. Add your first user above.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('companiesUsersModal', () => ({
        showModal: false,
        companyId: null,
        companyName: '',
        users: [],
        availableRoles: [],
        availableUserSlots: null,
        loadingUsers: false,
        addingUser: false,
        userError: '',
        newUser: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            role_id: '',
            password: ''
        },
        
        init() {
            window.companiesUsersModal = this;
        },
        
        async openModal(companyId) {
            this.companyId = companyId;
            this.loadingUsers = true;
            this.showModal = true;
            
            // Find company name from global companies data if available
            if (typeof companiesData !== 'undefined' && companiesData && companiesData.length) {
                const company = companiesData.find(c => c.id === companyId);
                this.companyName = company ? company.name : '';
            }
            
            await this.loadUsers();
            this.loadingUsers = false;
        },
        
        closeModal() {
            this.showModal = false;
            this.resetForm();
            this.userError = '';
            this.companyId = null;
            this.users = [];
            this.availableRoles = [];
        },
        
        resetForm() {
            this.newUser = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                role_id: '',
                password: ''
            };
        },
        
        async loadUsers() {
            try {
                const response = await fetch(`/admin/companies/${this.companyId}/users`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.users = data.users || [];
                    this.availableRoles = data.availableRoles || [];
                    this.availableUserSlots = data.availableUserSlots;
                    
                    // Update company name if not set from global data
                    if (!this.companyName && data.company_name) {
                        this.companyName = data.company_name;
                    }
                } else {
                    console.error('Failed to load users');
                    this.users = [];
                }
            } catch (error) {
                console.error('Error loading users:', error);
                this.users = [];
            }
        },
        
        async addUser() {
            if (!this.newUser.first_name || !this.newUser.last_name || !this.newUser.email || !this.newUser.role_id || !this.newUser.password) {
                this.userError = 'Please fill in all required fields.';
                return;
            }
            
            if (this.newUser.password.length < 8) {
                this.userError = 'Password must be at least 8 characters.';
                return;
            }
            
            this.addingUser = true;
            this.userError = '';
            
            try {
                const response = await fetch(`/admin/companies/${this.companyId}/users`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newUser)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.resetForm();
                    await this.loadUsers();
                    
                    // Update the companies table user count if refresh function exists
                    if (window.companiesTable && typeof window.companiesTable.loadCompanies === 'function') {
                        await window.companiesTable.loadCompanies();
                    }
                    
                    alert(result.message || 'User added successfully!');
                } else {
                    this.userError = result.message || 'Error adding user';
                }
            } catch (error) {
                console.error('Error adding user:', error);
                this.userError = 'An error occurred while adding the user.';
            } finally {
                this.addingUser = false;
            }
        },
        
        async removeUser(userId) {
            if (!confirm('Remove this user from the company? The user account will remain but will no longer be associated with this company.')) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/companies/${this.companyId}/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    await this.loadUsers();
                    
                    // Update the companies table user count if refresh function exists
                    if (window.companiesTable && typeof window.companiesTable.loadCompanies === 'function') {
                        await window.companiesTable.loadCompanies();
                    }
                    
                    alert(result.message || 'User removed successfully!');
                } else {
                    alert(result.message || 'Error removing user');
                }
            } catch (error) {
                console.error('Error removing user:', error);
                alert('Error removing user');
            }
        }
    }));
});
</script>
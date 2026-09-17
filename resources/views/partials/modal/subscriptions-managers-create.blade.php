{{-- resources/views/partials/modal/subscriptions-managers-create.blade.php --}}
<!-- Account Managers Create/Edit Modal -->
<div x-data="subscriptionsManagersModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-xl">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4 sticky top-0 z-50 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white" x-text="editing ? 'Edit Account Manager' : 'Assign Account Manager'"></h3>
                            <p class="text-sm text-purple-200" x-text="editing ? 'Update manager details' : 'Assign a manager to a subcounty'"></p>
                        </div>
                        <button @click="closeModal()" class="text-white/80 hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form @submit.prevent="saveManager" class="flex-1 overflow-y-auto px-6 py-6">
                    <div class="space-y-4">
                        <!-- User Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User <span class="text-red-500">*</span></label>
                            <select x-model="form.user_id" @change="onUserChange()" required 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Select User</option>
                                <template x-for="user in availableUsers" :key="user.id">
                                    <option :value="user.id" x-text="user.name + ' (' + user.email + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <!-- User Details (Auto-populated) -->
                        <div x-show="selectedUser" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800/30">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white" x-text="selectedUser?.name"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white" x-text="selectedUser?.email"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input type="text" x-model="form.title" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500"
                                placeholder="e.g., Senior Account Manager">
                        </div>

                        <!-- County Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">County <span class="text-red-500">*</span></label>
                            <select x-model="form.county_id" @change="onCountyChange()" required 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Select County</option>
                                <template x-for="county in counties" :key="county.id">
                                    <option :value="county.id" x-text="county.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Subcounty Selection (Filtered by County) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subcounty <span class="text-red-500">*</span></label>
                            <select x-model="form.subcounty_id" @change="onSubcountyChange()" required 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Select Subcounty</option>
                                <template x-for="subcounty in filteredSubcounties" :key="subcounty.id">
                                    <option :value="subcounty.id" x-text="subcounty.name"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span x-show="form.subcounty_id">
                                    <span x-text="estatesInSubcounty.length"></span> estates in this subcounty
                                </span>
                            </p>
                        </div>

                        <!-- Estates in Subcounty (Auto-displayed) -->
                        <div x-show="form.subcounty_id && estatesInSubcounty.length > 0" 
                             class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800/30">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Estates in this Subcounty</h4>
                            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                <template x-for="estate in estatesInSubcounty" :key="estate.id">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span x-text="estate.name"></span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                This manager will be responsible for all <strong x-text="estatesInSubcounty.length"></strong> estates in this subcounty
                            </p>
                        </div>

                        <!-- Manager Type & Status -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <input type="checkbox" x-model="form.is_primary" 
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Primary Manager</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Lead manager for this area</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <input type="checkbox" x-model="form.is_active" 
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Manager is currently active</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>

                <!-- Footer -->
                <div class="bg-gray-100 dark:bg-gray-800/80 px-6 py-4 flex justify-between items-center border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span> Required fields
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition font-medium">Cancel</button>
                        <button @click="saveManager()" :disabled="saving || !form.user_id || !form.county_id || !form.subcounty_id" 
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium">
                            <span x-show="!saving" x-text="editing ? '💾 Update Manager' : '✨ Assign Manager'"></span>
                            <span x-show="saving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subscriptionsManagersModal', () => ({
        showModal: false,
        planId: null,
        editing: false,
        editId: null,
        saving: false,
        availableUsers: [],
        counties: [],
        allSubcounties: [],
        allEstates: [],
        form: {
            user_id: '',
            title: '',
            county_id: '',
            subcounty_id: '',
            is_primary: false,
            is_active: true
        },
        
        // Computed: Get selected user details
        get selectedUser() {
            return this.availableUsers.find(u => u.id === parseInt(this.form.user_id));
        },
        
        // Computed: Filter subcounties by selected county
        get filteredSubcounties() {
            if (!this.form.county_id) return [];
            return this.allSubcounties.filter(s => s.county_id === this.form.county_id);
        },
        
        // Computed: Get estates in selected subcounty
        get estatesInSubcounty() {
            if (!this.form.subcounty_id) return [];
            return this.allEstates.filter(e => e.subcounty_id === this.form.subcounty_id);
        },
        
        init() {
            window.subscriptionsManagersModal = this;
            this.loadUsers();
            this.loadCounties();
            this.loadSubcounties();
            this.loadEstates();
        },
        
        async loadUsers() {
            try {
                const response = await fetch('/admin/subscriptions/api/users');
                const data = await response.json();
                this.availableUsers = data.users || [];
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },
        
        async loadCounties() {
            try {
                const response = await fetch('/admin/subscriptions/api/counties');
                const data = await response.json();
                this.counties = data.counties || [];
            } catch (error) {
                console.error('Error loading counties:', error);
            }
        },
        
        async loadSubcounties() {
            try {
                const response = await fetch('/admin/subscriptions/api/subcounties');
                const data = await response.json();
                this.allSubcounties = data.subcounties || [];
            } catch (error) {
                console.error('Error loading subcounties:', error);
            }
        },
        
        async loadEstates() {
            try {
                const response = await fetch('/admin/subscriptions/api/estates');
                const data = await response.json();
                this.allEstates = data.estates || [];
            } catch (error) {
                console.error('Error loading estates:', error);
            }
        },
        
        openModal(planId, managerId = null) {
            this.planId = planId;
            this.editing = !!managerId;
            this.editId = managerId;
            
            if (this.editing) {
                this.loadManager(managerId);
            } else {
                this.resetForm();
            }
            
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
            this.resetForm();
        },
        
        resetForm() {
            this.form = {
                user_id: '',
                title: '',
                county_id: '',
                subcounty_id: '',
                is_primary: false,
                is_active: true
            };
        },
        
        onUserChange() {
            // User details auto-populate via computed property
            console.log('User selected:', this.selectedUser);
        },
        
        onCountyChange() {
            // Reset subcounty when county changes
            this.form.subcounty_id = '';
        },
        
        onSubcountyChange() {
            // Update estates list when subcounty changes
            console.log('Subcounty changed, estates:', this.estatesInSubcounty.length);
        },
        
        async loadManager(managerId) {
            try {
                const response = await fetch(`/admin/subscriptions/api/managers/${managerId}`);
                const data = await response.json();
                this.form = {
                    user_id: data.user_id || '',
                    title: data.title || '',
                    county_id: data.county_id || '',
                    subcounty_id: data.subcounty_id || '',
                    is_primary: data.is_primary || false,
                    is_active: data.is_active !== undefined ? data.is_active : true
                };
            } catch (error) {
                console.error('Error loading manager:', error);
            }
        },
        
        async saveManager() {
            // Validate
            if (!this.form.user_id) {
                alert('Please select a user');
                return;
            }
            if (!this.form.county_id) {
                alert('Please select a county');
                return;
            }
            if (!this.form.subcounty_id) {
                alert('Please select a subcounty');
                return;
            }
            
            this.saving = true;
            try {
                const url = this.editing 
                    ? `/admin/subscriptions/managers/${this.editId}`
                    : `/admin/subscriptions/plans/${this.planId}/managers`;
                const method = this.editing ? 'PUT' : 'POST';
                
                const payload = {
                    user_id: this.form.user_id,
                    title: this.form.title || 'Account Manager',
                    subcounty_id: this.form.subcounty_id,
                    is_primary: this.form.is_primary,
                    is_active: this.form.is_active,
                };
                
                console.log('Saving manager payload:', payload);
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                console.log('Save result:', result);
                
                if (result.success) {
                    this.closeModal();
                    alert(result.message || (this.editing ? 'Manager updated successfully!' : 'Manager assigned successfully!'));
                    location.reload();
                } else {
                    alert(result.message || 'Error saving manager');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving manager: ' + error.message);
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
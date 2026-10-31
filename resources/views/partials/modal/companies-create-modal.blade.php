{{-- resources/views/partials/modal/companies-create-modal.blade.php --}}
<!-- Companies Create/Edit Slide-over Modal -->
<div x-data="companiesCreateModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <!-- Frosty Background Overlay -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

    <!-- Slide-over Panel -->
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-2xl">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 sticky top-0 z-50 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white" x-text="modalTitle"></h3>
                                <p class="text-sm text-blue-200" x-text="modalMode === 'create' ? 'Add a new company to the system' : 'Update company details'"></p>
                            </div>
                        </div>
                        <button @click="closeModal()" class="text-white/80 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="saveCompany" class="flex-1 flex flex-col overflow-hidden">
                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                        <!-- Company Details -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Company Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="formData.name" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="e.g., Acme Corp"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Registration Number</label>
                                    <input type="text" x-model="formData.registration_number" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="e.g., REG-2024-001">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                                    <input type="email" x-model="formData.email" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="contact@company.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone</label>
                                    <input type="text" x-model="formData.phone" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="+254 700 000 000">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Address</label>
                                <textarea x-model="formData.address" rows="2" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Street address, city, country"></textarea>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-3">
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" x-model="formData.is_active" 
                                        class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-2 focus:ring-blue-500 transition">
                                    <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Company is visible and operational</span>
                            </div>
                        </div>

                        <!-- Admin User Section (Only for create mode) -->
                        <div x-show="modalMode === 'create'" class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-5 border border-blue-200 dark:border-blue-800/30">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Admin User
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Create an administrator user for this company</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="newAdminUser.first_name" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="John"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="newAdminUser.last_name" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="Doe"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                                    <input type="email" x-model="newAdminUser.email" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="admin@company.com"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone</label>
                                    <input type="text" x-model="newAdminUser.phone" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="+254 700 000 000">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password <span class="text-red-500">*</span></label>
                                    <input type="password" x-model="newAdminUser.password" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        placeholder="••••••••"
                                        required
                                        minlength="8">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 8 characters</p>
                                </div>
                            </div>
                        </div>

                        <!-- Company Preview -->
                        <div x-show="formData.name" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800/30">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📋 Company Preview:</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Name:</span>
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="formData.name || '—'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Registration:</span>
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="formData.registration_number || '—'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="formData.email || '—'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Phone:</span>
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="formData.phone || '—'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-100 dark:bg-gray-800/80 px-6 py-4 flex justify-end gap-3 sticky bottom-0 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <button type="button" @click="closeModal()" 
                            class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition font-medium">
                            Cancel
                        </button>
                        <button type="submit" :disabled="saving" 
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center gap-2">
                            <span x-show="!saving" x-text="modalMode === 'create' ? '✨ Create Company' : '💾 Save Changes'"></span>
                            <span x-show="saving" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
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
    Alpine.data('companiesCreateModal', () => ({
        showModal: false,
        modalMode: 'create',
        editingId: null,
        saving: false,
        formData: {
            name: '',
            registration_number: '',
            email: '',
            phone: '',
            address: '',
            is_active: true
        },
        newAdminUser: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            password: ''
        },
        
        get modalTitle() {
            return this.modalMode === 'create' ? 'Create New Company' : 'Edit Company';
        },
        
        init() {
            window.companiesCreateModal = this;
        },
        
        openModal(companyId = null) {
            if (companyId) {
                this.modalMode = 'edit';
                this.editingId = companyId;
                this.loadCompany(companyId);
            } else {
                this.modalMode = 'create';
                this.editingId = null;
                this.resetForm();
            }
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            this.resetForm();
            document.body.style.overflow = '';
        },
        
        resetForm() {
            this.formData = {
                name: '',
                registration_number: '',
                email: '',
                phone: '',
                address: '',
                is_active: true
            };
            this.newAdminUser = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                password: ''
            };
            this.saving = false;
        },
        
        async loadCompany(companyId) {
            try {
                const response = await fetch(`/admin/companies/${companyId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load company data');
                }
                
                const data = await response.json();
                this.formData = {
                    name: data.name || '',
                    registration_number: data.registration_number || '',
                    email: data.email || '',
                    phone: data.phone || '',
                    address: data.address || '',
                    is_active: data.is_active !== undefined ? data.is_active : true
                };
            } catch (error) {
                console.error('Error loading company:', error);
                alert('Error loading company data. Please try again.');
            }
        },
        
        async saveCompany() {
            // Validate required fields
            if (!this.formData.name.trim()) {
                alert('Company Name is required');
                return;
            }
            
            // Validate admin user fields for create mode
            if (this.modalMode === 'create') {
                if (!this.newAdminUser.first_name.trim() || !this.newAdminUser.last_name.trim()) {
                    alert('Please fill in the admin user\'s first and last name');
                    return;
                }
                if (!this.newAdminUser.email.trim()) {
                    alert('Please enter the admin user\'s email');
                    return;
                }
                if (!this.newAdminUser.password || this.newAdminUser.password.length < 8) {
                    alert('Please enter a password with at least 8 characters for the admin user');
                    return;
                }
            }
            
            this.saving = true;
            
            const url = this.modalMode === 'create' ? '/admin/companies' : `/admin/companies/${this.editingId}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            
            let payload = { ...this.formData };
            if (this.modalMode === 'create') {
                payload.admin_user = { ...this.newAdminUser };
            }
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.closeModal();
                    // Reload the page to refresh data
                    if (this.modalMode === 'create' && result.credentials) {
                        alert(`${result.message}\n\nAdmin User Credentials:\nEmail: ${result.credentials.email}\nPassword: ${result.credentials.password}\n\nPlease save these credentials!`);
                    } else {
                        alert(result.message || (this.modalMode === 'create' ? 'Company created successfully!' : 'Company updated successfully!'));
                    }
                    location.reload();
                } else {
                    alert(result.message || 'Error saving company');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving company. Please try again.');
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
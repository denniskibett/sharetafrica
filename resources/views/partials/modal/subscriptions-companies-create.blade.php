{{-- resources/views/partials/modal/subscriptions-companies-create.blade.php --}}
<!-- Companies Assign Modal -->
<div x-data="subscriptionsCompaniesModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-xl">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4 sticky top-0 z-50 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Assign Companies</h3>
                            <p class="text-sm text-purple-200">Add companies to this subscription plan</p>
                        </div>
                        <button @click="closeModal()" class="text-white/80 hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-6">
                    <div class="space-y-4">
                        <!-- Company Selection with Unit Count Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Companies</label>
                            <div class="space-y-2 max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <template x-for="company in availableCompanies" :key="company.id">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded cursor-pointer">
                                        <input type="checkbox" :value="company.id" x-model="selectedCompanies"
                                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="company.name"></span>
                                                <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">
                                                    <span x-text="company.unit_count || 0"></span> units
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500" x-text="company.email"></p>
                                        </div>
                                    </label>
                                </template>
                                <p x-show="availableCompanies.length === 0" class="text-center text-gray-500 py-8">
                                    No available companies found. All companies are already subscribed to this plan.
                                </p>
                            </div>
                        </div>

                        <!-- Unit Count Summary -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Companies</span>
                                <span class="text-sm font-bold text-purple-600 dark:text-purple-400" x-text="selectedCompanies.length"></span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total Units</span>
                                <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="totalUnits"></span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Est. Monthly Revenue</span>
                                <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                    KES <span x-text="estimatedRevenue.toLocaleString()"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                            <select x-model="billingCycle" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly (10% discount)</option>
                            </select>
                        </div>

                        <!-- Auto Renew -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Auto Renew</label>
                            <select x-model="autoRenew" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option :value="true">Yes</option>
                                <option :value="false">No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-100 dark:bg-gray-800/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button @click="closeModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Cancel</button>
                    <button @click="assignCompanies()" :disabled="saving || selectedCompanies.length === 0" 
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!saving">Assign to Plan</span>
                        <span x-show="saving">
                            <svg class="animate-spin inline-block h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Assigning...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subscriptionsCompaniesModal', () => ({
        showModal: false,
        planId: null,
        saving: false,
        availableCompanies: [],
        selectedCompanies: [],
        billingCycle: 'monthly',
        autoRenew: true,
        pricePerUnit: 0,
        
        // Computed: Total units from selected companies
        get totalUnits() {
            let total = 0;
            this.selectedCompanies.forEach(companyId => {
                const company = this.availableCompanies.find(c => c.id === companyId);
                if (company) {
                    total += parseInt(company.unit_count || 0);
                }
            });
            return total;
        },
        
        // Computed: Estimated monthly revenue
        get estimatedRevenue() {
            return this.totalUnits * (parseFloat(this.pricePerUnit) || 0);
        },
        
        init() {
            window.subscriptionsCompaniesModal = this;
        },
        
        async openModal(planId) {
            this.planId = planId;
            this.selectedCompanies = [];
            this.billingCycle = 'monthly';
            this.autoRenew = true;
            this.pricePerUnit = 0;
            await this.loadAvailableCompanies();
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },
        
        async loadAvailableCompanies() {
            try {
                const response = await fetch(`/admin/subscriptions/api/plans/${this.planId}/available-companies`);
                const data = await response.json();
                this.availableCompanies = data.companies || [];
                this.pricePerUnit = data.price_per_unit || 0;
                
                // Debug: Log the data to verify
                console.log('Available companies:', this.availableCompanies);
                console.log('Price per unit:', this.pricePerUnit);
            } catch (error) {
                console.error('Error loading companies:', error);
            }
        },
        
        async assignCompanies() {
            if (this.selectedCompanies.length === 0) return;
            
            this.saving = true;
            try {
                // Build the companies array with unit counts
                const companies = this.selectedCompanies.map(companyId => {
                    const company = this.availableCompanies.find(c => c.id === companyId);
                    return {
                        company_id: companyId,
                        unit_count: company ? parseInt(company.unit_count || 0) : 0
                    };
                });
                
                // Debug: Log the payload
                console.log('Assigning companies:', {
                    companies: companies,
                    billing_cycle: this.billingCycle,
                    auto_renew: this.autoRenew
                });
                
                const response = await fetch(`/admin/subscriptions/plans/${this.planId}/assign-companies`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        companies: companies,
                        billing_cycle: this.billingCycle,
                        auto_renew: this.autoRenew
                    })
                });
                
                const result = await response.json();
                console.log('Assign result:', result);
                
                if (result.success) {
                    this.closeModal();
                    // Show success message with details
                    alert(`✅ ${result.message}\n\nTotal Units: ${result.total_units}\nTotal Revenue: KES ${(result.total_revenue || 0).toLocaleString()}`);
                    // Refresh the page to show updated data
                    location.reload();
                } else {
                    alert(result.message || 'Error assigning companies');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error assigning companies: ' + error.message);
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
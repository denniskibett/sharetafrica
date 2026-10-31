{{-- resources/views/partials/modal/subscriptions-show-modal.blade.php --}}
<!-- Subscriptions Show Modal (View Plan Details & Subscribers) -->
<div x-data="subscriptionsShowModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" @click="closeModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-90"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full dark:bg-gray-800">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-white" x-text="plan.display_name || plan.name + ' - Subscribers'"></h3>
                            <p class="text-sm text-purple-100" x-text="plan.region_name ? plan.region_name + ' - ' + plan.name : 'Companies subscribed to this plan'"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="text-white hover:text-gray-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <!-- Plan Details Summary -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-xl dark:bg-gray-700/50">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Price Per Unit</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            KES <span x-text="plan.price_per_unit?.toLocaleString() || 0"></span>
                        </p>
                        <p class="text-xs text-gray-400">per unit / month</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unit Range</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="plan.unit_range || 'Unlimited'"></p>
                        <p class="text-xs text-gray-400">units allowed</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Trial Days</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="plan.trial_days + ' days'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Subscribers</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="subscribers.length"></p>
                    </div>
                </div>

                <!-- Plan Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Region</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="plan.region_name || 'N/A'"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">County</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="plan.county_name || 'N/A'"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Discount</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="plan.discount_percentage + '%'"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                        <span :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-0.5 text-xs rounded-full" x-text="plan.is_active ? 'Active' : 'Inactive'"></span>
                    </div>
                </div>

                <!-- Features List -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Features</h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="feature in plan.features" :key="feature">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="feature"></span>
                            </span>
                        </template>
                        <span x-show="!plan.features || plan.features.length === 0" class="text-sm text-gray-500">No features listed</span>
                    </div>
                </div>

                <!-- Subscribers Table -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Subscribed Companies</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billing Cycle</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Auto Renew</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="sub in subscribers" :key="sub.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <span class="text-purple-600 text-sm font-medium" x-text="sub.company?.name?.charAt(0) || '?'"></span>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="sub.company?.name || 'N/A'"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="getStatusClass(sub.status)" class="inline-flex px-2 py-1 text-xs rounded-full" x-text="sub.status"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="sub.billing_cycle"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(sub.starts_at)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(sub.ends_at) || 'Never'"></td>
                                        <td class="px-4 py-3">
                                            <span :class="sub.auto_renew ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs rounded-full" x-text="sub.auto_renew ? 'Yes' : 'No'"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="subscribers.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No companies are currently subscribed to this plan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button @click="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subscriptionsShowModal', () => ({
        showModal: false,
        planId: null,
        plan: {
            name: '',
            display_name: '',
            region_name: '',
            county_name: '',
            price_per_unit: 0,
            unit_range: '',
            trial_days: 0,
            discount_percentage: 0,
            is_active: true,
            features: []
        },
        subscribers: [],
        
        init() {
            window.subscriptionsShowModal = this;
        },
        
        formatCurrency(value) {
            if (!value && value !== 0) return 'KES 0';
            return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 0 }).format(value);
        },
        
        formatDate(dateString) {
            if (!dateString) return null;
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        
        getStatusClass(status) {
            const classes = {
                'trial': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'active': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'past_due': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'expired': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        
        async openModal(planId) {
            this.planId = planId;
            this.showModal = true;
            await this.loadPlanDetails();
        },
        
        closeModal() {
            this.showModal = false;
            this.planId = null;
            this.plan = {
                name: '',
                display_name: '',
                region_name: '',
                county_name: '',
                price_per_unit: 0,
                unit_range: '',
                trial_days: 0,
                discount_percentage: 0,
                is_active: true,
                features: []
            };
            this.subscribers = [];
        },
        
        async loadPlanDetails() {
            if (!this.planId) return;
            
            try {
                const response = await fetch(`/admin/subscriptions/api/plans/${this.planId}/subscribers`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.plan = data.plan || {};
                    this.subscribers = data.subscribers || [];
                }
            } catch (error) {
                console.error('Error loading plan details:', error);
            }
        }
    }));
});
</script>
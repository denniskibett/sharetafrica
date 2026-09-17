{{-- resources/views/partials/modal/subscriptions-features-create.blade.php --}}
<!-- Features Create/Edit Modal -->
<div x-data="subscriptionsFeaturesModal()" x-init="init()" x-show="showModal" x-cloak class="fixed inset-0 z-99999 overflow-hidden" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="relative w-screen max-w-xl">
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4 sticky top-0 z-50 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Manage Features</h3>
                            <p class="text-sm text-purple-200">Add or remove features for this plan</p>
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
                    <div x-data="{ newFeature: '' }" class="space-y-4">
                        <div class="flex gap-2">
                            <input type="text" x-model="newFeature" 
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500"
                                placeholder="Enter feature name..."
                                @keydown.enter.prevent="addFeature()">
                            <button @click="addFeature()" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Add
                            </button>
                        </div>
                        
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                            <template x-for="(feature, index) in features" :key="index">
                                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-2.5 border border-gray-200 dark:border-gray-700">
                                    <span x-text="feature"></span>
                                    <button @click="removeFeature(index)" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <p x-show="features.length === 0" class="text-center text-gray-500 py-8">No features added yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-100 dark:bg-gray-800/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button @click="closeModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Cancel</button>
                    <button @click="saveFeatures()" :disabled="saving" 
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50">
                        <span x-show="!saving">Save Features</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subscriptionsFeaturesModal', () => ({
        showModal: false,
        planId: null,
        features: [],
        saving: false,
        
        init() {
            window.subscriptionsFeaturesModal = this;
        },
        
        openModal(planId) {
            this.planId = planId;
            this.loadFeatures();
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },
        
        async loadFeatures() {
            try {
                const response = await fetch(`/admin/subscriptions/api/plans/${this.planId}`);
                const data = await response.json();
                this.features = data.business_features || [];
            } catch (error) {
                console.error('Error loading features:', error);
            }
        },
        
        addFeature() {
            const input = document.querySelector('[x-model="newFeature"]');
            if (input && input.value.trim()) {
                this.features.push(input.value.trim());
                input.value = '';
            }
        },
        
        removeFeature(index) {
            this.features.splice(index, 1);
        },
        
        async saveFeatures() {
            this.saving = true;
            try {
                const response = await fetch(`/admin/subscriptions/plans/${this.planId}/features`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ business_features: this.features })
                });
                
                const result = await response.json();
                if (result.success) {
                    this.closeModal();
                    location.reload();
                } else {
                    alert(result.message || 'Error saving features');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving features');
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
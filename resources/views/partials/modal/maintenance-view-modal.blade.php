<div x-data="maintenanceViewModal()" x-init="init()" x-cloak>
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-999999 overflow-y-auto" 
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-90"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Maintenance Request Details
                                </h3>
                                <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div x-show="loading" class="text-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto"></div>
                                <p class="mt-2 text-gray-500">Loading...</p>
                            </div>
                            
                            <div x-show="!loading && request" class="space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs text-gray-500">Request #</span>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="request.request_number"></p>
                                    </div>
                                    <span :class="request.priority_color" class="px-2 py-1 text-xs font-medium rounded-full" x-text="request.priority_label"></span>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Status</span>
                                    <p><span :class="request.status_color" class="inline-block px-2 py-1 text-xs font-medium rounded-full" x-text="request.status_label"></span></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Unit</span>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="request.unit_number"></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Tenant</span>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="request.tenant_name || 'N/A'"></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Category</span>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="request.category_label || request.category"></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Title</span>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="request.title || request.name"></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Description</span>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="request.description"></p>
                                </div>
                                
                                <div x-show="request.admin_notes">
                                    <span class="text-xs text-gray-500">Admin Notes</span>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="request.admin_notes"></p>
                                </div>
                                
                                <div>
                                    <span class="text-xs text-gray-500">Reported On</span>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="formatDate(request.created_at)"></p>
                                </div>
                                
                                <div x-show="request.scheduled_date">
                                    <span class="text-xs text-gray-500">Scheduled Date</span>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="formatDate(request.scheduled_date)"></p>
                                </div>
                                
                                <div x-show="request.completed_date">
                                    <span class="text-xs text-gray-500">Completed Date</span>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="formatDate(request.completed_date)"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            @click="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'property_manager', 'maintenance']))
                    <button type="button"
                            @click="editRequest()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-600 text-base font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Edit Request
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('maintenanceViewModal', () => ({
        showModal: false,
        loading: false,
        request: null,
        requestId: null,
        
        init() {
            window.maintenanceViewModal = this;
        },
        
        async openModal(id) {
            this.requestId = id;
            this.showModal = true;
            this.loading = true;
            
            try {
                const response = await fetch(`/maintenance/${id}/json`);
                const data = await response.json();
                
                if (data.success) {
                    this.request = {
                        ...data.request,
                        priority_label: this.getPriorityLabel(data.request.priority),
                        priority_color: this.getPriorityColor(data.request.priority),
                        status_label: this.getStatusLabel(data.request.status),
                        status_color: this.getStatusColor(data.request.status),
                        category_label: this.getCategoryLabel(data.request.category),
                        request_number: data.request.request_number || '#' + String(data.request.id).padStart(6, '0')
                    };
                } else {
                    alert('Failed to load request details');
                    this.closeModal();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while loading the request');
                this.closeModal();
            } finally {
                this.loading = false;
            }
        },
        
        closeModal() {
            this.showModal = false;
            this.request = null;
            this.requestId = null;
        },
        
        editRequest() {
            if (this.requestId) {
                window.location.href = `/maintenance/${this.requestId}/edit`;
            }
        },
        
        getPriorityLabel(priority) {
            const labels = { emergency: 'Emergency', high: 'High', medium: 'Medium', low: 'Low' };
            return labels[priority] || 'Medium';
        },
        
        getPriorityColor(priority) {
            const colors = {
                emergency: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                low: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
            };
            return colors[priority] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusLabel(status) {
            const labels = { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved', pending_parts: 'Pending Parts', cancelled: 'Cancelled' };
            return labels[status] || status;
        },
        
        getStatusColor(status) {
            const colors = {
                resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                pending_parts: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                open: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },
        
        getCategoryLabel(category) {
            const labels = {
                plumbing: 'Plumbing', electrical: 'Electrical', hvac: 'HVAC / AC',
                appliance: 'Appliance', structural: 'Structural', pest_control: 'Pest Control',
                cleaning: 'Cleaning', other: 'Other'
            };
            return labels[category] || category;
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }));
});
</script>
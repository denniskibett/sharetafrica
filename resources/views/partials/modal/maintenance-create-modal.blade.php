<!-- ENHANCED MAINTENANCE REQUEST MODAL - ROLE AWARE WITH EDIT SUPPORT -->
<div x-data="maintenanceCreateModal" x-init="init()" x-cloak>
    <!-- Backdrop -->
    <template x-if="isOpen">
        <div @click="closeModal()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] transition-opacity z-99999"></div>
    </template>

    <!-- Modal Content -->
    <div x-show="isOpen" 
         x-transition:enter="transition transform ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-full max-w-3xl bg-white dark:bg-gray-900 shadow-2xl z-99999 overflow-y-auto">
        <div class="p-6 lg:p-8">
            <!-- Close Button -->
            <button @click="closeModal()" class="group absolute right-3 top-3 z-99999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/15">
                    <svg class="fill-orange-500 dark:fill-orange-500" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="modalTitle"></h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="modalSubtitle"></p>
                </div>
            </div>

            <!-- Form Errors -->
            <template x-if="formErrors.length > 0">
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <ul class="list-disc pl-5">
                        <template x-for="error in formErrors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <!-- TABS: Form + History (Only for Staff) -->
            <div x-show="userRole !== 'tenant'" class="mb-6">
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'form'" :class="activeTab === 'form' ? 'border-brand-500 text-brand-600 dark:text-brand-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-medium transition-colors">
                        <span x-text="isEditMode ? 'Edit Request' : 'New Request'"></span>
                    </button>
                    <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-brand-500 text-brand-600 dark:text-brand-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-medium transition-colors">
                        Previous History (<span x-text="previousRequests.length"></span>)
                    </button>
                    <span x-show="isEditMode" class="ml-auto text-sm text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Editing Request #<span x-text="editId"></span>
                    </span>
                </div>
            </div>

            <!-- ==================== FORM TAB ==================== -->
            <div x-show="activeTab === 'form'">
                <form @submit.prevent="submitRequest">
                    @csrf
                    
                    <!-- Hidden field for edit mode -->
                    <input type="hidden" x-model="editId" name="id">
                    
                    <!-- Unit Selection (Staff only) -->
                    <div class="mb-5" x-show="userRole !== 'tenant'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Unit *
                        </label>
                        <select x-model="formData.unit_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Select Unit</option>
                            <template x-for="unit in units" :key="unit.id">
                                <option :value="unit.id" x-text="unit.unit_number + ' - ' + (unit.estate?.name || 'No Estate')"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Unit Display (Tenant - Read-only) -->
                    <div class="mb-5" x-show="userRole === 'tenant'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Your Unit
                        </label>
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-3">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="currentUnitLabel"></p>
                            <p class="text-xs text-gray-500 mt-1">This request will be for your current unit</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Issue Title *
                        </label>
                        <input type="text" x-model="formData.name" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="e.g., Leaking faucet, Broken AC, etc.">
                    </div>

                    <!-- Description -->
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Description *
                        </label>
                        <textarea x-model="formData.description" rows="3" required class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="Please provide detailed description of the issue..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Category -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Category *
                            </label>
                            <select x-model="formData.category" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select Category</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="hvac">HVAC / AC</option>
                                <option value="appliance">Appliance</option>
                                <option value="structural">Structural</option>
                                <option value="pest_control">Pest Control</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Priority *
                            </label>
                            <select x-model="formData.priority" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="low">Low - Non-urgent</option>
                                <option value="medium">Medium - Normal priority</option>
                                <option value="high">High - Urgent</option>
                                <option value="emergency">Emergency - Immediate attention</option>
                            </select>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            How long has this issue been happening? *
                        </label>
                        <select x-model="formData.duration" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Select duration</option>
                            <option value="today">Just started today</option>
                            <option value="1-3_days">1-3 days</option>
                            <option value="4-7_days">4-7 days</option>
                            <option value="1-2_weeks">1-2 weeks</option>
                            <option value="2+_weeks">2+ weeks</option>
                            <option value="ongoing">Ongoing / Recurring issue</option>
                        </select>
                    </div>

                    <!-- Staff Only: Additional Fields for Edit -->
                    <div x-show="isEditMode && userRole !== 'tenant'" class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5">
                        <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Management Fields</h5>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Status -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status
                                </label>
                                <select x-model="formData.status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="pending_parts">Pending Parts</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Assigned To -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Assign To
                                </label>
                                <select x-model="formData.assigned_to" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Unassigned</option>
                                    <template x-for="staff in staffUsers" :key="staff.id">
                                        <option :value="staff.id" x-text="staff.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                            <!-- Scheduled Date -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Scheduled Date
                                </label>
                                <input type="date" x-model="formData.scheduled_date" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>

                            <!-- Cost -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Cost (KES)
                                </label>
                                <input type="number" step="0.01" min="0" x-model="formData.cost" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div class="mt-5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Admin Notes
                            </label>
                            <textarea x-model="formData.admin_notes" rows="2" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="Internal notes for staff..."></textarea>
                        </div>

                        <!-- Resolution Notes -->
                        <div class="mt-5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Resolution Notes
                            </label>
                            <textarea x-model="formData.resolution_notes" rows="2" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="How was this resolved?"></textarea>
                        </div>
                    </div>

                    <!-- Request Summary -->
                    <div class="mb-6 rounded-lg bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-800/50 p-4 mt-5">
                        <h5 class="font-medium text-gray-800 dark:text-white/90 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Request Summary
                        </h5>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">Category:</span>
                                <span class="ml-2 font-medium" x-text="getCategoryLabel()">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Priority:</span>
                                <span class="ml-2 font-medium" :class="getPriorityClass()" x-text="getPriorityLabel()">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Unit:</span>
                                <span class="ml-2 font-medium" x-text="getUnitLabel()">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Duration:</span>
                                <span class="ml-2 font-medium" x-text="getDurationLabel()">-</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Estimated Response:</span>
                                <span :class="getUrgencyClass()" class="font-medium" x-text="getUrgencyText()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeModal()" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
                            <span x-show="!isSubmitting" x-text="isEditMode ? 'Update Request' : 'Submit Request'"></span>
                            <span x-show="isSubmitting">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ==================== HISTORY TAB (Staff Only) ==================== -->
            <div x-show="activeTab === 'history' && userRole !== 'tenant'">
                <div class="space-y-4">
                    <template x-for="req in previousRequests" :key="req.id">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer" @click="viewRequest(req.id)">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-sm font-mono font-medium text-gray-500" x-text="req.request_number"></span>
                                    <h4 class="font-medium text-gray-800 dark:text-white/90" x-text="req.name"></h4>
                                </div>
                                <span :class="req.priority_color" class="px-2 py-0.5 text-xs font-medium rounded-full" x-text="req.priority_label"></span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2" x-text="req.description"></p>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="formatDate(req.created_at)"></span>
                                </span>
                                <span :class="req.status_color" class="px-2 py-0.5 text-xs rounded-full" x-text="req.status_label"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="previousRequests.length === 0" class="text-center py-8 text-gray-500">
                        No previous maintenance requests found for this unit.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('maintenanceCreateModal', () => ({
        isOpen: false,
        isEditMode: false,
        editId: null,
        isSubmitting: false,
        activeTab: 'form',
        formErrors: [],
        userRole: '{{ auth()->user()->role->name ?? "guest" }}',
        currentUnit: @json($currentUnit ?? null),
        units: @json($units ?? []),
        previousRequests: [],
        staffUsers: [],
        
        formData: {
            unit_id: '',
            name: '',
            description: '',
            category: '',
            priority: 'medium',
            duration: '',
            status: 'open',
            assigned_to: '',
            admin_notes: '',
            resolution_notes: '',
            scheduled_date: '',
            cost: '',
            images: []
        },
        imagePreviews: [],
        
        init() {
            window.maintenanceModal = this;
            
            // Listen for edit events from the table
            window.addEventListener('open-maintenance-edit', (event) => {
                this.openEditModal(event.detail);
            });
        },
        
        get modalTitle() {
            return this.isEditMode ? 'Edit Maintenance Request' : 'New Maintenance Request';
        },
        
        get modalSubtitle() {
            return this.isEditMode ? 'Update the maintenance request details' : 'Submit a new maintenance issue';
        },
        
        get currentUnitLabel() {
            if (this.currentUnit) {
                return `${this.currentUnit.unit_number} - ${this.currentUnit.estate?.name || 'No Estate'}`;
            }
            return 'No unit assigned';
        },
        
        openModal(unitId = null) {
            this.isOpen = true;
            this.isEditMode = false;
            this.editId = null;
            this.activeTab = 'form';
            this.resetForm();
            
            if (this.userRole === 'tenant' && this.currentUnit) {
                this.formData.unit_id = this.currentUnit.id;
            } else if (unitId) {
                this.formData.unit_id = unitId;
            }
            
            if (this.formData.unit_id) {
                this.loadPreviousRequests(this.formData.unit_id);
            }
            
            document.body.style.overflow = 'hidden';
        },
        
        openEditModal(editData) {
            this.isOpen = true;
            this.isEditMode = true;
            this.editId = editData.id;
            this.activeTab = 'form';
            this.resetForm();
            
            // Populate form with edit data
            this.formData.unit_id = editData.unit_id;
            this.formData.name = editData.name;
            this.formData.description = editData.description;
            this.formData.category = editData.category;
            this.formData.priority = editData.priority || 'medium';
            this.formData.duration = editData.duration || '';
            this.formData.status = editData.status || 'open';
            this.formData.assigned_to = editData.assigned_to || '';
            this.formData.admin_notes = editData.admin_notes || '';
            this.formData.resolution_notes = editData.resolution_notes || '';
            this.formData.scheduled_date = editData.scheduled_date || '';
            this.formData.cost = editData.cost || '';
            
            // Load staff users for assignment
            this.loadStaffUsers();
            
            // Load previous requests for this unit
            if (this.formData.unit_id) {
                this.loadPreviousRequests(this.formData.unit_id);
            }
            
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.isOpen = false;
            this.isEditMode = false;
            this.editId = null;
            this.formErrors = [];
            document.body.style.overflow = '';
        },
        
        resetForm() {
            this.formData = {
                unit_id: '',
                name: '',
                description: '',
                category: '',
                priority: 'medium',
                duration: '',
                status: 'open',
                assigned_to: '',
                admin_notes: '',
                resolution_notes: '',
                scheduled_date: '',
                cost: '',
                images: []
            };
            this.imagePreviews = [];
            this.formErrors = [];
            this.previousRequests = [];
            this.staffUsers = [];
        },
        
        async loadStaffUsers() {
            try {
                const response = await fetch('/api/users/staff', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.staffUsers = data.users;
                }
            } catch (error) {
                console.error('Error loading staff users:', error);
            }
        },
        
        async loadPreviousRequests(unitId) {
            try {
                const response = await fetch(`/maintenance/unit/${unitId}/history`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.previousRequests = data.requests.map(req => ({
                        ...req,
                        priority_label: this.getPriorityLabel(req.priority),
                        priority_color: this.getPriorityColor(req.priority),
                        status_label: this.getStatusLabel(req.status),
                        status_color: this.getStatusColor(req.status),
                        request_number: req.request_number || '#' + String(req.id).padStart(6, '0'),
                        created_at: req.created_at
                    }));
                }
            } catch (error) {
                console.error('Error loading previous requests:', error);
            }
        },
        
        viewRequest(id) {
            if (window.maintenanceViewModal) {
                this.closeModal();
                setTimeout(() => window.maintenanceViewModal.openModal(id), 300);
            }
        },
        
        getCategoryLabel() {
            const labels = {
                plumbing: 'Plumbing', electrical: 'Electrical', hvac: 'HVAC / AC',
                appliance: 'Appliance', structural: 'Structural', pest_control: 'Pest Control',
                cleaning: 'Cleaning', other: 'Other'
            };
            return labels[this.formData.category] || 'Not selected';
        },
        
        getPriorityLabel() {
            const labels = { low: 'Low', medium: 'Medium', high: 'High', emergency: 'Emergency' };
            return labels[this.formData.priority] || 'Medium';
        },
        
        getPriorityClass() {
            const classes = {
                low: 'text-green-600', medium: 'text-yellow-600',
                high: 'text-orange-600', emergency: 'text-red-600'
            };
            return classes[this.formData.priority] || '';
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
            const labels = { open: 'Open', in_progress: 'In Progress', pending_parts: 'Pending Parts', completed: 'Completed', cancelled: 'Cancelled' };
            return labels[status] || status;
        },
        
        getStatusColor(status) {
            const colors = {
                completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                pending_parts: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                open: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },
        
        getDurationLabel() {
            const labels = {
                today: 'Just started today',
                '1-3_days': '1-3 days',
                '4-7_days': '4-7 days',
                '1-2_weeks': '1-2 weeks',
                '2+_weeks': '2+ weeks',
                ongoing: 'Ongoing / Recurring issue'
            };
            return labels[this.formData.duration] || 'Not specified';
        },
        
        getUrgencyText() {
            const urgency = {
                low: 'Within 5-7 business days',
                medium: 'Within 2-3 business days',
                high: 'Within 24 hours',
                emergency: 'Immediate attention required'
            };
            return urgency[this.formData.priority] || 'To be determined';
        },
        
        getUrgencyClass() {
            const classes = {
                low: 'text-green-600', medium: 'text-yellow-600',
                high: 'text-orange-600', emergency: 'text-red-600 font-bold'
            };
            return classes[this.formData.priority] || '';
        },
        
        getUnitLabel() {
            if (this.userRole === 'tenant' && this.currentUnit) {
                return `${this.currentUnit.unit_number} - ${this.currentUnit.estate?.name || 'No Estate'}`;
            }
            const unit = this.units.find(u => u.id == this.formData.unit_id);
            return unit ? `${unit.unit_number} - ${unit.estate?.name || 'No Estate'}` : 'Not selected';
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        
        validateForm() {
            this.formErrors = [];
            
            if (!this.formData.unit_id) {
                this.formErrors.push('Please select a unit');
            }
            if (!this.formData.name || this.formData.name.trim() === '') {
                this.formErrors.push('Please enter an issue title');
            }
            if (!this.formData.description || this.formData.description.trim() === '') {
                this.formErrors.push('Please enter a description');
            }
            if (!this.formData.category) {
                this.formErrors.push('Please select a category');
            }
            if (!this.formData.duration) {
                this.formErrors.push('Please indicate how long this issue has been happening');
            }
            
            return this.formErrors.length === 0;
        },
        
        async submitRequest() {
            if (!this.validateForm()) {
                const modalContent = document.querySelector('.overflow-y-auto');
                if (modalContent) modalContent.scrollTop = 0;
                return;
            }
            
            this.isSubmitting = true;
            
            const formData = new FormData();
            formData.append('unit_id', this.formData.unit_id);
            formData.append('name', this.formData.name);
            formData.append('description', this.formData.description);
            formData.append('category', this.formData.category);
            formData.append('priority', this.formData.priority);
            formData.append('duration', this.formData.duration);
            
            if (this.isEditMode) {
                formData.append('status', this.formData.status);
                formData.append('assigned_to', this.formData.assigned_to || '');
                formData.append('admin_notes', this.formData.admin_notes || '');
                formData.append('resolution_notes', this.formData.resolution_notes || '');
                formData.append('scheduled_date', this.formData.scheduled_date || '');
                formData.append('cost', this.formData.cost || '');
            }
            
            // Add images if any
            this.formData.images.forEach((image, index) => {
                formData.append(`images[${index}]`, image);
            });
            
            try {
                const url = this.isEditMode 
                    ? `/maintenance/${this.editId}` 
                    : '{{ route("maintenance.store") }}';
                const method = this.isEditMode ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.closeModal();
                    alert(data.message || (this.isEditMode ? 'Maintenance request updated successfully!' : 'Maintenance request submitted successfully!'));
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    if (data.errors) {
                        this.formErrors = Object.values(data.errors).flat();
                    } else {
                        this.formErrors = [data.message || 'Failed to submit request'];
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.formErrors = ['An error occurred. Please try again.'];
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>
{{-- resources/views/partials/table/table-transactions.blade.php --}}
<div x-data="transactionsTable({{ json_encode($transactions ?? []) }}, {{ json_encode($showActions ?? false) }}, {{ json_encode($emptyMessage ?? 'No transactions found') }})" 
     x-init="init()" 
     class="space-y-4">
    
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative">
                <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="filterTransactions()" 
                    placeholder="Search transactions..." 
                    class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white w-48 sm:w-64 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            </div>
            
            <!-- Type Filter -->
            <select x-model="filterType" @change="filterTransactions()"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <option value="all">All Types</option>
                <option value="deposit">Deposits</option>
                <option value="withdraw">Withdrawals</option>
                <option value="payment">Payments</option>
            </select>
            
            <!-- Status Filter -->
            <select x-model="filterStatus" @change="filterTransactions()" x-show="showStatusFilter"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <option value="all">All Status</option>
                <option value="confirmed">Completed</option>
                <option value="pending">Pending</option>
            </select>
            
            <!-- Per Page -->
            <select x-model="itemsPerPage" @change="filterTransactions()"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        
        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
            <span>Total: <span x-text="filteredTransactions.length"></span></span>
            <button @click="refreshData" class="text-brand-500 hover:text-brand-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-8">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
        <p class="mt-2 text-sm text-gray-500">Loading transactions...</p>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && filteredTransactions.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="emptyMessage"></p>
    </div>

    <!-- Table -->
    <div x-show="!loading && filteredTransactions.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('description')">
                            Description
                            <svg class="w-3 h-3" :class="sortField === 'description' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('tenant_name')">
                            Tenant
                            <svg class="w-3 h-3" :class="sortField === 'tenant_name' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('payment_method')">
                            Method
                            <svg class="w-3 h-3" :class="sortField === 'payment_method' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Credit / Debit</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 justify-end cursor-pointer" @click="sortBy('amount')">
                            Amount
                            <svg class="w-3 h-3" :class="sortField === 'amount' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('bill_month')">
                            Month to Pay
                            <svg class="w-3 h-3" :class="sortField === 'bill_month' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('notes')">
                            Message
                            <svg class="w-3 h-3" :class="sortField === 'notes' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <span class="flex items-center gap-1 cursor-pointer" @click="sortBy('created_at')">
                            Date
                            <svg class="w-3 h-3" :class="sortField === 'created_at' ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th x-show="showActions" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <template x-for="(tx, index) in paginatedTransactions" :key="tx.id || index">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        
                        <!-- Description -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full"
                                    :class="tx.type === 'deposit' ? 'bg-green-100 dark:bg-green-900/20' : (tx.type === 'payment' ? 'bg-blue-100 dark:bg-blue-900/20' : 'bg-red-100 dark:bg-red-900/20')">
                                    <svg x-show="tx.type === 'deposit'" class="size-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <svg x-show="tx.type === 'payment'" class="size-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="tx.type === 'withdraw'" class="size-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m0 0l6-6m-6 6l6 6"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="tx.description || tx.type"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="tx.reference || tx.uuid?.slice(0, 8) || '—'"></p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Tenant -->
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-700 dark:text-gray-400" x-text="tx.tenant_name || 'System'"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="tx.tenant_unit || ''"></p>
                        </td>
                        
                        <!-- Method -->
                        <td class="px-4 py-3">
                            <span x-show="tx.payment_method && tx.payment_method !== 'Unknown'" 
                                class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <span x-text="tx.payment_method"></span>
                            </span>
                            <span x-show="!tx.payment_method || tx.payment_method === 'Unknown'" 
                                class="text-xs text-gray-400">—</span>
                        </td>
                        
                        <!-- Credit / Debit Pill -->
                        <td class="px-4 py-3 text-center">
                            <span x-show="tx.type === 'deposit' || tx.type === 'payment'" 
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Credit
                            </span>
                            <span x-show="tx.type === 'withdraw'" 
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m0 0l6-6m-6 6l6 6"/>
                                </svg>
                                Debit
                            </span>
                        </td>
                        
                        <!-- Amount -->
                        <td class="px-4 py-3 text-right">
                            <p class="text-sm font-medium" 
                                :class="tx.type === 'deposit' || tx.type === 'payment' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                KES <span x-text="formatNumber(tx.amount)"></span>
                            </p>
                            <p x-show="tx.is_pending" class="text-xs text-yellow-600 dark:text-yellow-400">Awaiting approval</p>
                        </td>
                        
                        <!-- Month to Pay -->
                        <td class="px-4 py-3">
                            <span x-show="tx.bill_month" class="text-sm text-gray-700 dark:text-gray-400" x-text="formatMonth(tx.bill_month)"></span>
                            <span x-show="!tx.bill_month" class="text-sm text-gray-400">—</span>
                        </td>
                        
                        <!-- Message -->
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-[150px] truncate" 
                                x-text="tx.notes || '—'" 
                                :title="tx.notes || ''"></p>
                        </td>

                        <!-- Date -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-gray-400" x-text="formatDate(tx.created_at)"></span>
                        </td>
                        
                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="tx.is_pending ? 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'">
                                <svg x-show="tx.is_pending" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="!tx.is_pending" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="tx.is_pending ? 'Pending' : (tx.status || 'Completed')"></span>
                            </span>
                            <span x-show="tx.is_reconciled" class="ml-1 inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                Reconciled
                            </span>
                        </td>
                        
                        <!-- Actions -->
                        <td x-show="showActions" class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- View Details -->
                                <button @click="viewTransaction(tx)" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                
                                <!-- Approve (only for pending) -->
                                <button x-show="tx.is_pending === true" @click="approveTransaction(tx)" 
                                    class="p-1.5 text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Approve">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                
                                <!-- Reject (only for pending) -->
                                <button x-show="tx.is_pending === true" @click="rejectTransaction(tx)" 
                                    class="p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Reject">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && filteredTransactions.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-200 dark:border-gray-800 pt-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> to 
            <span x-text="Math.min(currentPage * itemsPerPage, filteredTransactions.length)"></span> of 
            <span x-text="filteredTransactions.length"></span> entries
        </p>
        <div class="flex items-center gap-2">
            <button @click="prevPage" :disabled="currentPage === 1" 
                class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </span>
            </button>
            
            <span class="px-3 py-1.5 text-sm" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            
            <button @click="nextPage" :disabled="currentPage === totalPages" 
                class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                <span class="flex items-center gap-1">
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>

<script>
function transactionsTable(initialTransactions = [], initialShowActions = false, initialEmptyMessage = 'No transactions found') {
    return {
        // Data
        transactions: initialTransactions,
        filteredTransactions: [],
        loading: false,
        emptyMessage: initialEmptyMessage,
        showActions: initialShowActions,
        showStatusFilter: true,
        
        // Filters
        searchQuery: '',
        filterType: 'all',
        filterStatus: 'all',
        itemsPerPage: 15,
        
        // Pagination
        currentPage: 1,
        
        // Sorting
        sortField: 'created_at',
        sortDirection: 'desc',
        
        get totalPages() {
            return Math.ceil(this.filteredTransactions.length / this.itemsPerPage) || 1;
        },
        
        get paginatedTransactions() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredTransactions.slice(start, end);
        },
        
        init() {
            this.filterTransactions();
            console.log('Transactions loaded:', this.transactions.length);
            console.log('Show actions:', this.showActions);
        },
        
        filterTransactions() {
            let filtered = [...this.transactions];
            
            // Type filter
            if (this.filterType !== 'all') {
                filtered = filtered.filter(t => t.type === this.filterType);
            }
            
            // Status filter
            if (this.filterStatus !== 'all') {
                const isPending = this.filterStatus === 'pending';
                filtered = filtered.filter(t => t.is_pending === isPending);
            }
            
            // Search
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(t => 
                    (t.description && t.description.toLowerCase().includes(query)) ||
                    (t.reference && t.reference.toLowerCase().includes(query)) ||
                    (t.tenant_name && t.tenant_name.toLowerCase().includes(query)) ||
                    (t.notes && t.notes.toLowerCase().includes(query)) ||
                    (t.payment_method && t.payment_method.toLowerCase().includes(query))
                );
            }
            
            // Sort
            filtered.sort((a, b) => {
                let valA = a[this.sortField] || '';
                let valB = b[this.sortField] || '';
                
                if (this.sortField === 'amount') {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else if (this.sortField === 'created_at') {
                    valA = new Date(valA).getTime() || 0;
                    valB = new Date(valB).getTime() || 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }
                
                if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            this.filteredTransactions = filtered;
            this.currentPage = 1;
        },
        
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'desc';
            }
            this.filterTransactions();
        },
        
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },
        
        refreshData() {
            window.dispatchEvent(new CustomEvent('refresh-transactions'));
        },
        
        viewTransaction(tx) {
            window.dispatchEvent(new CustomEvent('view-transaction', { detail: tx }));
        },
        
        approveTransaction(tx) {
            if (confirm(`Approve this deposit of KES ${this.formatNumber(tx.amount)} for ${tx.tenant_name || 'tenant'}?`)) {
                window.dispatchEvent(new CustomEvent('approve-transaction', { detail: tx }));
            }
        },
        
        rejectTransaction(tx) {
            if (confirm(`Reject this deposit of KES ${this.formatNumber(tx.amount)} for ${tx.tenant_name || 'tenant'}?`)) {
                window.dispatchEvent(new CustomEvent('reject-transaction', { detail: tx }));
            }
        },
        
        formatNumber(value) {
            return parseFloat(value || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        formatMonth(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-KE', { year: 'numeric', month: 'long' });
        }
    };
}
</script>
{{-- resources/views/partials/table/table-subscriptions-invoices.blade.php --}}
<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Subscription Invoices</h3>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500 dark:text-gray-400">Total:</span>
                <span class="font-semibold text-gray-800 dark:text-white">KES {{ number_format($totalAmount ?? 0, 0) }}</span>
            </div>
            <button onclick="window.subscriptionsInvoicesModal?.openModal({{ $planId ?? 0 }})" 
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate
            </button>
        </div>
    </div>
    
    @if(isset($invoices) && $invoices && $invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Invoice #</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Company</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Plan</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Amount</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Due Date</th>
                        <th class="text-left py-2.5 px-3 text-gray-600 dark:text-gray-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-2.5 px-3 text-gray-800 dark:text-white font-medium">#{{ $invoice['invoice_number'] ?? $invoice->id }}</td>
                            <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300">{{ $invoice['company_name'] ?? $invoice->subscription?->company?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">{{ $invoice['plan_name'] ?? $invoice->subscription?->plan?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-gray-800 dark:text-white">KES {{ number_format($invoice['amount'] ?? $invoice->amount ?? 0, 0) }}</td>
                            <td class="py-2.5 px-3">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full font-medium 
                                    @if(($invoice['status'] ?? $invoice->status ?? '') === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif(($invoice['status'] ?? $invoice->status ?? '') === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @elseif(($invoice['status'] ?? $invoice->status ?? '') === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400 @endif">
                                    {{ ucfirst($invoice['status'] ?? $invoice->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">
                                {{ isset($invoice['due_date']) ? \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') : (isset($invoice->due_date) ? $invoice->due_date->format('M d, Y') : 'N/A') }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-2">
                                    <button onclick="window.subscriptionsInvoicesModal?.openModal({{ $planId ?? 0 }}, {{ $invoice['id'] ?? $invoice->id }})" 
                                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 text-sm">
                                        Edit
                                    </button>
                                    <button onclick="markInvoicePaid({{ $invoice['id'] ?? $invoice->id }})" 
                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 text-sm">
                                        Mark Paid
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No subscription invoices found.</p>
            <button onclick="window.subscriptionsInvoicesModal?.openModal({{ $planId ?? 0 }})" 
                class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                Generate Invoice
            </button>
        </div>
    @endif
</div>

<script>
function markInvoicePaid(invoiceId) {
    if (!confirm('Mark this invoice as paid?')) return;
    
    fetch(`/admin/subscriptions/invoices/${invoiceId}/mark-paid`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error marking invoice as paid');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking invoice as paid');
    });
}
</script>
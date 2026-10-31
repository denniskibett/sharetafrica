<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Account Managers</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $accountManagers->count() }} managers assigned to this region</span>
            <button onclick="window.subscriptionsManagersModal?.openModal({{ $planId }})" 
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Assign
            </button>
        </div>
    </div>
    @if($accountManagers && $accountManagers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($accountManagers as $manager)
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition">
                    <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-600 dark:text-purple-400 font-semibold text-base">
                            {{ strtoupper(substr($manager->user?->name ?? 'U', 0, 2)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $manager->user?->name ?? 'Unknown' }}</p>
                            @if($manager->is_primary)
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Primary</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $manager->title ?? 'Account Manager' }}</p>
                        <p class="text-xs text-gray-400">{{ $manager->user?->email ?? '' }}</p>
                        <p class="text-xs text-gray-400">Region: {{ $manager->region_name ?? 'N/A' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.subscriptionsManagersModal?.openModal({{ $planId }}, {{ $manager->id }})" 
                            class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 text-sm">
                            Edit
                        </button>
                        <button onclick="removeManager({{ $planId }}, {{ $manager->id }})" 
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm">
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No account managers assigned to this region.</p>
            <button onclick="window.subscriptionsManagersModal?.openModal({{ $planId }})" 
                class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                Assign Manager
            </button>
        </div>
    @endif
</div>

<script>
function removeManager(planId, managerId) {
    if (!confirm('Remove this account manager from the plan?')) return;
    
    fetch(`/admin/subscriptions/plans/${planId}/managers/${managerId}`, {
        method: 'DELETE',
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
            alert(data.message || 'Error removing manager');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing manager');
    });
}
</script>
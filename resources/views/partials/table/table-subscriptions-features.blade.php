<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Features & Benefits</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($planData['business_features'] ?? []) }} features included</span>
            <button onclick="window.subscriptionsFeaturesModal?.openModal({{ $planData['id'] }})" 
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage
            </button>
        </div>
    </div>
    @if(!empty($planData['business_features']) && count($planData['business_features']) > 0)
        <ul class="space-y-3">
            @foreach($planData['business_features'] as $feature)
                <li class="flex items-start gap-3 text-gray-700 dark:text-gray-300 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ is_array($feature) ? ($feature['name'] ?? $feature[0] ?? '') : $feature }}
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No features listed for this plan.</p>
            <button onclick="window.subscriptionsFeaturesModal?.openModal({{ $planData['id'] }})" 
                class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                Add Features
            </button>
        </div>
    @endif
</div>
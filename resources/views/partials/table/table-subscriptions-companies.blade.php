<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Companies Using This Plan</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $companies->count() }} companies actively subscribed</span>
            <button onclick="window.subscriptionsCompaniesModal?.openModal({{ $planId }})" 
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Assign
            </button>
        </div>
    </div>
    @if($companies && $companies->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($companies as $company)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <span class="text-purple-600 dark:text-purple-400 font-semibold text-sm">
                                {{ strtoupper(substr($company->name ?? 'C', 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $company->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $company->email ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        <a href="{{ route('admin.companies.show', $company->id) }}" class="text-purple-500 hover:text-purple-700 text-sm font-medium">
                            View →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No companies currently using this plan.</p>
            <p class="text-xs text-gray-400 mt-1">Companies will appear here once they subscribe.</p>
            <button onclick="window.subscriptionsCompaniesModal?.openModal({{ $planId }})" 
                class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 border border-purple-300 hover:border-purple-400 rounded-lg transition">
                Assign Companies
            </button>
        </div>
    @endif
</div>
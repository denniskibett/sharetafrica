@props(['tenants' => [], 'showActions' => true, 'showTenancyInfo' => true])

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
        @if($showTenancyInfo)
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Unit</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Move-in Date</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Rent</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Balance</th>
        @endif
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
        @if($showActions)
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
        @endif
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
      @forelse($tenants as $tenant)
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
              <span class="text-purple-600 font-medium">{{ substr($tenant['name'] ?? 'T', 0, 1) }}</span>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $tenant['name'] }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $tenant['email'] }}
              </div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm text-gray-500 dark:text-gray-400">{{ $tenant['phone'] }}</div>
          <div class="text-xs text-gray-400">{{ $tenant['email'] }}</div>
        </td>
        @if($showTenancyInfo)
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          @if($tenant['current_unit'] ?? false)
            <span class="font-medium text-blue-600">{{ $tenant['current_unit']['unit_number'] }}</span>
            <div class="text-xs text-gray-400">{{ $tenant['current_unit']['estate_name'] }}</div>
          @else
            <span class="text-gray-400">No active tenancy</span>
          @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          {{ $tenant['current_unit']['move_in_date_formatted'] ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right text-gray-900 dark:text-white">
          KES {{ number_format($tenant['current_unit']['rent_amount'] ?? 0, 2) }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right {{ ($tenant['balance'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
          KES {{ number_format($tenant['balance'] ?? 0, 2) }}
        </td>
        @endif
        <td class="px-6 py-4 whitespace-nowrap">
          @if($tenant['current_unit'] ?? false)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              Active
            </span>
          @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
              Inactive
            </span>
          @endif
        </td>
        @if($showActions)
        <td class="px-6 py-4 whitespace-nowrap text-sm">
          <button onclick="viewTenant({{ $tenant['id'] }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-2">
            View
          </button>
          <button onclick="editTenant({{ $tenant['id'] }})" class="text-green-600 hover:text-green-900 dark:text-green-400 mr-2">
            Edit
          </button>
          <button onclick="viewInvoices({{ $tenant['id'] }})" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 mr-2">
            Invoices
          </button>
          <button onclick="deleteTenant({{ $tenant['id'] }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
            Delete
          </button>
        </td>
        @endif
      </tr>
      @empty
      <tr>
        <td colspan="{{ 5 + ($showTenancyInfo ? 4 : 0) + ($showActions ? 1 : 0) }}" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          No tenants found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
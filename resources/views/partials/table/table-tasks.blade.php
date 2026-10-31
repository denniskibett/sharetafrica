@props(['tasks' => [], 'taskType' => 'cleaning'])

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estate</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task Type</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
      @forelse($tasks as $task)
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
          {{ $task['unit_number'] }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          {{ $task['estate_name'] }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          {{ ucfirst($task['type']) }}
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
          {{ $task['description'] }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($task['priority'] === 'emergency')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
              Emergency
            </span>
          @elseif($task['priority'] === 'high')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
              High
            </span>
          @elseif($task['priority'] === 'medium')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
              Medium
            </span>
          @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              Low
            </span>
          @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          {{ $task['assigned_to'] ?? 'Unassigned' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($task['status'] === 'completed')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              Completed
            </span>
          @elseif($task['status'] === 'in_progress')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
              In Progress
            </span>
          @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
              Pending
            </span>
          @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
          {{ isset($task['due_date']) ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'N/A' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
          @if($task['status'] !== 'completed')
          <button onclick="markTaskComplete({{ $task['id'] }})" class="text-green-600 hover:text-green-900 dark:text-green-400 mr-2">
            Complete
          </button>
          @endif
          <button onclick="viewTask({{ $task['id'] }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
            View
          </button>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          No {{ $taskType }} tasks found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
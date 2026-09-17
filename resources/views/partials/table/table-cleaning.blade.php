@props(['tasks' => []])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="cleaningTable()" x-init="init()">
    <!-- Table Header with Filters -->
    <div class="flex flex-col justify-between gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row lg:items-center dark:border-gray-800">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Cleaning Tasks</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage and track cleaning assignments</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Status Filters -->
            <div class="hidden h-11 items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 lg:inline-flex dark:bg-gray-900">
                <button @click="filterStatus = 'all'; currentPage = 1" :class="filterStatus === 'all' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                    All (<span x-text="statusCounts.all"></span>)
                </button>
                <button @click="filterStatus = 'pending'; currentPage = 1" :class="filterStatus === 'pending' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                    Pending (<span x-text="statusCounts.pending"></span>)
                </button>
                <button @click="filterStatus = 'in_progress'; currentPage = 1" :class="filterStatus === 'in_progress' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                    In Progress (<span x-text="statusCounts.in_progress"></span>)
                </button>
                <button @click="filterStatus = 'completed'; currentPage = 1" :class="filterStatus === 'completed' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                    Completed (<span x-text="statusCounts.completed"></span>)
                </button>
            </div>

            <!-- Priority Filter -->
            <div class="relative">
                <select 
                    x-model="filterPriority"
                    @change="currentPage = 1"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full min-w-[130px] appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-8 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
                    <option value="all">All Priorities</option>
                    <option value="high">High Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="low">Low Priority</option>
                </select>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.293 7.293C5.68342 6.90258 6.31658 6.90258 6.707 7.293L10 10.586L13.293 7.293C13.6834 6.90258 14.3166 6.90258 14.707 7.293C15.0974 7.68342 15.0974 8.31658 14.707 8.707L10.707 12.707C10.3166 13.0974 9.68342 13.0974 9.293 12.707L5.293 8.707C4.90258 8.31658 4.90258 7.68342 5.293 7.293Z" fill=""/>
                    </svg>
                </span>
            </div>

            <!-- Search -->
            <div class="relative">
                <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input.debounce.300ms="currentPage = 1"
                    placeholder="Search by unit, estate, or assigned staff..." 
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full min-w-[250px] rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
        <span class="ml-3 text-gray-500">Loading tasks...</span>
    </div>

    <!-- Table Content -->
    <div x-show="!loading" class="custom-scrollbar overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200" @click="sortBy('unit_number')">
                        <div class="flex items-center gap-1">
                            <span>Unit</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'unit_number' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                </svg>
                                <svg :class="sortColumn === 'unit_number' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Estate</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Task Type</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Description</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200" @click="sortBy('priority')">
                        <div class="flex items-center gap-1">
                            <span>Priority</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'priority' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                </svg>
                                <svg :class="sortColumn === 'priority' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200" @click="sortBy('status')">
                        <div class="flex items-center gap-1">
                            <span>Status</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'status' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                </svg>
                                <svg :class="sortColumn === 'status' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200" @click="sortBy('assigned_to')">
                        <div class="flex items-center gap-1">
                            <span>Assigned To</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'assigned_to' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                </svg>
                                <svg :class="sortColumn === 'assigned_to' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200" @click="sortBy('due_date')">
                        <div class="flex items-center gap-1">
                            <span>Due Date</span>
                            <span class="flex flex-col gap-0.5">
                                <svg :class="sortColumn === 'due_date' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                </svg>
                                <svg :class="sortColumn === 'due_date' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none">
                                    <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th class="p-4 text-right text-xs font-medium text-gray-700 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <template x-for="task in paginatedTasks" :key="task.id">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 font-medium text-sm" x-text="(task.unit_number || 'U').charAt(0)"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="task.unit_number"></span>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="task.estate_name"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                  :class="task.type === 'deep' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'"
                                  x-text="task.type.charAt(0).toUpperCase() + task.type.slice(1)"></span>
                        </td>
                        <td class="p-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" x-text="task.description"></div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="task.priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                         (task.priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                         'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400')"
                                  x-text="task.priority.charAt(0).toUpperCase() + task.priority.slice(1)"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="task.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                         (task.status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                                         'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400')"
                                  x-text="task.status === 'in_progress' ? 'In Progress' : (task.status.charAt(0).toUpperCase() + task.status.slice(1))"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400" x-text="(task.assigned_to || 'U').charAt(0)"></span>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="task.assigned_to"></span>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm" :class="task.isOverdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-400'">
                                <span x-text="formatDate(task.due_date)"></span>
                                <span x-show="task.isOverdue" class="ml-1 text-xs">(Overdue)</span>
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap text-right">
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" class="shadow-theme-lg dark:bg-gray-dark absolute right-0 z-10 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-cloak>
                                    <template x-if="task.status !== 'completed'">
                                        <button @click="markComplete(task.id); open = false" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-green-600 hover:bg-green-50 hover:text-green-700 dark:text-green-400 dark:hover:bg-green-500/10">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Mark Complete
                                        </button>
                                    </template>
                                    <button @click="viewTask(task.id); open = false" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-blue-600 hover:bg-blue-50 hover:text-blue-700 dark:text-blue-400 dark:hover:bg-blue-500/10">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Details
                                    </button>
                                    <button @click="reassignTask(task.id); open = false" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-purple-600 hover:bg-purple-50 hover:text-purple-700 dark:text-purple-400 dark:hover:bg-purple-500/10">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Reassign
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && filteredTasks.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No cleaning tasks found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No cleaning tasks match your current filters.</p>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && filteredTasks.length > 0" class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
        <div class="pb-3 sm:pb-0">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                Showing <span x-text="((currentPage - 1) * itemsPerPage) + (paginatedTasks.length ? 1 : 0)"></span>
                to <span x-text="((currentPage - 1) * itemsPerPage) + paginatedTasks.length"></span>
                of <span x-text="filteredTasks.length"></span>
            </span>
        </div>
        <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="previousPage" :disabled="currentPage === 1">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/>
                </svg>
            </button>
            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
            <ul class="hidden items-center gap-0.5 sm:flex">
                <template x-for="page in visiblePages" :key="page">
                    <li><a href="#" @click.prevent="goToPage(page)" :class="page === currentPage ? 'bg-brand-500 text-white' : 'hover:bg-brand-500 text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white'" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium" x-text="page"></a></li>
                </template>
            </ul>
            <button class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" @click="nextPage" :disabled="currentPage === totalPages">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cleaningTable', () => ({
        tasks: [],
        filteredTasks: [],
        paginatedTasks: [],
        searchQuery: '',
        filterStatus: 'all',
        filterPriority: 'all',
        sortColumn: 'due_date',
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: 10,
        loading: false,
        
        init() {
            // Get tasks from props
            let rawTasks = @json($tasks);
            console.log('Cleaning tasks loaded:', rawTasks.length);
            
            // Process tasks with additional computed properties
            this.tasks = rawTasks.map(task => ({
                ...task,
                isOverdue: this.checkIfOverdue(task.due_date, task.status)
            }));
            
            this.filterTasks();
        },
        
        get statusCounts() {
            const counts = {
                all: this.tasks.length,
                pending: 0,
                in_progress: 0,
                completed: 0
            };
            this.tasks.forEach(task => {
                if (task.status === 'pending') counts.pending++;
                else if (task.status === 'in_progress') counts.in_progress++;
                else if (task.status === 'completed') counts.completed++;
            });
            return counts;
        },
        
        checkIfOverdue(dueDate, status) {
            if (status === 'completed') return false;
            if (!dueDate) return false;
            return new Date(dueDate) < new Date();
        },
        
        filterTasks() {
            let filtered = [...this.tasks];
            
            // Apply status filter
            if (this.filterStatus !== 'all') {
                filtered = filtered.filter(task => task.status === this.filterStatus);
            }
            
            // Apply priority filter
            if (this.filterPriority !== 'all') {
                filtered = filtered.filter(task => task.priority === this.filterPriority);
            }
            
            // Apply search filter
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(task => 
                    (task.unit_number && task.unit_number.toLowerCase().includes(query)) ||
                    (task.estate_name && task.estate_name.toLowerCase().includes(query)) ||
                    (task.assigned_to && task.assigned_to.toLowerCase().includes(query)) ||
                    (task.description && task.description.toLowerCase().includes(query))
                );
            }
            
            this.filteredTasks = filtered;
            this.sortTasks();
            this.updatePagination();
            this.currentPage = 1;
        },
        
        sortTasks() {
            this.filteredTasks.sort((a, b) => {
                let aVal = a[this.sortColumn];
                let bVal = b[this.sortColumn];
                
                if (this.sortColumn === 'due_date') {
                    aVal = aVal ? new Date(aVal).getTime() : 0;
                    bVal = bVal ? new Date(bVal).getTime() : 0;
                }
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.sortTasks();
            this.updatePagination();
        },
        
        updatePagination() {
            this.totalPages = Math.ceil(this.filteredTasks.length / this.itemsPerPage);
            const start = (this.currentPage - 1) * this.itemsPerPage;
            this.paginatedTasks = this.filteredTasks.slice(start, start + this.itemsPerPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredTasks.length / this.itemsPerPage);
        },
        
        get visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePagination();
            }
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updatePagination();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePagination();
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        
        markComplete(taskId) {
            if (confirm('Mark this task as complete?')) {
                // Call your API endpoint
                fetch(`/cleaning/tasks/${taskId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          // Update local task status
                          const task = this.tasks.find(t => t.id === taskId);
                          if (task) {
                              task.status = 'completed';
                              task.isOverdue = false;
                          }
                          this.filterTasks();
                      } else {
                          alert(data.message || 'Failed to mark task as complete');
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('An error occurred while marking the task as complete');
                  });
            }
        },
        
        viewTask(taskId) {
            window.location.href = `/cleaning/tasks/${taskId}`;
        },
        
        reassignTask(taskId) {
            window.location.href = `/cleaning/tasks/${taskId}/reassign`;
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
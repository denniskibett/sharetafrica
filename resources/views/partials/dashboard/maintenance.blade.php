{{-- resources/views/partials/dashboard/maintenance.blade.php --}}
@extends('layouts.app')

@section('title', 'Maintenance Dashboard')

@section('content')
<div x-data="maintenanceDashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-red-600 p-6 shadow-lg">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="absolute -right-20 -top-20 h-64 w-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Maintenance Dashboard</h2>
                                        <p class="text-orange-100 mt-1" x-text="currentDate"></p>
                                        <div class="mt-2 flex items-center gap-2 text-orange-100 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span>Company: <strong>{{ $company->name ?? 'N/A' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-orange-100">Your Role</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                        Maintenance Staff
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- @include('partials.card.card-dashboard', ['cardData' => array_merge($stats, ['user_role' => 'maintenance_staff'])]) --}}

         <!-- Tabs -->
         <div class="mt-6">
             <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                 <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                     <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Maintenance Tasks</h3>
                     <p class="text-sm text-gray-500 dark:text-gray-400">Tasks assigned to you for maintenance and repairs</p>

        <!-- Tabs -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <button @click="activeTab = 'open'" :class="activeTab === 'open' ? 'border-orange-500 text-orange-600 dark:text-orange-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Open ({{ $roleData['openRequests']->count() ?? 0 }})
                        </button>
                        <button @click="activeTab = 'in_progress'" :class="activeTab === 'in_progress' ? 'border-orange-500 text-orange-600 dark:text-orange-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            In Progress ({{ $roleData['inProgressRequests']->count() ?? 0 }})
                        </button>
                        <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'border-orange-500 text-orange-600 dark:text-orange-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Completed ({{ $roleData['completedRequests']->count() ?? 0 }})
                        </button>
                        <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'border-orange-500 text-orange-600 dark:text-orange-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            All ({{ $roleData['allRequests']->count() ?? 0 }})
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <div x-show="activeTab === 'open'">
                        @include('partials.table.table-maintenance', ['requests' => $roleData['openRequests'] ?? []])
                    </div>
                    <div x-show="activeTab === 'in_progress'">
                        @include('partials.table.table-maintenance', ['requests' => $roleData['inProgressRequests'] ?? []])
                    </div>
                    <div x-show="activeTab === 'completed'">
                        @include('partials.table.table-maintenance', ['requests' => $roleData['completedRequests'] ?? []])
                    </div>
                    <div x-show="activeTab === 'all'">
                        @include('partials.table.table-maintenance', ['requests' => $roleData['allRequests'] ?? []])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function maintenanceDashboard() {
    return {
        activeTab: 'open',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        init() {
            console.log('Maintenance Dashboard loaded');
            console.log('Open requests:', {{ $roleData['openRequests']->count() ?? 0 }});
        }
    };
}
</script>
@endsection
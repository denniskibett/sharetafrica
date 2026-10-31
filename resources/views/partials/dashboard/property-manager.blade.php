{{-- resources/views/partials/dashboard/property-manager.blade.php --}}
@extends('layouts.app')

@section('title', 'Property Manager Dashboard')

@section('content')
<div x-data="propertyManagerDashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 p-6 shadow-lg">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="absolute -right-20 -top-20 h-64 w-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ffffff&color=4f46e5' }}" 
                                             alt="avatar" class="h-14 w-14 rounded-full">
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Property Manager Dashboard</h2>
                                        <p class="text-indigo-100 mt-1" x-text="currentDate"></p>
                                        <div class="mt-2 flex items-center gap-2 text-indigo-100 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span>Managing: <strong>{{ $company->name ?? 'N/A' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-indigo-100">Your Role</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                        Property Manager
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        @include('partials.card.card-dashboard', ['cardData' => array_merge($stats, ['user_role' => 'property_manager'])])

        <!-- Tabs -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <button @click="activeTab = 'readings'" :class="activeTab === 'readings' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Water Readings
                        </button>
                        <button @click="activeTab = 'vacant'" :class="activeTab === 'vacant' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Long Term Vacant ({{ collect($roleData['longTermVacant'] ?? [])->count() }})
                        </button>
                        <button @click="activeTab = 'maintenance'" :class="activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Maintenance ({{ collect($roleData['maintenanceRequests'] ?? [])->count() }})
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <div x-show="activeTab === 'readings'">
                        @include('partials.table.table-readings', ['readings' => $roleData['recentReadings'] ?? [], 'showActions' => true, 'showConsumption' => true])
                    </div>
                    <div x-show="activeTab === 'vacant'">
                        @include('partials.table.table-units', ['units' => $roleData['longTermVacant'] ?? []])
                    </div>
                    <div x-show="activeTab === 'maintenance'">
                        @include('partials.table.table-maintenance', ['requests' => $roleData['maintenanceRequests'] ?? []])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function propertyManagerDashboard() {
    return {
        activeTab: 'readings',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        init() {
            console.log('Property Manager Dashboard loaded');
        }
    };
}
</script>
@endsection
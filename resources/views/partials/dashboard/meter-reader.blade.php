{{-- resources/views/partials/dashboard/meter-reader.blade.php --}}
@extends('layouts.app')

@section('title', 'Meter Reader Dashboard')

@section('content')
<div x-data="meterReaderDashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-6 shadow-lg">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="absolute -right-20 -top-20 h-64 w-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Meter Reader Dashboard</h2>
                                        <p class="text-emerald-100 mt-1" x-text="currentDate"></p>
                                        <div class="mt-2 flex items-center gap-2 text-emerald-100 text-sm">
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
                                    <p class="text-sm text-emerald-100">Your Role</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                        Meter Reader
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6 mb-6">
            <!-- Pending Readings - FIXED: use pendingCount or pendingReadings -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::now()->format('F Y') }} Pending</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ $roleData['pendingCount'] ?? $roleData['pendingReadings']->count() ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/15">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Month Readings -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::now()->format('F Y') }} Readings</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ $roleData['thisMonthReadings'] ?? $roleData['currentMonthReadings']->count() ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Consumption -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Consumption</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($roleData['totalConsumption'] ?? 0) }} m³
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 dark:bg-green-500/15">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Readings -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Readings</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ $roleData['allWaterReadings'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-500/15">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.card.card-dashboard', ['cardData' => array_merge($stats, ['user_role' => 'meter_reader'])])

        <!-- Tabs -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <!-- Pending Readings Tab -->
                        <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            {{ \Carbon\Carbon::now()->format('F Y') }} Pending ({{ $roleData['pendingCount'] ?? $roleData['pendingReadings']->count() ?? 0 }})
                        </button>
                        
                        <!-- Current Month Readings Tab -->
                        <button @click="activeTab = 'current'" :class="activeTab === 'current' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            {{ \Carbon\Carbon::now()->format('F Y') }} Readings ({{ $roleData['thisMonthReadings'] ?? $roleData['currentMonthReadings']->count() ?? 0 }})
                        </button>
                        
                        <!-- Reading History Tab -->
                        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Reading History ({{ $roleData['firstReadingDate'] ?? 'N/A' }} - {{ $roleData['lastReadingDate'] ?? 'N/A' }}) ({{ $roleData['unitsWithHistory']->count() ?? 0 }})
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <!-- Pending Readings Tab -->
                    <div x-show="activeTab === 'pending'">
                        @include('partials.table.table-readings', [
                            'readings' => $roleData['pendingReadings'] ?? [],
                            'showActions' => true,
                            'showConsumption' => false,
                            'units' => $roleData['units'] ?? [],
                            'estates' => $roleData['estates'] ?? []
                        ])
                    </div>
                    
                    <!-- Current Month Readings Tab -->
                    <div x-show="activeTab === 'current'">
                        @include('partials.table.table-readings', [
                            'readings' => $roleData['currentMonthReadings'] ?? [],
                            'showActions' => true,
                            'showConsumption' => true,
                            'units' => $roleData['units'] ?? [],
                            'estates' => $roleData['estates'] ?? []
                        ])
                    </div>
                    
                    <!-- Reading History Tab -->
                    <div x-show="activeTab === 'history'">
                        @include('partials.table.table-readings', [
                            'readings' => $roleData['unitsWithHistory'] ?? [],
                            'showActions' => false,
                            'showConsumption' => true,
                            'units' => $roleData['units'] ?? [],
                            'estates' => $roleData['estates'] ?? []
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function meterReaderDashboard() {
    return {
        activeTab: 'pending',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        init() {
            console.log('Meter Reader Dashboard loaded');
            console.log('Pending readings:', {{ $roleData['pendingCount'] ?? $roleData['pendingReadings']->count() ?? 0 }});
            console.log('Current month readings:', {{ $roleData['thisMonthReadings'] ?? $roleData['currentMonthReadings']->count() ?? 0 }});
            console.log('Units with history:', {{ $roleData['unitsWithHistory']->count() ?? 0 }});
        }
    };
}
</script>
@endsection
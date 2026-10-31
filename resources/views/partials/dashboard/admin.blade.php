{{-- resources/views/dashboard/admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard - ' . ($company->name ?? 'Property Management'))

@section('content')
<div x-data="adminDashboard()" x-init="init()">
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card - Admin Style -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 p-6 shadow-lg">
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
                                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ffffff&color=3b82f6' }}" 
                                             alt="avatar" class="h-14 w-14 rounded-full">
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Welcome back, {{ Auth::user()->first_name ?: Auth::user()->name }}!</h2>
                                        <p class="text-blue-100 mt-1" x-text="currentDate"></p>
                                        <div class="mt-2 flex items-center gap-2 text-blue-100 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span>Managing: <strong>{{ $company->name ?? 'Your Company' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-blue-100">Your Role</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                        {{ ucfirst(str_replace('_', ' ', Auth::user()->role->name ?? 'Admin')) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards (6 cards as before, but company-specific) -->
        @include('partials.card.card-dashboard', ['cardData' => array_merge($stats, ['user_role' => 'admin'])])


        <!-- Tab Content -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'border-blue-500 text-blue-600 dark:text-blue-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Recent Invoices
                        </button>
                        <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-blue-500 text-blue-600 dark:text-blue-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Recent Payments
                        </button>
                        <button @click="activeTab = 'readings'" :class="activeTab === 'readings' ? 'border-blue-500 text-blue-600 dark:text-blue-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Water Readings
                        </button>
                        <button @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'border-blue-500 text-blue-600 dark:text-blue-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            Analytics
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <div x-show="activeTab === 'invoices'">
                        @include('partials.table.table-invoices', ['invoices' => $recentInvoices])
                    </div>
                    <div x-show="activeTab === 'payments'">
                        @include('partials.table.table-payments', ['payments' => $recentPayments])
                    </div>
                    <div x-show="activeTab === 'readings'">
                        @include('partials.table.table-readings', ['readings' => $waterReadings])
                    </div>
                    <div x-show="activeTab === 'analytics'">
                        @include('partials.chart.revenue-chart', ['monthlyRevenue' => $monthlyRevenue])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adminDashboard() {
    return {
        activeTab: 'invoices',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        init() {
            console.log('Admin Dashboard loaded');
        }
    };
}
</script>
@endsection
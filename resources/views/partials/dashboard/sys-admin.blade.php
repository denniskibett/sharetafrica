{{-- resources/views/partials/dashboard/sys-admin.blade.php --}}
@extends('layouts.app')

@section('title', 'System Administration')

@section('content')
<div x-data="sysAdminDashboard()" x-init="init()" x-cloak>
    <div class="container-fluid px-4 py-4">
        
        <!-- Welcome Card - SysAdmin Style -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-700 p-6 shadow-lg">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="absolute -right-20 -top-20 h-64 w-64 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">System Administration</h2>
                                        <p class="text-purple-100 mt-1" x-text="currentDate"></p>
                                        <p class="text-purple-50 text-sm mt-2">Manage companies, subscriptions, and system-wide settings</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-sm text-purple-100">Logged in as</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                                            {{ Auth::user()->name }}
                                        </span>
                                    </div>
                                    <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ffffff&color=7c3aed' }}" 
                                         alt="avatar" class="h-14 w-14 rounded-full border-2 border-white shadow-lg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Companies</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($stats['total_companies'] ?? 0) }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-500/15">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        <span>Active: {{ number_format($stats['active_companies'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                        <span>Pending: {{ number_format($stats['pending_companies'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Users</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($stats['total_users'] ?? 0) }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        <span>Verified: {{ number_format($stats['verified_users'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                        <span>Pending: {{ number_format($stats['pending_verification_users'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Platform Metrics</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($stats['total_units'] ?? 0) }} Units
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 dark:bg-green-500/15">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4V4z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                    {{ number_format($stats['total_tenants'] ?? 0) }} Tenants | 
                    KES {{ number_format($stats['total_revenue'] ?? 0, 0) }} Revenue
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Monthly Recurring Revenue</span>
                        <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                            KES {{ number_format($stats['monthly_recurring_revenue'] ?? 0, 0) }}
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/15">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                    Platform-wide MRR
                </div>
            </div>
        </div>

        <!-- Subscription Stats Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Active Subscriptions</span>
                    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($subscriptionStats['active'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-500/15">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs">
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    <span>Active: {{ number_format($subscriptionStats['active'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    <span>Trial: {{ number_format($subscriptionStats['trial'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                    <span>Past Due: {{ number_format($subscriptionStats['past_due'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    <span>Cancelled: {{ number_format($subscriptionStats['cancelled'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                    <span>Expired: {{ number_format($subscriptionStats['expired'] ?? 0) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">MRR: <strong class="text-gray-900 dark:text-white">KES {{ number_format($subscriptionStats['total_mrr'] ?? 0, 0) }}</strong></span>
                    <span class="text-gray-500">Monthly: {{ number_format($subscriptionStats['monthly_cycle'] ?? 0) }}</span>
                    <span class="text-gray-500">Yearly: {{ number_format($subscriptionStats['yearly_cycle'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Revenue by Plan Chart -->
        @if(!empty($subscriptionStats['revenue_by_plan']) && count($subscriptionStats['revenue_by_plan']) > 0)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 mb-6">
            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Revenue by Plan</h4>
            <div class="space-y-3">
                @foreach($subscriptionStats['revenue_by_plan'] as $plan)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $plan['plan_name'] }}</span>
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ number_format($plan['count']) }} companies · 
                            {{ number_format($plan['avg_units'] ?? 0) }} avg units · 
                            KES {{ number_format($plan['revenue'] ?? 0, 0) }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        @php
                            $percentage = $maxRevenue > 0 ? min(100, (($plan['revenue'] ?? 0) / $maxRevenue) * 100) : 0;
                        @endphp
                        <div class="bg-purple-600 h-2 rounded-full" 
                            style="width: {{ max(0, $percentage) }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tab Navigation -->
        <div class="mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <!-- Tab 1: Companies -->
                        <button @click="activeTab = 'companies'" :class="activeTab === 'companies' ? 'border-purple-500 text-purple-600 dark:text-purple-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Companies
                        </button>
                        
                        <!-- Tab 2: Pending Users -->
                        <button @click="activeTab = 'users'" :class="activeTab === 'users' ? 'border-purple-500 text-purple-600 dark:text-purple-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Pending Users
                        </button>
                        
                        <!-- Tab 3: Subscription Plans -->
                        <button @click="activeTab = 'subscription_plans'" :class="activeTab === 'subscription_plans' ? 'border-purple-500 text-purple-600 dark:text-purple-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Plans
                        </button>
                        
                        <!-- Tab 4: Company Subscriptions -->
                        <button @click="activeTab = 'company_subscriptions'" :class="activeTab === 'company_subscriptions' ? 'border-purple-500 text-purple-600 dark:text-purple-400 border-b-2 -mb-px' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-4 py-2 text-sm font-medium transition-colors">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Company Subs
                        </button>
                    </div>
                </div>
                
                <div class="p-5">
                    <!-- Companies Tab Content -->
                    <div x-show="activeTab === 'companies'">
                        @include('partials.table.table-companies', ['companies' => $companies ?? []])
                    </div>
                    
                    <!-- Pending Users Tab Content -->
                    <div x-show="activeTab === 'users'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($pendingUsers ?? [] as $user)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-sm font-medium">{{ substr($user['name'], 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $user['email'] }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                {{ ucfirst($user['role_name']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            @if($user['company_name'])
                                                {{ $user['company_name'] }}
                                            @else
                                                <span class="text-gray-400">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $user['created_at_formatted'] }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <!-- Verify Button -->
                                                <button @click="verifyUser({{ $user['id'] }})" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 dark:bg-green-900/30 dark:hover:bg-green-900/50 dark:text-green-400 text-xs font-medium rounded-lg transition">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Verify
                                                </button>
                                                <!-- Assign Company Button -->
                                                <button @click="openAssignModal({{ $user['id'] }}, '{{ addslashes($user['name']) }}', '{{ addslashes($user['email']) }}', '{{ addslashes($user['role_name']) }}')" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 text-xs font-medium rounded-lg transition">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    Assign
                                                </button>
                                                <!-- Suspend Button -->
                                                <button @click="suspendUser({{ $user['id'] }}, '{{ addslashes($user['name']) }}')" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-orange-100 hover:bg-orange-200 text-orange-700 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 dark:text-orange-400 text-xs font-medium rounded-lg transition">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    </svg>
                                                    Suspend
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium">No pending users</h3>
                                            <p class="mt-1 text-sm">All users have been verified and assigned to companies.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- SUBSCRIPTION PLANS TAB CONTENT -->
                    <div x-show="activeTab === 'subscription_plans'">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Subscription Plans</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage available subscription plans and pricing tiers</p>
                            </div>
                            <a href="{{ route('admin.subscriptions.plans.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Manage Plans
                            </a>
                        </div>
                        @include('partials.table.table-subscriptions')
                    </div>
                    
                    <!-- COMPANY SUBSCRIPTIONS TAB CONTENT -->
                    <div x-show="activeTab === 'company_subscriptions'">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Company Subscriptions</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View which companies are subscribed to which plans</p>
                            </div>
                            <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                View All
                            </a>
                        </div>
                        
                        <!-- Server-side rendered table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billing Cycle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Starts</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ends</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Auto Renew</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($activeSubscriptions ?? [] as $subscription)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $subscription['company_name'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $subscription['plan_name'] ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full 
                                                @if($subscription['status'] === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                                @elseif($subscription['status'] === 'trial') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                                @elseif($subscription['status'] === 'past_due') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                                @elseif($subscription['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                                @endif">
                                                {{ ucfirst($subscription['status'] ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="capitalize">{{ $subscription['billing_cycle'] ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $subscription['starts_at'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $subscription['ends_at'] ?? 'Never' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full 
                                                @if($subscription['auto_renew']) bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                                @endif">
                                                {{ $subscription['auto_renew'] ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex gap-2">
                                                @if($subscription['company_id'])
                                                <a href="{{ route('admin.companies.show', $subscription['company_id']) }}" 
                                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                    View Company
                                                </a>
                                                @endif
                                                <button onclick="cancelSubscription({{ $subscription['id'] }})" 
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium">No active subscriptions</h3>
                                            <p class="mt-1 text-sm">No companies have active subscriptions yet.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('partials.modal.alert-modal')
@include('partials.modal.user-assign-modal')


<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function sysAdminDashboard() {
    return {
        activeTab: 'companies',
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        
        init() {
            console.log('SysAdmin Dashboard loaded');
        },
        
        verifyUser(userId) {
            if (confirm('Verify this user? They will be able to log in and access the system.')) {
                fetch(`/admin/users/${userId}/verify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User verified successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to verify user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while verifying the user');
                });
            }
        },
        
        suspendUser(userId, userName) {
            if (confirm(`Are you sure you want to suspend "${userName}"? They will not be able to log in.`)) {
                fetch(`/admin/users/${userId}/suspend`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'User suspended successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to suspend user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while suspending the user');
                });
            }
        },
        
        openAssignModal(userId, userName, userEmail, userRole) {
            if (window.userAssignModal) {
                window.userAssignModal.openModal(userId, userName, userEmail, userRole);
            } else {
                console.error('User assign modal not found');
                alert('Modal not loaded. Please refresh the page and try again.');
            }
        }
    };
}

function cancelSubscription(subscriptionId) {
    if (confirm('Are you sure you want to cancel this subscription?')) {
        fetch(`/admin/subscriptions/subscription/${subscriptionId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Subscription cancelled successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel subscription');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the subscription');
        });
    }
}
</script>
@endsection
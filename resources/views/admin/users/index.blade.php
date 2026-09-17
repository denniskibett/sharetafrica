@extends('layouts.app')

@section('title', 'User Management')

@php
    $users = $users ?? collect([]);
    $roles = $roles ?? collect([]);
    $companies = $companies ?? collect([]);

    $totalUsers = $users->total() ?? 0;
    $activeCount = $users->where('status', 0)->count();
    $pendingCount = $users->where('status', 2)->count();
    $inactiveCount = $users->where('status', 1)->count();

    // Additional stats – role-based breakdown using Spatie
    $staffCount = $users->filter(function($u) { 
        $roles = $u->getRoleNames()->toArray();
        return !empty(array_intersect($roles, ['admin', 'property_manager', 'accountant', 'security', 'maintenance', 'meter_reader', 'cleaning_staff']));
    })->count();
    
    $tenantCount = $users->filter(function($u) { 
        return $u->hasRole('tenant'); 
    })->count();
    
    $verifiedCount = $users->filter(function($u) { 
        return !is_null($u->email_verified_at); 
    })->count();

    $currentRole = request('role_id', '');
    $currentCompany = request('company_id', '');
    $currentStatus = request('status', '');
    $currentSearch = request('search', '');
    $currentVerified = request('verified', '');
    $currentSort = request('sort', 'created_at');
    $currentDirection = request('direction', 'desc');
    $currentDateFrom = request('date_from', '');
    $currentDateTo = request('date_to', '');
@endphp

@section('content')
<div class="container mx-auto px-4 py-6 max-w-screen-2xl">

    <!-- CSRF Meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ============================================================ -->
    <!-- HEADER – Distinct Purple/Indigo Theme -->
    <!-- ============================================================ -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-users text-indigo-600 dark:text-indigo-400"></i>
                User Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all system users, their roles, and access across the platform.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="exportUsers()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                <i class="fas fa-file-export"></i> Export
            </button>
            <button onclick="exportSelectedUsers()" id="exportSelectedBtn" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                <i class="fas fa-file-export"></i> Export Selected
            </button>
            <button onclick="openAddUserDrawer()" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- STATS CARDS – Unique Layout with Role Breakdown -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-indigo-100 dark:bg-indigo-900/20 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white" id="statsTotal">{{ $totalUsers }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-100 dark:bg-emerald-900/20 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
                    <p class="text-2xl font-bold text-emerald-600" id="statsActive">{{ $activeCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-100 dark:bg-amber-900/20 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-amber-600" id="statsPending">{{ $pendingCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 dark:text-amber-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-rose-100 dark:bg-rose-900/20 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactive</p>
                    <p class="text-2xl font-bold text-rose-600" id="statsInactive">{{ $inactiveCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                    <i class="fas fa-user-slash text-rose-600 dark:text-rose-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Row – Unique to Users Module -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-tie text-indigo-600 dark:text-indigo-400 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Staff</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $staffCount }}</p>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-emerald-600 dark:text-emerald-400 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tenants</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $tenantCount }}</p>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-double text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Verified</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $verifiedCount }}</p>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-building text-purple-600 dark:text-purple-400 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Companies</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $companies->count() }}</p>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ACTIVE FILTER BAR -->
    <!-- ============================================================ -->
    @if($currentRole || $currentCompany || $currentStatus !== '' || $currentSearch || $currentVerified || $currentDateFrom || $currentDateTo)
    <div class="mb-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 p-3 border border-indigo-200 dark:border-indigo-800 flex flex-wrap items-center gap-2 text-sm">
        <i class="fas fa-filter text-indigo-600 dark:text-indigo-400"></i>
        <span class="font-medium text-indigo-700 dark:text-indigo-300">Active filters:</span>
        @if($currentRole)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                Role: {{ $roles->where('id', $currentRole)->first()?->name ?? 'Unknown' }}
            </span>
        @endif
        @if($currentCompany)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                Company: {{ $companies->where('id', $currentCompany)->first()?->name ?? 'Unknown' }}
            </span>
        @endif
        @if($currentStatus !== '')
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                Status: {{ $currentStatus == 0 ? 'Active' : ($currentStatus == 1 ? 'Inactive' : 'Pending') }}
            </span>
        @endif
        @if($currentSearch)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                Search: "{{ $currentSearch }}"
            </span>
        @endif
        @if($currentVerified)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                Verified: {{ $currentVerified == 'verified' ? '✅ Verified' : '⏳ Unverified' }}
            </span>
        @endif
        @if($currentDateFrom)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                From: {{ \Carbon\Carbon::parse($currentDateFrom)->format('d M Y') }}
            </span>
        @endif
        @if($currentDateTo)
            <span class="inline-flex items-center rounded-full bg-indigo-200 dark:bg-indigo-800 px-3 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-200">
                To: {{ \Carbon\Carbon::parse($currentDateTo)->format('d M Y') }}
            </span>
        @endif
        <a href="{{ route('users.index') }}" class="ml-auto text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-xs">
            <i class="fas fa-times"></i> Clear All
        </a>
    </div>
    @endif

    <!-- ============================================================ -->
    <!-- FILTER FORM – Cleaner Layout -->
    <!-- ============================================================ -->
    <div class="mb-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
        <form id="filterForm" method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2">
            <div class="relative lg:col-span-1">
                <input type="text" name="search" id="searchInput" value="{{ $currentSearch }}" placeholder="🔍 Search users..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 pl-8 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
                <div class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <select name="role_id" id="roleFilter" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected($currentRole == $role->id)>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                @endforeach
            </select>
            <select name="company_id" id="companyFilter" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
                <option value="">All Companies</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" @selected($currentCompany == $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            <select name="status" id="statusFilter" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
                <option value="">All Status</option>
                <option value="0" @selected($currentStatus === '0')>Active</option>
                <option value="1" @selected($currentStatus === '1')>Inactive</option>
                <option value="2" @selected($currentStatus === '2')>Pending</option>
            </select>
            <select name="verified" id="verifiedFilter" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
                <option value="">All</option>
                <option value="verified" @selected($currentVerified == 'verified')>✅ Verified</option>
                <option value="unverified" @selected($currentVerified == 'unverified')>⏳ Unverified</option>
            </select>
            <input type="date" name="date_from" id="dateFrom" value="{{ $currentDateFrom }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
            <input type="date" name="date_to" id="dateTo" value="{{ $currentDateTo }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition h-9">
            <div class="flex gap-1.5">
                <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition h-9">
                    <i class="fas fa-search mr-1.5 text-xs"></i> Apply
                </button>
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition h-9 w-9" title="Clear filters">
                    <i class="fas fa-undo text-xs"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- ============================================================ -->
    <!-- BULK ACTIONS BAR -->
    <!-- ============================================================ -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button id="selectAllBtn" onclick="selectAllUsers()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="fas fa-check-double"></i> Select All
            </button>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                <span id="selectedCount" class="font-semibold text-indigo-600 dark:text-indigo-400">0</span> selected
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select id="bulkAction" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="change_role">Change Role</option>
                <option value="change_company">Change Company</option>
                <option value="delete">Delete</option>
            </select>
            <button id="applyBulkAction" onclick="applyBulkAction()" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                Apply
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- USERS TABLE – Clean with Visual Status Indicators -->
    <!-- ============================================================ -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10">
                            <input type="checkbox" id="masterCheckbox" onchange="toggleMasterCheckbox()" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('id')">
                            ID <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('name')">
                            User <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('email')">
                            Email <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('phone')">
                            Phone <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('role_id')">
                            Role <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('company_id')">
                            Company <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('status')">
                            Status <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-300 transition" onclick="sortTable('created_at')">
                            Joined <i class="fas fa-sort ml-1 text-xs"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        @php
                            $primaryRole = $user->getRoleNames()->first() ?? 'No Role';
                            $roleNames = $user->getRoleNames()->toArray();
                            $isSysadmin = $user->hasRole('sysadmin');
                            $isTenant = $user->hasRole('tenant');
                            $verified = !is_null($user->email_verified_at);
                            $statusLabel = $user->status == 0 ? 'Active' : ($user->status == 1 ? 'Inactive' : 'Pending');
                            $statusColor = $user->status == 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($user->status == 1 ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400');
                            $verifiedIcon = $verified ? 'fa-check-circle text-emerald-500' : 'fa-clock text-amber-500';
                        @endphp
                        <tr id="user-row-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" data-id="{{ $user->id }}">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                #{{ $user->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-sm font-bold">
                                            {{ $user->getInitialsAttribute() ?? substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-white" data-field="name">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <i class="fas {{ $verifiedIcon }} text-xs"></i>
                                            {{ $verified ? 'Verified' : 'Unverified' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300" data-field="email">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300" data-field="phone">{{ $user->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                    {{ $primaryRole }}
                                </span>
                                @if(count($roleNames) > 1)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+{{ count($roleNames) - 1 }}</span>
                                @endif
                                <span data-field="role_id" data-value="{{ $user->roles->first()?->id ?? '' }}" style="display:none;">{{ $user->roles->first()?->id ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $user->company->name ?? '-' }}
                                <span data-field="company_id" data-value="{{ $user->company_id ?? '' }}" style="display:none;">{{ $user->company_id ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                                <span data-field="status" data-value="{{ $user->status }}" style="display:none;">{{ $user->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('users.show', $user->id) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$isSysadmin)
                                        <button onclick="if(confirm('Delete user {{ $user->name }}?')) { fetch('{{ route('users.destroy', $user->id) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(r=>r.json()).then(d=>{ if(d.success){ location.reload(); } else { alert(d.message); } }); }" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                    @if(!$verified)
                                        <button onclick="verifyUser({{ $user->id }})" class="p-1.5 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition" title="Verify">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    <button onclick="openAssignModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $primaryRole }}')" class="p-1.5 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition" title="Assign Company">
                                        <i class="fas fa-building"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-3xl block mb-2 text-gray-300 dark:text-gray-600"></i>
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Show:</span>
                <select id="perPage" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition" onchange="changePerPage()">
                    <option value="10" @selected($users->perPage() == 10)>10</option>
                    <option value="20" @selected($users->perPage() == 20)>20</option>
                    <option value="50" @selected($users->perPage() == 50)>50</option>
                    <option value="100" @selected($users->perPage() == 100)>100</option>
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ADD USER DRAWER -->
<!-- ============================================================ -->
<div id="addUserDrawer" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddUserDrawer()"></div>
    <div class="fixed right-0 top-0 h-full w-full max-w-2xl bg-white dark:bg-gray-900 shadow-2xl transform transition-transform duration-300 ease-in-out overflow-y-auto" style="transform: translateX(100%);">
        <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <i class="fas fa-user-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Add User</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Create a new system user</p>
                </div>
            </div>
            <button onclick="closeAddUserDrawer()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="addUserForm" onsubmit="submitAddUser(event)">
                @csrf
                <div class="space-y-4">
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-500"></i> Personal Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="userName" name="name" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="userEmail" name="email" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                                <input type="text" id="userPhone" name="phone" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-lock text-indigo-500"></i> Password
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="userPassword" name="password" required minlength="8" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="userPasswordConfirmation" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-briefcase text-indigo-500"></i> Role &amp; Company
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <select id="userRole" name="role_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                                    <option value="">Select Role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                                <select id="userCompany" name="company_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                                    <option value="">No Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select id="userStatus" name="status" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                                    <option value="0">Active</option>
                                    <option value="1">Inactive</option>
                                    <option value="2">Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeAddUserDrawer()" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">Cancel</button>
                        <button type="submit" id="submitUserBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODALS -->
<!-- ============================================================ -->
<div id="changeRoleModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('changeRoleModal')"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Change Role</h3>
                <button onclick="closeModal('changeRoleModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select new role for <span id="changeRoleCount" class="font-semibold">0</span> user(s).</p>
                <select id="newRoleId" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                    <option value="">Select Role...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                <button onclick="closeModal('changeRoleModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="executeBulkAction('change_role')" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Apply</button>
            </div>
        </div>
    </div>
</div>

<div id="changeCompanyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('changeCompanyModal')"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Change Company</h3>
                <button onclick="closeModal('changeCompanyModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select new company for <span id="changeCompanyCount" class="font-semibold">0</span> user(s).</p>
                <select id="newCompanyId" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                    <option value="">Select Company...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                <button onclick="closeModal('changeCompanyModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="executeBulkAction('change_company')" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Apply</button>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('deleteModal')"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">Confirm Delete</h3>
                <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Are you sure you want to delete <span id="deleteCount" class="font-semibold text-red-600">0</span> user(s)? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                <button onclick="closeModal('deleteModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                <button onclick="executeBulkAction('delete')" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 px-4 py-3 shadow-lg border border-green-200 dark:border-green-800 flex items-center gap-2">
        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
        <p id="toastMessage" class="text-sm font-medium text-green-800 dark:text-green-200"></p>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================================ -->
<script>
    console.log('🔍 User Management script loaded');

    let selectedIds = new Set();
    let allIds = [];

    const masterCheckbox = document.getElementById('masterCheckbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkActionSelect = document.getElementById('bulkAction');
    const drawer = document.getElementById('addUserDrawer');
    const drawerPanel = drawer ? drawer.querySelector('.fixed.right-0') : null;

    function openAddUserDrawer() {
        if (!drawer) return;
        drawer.style.display = 'block';
        setTimeout(function() {
            if (drawerPanel) drawerPanel.style.transform = 'translateX(0)';
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeAddUserDrawer() {
        if (!drawer || !drawerPanel) return;
        drawerPanel.style.transform = 'translateX(100%)';
        setTimeout(function() {
            drawer.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    function showToast(message, type) {
        type = type || 'success';
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMessage');
        if (!toast || !toastMsg) {
            console.warn('Toast elements missing, using alert');
            alert(message);
            return;
        }
        toastMsg.textContent = message;
        toast.classList.remove('hidden');

        const colors = {
            success: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
            error: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200',
            warning: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200'
        };
        const container = toast.querySelector('div');
        if (container) {
            container.className = 'rounded-lg px-4 py-3 shadow-lg border flex items-center gap-2 ' + (colors[type] || colors.success);
            var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle');
            container.innerHTML = '<i class="fas ' + icon + '"></i><p class="text-sm font-medium">' + message + '</p>';
        }

        clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(function() {
            toast.classList.add('hidden');
        }, 5000);
    }

    function verifyUser(userId) {
        if (!confirm('Verify this user?')) return;
        
        fetch('/admin/users/' + userId + '/verify', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, 'error');
        });
    }

    function openAssignModal(userId, userName, userEmail, userRole) {
        if (window.userAssignModal) {
            window.userAssignModal.openModal(userId, userName, userEmail, userRole);
        } else {
            showToast('Assign modal not loaded. Please refresh.', 'warning');
        }
    }

    function submitAddUser(event) {
        event.preventDefault();
        console.log('🔄 Add User form submitted');

        const form = document.getElementById('addUserForm');
        const submitBtn = document.getElementById('submitUserBtn');
        const originalText = submitBtn.innerHTML;

        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: formData.get('password'),
            password_confirmation: formData.get('password_confirmation'),
            phone: formData.get('phone'),
            role_id: parseInt(formData.get('role_id')),
            company_id: formData.get('company_id') ? parseInt(formData.get('company_id')) : null,
            status: parseInt(formData.get('status')) || 0,
        };

        if (!data.name || !data.email || !data.password || !data.role_id) {
            showToast('Please fill in all required fields.', 'warning');
            return;
        }
        if (data.password !== data.password_confirmation) {
            showToast('Passwords do not match.', 'error');
            return;
        }
        if (data.password.length < 8) {
            showToast('Password must be at least 8 characters.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showToast('CSRF token missing. Please refresh the page.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        const url = '{{ route("users.store") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('❌ Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check logs.');
            }
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || JSON.stringify(errorData.errors || 'Validation error'));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message || 'User created successfully!', 'success');
                closeAddUserDrawer();
                form.reset();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                let errorMsg = data.message || 'Something went wrong.';
                if (data.errors && Array.isArray(data.errors)) {
                    errorMsg = data.errors.join('\n');
                }
                showToast(errorMsg, 'error');
            }
        })
        .catch(error => {
            console.error('❌ Fetch error:', error);
            showToast('Error: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    function updateSelectedCount() {
        if (selectedCountSpan) {
            selectedCountSpan.textContent = selectedIds.size;
        }
    }

    function updateMasterCheckbox() {
        if (!masterCheckbox) return;
        var checkboxes = document.querySelectorAll('.user-checkbox');
        var checked = document.querySelectorAll('.user-checkbox:checked');

        if (allIds.length > 0 && checked.length === checkboxes.length && checked.length === allIds.length) {
            masterCheckbox.checked = true;
            masterCheckbox.indeterminate = false;
        } else if (checkboxes.length === 0) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
        } else if (checked.length === checkboxes.length) {
            masterCheckbox.checked = true;
            masterCheckbox.indeterminate = false;
        } else if (checked.length > 0) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = true;
        } else {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
        }
    }

    function toggleMasterCheckbox() {
        var checkboxes = document.querySelectorAll('.user-checkbox');
        if (masterCheckbox.checked) {
            checkboxes.forEach(function(cb) {
                cb.checked = true;
                selectedIds.add(cb.dataset.id);
            });
        } else {
            checkboxes.forEach(function(cb) {
                cb.checked = false;
                selectedIds.delete(cb.dataset.id);
            });
            allIds = [];
        }
        updateSelectedCount();
        updateMasterCheckbox();
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList && e.target.classList.contains('user-checkbox')) {
            var id = e.target.dataset.id;
            if (e.target.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
                if (allIds.length > 0) {
                    allIds = [];
                    if (masterCheckbox) masterCheckbox.checked = false;
                }
            }
            updateSelectedCount();
            updateMasterCheckbox();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddUserDrawer();
            closeModal('changeRoleModal');
            closeModal('changeCompanyModal');
            closeModal('deleteModal');
        }
    });

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function selectAllUsers() {
        var btn = document.getElementById('selectAllBtn');
        var originalText = btn ? btn.innerHTML : 'Select All';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }

        var url = new URL(window.location.href);
        url.searchParams.set('select_all', 'true');

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function(data) {
            if (data.ids) {
                allIds = data.ids;
                document.querySelectorAll('.user-checkbox').forEach(function(cb) {
                    cb.checked = true;
                    selectedIds.add(cb.dataset.id);
                });
                selectedIds = new Set(allIds);
                updateSelectedCount();
                if (masterCheckbox) masterCheckbox.checked = true;
                showToast('Selected ' + allIds.length + ' users across all pages.', 'success');
            } else {
                showToast('No IDs returned.', 'error');
            }
        })
        .catch(function(error) {
            console.error('❌ Select All error:', error);
            showToast('Error loading all IDs: ' + error.message, 'error');
        })
        .finally(function() {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    function applyBulkAction() {
        var action = bulkActionSelect ? bulkActionSelect.value : '';
        if (!action) {
            showToast('Please select a bulk action.', 'warning');
            return;
        }
        if (selectedIds.size === 0) {
            showToast('Please select at least one user.', 'warning');
            return;
        }

        switch(action) {
            case 'activate':
                if (confirm('Activate ' + selectedIds.size + ' user(s)?')) {
                    executeBulkAction('activate');
                }
                break;
            case 'deactivate':
                if (confirm('Deactivate ' + selectedIds.size + ' user(s)?')) {
                    executeBulkAction('deactivate');
                }
                break;
            case 'change_role':
                document.getElementById('changeRoleCount').textContent = selectedIds.size;
                document.getElementById('changeRoleModal').style.display = 'block';
                break;
            case 'change_company':
                document.getElementById('changeCompanyCount').textContent = selectedIds.size;
                document.getElementById('changeCompanyModal').style.display = 'block';
                break;
            case 'delete':
                document.getElementById('deleteCount').textContent = selectedIds.size;
                document.getElementById('deleteModal').style.display = 'block';
                break;
            default:
                showToast('Invalid action.', 'error');
        }
    }

    function executeBulkAction(action) {
        var ids = Array.from(selectedIds);
        var data = { user_ids: ids, action: action };

        if (action === 'change_role') {
            var roleId = document.getElementById('newRoleId').value;
            if (!roleId) {
                showToast('Please select a role.', 'warning');
                return;
            }
            data.role_id = roleId;
            closeModal('changeRoleModal');
        }

        if (action === 'change_company') {
            var companyId = document.getElementById('newCompanyId').value;
            if (!companyId) {
                showToast('Please select a company.', 'warning');
                return;
            }
            data.company_id = companyId;
            closeModal('changeCompanyModal');
        }

        if (action === 'delete') {
            closeModal('deleteModal');
            if (!confirm('Are you sure you want to delete these users?')) {
                return;
            }
        }

        var btn = document.getElementById('applyBulkAction');
        var originalText = btn ? btn.innerHTML : 'Apply';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : null;
        if (!csrfToken) {
            showToast('CSRF token missing.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
            return;
        }

        fetch('{{ route("users.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                showToast(data.error || 'Something went wrong.', 'error');
            }
        })
        .catch(function(error) {
            console.error('❌ Bulk action error:', error);
            showToast('Error: ' + error.message, 'error');
        })
        .finally(function() {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    function quickUpdate(userId, field, value) {
        var row = document.querySelector('#user-row-' + userId);
        if (!row) {
            showToast('Row not found.', 'error');
            return;
        }

        var nameEl = row.querySelector('[data-field="name"]');
        var emailEl = row.querySelector('[data-field="email"]');
        var phoneEl = row.querySelector('[data-field="phone"]');
        var roleEl = row.querySelector('[data-field="role_id"]');
        var companyEl = row.querySelector('[data-field="company_id"]');
        var statusEl = row.querySelector('[data-field="status"]');

        var name = nameEl ? nameEl.innerText : '';
        var email = emailEl ? emailEl.innerText : '';
        var phone = phoneEl ? phoneEl.innerText : '';
        var roleId = roleEl ? roleEl.dataset.value : '';
        var companyId = companyEl ? companyEl.dataset.value : '';
        var status = statusEl ? statusEl.dataset.value : '';

        var payload = {
            name: name,
            email: email,
            phone: phone,
            role_id: parseInt(roleId) || 0,
            company_id: companyId ? parseInt(companyId) : null,
            status: parseInt(status) || 0
        };

        if (field === 'status') {
            payload.status = parseInt(value);
        } else if (field === 'role_id') {
            payload.role_id = parseInt(value);
        } else if (field === 'company_id') {
            payload.company_id = value ? parseInt(value) : null;
        } else {
            payload[field] = value;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : null;
        if (!csrfToken) {
            showToast('CSRF token missing.', 'error');
            return;
        }

        fetch('{{ url("admin/users/quick-update") }}/' + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                showToast(data.message, 'success');
                if (data.row) {
                    var rowEl = document.getElementById('user-row-' + userId);
                    if (rowEl) rowEl.outerHTML = data.row;
                }
                updateStats();
            } else {
                showToast(data.message || 'Update failed.', 'error');
            }
        })
        .catch(function(error) {
            console.error('❌ Quick update error:', error);
            showToast('Error: ' + error.message, 'error');
        });
    }

    function exportSelectedUsers() {
        if (selectedIds.size === 0) {
            showToast('Please select at least one user.', 'warning');
            return;
        }

        var ids = Array.from(selectedIds);
        var url = new URL('{{ route("users.create") }}');
        url.searchParams.append('ids', ids.join(','));

        var search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        var role = document.getElementById('roleFilter') ? document.getElementById('roleFilter').value : '';
        var company = document.getElementById('companyFilter') ? document.getElementById('companyFilter').value : '';
        var status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
        var verified = document.getElementById('verifiedFilter') ? document.getElementById('verifiedFilter').value : '';
        var dateFrom = document.getElementById('dateFrom') ? document.getElementById('dateFrom').value : '';
        var dateTo = document.getElementById('dateTo') ? document.getElementById('dateTo').value : '';

        if (search) url.searchParams.append('search', search);
        if (role) url.searchParams.append('role_id', role);
        if (company) url.searchParams.append('company_id', company);
        if (status !== '') url.searchParams.append('status', status);
        if (verified) url.searchParams.append('verified', verified);
        if (dateFrom) url.searchParams.append('date_from', dateFrom);
        if (dateTo) url.searchParams.append('date_to', dateTo);

        window.location.href = url.toString();
    }

    function exportUsers() {
        var url = new URL('{{ route("users.create") }}');

        var search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        var role = document.getElementById('roleFilter') ? document.getElementById('roleFilter').value : '';
        var company = document.getElementById('companyFilter') ? document.getElementById('companyFilter').value : '';
        var status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
        var verified = document.getElementById('verifiedFilter') ? document.getElementById('verifiedFilter').value : '';
        var dateFrom = document.getElementById('dateFrom') ? document.getElementById('dateFrom').value : '';
        var dateTo = document.getElementById('dateTo') ? document.getElementById('dateTo').value : '';

        if (search) url.searchParams.append('search', search);
        if (role) url.searchParams.append('role_id', role);
        if (company) url.searchParams.append('company_id', company);
        if (status !== '') url.searchParams.append('status', status);
        if (verified) url.searchParams.append('verified', verified);
        if (dateFrom) url.searchParams.append('date_from', dateFrom);
        if (dateTo) url.searchParams.append('date_to', dateTo);

        window.location.href = url.toString();
    }

    function updateStats() {
        var url = new URL('{{ route("users.index") }}');
        url.searchParams.set('stats_only', '1');

        var search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        var role = document.getElementById('roleFilter') ? document.getElementById('roleFilter').value : '';
        var company = document.getElementById('companyFilter') ? document.getElementById('companyFilter').value : '';
        var status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
        var verified = document.getElementById('verifiedFilter') ? document.getElementById('verifiedFilter').value : '';
        var dateFrom = document.getElementById('dateFrom') ? document.getElementById('dateFrom').value : '';
        var dateTo = document.getElementById('dateTo') ? document.getElementById('dateTo').value : '';

        if (search) url.searchParams.append('search', search);
        if (role) url.searchParams.append('role_id', role);
        if (company) url.searchParams.append('company_id', company);
        if (status !== '') url.searchParams.append('status', status);
        if (verified) url.searchParams.append('verified', verified);
        if (dateFrom) url.searchParams.append('date_from', dateFrom);
        if (dateTo) url.searchParams.append('date_to', dateTo);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.stats) {
                document.getElementById('statsTotal').textContent = data.stats.total;
                document.getElementById('statsActive').textContent = data.stats.active;
                document.getElementById('statsPending').textContent = data.stats.pending;
                document.getElementById('statsInactive').textContent = data.stats.inactive;
            }
        })
        .catch(function() {});
    }

    function sortTable(column) {
        var currentUrl = new URL(window.location.href);
        var currentSort = currentUrl.searchParams.get('sort');
        var currentDir = currentUrl.searchParams.get('direction') || 'desc';

        var newDir = 'asc';
        if (currentSort === column) {
            newDir = currentDir === 'asc' ? 'desc' : 'asc';
        }

        currentUrl.searchParams.set('sort', column);
        currentUrl.searchParams.set('direction', newDir);
        window.location.href = currentUrl.toString();
    }

    function changePerPage() {
        var perPage = document.getElementById('perPage').value;
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    updateSelectedCount();
    updateMasterCheckbox();
    console.log('✅ User Management JS initialised successfully.');
</script>
@endsection
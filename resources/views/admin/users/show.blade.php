@extends('layouts.app')

@section('title', 'User Details - ' . ($user->name ?? 'User'))

@php
    $primaryRole = $user->getRoleNames()->first() ?? 'No Role';
    $allRoles = $user->getRoleNames()->toArray();
    $isSysadmin = $user->hasRole('sysadmin');
    $isTenant = $user->hasRole('tenant');
    $isStaff = $user->hasAnyRole(['admin', 'property_manager', 'accountant', 'security', 'maintenance', 'meter_reader', 'cleaning_staff']);
    $verified = !is_null($user->email_verified_at);
    $statusLabel = $user->status == 0 ? 'Active' : ($user->status == 1 ? 'Inactive' : 'Pending');
    $statusColor = $user->status == 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($user->status == 1 ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400');
    $verifiedIcon = $verified ? 'fa-check-circle text-emerald-500' : 'fa-clock text-amber-500';
@endphp

@section('content')
<div class="container mx-auto px-4 py-6 max-w-screen-2xl">

    <!-- ============================================================ -->
    <!-- HEADER -->
    <!-- ============================================================ -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-circle text-indigo-600 dark:text-indigo-400"></i>
                    User Details
                </h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View and manage user information</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                <i class="fas fa-edit"></i> Edit User
            </a>
            @if(!$isSysadmin)
                <button onclick="if(confirm('Delete user {{ $user->name }}?')) { fetch('{{ route('admin.users.destroy', $user->id) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(r=>r.json()).then(d=>{ if(d.success){ window.location.href='{{ route('admin.users.index') }}'; } else { alert(d.message); } }); }" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endif
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- USER PROFILE CARD -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left Column - Profile -->
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8 text-center">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full border-4 border-white/50 mx-auto object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full border-4 border-white/50 mx-auto flex items-center justify-center bg-white/20 text-white text-3xl font-bold">
                            {{ $user->getInitialsAttribute() ?? substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <h2 class="text-xl font-bold text-white mt-4">{{ $user->name }}</h2>
                    <p class="text-indigo-200 text-sm">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                            {{ $primaryRole }}
                        </span>
                        @if($verified)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                <i class="fas fa-check-circle mr-1"></i> Verified
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
                                <i class="fas fa-clock mr-1"></i> Unverified
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Company</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $user->company->name ?? 'Not assigned' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Joined</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $user->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @if($user->email_verified_at)
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Verified At</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $user->email_verified_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($user->last_login_at)
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 flex-shrink-0">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last Login</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $user->last_login_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @if(!$verified && !$isSysadmin)
                <div class="px-6 pb-6">
                    <button onclick="verifyUser({{ $user->id }})" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition">
                        <i class="fas fa-check-circle"></i> Verify User
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Role Information -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-tag text-indigo-600 dark:text-indigo-400"></i>
                        Roles & Permissions
                    </h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($allRoles) }} role(s)</span>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned Roles</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($allRoles as $role)
                                <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-3 py-1 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                    <i class="fas fa-shield-alt mr-1.5 text-xs"></i>
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-500 dark:text-gray-400">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                    @if($user->permissions->count() > 0)
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Direct Permissions</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->permissions as $permission)
                                <span class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-900/30 px-3 py-1 text-xs font-medium text-purple-700 dark:text-purple-300">
                                    <i class="fas fa-key mr-1.5 text-xs"></i>
                                    {{ $permission->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Is Staff:</span>
                                <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">{{ $isStaff ? 'Yes' : 'No' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Is Tenant:</span>
                                <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">{{ $isTenant ? 'Yes' : 'No' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Is System Admin:</span>
                                <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">{{ $isSysadmin ? 'Yes' : 'No' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Account Status:</span>
                                <span class="ml-2 font-medium {{ $user->status == 0 ? 'text-emerald-600' : ($user->status == 1 ? 'text-rose-600' : 'text-amber-600') }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenant Information (if tenant) -->
            @if($isTenant && $user->tenant)
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-home text-emerald-600 dark:text-emerald-400"></i>
                        Tenant Information
                    </h3>
                    <a href="{{ route('admin.tenants.show', $user->tenant->id) }}" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View Full Profile <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tenant ID</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">#{{ $user->tenant->id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->tenant->status == 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400' }}">
                                    {{ ucfirst($user->tenant->status ?? 'Active') }}
                                </span>
                            </p>
                        </div>
                        @if($user->tenant->activeTenancy)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Current Unit</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $user->tenant->activeTenancy->unit->unit_number ?? 'N/A' }}
                                @if($user->tenant->activeTenancy->unit && $user->tenant->activeTenancy->unit->estate)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $user->tenant->activeTenancy->unit->estate->name }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Move In Date</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $user->tenant->activeTenancy->move_in_date ? \Carbon\Carbon::parse($user->tenant->activeTenancy->move_in_date)->format('d M Y') : 'N/A' }}
                            </p>
                        </div>
                        @endif
                        @if($user->tenant->emergency_contact)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Emergency Contact</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->tenant->emergency_contact }}</p>
                        </div>
                        @endif
                        @if($user->tenant->emergency_phone)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Emergency Phone</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->tenant->emergency_phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Staff Information (if staff) -->
            @if($isStaff)
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-briefcase text-blue-600 dark:text-blue-400"></i>
                        Staff Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Staff Type</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $primaryRole)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Company</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->company->name ?? 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Account Created</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-600 dark:text-yellow-400"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @if(!$verified && !$isSysadmin)
                        <button onclick="verifyUser({{ $user->id }})" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Verify</span>
                        </button>
                        @endif
                        @if($user->status == 0)
                        <button onclick="suspendUser({{ $user->id }})" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition">
                                <i class="fas fa-pause-circle"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Suspend</span>
                        </button>
                        @elseif($user->status == 1)
                        <button onclick="activateUser({{ $user->id }})" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Activate</span>
                        </button>
                        @endif
                        @if(!$isSysadmin)
                        <button onclick="if(confirm('Delete user {{ $user->name }}?')) { fetch('{{ route('admin.users.destroy', $user->id) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(r=>r.json()).then(d=>{ if(d.success){ window.location.href='{{ route('admin.users.index') }}'; } else { alert(d.message); } }); }" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 group-hover:scale-110 transition">
                                <i class="fas fa-trash"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Delete</span>
                        </button>
                        @endif
                        <button onclick="window.location.href='{{ route('admin.users.edit', $user->id) }}'" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition">
                                <i class="fas fa-edit"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Edit</span>
                        </button>
                        @if(!$user->company_id)
                        <button onclick="openAssignModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $primaryRole }}')" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition group">
                            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 group-hover:scale-110 transition">
                                <i class="fas fa-building"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Assign Company</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TOAST NOTIFICATION -->
<!-- ============================================================ -->
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
    function showToast(message, type) {
        type = type || 'success';
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMessage');
        if (!toast || !toastMsg) {
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
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showToast('CSRF token missing. Please refresh.', 'error');
            return;
        }

        fetch('/admin/users/' + userId + '/verify', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Verification failed.', 'error');
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, 'error');
        });
    }

    function suspendUser(userId) {
        if (!confirm('Suspend this user? They will not be able to log in.')) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showToast('CSRF token missing. Please refresh.', 'error');
            return;
        }

        fetch('/admin/users/' + userId + '/suspend', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to suspend user.', 'error');
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, 'error');
        });
    }

    function activateUser(userId) {
        if (!confirm('Activate this user?')) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showToast('CSRF token missing. Please refresh.', 'error');
            return;
        }

        fetch('/admin/users/' + userId + '/activate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to activate user.', 'error');
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
</script>
@endsection
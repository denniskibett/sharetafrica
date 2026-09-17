@extends('layouts.app')

@section('content')
<main>
    <div x-data="rolesIndex()" x-init="init()" class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        
        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'Roles Management' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Roles & Permissions
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Manage system roles and their permissions
                </p>
            </div>
            <button @click="openCreateModal()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Role
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Roles</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="roles.length"></h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Permissions</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="totalPermissions"></h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Protected Roles</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="protectedCount"></h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Custom Roles</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="customCount"></h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                    x-model="searchQuery"
                    @input="filterRoles()"
                    placeholder="Search roles by name or description..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
            <div class="flex gap-2">
                <button @click="filterRoles()" 
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Search
                </button>
                <button @click="searchQuery = ''; filterRoles()" 
                    class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Clear
                </button>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    All Roles
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="filteredRoles.length + ' roles'"></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="role in filteredRoles" :key="role.id">
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-lg flex items-center justify-center" 
                                            :class="{
                                                'bg-red-100 dark:bg-red-900/30': role.name === 'super_admin',
                                                'bg-blue-100 dark:bg-blue-900/30': role.name === 'admin',
                                                'bg-green-100 dark:bg-green-900/30': role.name === 'property_manager',
                                                'bg-purple-100 dark:bg-purple-900/30': role.name === 'accountant',
                                                'bg-gray-100 dark:bg-gray-800/50': true
                                            }">
                                            <span class="font-bold text-sm" 
                                                :class="{
                                                    'text-red-600 dark:text-red-400': role.name === 'super_admin',
                                                    'text-blue-600 dark:text-blue-400': role.name === 'admin',
                                                    'text-green-600 dark:text-green-400': role.name === 'property_manager',
                                                    'text-purple-600 dark:text-purple-400': role.name === 'accountant',
                                                    'text-gray-600 dark:text-gray-400': true
                                                }" 
                                                x-text="role.initial || role.name.charAt(0).toUpperCase()">
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="role.display_name || role.name.replace('_', ' ').toTitleCase()"></p>
                                            <span class="text-xs px-2 py-0.5 rounded-full" 
                                                :class="{
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': role.name === 'super_admin',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': role.name === 'admin',
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': role.name === 'property_manager',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': role.name === 'accountant',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': role.name === 'tenant',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-800/50 dark:text-gray-400': true
                                                }"
                                                x-text="role.is_protected ? '🔒 Protected' : '✨ Custom'">
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span x-text="role.users_count"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400" x-text="role.permissions_count + ' permissions'"></span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate" x-text="role.description || '-'"></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'/roles/' + role.id" 
                                            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                            title="View Role Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <button @click="openEditModal(role)" 
                                            class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition"
                                            title="Edit Role">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(role)" 
                                            x-show="!role.is_protected"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                            title="Delete Role">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                        <span x-show="role.is_protected" class="text-xs text-gray-400 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-full">Protected</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredRoles.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-4 font-medium" x-text="searchQuery ? 'No roles match your search' : 'No roles found'"></p>
                                <p class="text-sm mt-1" x-text="searchQuery ? 'Try adjusting your search terms' : 'Click "Create Role" to add your first role'"></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('partials.modal.role-create-modal')
    @include('partials.modal.role-delete-modal')
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rolesIndex', () => ({
        roles: @json($roles),
        totalPermissions: {{ $permissions->count() ?? 0 }},
        searchQuery: '',
        filteredRoles: [],
        
        get protectedCount() {
            return this.roles.filter(r => r.is_protected).length;
        },
        
        get customCount() {
            return this.roles.filter(r => !r.is_protected).length;
        },
        
        init() {
            this.filteredRoles = this.roles;
        },
        
        filterRoles() {
            const query = this.searchQuery.toLowerCase().trim();
            if (!query) {
                this.filteredRoles = this.roles;
                return;
            }
            this.filteredRoles = this.roles.filter(role => {
                const searchable = [
                    role.name,
                    role.description,
                    role.display_name
                ].filter(Boolean).join(' ').toLowerCase();
                return searchable.includes(query);
            });
        },
        
        openCreateModal() {
            if (window.roleCreateModal) {
                window.roleCreateModal.openModal();
            }
        },
        
        openEditModal(role) {
            if (window.roleEditModal) {
                // Load permissions for this role
                fetch('/roles/' + role.id + '/data')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.roleEditModal.openModal(data.role);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading role data:', error);
                        window.alertModal.showError('Error', 'Failed to load role data');
                    });
            }
        },
        
        confirmDelete(role) {
            if (window.roleDeleteModal) {
                window.roleDeleteModal.openModal(role);
            }
        }
    }));
});

// Helper: String to Title Case
String.prototype.toTitleCase = function() {
    return this.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
};
</script>
@endsection
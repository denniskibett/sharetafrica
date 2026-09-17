@extends('layouts.app')

@section('content')
<main>
    <div x-data="roleShow()" x-init="init()" class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        
        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'Role Details' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl flex items-center justify-center"
                        :class="{
                            'bg-red-100 dark:bg-red-900/30': role.name === 'super_admin',
                            'bg-blue-100 dark:bg-blue-900/30': role.name === 'admin',
                            'bg-green-100 dark:bg-green-900/30': role.name === 'property_manager',
                            'bg-purple-100 dark:bg-purple-900/30': role.name === 'accountant',
                            'bg-gray-100 dark:bg-gray-800/50': true
                        }">
                        <span class="text-xl font-bold"
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
                        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="role.name.replace('_', ' ').toTitleCase()"></h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="role.description || 'No description'"></p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/roles" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </a>
                <button @click="window.roleEditModal.openModal(role)" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Role
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Users with this role</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="role.users_count"></h4>
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
                        <span class="text-sm text-gray-500 dark:text-gray-400">Permissions</span>
                        <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="role.permissions_count"></h4>
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
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        <h4 class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            <span class="px-2 py-1 rounded-full text-sm"
                                :class="role.is_protected ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'"
                                x-text="role.is_protected ? 'Protected' : 'Custom'">
                            </span>
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full"
                        :class="role.is_protected ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-green-100 dark:bg-green-900/30'">
                        <svg class="w-6 h-6" 
                            :class="role.is_protected ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Users with this role -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Users with this role
                    </h3>
                </div>
                <div class="p-6">
                    <template x-if="users.length > 0">
                        <div class="space-y-3">
                            <template x-for="user in users" :key="user.id">
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300" 
                                            x-text="user.name ? user.name.charAt(0).toUpperCase() : '?'">
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate" x-text="user.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="user.email"></p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400" 
                                        x-text="user.company ? user.company.name : 'No Company'">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="users.length === 0">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No users assigned to this role</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Permissions -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Assigned Permissions
                    </h3>
                </div>
                <div class="p-6">
                    <template x-if="permissions.length > 0">
                        <div>
                            <!-- Group permissions by category -->
                            <template x-for="(perms, group) in permissionsByGroup" :key="group">
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2" 
                                        x-text="group.toTitleCase() + ' Permissions'">
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="permission in perms" :key="permission.id">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium"
                                                :class="permission.assigned ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                                                <svg class="w-3.5 h-3.5" 
                                                    :class="permission.assigned ? 'text-green-600 dark:text-green-400' : 'text-gray-400'"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        :d="permission.assigned ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"/>
                                                </svg>
                                                <span x-text="permission.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="permissions.length === 0">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No permissions assigned to this role</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Edit Modal -->
    @include('partials.modal.role-create-modal')
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roleShow', () => ({
        role: @json($role),
        users: @json($users ?? []),
        permissions: [],
        permissionsByGroup: {},
        
        init() {
            this.loadPermissions();
        },
        
        loadPermissions() {
            const allPermissions = @json($permissions ?? []);
            const rolePermissions = @json($role->permissions->pluck('id')->toArray() ?? []);
            
            // Transform permissions
            this.permissions = [];
            for (const [group, perms] of Object.entries(allPermissions)) {
                for (const perm of perms) {
                    this.permissions.push({
                        id: perm.id,
                        name: perm.name,
                        group: group,
                        assigned: rolePermissions.includes(perm.id)
                    });
                }
            }
            
            // Group by category
            this.permissionsByGroup = {};
            for (const perm of this.permissions) {
                if (!this.permissionsByGroup[perm.group]) {
                    this.permissionsByGroup[perm.group] = [];
                }
                this.permissionsByGroup[perm.group].push(perm);
            }
        }
    }));
});
</script>
@endsection
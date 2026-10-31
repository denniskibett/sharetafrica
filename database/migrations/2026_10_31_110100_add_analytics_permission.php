<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Website analytics (Administration › Website analytics) for admin, operations and support. */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::findOrCreate('analytics.view', 'web');
        foreach (['admin', 'operations', 'support'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'analytics.view')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

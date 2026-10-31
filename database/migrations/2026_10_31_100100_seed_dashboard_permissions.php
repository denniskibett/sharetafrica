<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions the dashboard checks, with default grants per role. Admins change grants on
 * Administration › Roles & permissions; super_admin passes every check (AppServiceProvider).
 */
return new class extends Migration
{
    protected array $grants = [
        'integrations.manage' => ['admin', 'merchant', 'business'],
        'developer.api_keys' => ['admin', 'techie', 'merchant', 'business'],
        'admin.roles' => ['admin'],
        'admin.users' => ['admin', 'support'],
        'waiting_list.invite' => ['admin', 'support'],
        'admin.system' => ['admin'],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->grants as $name => $roles) {
            $permission = Permission::findOrCreate($name, 'web');
            foreach ($roles as $role) {
                Role::findOrCreate($role, 'web')->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Only remove what this migration introduced; the others existed before it
        Permission::where('name', 'integrations.manage')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

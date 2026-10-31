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
        'api_keys.manage' => ['admin', 'techie', 'merchant', 'business'],
        'roles.manage' => ['admin'],
        'users.manage' => ['admin', 'support'],
        'waiting_list.manage' => ['admin', 'support'],
        'system.settings' => ['admin'],
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
        Permission::whereIn('name', array_keys($this->grants))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        $permissions = Permission::all()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0] ?? 'other');

        $roleConstants = $this->getRoleConstants();

        return view('roles.index', compact('roles', 'permissions', 'roleConstants'));
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->loadCount('users');
        $users = $role->users()->with('company')->limit(50)->get();

        $permissions = Permission::all()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0] ?? 'other');

        return view('roles.show', compact('role', 'users', 'permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);

            if ($request->filled('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully!',
                'role' => $role->loadCount('users'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            if ($this->isProtectedRole($role->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role is protected and cannot be modified.',
                ], 422);
            }

            DB::beginTransaction();

            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully!',
                'role' => $role->loadCount('users'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        try {
            if ($this->isProtectedRole($role->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role is protected and cannot be deleted.',
                ], 422);
            }

            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role with assigned users. Please reassign users first.',
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role details for AJAX.
     */
    public function getRole(Role $role)
    {
        $role->loadCount('users');
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'is_protected' => $this->isProtectedRole($role->name),
                'permissions' => $role->permissions->pluck('id')->toArray(),
            ],
            'all_permissions' => $permissions->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'group' => explode('.', $p->name)[0] ?? 'other',
            ]),
        ]);
    }

    /**
     * List roles for dropdown/select (AJAX endpoint).
     */
    public function list(Request $request)
    {
        try {
            $roles = Role::orderBy('name')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->get()
                ->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $this->roleDescription($role->name),
                    'users_count' => $role->users()->count(),
                    'is_protected' => $this->isProtectedRole($role->name),
                    'initial' => strtoupper(substr($role->name, 0, 1)),
                ]);

            return response()->json([
                'success' => true,
                'roles' => $roles,
                'total' => $roles->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load roles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a role is protected (system role).
     */
    private function isProtectedRole(string $roleName): bool
    {
        return in_array($roleName, [
            'super_admin',
            'admin',
            'support',
            'operations',
            'waiting_list',
            'individual',
            'merchant',
            'business',
            'techie',
        ]);
    }

    /**
     * Human description for a role.
     */
    private function roleDescription(string $roleName): string
    {
        return match ($roleName) {
            'super_admin' => 'Full access to everything. Bypasses all permission checks.',
            'admin' => 'Administrative access across users, companies, and system settings.',
            'support' => 'Handles waiting list, onboarding review, KYC, and support tickets.',
            'operations' => 'Treasury, rails, reconciliation, and compliance operations.',
            'waiting_list' => 'New signup. Not yet assigned a lane.',
            'individual' => 'Personal wallet user. Send, receive, QR payments.',
            'merchant' => 'Accepts payments from any rail. Settles externally.',
            'business' => 'B2B trade, invoicing, supplier payments, trade finance.',
            'techie' => 'Developer or platform. API, sandbox, webhooks, licensing.',
            default => ucfirst(str_replace('_', ' ', $roleName)) . ' role.',
        };
    }

    /**
     * Get role constants for predefined roles.
     */
    private function getRoleConstants(): array
    {
        return [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'color' => 'danger',
                'icon' => 'shield-check',
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Administrative access across all domains',
                'color' => 'danger',
                'icon' => 'user-shield',
            ],
            'support' => [
                'name' => 'Support',
                'description' => 'Waiting list, onboarding review, KYC',
                'color' => 'primary',
                'icon' => 'headset',
            ],
            'operations' => [
                'name' => 'Operations',
                'description' => 'Treasury, rails, reconciliation, compliance',
                'color' => 'success',
                'icon' => 'chart',
            ],
            'waiting_list' => [
                'name' => 'Waiting List',
                'description' => 'New signup. Not yet assigned a lane.',
                'color' => 'secondary',
                'icon' => 'clock',
            ],
            'individual' => [
                'name' => 'Individual',
                'description' => 'Personal wallet user',
                'color' => 'info',
                'icon' => 'user',
            ],
            'merchant' => [
                'name' => 'Merchant',
                'description' => 'Accepts payments, settles externally',
                'color' => 'warning',
                'icon' => 'store',
            ],
            'business' => [
                'name' => 'Business',
                'description' => 'B2B trade, invoicing, trade finance',
                'color' => 'dark',
                'icon' => 'building',
            ],
            'techie' => [
                'name' => 'Techie',
                'description' => 'Developer or platform',
                'color' => 'light',
                'icon' => 'code',
            ],
        ];
    }
}
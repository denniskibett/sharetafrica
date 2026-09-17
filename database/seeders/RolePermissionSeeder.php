<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |----------------------------------------------------------------------
        | Permissions — grouped by domain
        |----------------------------------------------------------------------
        */

        $permissions = [
            // ---- Shared / Core ----
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'wallet.view',
            'wallet.transactions',
            'wallet.deposit',
            'wallet.withdraw',
            'wallet.transfer',

            // ---- Waiting list / onboarding ----
            'waiting_list.view',            // admin sees waiting list
            'waiting_list.invite',          // admin invites a user
            'onboarding.self',              // user onboards themselves
            'onboarding.review',            // admin reviews applications

            // ---- Individual (personal wallet) ----
            'personal.send',
            'personal.receive',
            'personal.qr',

            // ---- Merchant ----
            'merchant.accept',
            'merchant.settle',
            'merchant.reconcile',
            'merchant.tools',
            'merchant.qr',

            // ---- Business / Trade ----
            'business.invoice',
            'business.pay_supplier',
            'business.receive_payment',
            'business.trade_finance',
            'business.treasury',
            'business.docs',

            // ---- Techie / Developer ----
            'developer.api_keys',
            'developer.sandbox',
            'developer.webhooks',
            'developer.logs',
            'developer.docs',
            'developer.licensing',

            // ---- Admin / Operations ----
            'admin.users',
            'admin.companies',
            'admin.roles',
            'admin.audit',
            'admin.system',
            'admin.reconciliation',
            'admin.treasury',
            'admin.rails',
            'admin.support',

            // ---- Compliance / KYC ----
            'kyc.review',
            'kyc.approve',
            'kyc.reject',
            'compliance.view',
            'compliance.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        /*
        |----------------------------------------------------------------------
        | Roles — one for the waiting list, one per intent lane, plus staff
        |----------------------------------------------------------------------
        */

        // 1. Waiting list — everyone starts here
        $waiting = Role::firstOrCreate(['name' => 'waiting_list', 'guard_name' => 'web']);
        $waiting->syncPermissions([
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'onboarding.self',
        ]);

        // 2. Individual
        $individual = Role::firstOrCreate(['name' => 'individual', 'guard_name' => 'web']);
        $individual->syncPermissions([
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'wallet.view',
            'wallet.transactions',
            'wallet.deposit',
            'wallet.withdraw',
            'wallet.transfer',
            'personal.send',
            'personal.receive',
            'personal.qr',
        ]);

        // 3. Merchant
        $merchant = Role::firstOrCreate(['name' => 'merchant', 'guard_name' => 'web']);
        $merchant->syncPermissions([
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'wallet.view',
            'wallet.transactions',
            'wallet.deposit',
            'wallet.withdraw',
            'wallet.transfer',
            'merchant.accept',
            'merchant.settle',
            'merchant.reconcile',
            'merchant.tools',
            'merchant.qr',
        ]);

        // 4. Business / Trade
        $business = Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        $business->syncPermissions([
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'wallet.view',
            'wallet.transactions',
            'wallet.deposit',
            'wallet.withdraw',
            'wallet.transfer',
            'business.invoice',
            'business.pay_supplier',
            'business.receive_payment',
            'business.trade_finance',
            'business.treasury',
            'business.docs',
        ]);

        // 5. Techie / Developer
        $techie = Role::firstOrCreate(['name' => 'techie', 'guard_name' => 'web']);
        $techie->syncPermissions([
            'dashboard.view',
            'profile.view',
            'profile.update',
            'notifications.view',
            'developer.api_keys',
            'developer.sandbox',
            'developer.webhooks',
            'developer.logs',
            'developer.docs',
            'developer.licensing',
        ]);

        // 6. Support staff
        $support = Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        $support->syncPermissions([
            'dashboard.view',
            'profile.view',
            'waiting_list.view',
            'onboarding.review',
            'kyc.review',
            'compliance.view',
            'admin.support',
        ]);

        // 7. Operations (treasury, rails, reconciliation)
        $operations = Role::firstOrCreate(['name' => 'operations', 'guard_name' => 'web']);
        $operations->syncPermissions([
            'dashboard.view',
            'profile.view',
            'waiting_list.view',
            'onboarding.review',
            'kyc.review',
            'kyc.approve',
            'kyc.reject',
            'compliance.view',
            'compliance.export',
            'admin.reconciliation',
            'admin.treasury',
            'admin.rails',
        ]);

        // 8. Admin
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // 9. Super admin (implicitly has everything via Gate::before)
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }
}
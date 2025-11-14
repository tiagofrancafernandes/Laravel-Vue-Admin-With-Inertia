<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Summary of RolesAndPermissionsSeeder
 * @author Tiago França
 * @copyright (c) 2025
 *
 * @suppress PHP0413
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @suppress PHP0419
     */
    public function run(): void
    {
        if (!class_exists(Role::class)) {
            return;
        }

        return; // Adiar enquanto não implementado
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Users Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.suspend',
            'users.activate',

            // Reports Management
            'reports.validate',
            'reports.approve',
            'reports.reject',

            // Customer Management
            'customer.view',
            'customer.create',
            'customer.update',
            'customer.delete',

            // Transaction Management
            'transactions.view',
            'transactions.create',
            'transactions.refund',
            'transactions.cancel',

            // Financial Operations
            'financial.view',
            'financial.view_reports',
            'financial.create',
            'financial.update',

            // Support/Staff Operations
            'support.view_tickets',
            'support.create_tickets',
            'support.resolve_tickets',
            'support.view_logs',

            // System Settings
            'settings.view',
            'settings.update',

            // Roles and Permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.assign',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // Super Admin - Full access to everything
        /** @var Role $superAdmin */
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin'], ['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - System administrator (below super_admin)
        /** @var Role $admin */
        $admin = Role::firstOrCreate(['name' => 'admin'], ['name' => 'admin']);
        $admin->syncPermissions([
            // Users Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.suspend',
            'users.activate',

            // Sales Management
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
            'sales.suspend',
            'sales.cancell',

            // Reports Management
            'reports.validate',
            'reports.approve',
            'reports.reject',

            'transactions.view',
            'transactions.refund',
            'transactions.cancel',
            'wallets.view',
            'wallets.create',
            'wallets.update',
            'financial.view_reports',
            'financial.view',
            'financial.create',
            'financial.update',
            'support.view_tickets',
            'support.create_tickets',
            'support.resolve_tickets',
            'support.view_logs',
            'settings.view',

            // Sales Management
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
            'sales.suspend',
            'sales.cancell',
        ]);

        // Manager - Almost admin with reduced permissions
        /** @var Role $manager */
        $manager = Role::firstOrCreate(['name' => 'manager'], ['name' => 'manager']);
        $manager->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'documents.validate',
            'documents.approve',
            'apps.view',
            'apps.create',
            'apps.update',
            'transactions.view',
            'transactions.refund',
            'wallets.view',
            'wallets.create',
            'financial.view_reports',
            'support.view_tickets',
            'support.create_tickets',
            'support.resolve_tickets',
            'support.view_logs',
            'webhooks.view',
            'settings.view',

            // Sales Management
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
            'sales.suspend',
            'sales.cancell',
        ]);

        // Financial - Financial operations specialist
        /** @var Role $financial */
        $financial = Role::firstOrCreate(['name' => 'financial'], ['name' => 'financial']);
        $financial->syncPermissions([
            'customers.view',
            'users.view',
            'transactions.view',
            'transactions.refund',
            'transactions.cancel',
            'wallets.view',
            'financial.view_reports',
            'financial.manage_fees',
            'financial.manage_exchange_rates',
            'financial.approve_withdrawals',
            'support.view_logs',

            // Sales Management
            'sales.view',
        ]);

        // Staff - Support team with specific permissions
        /** @var Role $staff */
        $staff = Role::firstOrCreate(['name' => 'staff'], ['name' => 'staff']);
        $staff->syncPermissions([
            'users.view',
            'documents.validate',
            'apps.view',
            'transactions.view',
            'reports.view',
            'support.view_tickets',
            'support.create_tickets',
            'support.resolve_tickets',
            'support.view_logs',

            // Sales Management
            'sales.view',
            'sales.create',
            // 'sales.update',
            // 'sales.delete',
            'sales.suspend',
            'sales.cancell',
        ]);
    }
}

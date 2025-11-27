<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles();

        $this->command->info('Roles and permissions created successfully!');
    }

    /**
     * Create all permissions
     */
    protected function createPermissions(): void
    {
        $permissions = array_keys(config('acl-data.permissions', []));

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['name' => $name],
            );
        }

        $this->command->info('Permissions created: ' . count($permissions));
    }

    /**
     * Create roles and assign permissions
     */
    protected function createRoles(): void
    {
        // SuperAdmin - Has ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());
        $this->command->info('SuperAdmin role created with ALL permissions');

        // Admin - Has most permissions except some critical system operations
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.restore',
            'settings.view',
            'settings.update',
            'reports.view',
            'reports.export',
            'reports.financial',
            'audit.view',
            'roles.view',
        ];
        $admin->syncPermissions($adminPermissions);
        $this->command->info('Admin role created with ' . count($adminPermissions) . ' permissions');

        // Manager - Can manage users (except admins), products, view reports
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'reports.view',
            'reports.export',
            'settings.view',
        ];
        $manager->syncPermissions($managerPermissions);
        $this->command->info('Manager role created with ' . count($managerPermissions) . ' permissions');

        // Staff - Can view and manage products, view dashboard
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staffPermissions = [
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'reports.view',
        ];
        $staff->syncPermissions($staffPermissions);
        $this->command->info('Staff role created with ' . count($staffPermissions) . ' permissions');

        // newUser - Can only view dashboard
        $newUser = Role::firstOrCreate(['name' => 'user']);
        $newUserPermissions = [
            'dashboard.view',
        ];
        $newUser->syncPermissions($newUserPermissions);
        $this->command->info('newUser role created with ' . count($newUserPermissions) . ' permissions');
    }
}

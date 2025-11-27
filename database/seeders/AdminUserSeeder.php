<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: RolesAndPermissionsSeeder must be run before this seeder
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@mail.com',
                'password' => 'power@123',
                'role' => 'admin', // Field in users table
                'email_verified_at' => now(),
                'roles' => ['super-admin'], // Spatie role
                'permissions' => [],
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@mail.com',
                'password' => 'power@123',
                'role' => 'admin',
                'email_verified_at' => now(),
                'roles' => ['admin'],
                'permissions' => [],
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@mail.com',
                'password' => 'power@123',
                'role' => 'user',
                'email_verified_at' => now(),
                'roles' => ['manager'],
                'permissions' => [],
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@mail.com',
                'password' => 'power@123',
                'role' => 'user',
                'email_verified_at' => now(),
                'roles' => ['staff'],
                'permissions' => [],
            ],
        ];

        foreach ($users as $u) {
            $roles = Arr::get($u, 'roles', []);
            $permissions = Arr::get($u, 'permissions', []);
            $userData = Arr::except($u, ['roles', 'permissions']);
            $password = $userData['password'] ?? 'password';

            $userData['password'] = Hash::make($password);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign Spatie roles
            if ($roles) {
                $user->syncRoles($roles);
                $this->command->info(sprintf('Assigned roles: %s', implode(', ', $roles)));
            }

            // Assign additional Spatie permissions (if any)
            if ($permissions) {
                $user->givePermissionTo($permissions);
                $this->command->info(sprintf('Assigned permissions: %s', implode(', ', $permissions)));
            }

            $this->command->info('User created/updated successfully!');
            $this->command->info(sprintf('Name: %s', $userData['name']));
            $this->command->info(sprintf('Email: %s', $userData['email']));
            $this->command->info(sprintf('Password: %s', $password));
            $this->command->newLine();
        }
    }
}

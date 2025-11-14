<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update super admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('power@123'),
                'email_verified_at' => now(),
                'type' => 'super_admin',
            ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@mail.com');
        $this->command->info('Password: power@123');

        // Create attendant users for testing
        User::updateOrCreate(
            ['email' => 'attendant@mail.com'],
            [
                'name' => 'Attendant User',
                'password' => Hash::make('power@123'),
                'email_verified_at' => now(),
                'type' => 'attendant',
            ]
        );

        $this->command->info('Attendant user created/updated successfully!');
        $this->command->info('Email: attendant@mail.com');
        $this->command->info('Password: power@123');
    }
}

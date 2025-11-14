<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed payment methods
        $this->call(PaymentMethodSeeder::class);

        // Seed default anonymous client
        $this->call(DefaultClientSeeder::class);

        // Seed admin and attendant users
        $this->call(AdminUserSeeder::class);
    }
}

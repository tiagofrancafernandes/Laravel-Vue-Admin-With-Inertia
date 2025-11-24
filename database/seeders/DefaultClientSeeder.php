<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientBalance;
use Illuminate\Database\Seeder;

class DefaultClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create anonymous client for one-time purchases
        $anonymousClientCode = 'CANON00';
        $anonymousClient = Client::firstOrCreate(
            ['email' => 'anonimo@system.local'],
            [
                'name' => 'Anônimo',
                'code' => $anonymousClientCode,
                'phone' => null,
                'cpf_cnpj' => null,
            ]
        );

        // Create initial balance for anonymous client if it doesn't exist
        ClientBalance::firstOrCreate(
            ['client_id' => $anonymousClient->id],
            [
                'balance' => 0,
                'credit_limit' => 0,
                'last_transaction_at' => now(),
            ]
        );

        $this->command->info('Default anonymous client created successfully!');
    }
}

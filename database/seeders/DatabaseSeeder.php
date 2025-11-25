<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

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

        $this->demoItems();
        $this->demoClients();
    }

    public function demoItems(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->demoProducts(app()->environment(['dev', 'local', 'staging', 'testing']));
    }

    protected function demoProducts(?bool $fake = null): void
    {
        $products = [
            [
                'name' => 'Produto valor R$ 0.50',
                'description' => 'Produto genérico valor R$ 0.50',
                'price' => '0.50',
                'sort_val' => 5,
            ],
            [
                'name' => 'Produto valor R$ 1.00',
                'description' => 'Produto genérico valor R$ 1.00',
                'price' => '1.00',
                'sort_val' => 10,
            ],
        ];

        foreach (range(1, 5) as $v) {
            $priceStr = number_format(1.00 + (0.50 * $v), 2, '.', '');

            $products[] = [
                'name' => 'Produto valor R$ ' . $priceStr,
                'description' => 'Produto genérico valor R$ ' . $priceStr,
                'price' => $priceStr,
                'sort_val' => 10 + $v,
            ];
        }

        foreach ($products as $product) {
            Product::updateOrCreate([
                'name' => $product['name'],
            ], $product);
        }

        if ($fake) {
            Product::factory(20)->create();
        }
    }

    protected function demoClients(?bool $fake = null): void
    {
        $fake ??= app()->environment(['dev', 'local', 'staging', 'testing']);

        $clients = [
            [
                'name' => 'Tiago França',
                'email' => 'tiago@mail.com',
                'phone' => '+5541988887777',
                'cpf_cnpj' => null,
            ]
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate([
                'email' => $client['email'],
                'phone' => $client['phone'],
            ], $client);
        }
    }
}

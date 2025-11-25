<?php

namespace Database\Seeders;

use App\Models\Product;
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

        $this->demoItems();
    }

    public function demoItems(): void
    {
        if (!app()->environment(['dev', 'local', 'staging'])) {
            return;
        }

        $this->demoProducts();
    }

    protected function demoProducts(bool $fake = true): void
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
}

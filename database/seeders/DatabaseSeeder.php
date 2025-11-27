<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // IMPORTANT: Seed roles and permissions FIRST
        $this->call(RolesAndPermissionsSeeder::class);

        // Then seed users (which will be assigned roles)
        $this->call(AdminUserSeeder::class);

        // Seed demo data only in non-production environments
        if (!app()->isProduction()) {
            $this->seedDemoProducts();
        }
    }

    /**
     * Seed demo products for development/testing
     */
    protected function seedDemoProducts(): void
    {
        $products = [
            [
                'name' => 'Product R$ 0.50',
                'description' => 'Generic product priced at R$ 0.50',
                'price' => '0.50',
            ],
            [
                'name' => 'Product R$ 1.00',
                'description' => 'Generic product priced at R$ 1.00',
                'price' => '1.00',
            ],
        ];

        foreach (range(1, 5) as $v) {
            $priceStr = number_format(1.00 + (0.50 * $v), 2, '.', '');

            $products[] = [
                'name' => 'Product R$ ' . $priceStr,
                'description' => 'Generic product priced at R$ ' . $priceStr,
                'price' => $priceStr,
            ];
        }

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }

        // Create additional random products in dev/testing environments
        if (app()->environment(['dev', 'local', 'staging', 'testing'])) {
            Product::factory(20)->create();
        }
    }
}

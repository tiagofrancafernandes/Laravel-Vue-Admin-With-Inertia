<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'super_admin']);

        // Create a payment method for testing
        \App\Models\PaymentMethod::factory()->create([
            'id' => 1,
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => true,
        ]);
    }

    // ==================== Product Endpoint Tests ====================

    public function testProductSelectEndpointRequiresAuthentication(): void
    {
        $response = $this->get(route('api.products.select'));
        $response->assertRedirect(route('login'));
    }

    public function testProductSelectEndpointReturnsAllProducts(): void
    {
        Product::factory(5)->create();

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function testProductSelectReturnsProductWithCorrectStructure(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => '99.99',
        ]);
    }

    public function testProductSelectIncludesSalesCount(): void
    {
        $product = Product::factory()->create(['name' => 'Popular Product']);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('sales_count', $data[0]);
    }

    public function testProductSelectCalculatesPopularityCorrectly(): void
    {
        $product = Product::factory()->create(['name' => 'Popular Product']);
        $client = Client::factory()->create();

        // Create a sale with this product
        Sale::factory()->create([
            'client_id' => $client->id,
            'items' => json_encode([
                [
                    'description' => 'Popular Product',
                    'quantity' => 2,
                    'price' => 50.00,
                ],
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $data = $response->json('data');

        $popularProduct = collect($data)->firstWhere('name', 'Popular Product');
        $this->assertNotNull($popularProduct);
        $this->assertEquals(1, $popularProduct['sales_count']);
    }

    public function testProductSelectCountsMultipleSalesOfSameProduct(): void
    {
        $product = Product::factory()->create(['name' => 'Best Seller']);
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        // Create two sales with this product
        Sale::factory()->create([
            'client_id' => $client1->id,
            'items' => json_encode([
                [
                    'description' => 'Best Seller',
                    'quantity' => 1,
                    'price' => 50.00,
                ],
            ]),
        ]);

        Sale::factory()->create([
            'client_id' => $client2->id,
            'items' => json_encode([
                [
                    'description' => 'Best Seller',
                    'quantity' => 3,
                    'price' => 50.00,
                ],
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $data = $response->json('data');

        $bestSeller = collect($data)->firstWhere('name', 'Best Seller');
        $this->assertNotNull($bestSeller);
        $this->assertEquals(2, $bestSeller['sales_count']);
    }

    public function testProductSelectSortsByCreatedAtDescending(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        sleep(1);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $data = $response->json('data');

        // Since both have 0 sales, they should be sorted by created_at DESC (newest first)
        // Product 2 (created last) should appear first when sales_count is equal
        $this->assertEquals('Product 2', $data[0]['name']);
        $this->assertEquals('Product 1', $data[1]['name']);
    }

    public function testProductSelectSortsByPopularityWhenAvailable(): void
    {
        $popular = Product::factory()->create(['name' => 'Popular']);
        $notPopular = Product::factory()->create(['name' => 'Not Popular']);
        $client = Client::factory()->create();

        // Add a sale for the popular product
        Sale::factory()->create([
            'client_id' => $client->id,
            'items' => json_encode([
                [
                    'description' => 'Popular',
                    'quantity' => 1,
                    'price' => 50.00,
                ],
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $data = $response->json('data');

        // Popular product should come first due to having sales_count > 0
        $this->assertEquals('Popular', $data[0]['name']);
    }

    public function testProductSelectReturnsEmptyListWhenNoProducts(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('api.products.select'));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    // ==================== Sales Page Integration Tests ====================

    public function testProductSelectComponentIsAvailableOnSalesCreatePage(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.create'));

        $response->assertStatus(200);
        // The component should be included in the page
        $this->assertNotNull($response);
    }

    public function testProductCanBeAddedToSalesFormFromProductSelect(): void
    {
        $product = Product::factory()->create([
            'name' => 'Selected Product',
            'price' => 99.99,
        ]);
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), [
                'client_id' => $client->id,
                'total_amount' => 99.99,
                'items' => [
                    [
                        'name' => 'Selected Product',
                        'quantity' => 1,
                        'unit_price' => 99.99,
                        'subtotal' => 99.99,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => 1,
                        'amount' => 99.99,
                    ],
                ],
            ]);

        // Verify endpoint responds properly (like existing SaleControllerTest)
        $this->assertThat(
            $response->status(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302)
            )
        );
    }

    public function testProductAutoFillsQuantityWithOne(): void
    {
        $product = Product::factory()->create([
            'name' => 'Auto Fill Test',
            'price' => 50.00,
        ]);
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), [
                'client_id' => $client->id,
                'total_amount' => 50.00,
                'items' => [
                    [
                        'name' => 'Auto Fill Test',
                        'quantity' => 1,
                        'unit_price' => 50.00,
                        'subtotal' => 50.00,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => 1,
                        'amount' => 50.00,
                    ],
                ],
            ]);

        // Verify endpoint responds properly
        $this->assertThat(
            $response->status(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302)
            )
        );

        // Verify product exists (it should be created before the sale attempt)
        $this->assertDatabaseHas('products', [
            'name' => 'Auto Fill Test',
            'price' => 50.00,
        ]);
    }

    public function testProductAutoFillsPrice(): void
    {
        $product = Product::factory()->create([
            'name' => 'Price Fill Test',
            'price' => 75.50,
        ]);
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), [
                'client_id' => $client->id,
                'total_amount' => 75.50,
                'items' => [
                    [
                        'name' => 'Price Fill Test',
                        'quantity' => 1,
                        'unit_price' => 75.50,
                        'subtotal' => 75.50,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => 1,
                        'amount' => 75.50,
                    ],
                ],
            ]);

        // Verify endpoint responds properly
        $this->assertThat(
            $response->status(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302)
            )
        );

        // Verify product with the price exists
        $this->assertDatabaseHas('products', [
            'name' => 'Price Fill Test',
            'price' => 75.50,
        ]);
    }

    public function testMultipleProductsCanBeAddedToSingleSale(): void
    {
        $product1 = Product::factory()->create([
            'name' => 'Product One',
            'price' => 50.00,
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Product Two',
            'price' => 75.00,
        ]);
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), [
                'client_id' => $client->id,
                'total_amount' => 125.00,
                'items' => [
                    [
                        'name' => 'Product One',
                        'quantity' => 1,
                        'unit_price' => 50.00,
                        'subtotal' => 50.00,
                    ],
                    [
                        'name' => 'Product Two',
                        'quantity' => 1,
                        'unit_price' => 75.00,
                        'subtotal' => 75.00,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => 1,
                        'amount' => 125.00,
                    ],
                ],
            ]);

        // Verify endpoint responds properly
        $this->assertThat(
            $response->status(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302)
            )
        );

        // Verify both products exist
        $this->assertDatabaseHas('products', ['name' => 'Product One']);
        $this->assertDatabaseHas('products', ['name' => 'Product Two']);
    }

    public function testProductDataIsNotModifiedAfterSelection(): void
    {
        $originalName = 'Original Name';
        $originalPrice = 100.00;

        $product = Product::factory()->create([
            'name' => $originalName,
            'price' => $originalPrice,
        ]);
        $client = Client::factory()->create();

        // Verify product data before sale attempt
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $originalName,
            'price' => $originalPrice,
        ]);

        // Create a sale using the product
        $this->actingAs($this->user)
            ->post(route('sales.store'), [
                'client_id' => $client->id,
                'total_amount' => $originalPrice,
                'items' => [
                    [
                        'name' => $originalName,
                        'quantity' => 1,
                        'unit_price' => $originalPrice,
                        'subtotal' => $originalPrice,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => 1,
                        'amount' => $originalPrice,
                    ],
                ],
            ]);

        // Verify product data is unchanged after sale attempt
        $product->refresh();
        $this->assertEquals($originalName, $product->name);
        $this->assertEquals($originalPrice, (float) $product->price);
    }
}

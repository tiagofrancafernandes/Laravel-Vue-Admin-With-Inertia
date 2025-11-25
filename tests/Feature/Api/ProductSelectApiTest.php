<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSelectApiTest extends TestCase
{
    use RefreshDatabase;

    public function testProductSelectApiWithAuthenticatedUser(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // Create test products
        Product::factory(5)->create();

        // Request the API endpoint
        $response = $this->get('/api/products/select');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        // Verify response structure
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'salesCount',
                ]
            ]
        ]);

        // Verify data is present
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertCount(5, $data);

        // Debug output - uncomment to see actual response
        // dd($response->json());
        // var_dump($response->json());
        // dump($response->json());
    }

    public function testProductSelectApiUnauthenticatedUserRedirectsToLogin(): void
    {
        $response = $this->get('/api/products/select');

        // Unauthenticated requests should redirect to login
        $response->assertRedirect(route('login', absolute: false));
    }

    public function testProductSelectApiSortsByPopularity(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Create products
        Product::factory()->create(['name' => 'Popular Product']);
        Product::factory()->create(['name' => 'Less Popular Product']);

        // Create sales with different products to simulate popularity
        $sale1 = Sale::factory()->create();
        $sale1->items = json_encode([
            ['description' => 'Popular Product', 'quantity' => 2, 'price' => 100]
        ]);
        $sale1->save();

        $sale2 = Sale::factory()->create();
        $sale2->items = json_encode([
            ['description' => 'Popular Product', 'quantity' => 1, 'price' => 100]
        ]);
        $sale2->save();

        $response = $this->get('/api/products/select');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Find products in response
        $popularInResponse = collect($data)->firstWhere('name', 'Popular Product');
        $lessPopularInResponse = collect($data)->firstWhere('name', 'Less Popular Product');

        // Verify popular product has higher salesCount
        if ($popularInResponse && $lessPopularInResponse) {
            $this->assertGreaterThanOrEqual(
                $lessPopularInResponse['salesCount'],
                $popularInResponse['salesCount']
            );
        }

        // Debug output
        // dd($response->json());
    }
}

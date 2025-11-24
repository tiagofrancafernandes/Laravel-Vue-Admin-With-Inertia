<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'super_admin']);
        $this->client = Client::factory()->create();
        $this->paymentMethod = PaymentMethod::factory()->create();
    }

    // ============ GET /sales (Index) ============

    /**
     * Test GET /sales returns sale list
     */
    public function testIndexReturnsSaleList(): void
    {
        Sale::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->get('/sales');

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /**
     * Test GET /sales with search filter by code
     */
    public function testIndexWithSearchFilterByCode(): void
    {
        $sale = Sale::factory()->create([
            'code' => 'S123ABC',
            'client_id' => $this->client->id,
        ]);

        Sale::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/sales?search=S123ABC');

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /**
     * Test GET /sales with search filter by client name
     */
    public function testIndexWithSearchFilterByClientName(): void
    {
        $client = Client::factory()->create(['name' => 'John Smith']);
        $sale = Sale::factory()->create(['client_id' => $client->id]);

        Sale::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/sales?search=John');

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /**
     * Test GET /sales with date_from filter
     */
    public function testIndexWithDateFromFilter(): void
    {
        $sale = Sale::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/sales?date_from=' . now()->subDays(10)->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /**
     * Test GET /sales with date_to filter
     */
    public function testIndexWithDateToFilter(): void
    {
        $sale = Sale::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/sales?date_to=' . now()->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /**
     * Test GET /sales with status filter
     */
    public function testIndexWithStatusFilter(): void
    {
        Sale::factory()->create(['status' => 'completed']);
        Sale::factory()->create(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->get('/sales?status=completed');

        $response->assertStatus(200);
    }

    /**
     * Test GET /sales with multiple filters
     */
    public function testIndexWithMultipleFilters(): void
    {
        $sale = Sale::factory()->create([
            'code' => 'S999XYZ',
            'status' => 'completed',
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/sales?search=S999XYZ&status=completed&date_from=' . now()->subDays(5)->format('Y-m-d'));

        $response->assertStatus(200);
    }

    /**
     * Test GET /sales requires authentication
     */
    public function testIndexRequiresAuthentication(): void
    {
        $response = $this->get('/sales');

        $response->assertRedirect('/login');
    }

    // ============ GET /sales/create (Create Form) ============

    /**
     * Test GET /sales/create shows creation form
     */
    public function testCreateShowsCreationForm(): void
    {
        $response = $this->actingAs($this->user)->get('/sales/create');

        $response->assertStatus(200);
        $response->assertViewHas('paymentMethods');
        $response->assertViewHas('anonymousClientId');
    }

    /**
     * Test GET /sales/create requires authentication
     */
    public function testCreateRequiresAuthentication(): void
    {
        $response = $this->get('/sales/create');

        $response->assertRedirect('/login');
    }

    // ============ POST /sales (Store) ============

    /**
     * Test POST /sales creates new sale
     */
    public function testStoreCreatesNewSale(): void
    {
        $saleData = [
            'client_id' => $this->client->id,
            'total_amount' => 100,
            'items' => [
                [
                    'description' => 'Item 1',
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
            'payments' => [
                [
                    'payment_method_id' => $this->paymentMethod->id,
                    'amount' => 100,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post('/sales', $saleData);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'client_id' => $this->client->id,
            'total_amount' => 100,
        ]);
    }

    /**
     * Test POST /sales validates required fields
     */
    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/sales', []);

        $response->assertSessionHasErrors();
    }

    /**
     * Test POST /sales validates client_id
     */
    public function testStoreValidatesClientId(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/sales', [
                'client_id' => 9999,
                'total_amount' => 100,
            ]);

        $response->assertSessionHasErrors('client_id');
    }

    /**
     * Test POST /sales validates total_amount
     */
    public function testStoreValidatesTotalAmount(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/sales', [
                'client_id' => $this->client->id,
                'total_amount' => -50,
            ]);

        $response->assertSessionHasErrors('total_amount');
    }

    /**
     * Test POST /sales requires authentication
     */
    public function testStoreRequiresAuthentication(): void
    {
        $response = $this->post('/sales', [
            'client_id' => $this->client->id,
            'total_amount' => 100,
        ]);

        $response->assertRedirect('/login');
    }

    // ============ GET /sales/{sale} (Show) ============

    /**
     * Test GET /sales/{sale} shows sale details
     */
    public function testShowDisplaysSaleDetails(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/sales/{$sale->id}");

        $response->assertStatus(200);
        $response->assertViewHas('sale');
    }

    /**
     * Test GET /sales/{sale} for non-existent sale returns 404
     */
    public function testShowForNonExistentSaleReturns404(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/sales/9999');

        $response->assertStatus(404);
    }

    /**
     * Test GET /sales/{sale} requires authentication
     */
    public function testShowRequiresAuthentication(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->get("/sales/{$sale->id}");

        $response->assertRedirect('/login');
    }

    // ============ POST /sales/{sale}/cancel ============

    /**
     * Test POST /sales/{sale}/cancel cancels a sale
     */
    public function testCancelCancelsSale(): void
    {
        $sale = Sale::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->post("/sales/{$sale->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test POST /sales/{sale}/cancel for non-existent sale returns 404
     */
    public function testCancelForNonExistentSaleReturns404(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/sales/9999/cancel');

        $response->assertStatus(404);
    }

    /**
     * Test POST /sales/{sale}/cancel requires authentication
     */
    public function testCancelRequiresAuthentication(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->post("/sales/{$sale->id}/cancel");

        $response->assertRedirect('/login');
    }
}

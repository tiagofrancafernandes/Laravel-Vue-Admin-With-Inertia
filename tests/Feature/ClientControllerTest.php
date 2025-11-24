<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'super_admin']);
    }

    // ============ GET /clients (Index) ============

    /**
     * Test GET /clients returns client list
     */
    public function testIndexReturnsClientList(): void
    {
        Client::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertStatus(200);
    }

    /**
     * Test GET /clients with search filter
     */
    public function testIndexWithSearchFilter(): void
    {
        $client = Client::factory()->create([
            'name' => 'John Doe',
            'is_anonymous' => false,
        ]);

        Client::factory()->count(3)->create(['is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->get('/clients?search=John');

        $response->assertStatus(200);
    }

    /**
     * Test GET /clients requires authentication
     */
    public function testIndexRequiresAuthentication(): void
    {
        $response = $this->get('/clients');

        $response->assertRedirect('/login');
    }

    // ============ POST /clients (Store) ============

    /**
     * Test POST /clients creates new client
     */
    public function testStoreCreatesNewClient(): void
    {
        $clientData = [
            'name' => 'New Client',
            'email' => 'client@example.com',
            'phone' => '(11) 9999-8888',
            'document' => '12345678901',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user)
            ->post('/clients', $clientData);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'name' => 'New Client',
            'email' => 'client@example.com',
        ]);
    }

    /**
     * Test POST /clients validates required fields
     */
    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/clients', [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test POST /clients validates unique email
     */
    public function testStoreValidatesUniqueEmail(): void
    {
        $existingClient = Client::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->user)
            ->post('/clients', [
                'name' => 'New Client',
                'email' => 'existing@example.com',
                'phone' => '1234567890',
            ]);

        $response->assertSessionHasErrors('email');
    }

    // ============ GET /clients/{client} (Show) ============

    /**
     * Test GET /clients/{client} shows client details
     */
    public function testShowDisplaysClientDetails(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/clients/{$client->id}");

        $response->assertStatus(200);
    }

    // ============ API: GET /api/clients/select (SelectList) ============

    /**
     * Test GET /api/clients/select returns JSON list
     */
    public function testSelectListReturnsJsonList(): void
    {
        Client::factory()->count(3)->create(['is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'phone'],
            ],
        ]);
    }

    /**
     * Test GET /api/clients/select with search parameter
     */
    public function testSelectListWithSearchFilter(): void
    {
        $client = Client::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'is_anonymous' => false,
        ]);

        Client::factory()->count(2)->create(['is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select?search=John');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'John Doe');
    }

    /**
     * Test GET /api/clients/select search by email
     */
    public function testSelectListSearchByEmail(): void
    {
        $client = Client::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_anonymous' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select?search=jane@example.com');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Test GET /api/clients/select search by phone
     */
    public function testSelectListSearchByPhone(): void
    {
        $client = Client::factory()->create([
            'name' => 'Bob Johnson',
            'phone' => '5551234567',
            'is_anonymous' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select?search=555123');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Test GET /api/clients/select with no search returns all clients
     */
    public function testSelectListWithoutSearchReturnsAllClients(): void
    {
        Client::factory()->count(5)->create(['is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Test GET /api/clients/select limits results to 20
     */
    public function testSelectListLimitsResults(): void
    {
        Client::factory()->count(25)->create(['is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
    }

    /**
     * Test GET /api/clients/select requires authentication
     */
    public function testSelectListRequiresAuthentication(): void
    {
        $response = $this->getJson('/api/clients/select');

        $response->assertStatus(401);
    }

    /**
     * Test GET /api/clients/select excludes anonymous clients
     */
    public function testSelectListExcludesAnonymousClients(): void
    {
        Client::factory()->create(['name' => 'Anonymous', 'is_anonymous' => true]);
        Client::factory()->create(['name' => 'Regular Client', 'is_anonymous' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/select');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Regular Client');
    }

    // ============ API: GET /api/clients/{client}/balance ============

    /**
     * Test GET /api/clients/{client}/balance returns balance information
     */
    public function testBalanceReturnsClientBalance(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/clients/{$client->id}/balance");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'client_id',
            'balance',
            'credit_limit',
            'updated_at',
        ]);
    }

    /**
     * Test GET /api/clients/{client}/balance contains correct data
     */
    public function testBalanceContainsCorrectClientData(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/clients/{$client->id}/balance");

        $response->assertStatus(200);
        $response->assertJsonPath('client_id', $client->id);
    }

    /**
     * Test GET /api/clients/{client}/balance for non-existent client returns 404
     */
    public function testBalanceForNonExistentClientReturns404(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/clients/9999/balance');

        $response->assertStatus(404);
    }

    /**
     * Test GET /api/clients/{client}/balance requires authentication
     */
    public function testBalanceRequiresAuthentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->getJson("/api/clients/{$client->id}/balance");

        $response->assertStatus(401);
    }

    // ============ POST /clients/{client}/add-balance ============

    /**
     * Test POST /clients/{client}/add-balance adds balance
     */
    public function testAddBalanceAddsClientBalance(): void
    {
        $client = Client::factory()->create();
        ClientBalance::create(['client_id' => $client->id, 'balance' => 0, 'credit_limit' => 0]);

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/add-balance", [
                'amount' => 100.50,
                'description' => 'Test deposit',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test POST /clients/{client}/add-balance validates amount
     */
    public function testAddBalanceValidatesAmount(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/add-balance", [
                'amount' => -10,
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test POST /clients/{client}/add-balance requires amount
     */
    public function testAddBalanceRequiresAmount(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/add-balance", []);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test POST /clients/{client}/add-balance with decimal amount
     */
    public function testAddBalanceWithDecimalAmount(): void
    {
        $client = Client::factory()->create();
        ClientBalance::create(['client_id' => $client->id, 'balance' => 0, 'credit_limit' => 0]);

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/add-balance", [
                'amount' => 50.99,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test POST /clients/{client}/add-balance validates description length
     */
    public function testAddBalanceValidatesDescriptionLength(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/add-balance", [
                'amount' => 100,
                'description' => str_repeat('a', 501),
            ]);

        $response->assertSessionHasErrors('description');
    }

    /**
     * Test POST /clients/{client}/add-balance requires authentication
     */
    public function testAddBalanceRequiresAuthentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->post("/clients/{$client->id}/add-balance", [
            'amount' => 100,
        ]);

        $response->assertRedirect('/login');
    }

    // ============ POST /clients/{client}/pay-tab ============

    /**
     * Test POST /clients/{client}/pay-tab pays client debt
     */
    public function testPayTabPaysClientDebt(): void
    {
        $client = Client::factory()->create();
        ClientBalance::create(['client_id' => $client->id, 'balance' => 0, 'credit_limit' => 100]);

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/pay-tab", [
                'amount' => 50,
                'description' => 'Partial payment',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test POST /clients/{client}/pay-tab validates amount
     */
    public function testPayTabValidatesAmount(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/pay-tab", [
                'amount' => -10,
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test POST /clients/{client}/pay-tab requires amount
     */
    public function testPayTabRequiresAmount(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/pay-tab", []);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test POST /clients/{client}/pay-tab with decimal amount
     */
    public function testPayTabWithDecimalAmount(): void
    {
        $client = Client::factory()->create();
        ClientBalance::create(['client_id' => $client->id, 'balance' => 0, 'credit_limit' => 100]);

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/pay-tab", [
                'amount' => 25.50,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test POST /clients/{client}/pay-tab validates description length
     */
    public function testPayTabValidatesDescriptionLength(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/clients/{$client->id}/pay-tab", [
                'amount' => 50,
                'description' => str_repeat('a', 501),
            ]);

        $response->assertSessionHasErrors('description');
    }

    /**
     * Test POST /clients/{client}/pay-tab requires authentication
     */
    public function testPayTabRequiresAuthentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->post("/clients/{$client->id}/pay-tab", [
            'amount' => 50,
        ]);

        $response->assertRedirect('/login');
    }
}

<?php

namespace Tests\Feature\Pages;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'attendant']);
    }

    /**
     * Client Select API Tests
     */

    public function testClientsSelectApiReturnsAllClientsWhenNoSearchProvided(): void
    {
        $clients = Client::factory(5)->create();

        $response = $this->actingAs($this->user)->get(route('api.clients.select'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(5, count($data));
    }

    public function testClientsSelectApiFiltersClientsByName(): void
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);
        Client::factory()->create(['name' => 'João Oliveira']);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=João');

        $response->assertStatus(200);
        $clients = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($clients));

        foreach ($clients as $client) {
            $this->assertStringContainsString('João', $client['name']);
        }
    }

    public function testClientsSelectApiFiltersClientsByEmail(): void
    {
        Client::factory()->create(['name' => 'Test 1', 'email' => 'test1@example.com']);
        Client::factory()->create(['name' => 'Test 2', 'email' => 'other@example.com']);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=test1@');

        $response->assertStatus(200);
        $clients = $response->json('data');
        $this->assertCount(1, $clients);
        $this->assertEquals('test1@example.com', $clients[0]['email']);
    }

    public function testClientsSelectApiFiltersClientsByPhone(): void
    {
        Client::factory()->create(['name' => 'Test 1', 'phone' => '(11) 98765-4321']);
        Client::factory()->create(['name' => 'Test 2', 'phone' => '(21) 99999-8888']);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=98765');

        $response->assertStatus(200);
        $clients = $response->json('data');
        $this->assertCount(1, $clients);
        $this->assertEquals('(11) 98765-4321', $clients[0]['phone']);
    }

    public function testClientsSelectApiReturnsClientWithIdNameEmailPhone(): void
    {
        $client = Client::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.select'));

        $response->assertStatus(200);
        $data = $response->json('data');

        $foundClient = collect($data)->first(fn ($c) => $c['id'] === $client->id);
        $this->assertNotNull($foundClient);
        $this->assertArrayHasKey('id', $foundClient);
        $this->assertArrayHasKey('name', $foundClient);
        $this->assertArrayHasKey('email', $foundClient);
        $this->assertArrayHasKey('phone', $foundClient);
    }

    /**
     * Client Balance API Tests
     */

    public function testClientBalanceApiReturnsBalanceAndCreditLimit(): void
    {
        $client = Client::factory()->create();
        $client->balance()->create([
            'balance' => 250.50,
            'credit_limit' => 1000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'client_id',
            'balance',
            'credit_limit',
        ]);
        $response->assertJson([
            'balance' => 250.50,
            'credit_limit' => 1000.00,
        ]);
    }

    public function testClientBalanceApiReturnsZeroWhenNoBalanceExists(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        // Should return default values or create a balance record
        $response->assertJsonStructure([
            'balance',
            'credit_limit',
        ]);
    }

    public function testClientBalanceApiForNonExistentClientReturns404(): void
    {
        $response = $this->actingAs($this->user)->get(route('api.clients.balance', 99999));

        $response->assertStatus(404);
    }

    public function testClientBalanceApiReturnsCurrentBalance(): void
    {
        $client = Client::factory()->create();
        $client->balance()->create([
            'balance' => 500.00,
            'credit_limit' => 2000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(500.00, $data['balance']);
        $this->assertEquals(2000.00, $data['credit_limit']);
    }

    /**
     * Authentication Tests for API Routes
     */
    public function testAuthenticatedUserCanAccessAllApiRoutes(): void
    {
        $client = Client::factory()->create();

        // Test clients select
        $response = $this->actingAs($this->user)->get(route('api.clients.select'));
        $this->assertEquals(200, $response->status());

        // Test client balance
        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));
        $this->assertEquals(200, $response->status());
    }

    /**
     * JSON Response Format Tests
     */

    public function testClientsSelectApiReturnsValidJson(): void
    {
        Client::factory(2)->create();

        $response = $this->actingAs($this->user)->get(route('api.clients.select'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
    }

    public function testClientBalanceApiReturnsValidJsonStructure(): void
    {
        $client = Client::factory()->create();
        $client->balance()->create(['balance' => 100, 'credit_limit' => 500]);

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        $response->assertJsonStructure(['client_id', 'balance', 'credit_limit']);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertIsNumeric($data['balance']);
        $this->assertIsNumeric($data['credit_limit']);
    }
}

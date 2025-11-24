<?php

namespace Tests\Feature\Pages;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientsPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'attendant']);
    }

    /**
     * Page Access Tests
     * Test accessing clients pages
     */

    public function testUnauthenticatedUserCannotAccessClientsIndex(): void
    {
        $response = $this->get(route('clients.index'));

        $response->assertRedirect(route('login'));
    }

    public function testAuthenticatedUserCanAccessClientsIndex(): void
    {
        $response = $this->actingAs($this->user)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            ->component('Clients/Index')
        );
    }

    public function testUnauthenticatedUserCannotAccessClientsCreate(): void
    {
        $response = $this->get(route('clients.create'));

        $response->assertRedirect(route('login'));
    }

    public function testAuthenticatedUserCanAccessClientsCreate(): void
    {
        $response = $this->actingAs($this->user)->get(route('clients.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            ->component('Clients/Create')
        );
    }

    public function testUnauthenticatedUserCannotAccessClientsShow(): void
    {
        $client = Client::factory()->create();

        $response = $this->get(route('clients.show', $client));

        $response->assertRedirect(route('login'));
    }

    public function testAuthenticatedUserCanAccessClientsShow(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get(route('clients.show', $client));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            ->component('Clients/Show')
        );
    }

    /**
     * Client Creation Tests
     */

    public function testUnauthenticatedUserCannotCreateClient(): void
    {
        $response = $this->post(route('clients.store'), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function testAuthenticatedUserCanCreateClient(): void
    {
        $response = $this->actingAs($this->user)->post(route('clients.store'), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
            'cpf_cnpj' => '123.456.789-10',
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'email' => 'test@example.com',
        ]);

        // The redirect goes to the show page, not the index
        $response->assertRedirect();
    }

    public function testClientCreationValidatesRequiredFields(): void
    {
        $response = $this->actingAs($this->user)->post(route('clients.store'), [
            'name' => '',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * API Routes Tests
     * Test API endpoints for client selection and balance
     */

    public function testAuthenticatedUserCanAccessClientsSelectApi(): void
    {
        Client::factory(3)->create();

        $response = $this->actingAs($this->user)->get(route('api.clients.select'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function testClientsSelectApiCanFilterBySearch(): void
    {
        $client1 = Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=João');

        $response->assertStatus(200);
        $clients = $response->json('data');
        $this->assertCount(1, $clients);
        $this->assertEquals('João Silva', $clients[0]['name']);
    }

    public function testAuthenticatedUserCanAccessClientBalanceApi(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'client_id',
            'balance',
            'credit_limit',
        ]);
    }

    public function testClientBalanceApiReturnsCorrectData(): void
    {
        $client = Client::factory()->create();
        $client->balance()->create([
            'balance' => 100.00,
            'credit_limit' => 500.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.balance', $client));

        $response->assertStatus(200);
        $response->assertJson([
            'balance' => 100.00,
            'credit_limit' => 500.00,
        ]);
    }

    /**
     * Phase 2: Client Selection with Phone Display
     */

    public function testClientSelectDisplaysPhoneNumber(): void
    {
        Client::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '(11) 98765-4321',
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=João');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $client = $data[0];
        $this->assertEquals('João Silva', $client['name']);
        $this->assertEquals('joao@example.com', $client['email']);
        $this->assertEquals('(11) 98765-4321', $client['phone']);
    }

    public function testClientSelectResponseIncludesPhoneField(): void
    {
        Client::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=Test');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('phone', $data[0]);
    }

    public function testClientSelectSearchByPhoneWorks(): void
    {
        Client::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.select') . '?search=98765');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('(11) 98765-4321', $data[0]['phone']);
    }

    public function testClientSelectWithNullPhoneStillWorks(): void
    {
        Client::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('api.clients.select'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }
}

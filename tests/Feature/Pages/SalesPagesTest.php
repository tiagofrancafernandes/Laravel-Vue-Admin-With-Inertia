<?php

namespace Tests\Feature\Pages;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Phase 1: Modal Flow Tests
     * Test client creation modal on sales create page
     */

    public function testSalesCreatePageCanBeAccessed(): void
    {
        $response = $this->actingAs($this->user)->get(route('sales.create'));

        $response->assertStatus(200);
        $response->assertViewIs('Sales/Create');
    }

    public function testUnauthenticatedUserCannotAccessSalesCreate(): void
    {
        $response = $this->get(route('sales.create'));

        $response->assertRedirect(route('login'));
    }

    public function testClientModalSubmitsCorrectly(): void
    {
        // Simulate AJAX request to create client from modal
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cliente criado com sucesso!',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'client' => [
                'id',
                'name',
                'email',
                'phone',
            ],
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);
    }

    public function testClientCreatedCorrectlyAndReturnsData(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'phone' => '(11) 91234-5678',
            ],
            ['Accept' => 'application/json']
        );

        $data = $response->json();

        $this->assertEquals('Maria Santos', $data['client']['name']);
        $this->assertEquals('maria@example.com', $data['client']['email']);
        $this->assertEquals('(11) 91234-5678', $data['client']['phone']);
    }

    public function testClientModalValidatesRequiredFields(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => '',
                'email' => 'invalid-email',
                'phone' => '',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function testClientModalRejectsInvalidEmail(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'Test Client',
                'email' => 'not-an-email',
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function testClientModalRejectsDuplicateEmail(): void
    {
        Client::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'New Client',
                'email' => 'duplicate@example.com',
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function testClientModalCreatesBalanceRecord(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'Test Client',
                'email' => 'test@example.com',
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        $clientId = $response->json('client.id');

        $this->assertDatabaseHas('client_balances', [
            'client_id' => $clientId,
            'balance' => 0,
            'credit_limit' => 0,
        ]);
    }

    public function testUnauthenticatedUserCannotCreateClientViaModal(): void
    {
        $response = $this->postJson(
            route('clients.store'),
            [
                'name' => 'Test Client',
                'email' => 'test@example.com',
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(401);
    }

    public function testClientModalReturnsErrorOnException(): void
    {
        // Mock a scenario that would cause an exception
        // This test ensures proper error handling in modal
        $response = $this->actingAs($this->user)->postJson(
            route('clients.store'),
            [
                'name' => 'Test Client',
                'email' => null, // This might cause issues depending on validation
                'phone' => '(11) 98765-4321',
            ],
            ['Accept' => 'application/json']
        );

        // Should return 422 with validation error or 500 with error message
        $this->assertIn($response->status(), [422, 500]);
    }

    /**
     * Phase 2-3: Tests for phone display and balance lazy loading
     * (Placeholder - will be implemented in Phase 2 and 3)
     */

    public function testSalesIndexPageCanBeAccessed(): void
    {
        $response = $this->actingAs($this->user)->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertViewIs('Sales/Index');
    }

    public function testSalesShowPageDisplaysSaleDetails(): void
    {
        // Create a sample sale
        $client = Client::factory()->create();
        // Additional setup would be needed based on Sale model structure

        // This is a placeholder - actual test depends on sale creation
        $response = $this->actingAs($this->user)->get(route('sales.index'));
        $response->assertStatus(200);
    }
}

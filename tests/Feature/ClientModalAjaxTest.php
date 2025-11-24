<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientModalAjaxTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'super_admin']);
    }

    /**
     * Test AJAX POST /clients creates client and returns JSON
     */
    public function testStoreClientViaAjaxReturnsJson(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('clients.store'), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);

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

        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '(11) 98765-4321',
        ]);
    }

    /**
     * Test AJAX POST /clients with validation errors returns proper error response
     */
    public function testStoreClientViaAjaxValidationErrors(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('clients.store'), [
            'name' => '', // Missing required name
            'email' => 'invalid-email', // Invalid email format
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Test AJAX POST /clients enforces unique email constraint
     */
    public function testStoreClientViaAjaxEnforcesUniqueEmail(): void
    {
        Client::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->user)->postJson(route('clients.store'), [
            'name' => 'Another Client',
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test AJAX POST /clients creates client with minimal data (name only)
     */
    public function testStoreClientViaAjaxWithMinimalData(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('clients.store'), [
            'name' => 'Minimal Client',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'client' => [
                'name' => 'Minimal Client',
                'email' => null,
                'phone' => null,
            ],
        ]);
    }
}

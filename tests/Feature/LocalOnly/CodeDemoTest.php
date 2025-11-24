<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeDemoTest extends TestCase
{
    use RefreshDatabase;

    public function testCodeDemoTestAccessToDashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }
}

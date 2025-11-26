<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Welcome page is accessible to unauthenticated users.
     */
    public function testWelcomePageIsAccessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('canLogin')
            ->has('canRegister')
            ->has('laravelVersion')
            ->has('phpVersion')
        );
    }

    /**
     * Welcome page shows correct properties.
     */
    public function testWelcomePageHasCorrectProperties(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('canLogin', true)
            ->where('canRegister', true)
        );
    }

    /**
     * Authenticated user can access welcome page.
     */
    public function testAuthenticatedUserCanAccessWelcomePage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Login route exists and is not required to be authenticated.
     */
    public function testLoginRouteIsAccessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Register route exists and is not required to be authenticated.
     */
    public function testRegisterRouteIsAccessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Forgot password route is accessible.
     */
    public function testForgotPasswordRouteIsAccessible(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    /**
     * Invalid routes return 404.
     */
    public function testInvalidRouteReturns404(): void
    {
        $response = $this->get('/invalid-route-that-does-not-exist');

        $response->assertNotFound();
    }

    /**
     * Authenticated user trying to access login is redirected to dashboard.
     */
    public function testAuthenticatedUserAccessingLoginIsRedirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Authenticated user trying to access register is redirected to dashboard.
     */
    public function testAuthenticatedUserAccessingRegisterIsRedirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }

    /**
     * User can logout successfully.
     */
    public function testUserCanLogoutSuccessfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Unauthenticated user cannot logout.
     */
    public function testUnauthenticatedUserCannotLogout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }
}

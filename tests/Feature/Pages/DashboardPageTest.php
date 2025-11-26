<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Unauthenticated user should be redirected to login.
     */
    public function testUnauthenticatedUserRedirectedToDashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user can access dashboard.
     */
    public function testAuthenticatedUserCanAccessDashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('recentUsers')
        );
    }

    /**
     * Dashboard displays correct stats structure.
     */
    public function testDashboardDisplaysCorrectStats(): void
    {
        // Create multiple users with different roles
        $adminUser = User::factory()->admin()->create();
        $regularUsers = User::factory()->count(3)->create();
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.totalUsers', 5)
            ->where('stats.adminUsers', 1)
            ->where('stats.regularUsers', 4)
            ->where('stats.verifiedUsers', 1)
        );
    }

    /**
     * Dashboard shows recent users (last 10).
     */
    public function testDashboardShowsRecentUsers(): void
    {
        $user = User::factory()->create();
        $otherUsers = User::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentUsers', 6)
        );
    }

    /**
     * Admin user can see dashboard.
     */
    public function testAdminUserCanSeeDashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Regular user can see dashboard.
     */
    public function testRegularUserCanSeeDashboard(): void
    {
        $user = User::factory()->withRole('user')->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Email verified at is counted correctly.
     */
    public function testEmailVerifiedAtCountedCorrectly(): void
    {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($verifiedUser)->get('/dashboard');

        $response->assertInertia(fn ($page) => $page
            ->where('stats.verifiedUsers', 1)
        );
    }
}

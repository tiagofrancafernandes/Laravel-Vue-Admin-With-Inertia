<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;
use Database\Seeders\AdminUserSeeder;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDatabase;
    use DatabaseTruncation;

    protected static int $seederInitialUsersCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncateDatabaseTables();

        // Run seeds before each test
        $this->seed();
        static::$seederInitialUsersCount = count(AdminUserSeeder::initialUsers());

        // $this->seed(MySeeder::class);
    }

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
        $response->assertInertia(
            fn ($page) => $page
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
        $this->truncateDatabaseTables();
        $this->seed();

        $totalUsers = static::$seederInitialUsersCount;

        $this->assertEquals(0, User::role('user')->count());

        $this->assertEquals(4, $totalUsers);

        $this->assertEquals(1, User::role('admin')->count());
        // $adminUser = User::factory()->create()->syncRoles(['admin']);
        $adminUser = User::factory()->admin()->create();
        $totalUsers++;
        $this->assertEquals(2, User::role('admin')->count());

        $this->assertEquals(1, User::role('user')->count());
        $regularUsers = User::factory()->count(3)->withRole('user')->create();
        $totalUsers += 3;
        $regularUsersCount = 4;
        $this->assertEquals($regularUsersCount, User::role('user')->count());

        $verifiedUser = User::factory()->create();

        if ($verifiedUser) {
            $totalUsers++;
            $regularUsersCount++;
        }

        $unverifiedUser = User::factory()->unverified()->create();

        if ($unverifiedUser) {
            $totalUsers++;
            $regularUsersCount++;
        }

        $this->assertEquals(10, $totalUsers);

        $response = $this->actingAs($adminUser)->get('/dashboard');

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($page) => $page
            ->where('stats.totalUsers', $totalUsers)
            ->where('stats.adminUsers', 2)
            ->where('stats.unverifiedUsers', 1)
            ->where('stats.regularUsers', $regularUsersCount) // $regularUsers + $unverifiedUser + $verifiedUser
            ->where('stats.verifiedUsers', $totalUsers - 1) // admins + verifiedUser
        );
    }

    /**
     * Dashboard shows recent users (last 10).
     */
    public function testDashboardShowsRecentUsers(): void
    {
        $user = User::factory()->create();

        $user = User::factory()->create();
        $otherUsers = User::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            // ->has('recentUsers', 6)
            ->has('recentUsers', 10)
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
        $totalUsers = static::$seederInitialUsersCount;

        $this->assertEquals(4, $totalUsers);

        $verifiedUser = User::factory()->create();
        $totalUsers++;
        $unverifiedUser = User::factory()->unverified()->create();
        $totalUsers++;

        $response = $this->actingAs($verifiedUser)->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
            ->where('stats.verifiedUsers', static::$seederInitialUsersCount + 1)
            ->where('stats.unverifiedUsers', 1)
            ->where('stats.totalUsers', $totalUsers)
        );
    }
}

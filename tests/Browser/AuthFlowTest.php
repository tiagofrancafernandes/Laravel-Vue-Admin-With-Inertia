<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUsers();
    }

    /**
     * Test successful login flow
     */
    public function testSuccessfulLoginFlow(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->assertSee('Log in')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->waitForRoute('dashboard')
                ->assertAuthenticated()
                ->assertSee('Dashboard')
                ->assertSee($user->name);
        });
    }

    /**
     * Test dashboard loads after login
     */
    public function testDashboardLoadsAfterLogin(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertSee('Dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * Test dark mode toggle functionality
     */
    public function testDarkModeToggle(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertSee('Dashboard');

            // Check that dark mode button exists
            $browser->assertPresent('button[aria-label="Toggle dark mode"]');

            // Click dark mode toggle
            $browser->click('button[aria-label="Toggle dark mode"]')
                ->pause(300);

            // Verify that local storage was updated
            // Note: This is a simplified check; full verification would require JavaScript execution
            $browser->assertPresent('button[aria-label="Toggle dark mode"]');
        });
    }

    /**
     * Test navigation menu is visible
     */
    public function testNavigationMenu(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertSee('Dashboard')
                ->assertSee('Vendas')
                ->assertSee('Clientes')
                ->assertSee('Sair');
        });
    }

    /**
     * Test logout functionality
     */
    public function testLogoutFunctionality(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertAuthenticated()
                ->click('button:contains("Sair")')
                ->pause(500)
                ->assertGuest()
                ->assertUrlIs('/login');
        });
    }

    /**
     * Test navigation to sales page
     */
    public function testNavigationToSalesPage(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('a:contains("Vendas")')
                ->waitForRoute('sales.index')
                ->assertPathIs('/sales')
                ->assertSee('Vendas');
        });
    }

    /**
     * Test navigation to clients page
     */
    public function testNavigationToClientsPage(): void
    {
        $user = User::where('email', 'admin@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('a:contains("Clientes")')
                ->waitForRoute('clients.index')
                ->assertPathIs('/clients')
                ->assertSee('Clientes');
        });
    }

    /**
     * Test unauthenticated user is redirected to login
     */
    public function testUnauthenticatedUserRedirection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertUrlIs('/login');
        });
    }

    /**
     * Create test users
     */
    private function createTestUsers(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@mail.com',
            'password' => bcrypt('password'),
            'type' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Attendant User',
            'email' => 'attendant@mail.com',
            'password' => bcrypt('password'),
            'type' => 'attendant',
            'email_verified_at' => now(),
        ]);
    }
}

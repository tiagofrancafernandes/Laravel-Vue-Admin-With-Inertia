<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ClientsFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestUser();
        $this->createTestClients();
    }

    /**
     * Test complete client creation flow
     */
    public function testCompleteClientCreationFlow(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            // Login and navigate
            $browser->loginAs($user)
                ->visit('/clients')
                ->assertSee('Clientes');

            // Navigate to client creation
            $browser->click('a[href="/clients/create"]')
                ->waitForRoute('clients.create')
                ->assertSee('Novo Cliente');

            // Fill personal information
            $browser->type('input[placeholder*="João Silva"]', 'João da Silva')
                ->type('input[type="email"]', 'joao@email.com')
                ->type('input[placeholder*="(11)"]', '(11) 98765-4321')
                ->pause(300);

            // Fill document (CPF)
            $browser->type('input[placeholder*="123.456"]', '123.456.789-10');

            // Click validate button
            $browser->script("
                const buttons = document.querySelectorAll('button');
                const validateBtn = Array.from(buttons).find(b => b.textContent.includes('Validar'));
                if (validateBtn) validateBtn.click();
            ");
            $browser->pause(500);

            // Verify validation feedback
            $browser->assertSee('CPF válido');

            // Set initial balance
            $browser->type('input[type="number"][placeholder="0.00"]', '100.00')
                ->pause(300);

            // Submit form
            $browser->script("
                const buttons = document.querySelectorAll('button');
                const submitBtn = Array.from(buttons).find(b => b.textContent.includes('Salvar'));
                if (submitBtn) submitBtn.click();
            ");
            $browser->waitForRoute('clients.show')
                ->pause(500)
                ->assertSee('João da Silva');
        });
    }

    /**
     * Test client creation with CNPJ validation
     */
    public function testClientCreationWithCNPJValidation(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/clients/create')
                ->assertSee('Novo Cliente');

            // Fill basic info
            $browser->type('input[placeholder*="João Silva"]', 'Empresa LTDA')
                ->type('input[type="email"]', 'empresa@email.com')
                ->pause(300);

            // Fill CNPJ
            $browser->type('input[placeholder*="123.456"]', '11.222.333/0001-81');

            // Click validate button
            $browser->script("
                const buttons = document.querySelectorAll('button');
                const validateBtn = Array.from(buttons).find(b => b.textContent.includes('Validar'));
                if (validateBtn) validateBtn.click();
            ");
            $browser->pause(500);

            // Verify validation feedback
            $browser->assertSee('CNPJ válido');

            // Submit form
            $browser->script("
                const buttons = document.querySelectorAll('button');
                const submitBtn = Array.from(buttons).find(b => b.textContent.includes('Salvar'));
                if (submitBtn) submitBtn.click();
            ");
            $browser->waitForRoute('clients.show')
                ->pause(500)
                ->assertSee('Empresa LTDA');
        });
    }

    /**
     * Test client creation with invalid document
     */
    public function testClientCreationWithInvalidDocument(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/clients/create')
                ->assertSee('Novo Cliente');

            // Fill basic info
            $browser->type('input[placeholder*="João Silva"]', 'Invalid Client')
                ->type('input[type="email"]', 'invalid@email.com')
                ->pause(300);

            // Fill invalid CPF
            $browser->type('input[placeholder*="123.456"]', '111.111.111-11');

            // Click validate button
            $browser->script("
                const buttons = document.querySelectorAll('button');
                const validateBtn = Array.from(buttons).find(b => b.textContent.includes('Validar'));
                if (validateBtn) validateBtn.click();
            ");
            $browser->pause(500);

            // Verify error message
            $browser->assertSee('CPF inválido');
        });
    }

    /**
     * Test clients listing page loads
     */
    public function testClientsListingPageLoads(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/clients')
                ->assertSee('Clientes')
                ->assertSee('Filtrar Clientes')
                ->assertSee('Novo Cliente');
        });
    }

    /**
     * Test client search functionality
     */
    public function testClientSearchFunctionality(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/clients')
                ->type('input[placeholder*="Nome, e-mail"]', 'Test Client 1')
                ->press('Filtrar')
                ->pause(500)
                ->assertPathIs('/clients');
        });
    }

    /**
     * Test client profile page
     */
    public function testClientProfilePage(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();
        $client = Client::where('name', 'Test Client 1')->first();

        $this->browse(function (Browser $browser) use ($user, $client) {
            $browser->loginAs($user)
                ->visit("/clients/{$client->id}")
                ->assertSee('Test Client 1')
                ->assertSee('Contato')
                ->assertSee('Informações da Conta')
                ->assertSee('Gasto Total');
        });
    }

    /**
     * Test client profile displays sections
     */
    public function testClientProfileDisplaysSections(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();
        $client = Client::where('name', 'Test Client 1')->first();

        $this->browse(function (Browser $browser) use ($user, $client) {
            $browser->loginAs($user)
                ->visit("/clients/{$client->id}")
                ->assertSee('Últimas Compras')
                ->assertSee('Histórico de Movimentações');
        });
    }

    /**
     * Test navigation back from create form
     */
    public function testNavigationBackFromCreateForm(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/clients/create')
                ->assertSee('Voltar')
                ->click('a[href="/clients"]')
                ->waitForRoute('clients.index')
                ->assertSee('Clientes');
        });
    }

    /**
     * Test that unauthenticated users are redirected
     */
    public function testUnauthenticatedUserRedirection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/clients')
                ->assertUrlIs('/login');
        });
    }

    /**
     * Create test user
     */
    private function createTestUser(): void
    {
        User::create([
            'name' => 'Test Attendant',
            'email' => 'test-attendant@mail.com',
            'password' => bcrypt('password'),
            'type' => 'attendant',
        ]);
    }

    /**
     * Create test clients
     */
    private function createTestClients(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Client::create([
                'name' => "Test Client {$i}",
                'email' => "client{$i}@test.com",
                'phone' => "(11) 9999{$i}999-999{$i}",
                'cpf_cnpj' => "1234567890{$i}{$i}",
            ]);
        }
    }
}

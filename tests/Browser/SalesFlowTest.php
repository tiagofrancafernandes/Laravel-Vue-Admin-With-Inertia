<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SalesFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->seedPaymentMethods();
        $this->createTestClient();
        $this->createTestUser();
    }

    /**
     * Test complete sales creation flow
     */
    public function testCompleteSalesCreationFlow(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();
        $client = Client::first();

        $this->browse(function (Browser $browser) use ($user, $client) {
            // Login
            $browser->visit('/login')
                ->assertSee('Log in')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->waitForRoute('dashboard')
                ->assertAuthenticated();

            // Navigate to sales creation
            $browser->visit('/sales')
                ->assertSee('Vendas')
                ->assertSee('Nova Venda')
                ->click('a[href="/sales/create"]')
                ->waitForRoute('sales.create')
                ->assertSee('Nova Venda');

            // Fill form - Select client by searching
            $browser->assertSee('Cliente')
                ->type('input[placeholder*="Pesquisar cliente"]', 'Test')
                ->pause(500)
                ->click('div.hover\\:bg-blue-100:first-child')
                ->pause(300);

            // Verify client was selected
            $browser->assertSeeIn('input[placeholder*="Pesquisar cliente"]', 'Test');

            // Add first item
            $browser->assertSee('Itens')
                ->type('input[placeholder*="Descrição"]', 'Produto A')
                ->press('Tab')
                ->type('input[type="number"][step="0.01"]', '2')
                ->press('Tab')
                ->type('input[type="number"][min="0.01"]', '50.00')
                ->pause(300);

            // Verify first item subtotal displays
            $browser->assertSee('R$');

            // Add second item
            $browser->press('Tab')
                ->pause(100)
                ->press('Tab')
                ->pause(300);

            // Find and click "Adicionar Item" button
            $this->findAndClickByText($browser, 'Adicionar Item');
            $browser->pause(300);

            // Add second item details
            $browser->type('input[placeholder*="Descrição"]:last', 'Produto B')
                ->press('Tab')
                ->type('input[type="number"][step="0.01"]:last', '3')
                ->press('Tab')
                ->type('input[type="number"][min="0.01"]:last', '30.00')
                ->pause(300);

            // Apply discount - scroll down first
            $browser->scrollIntoView('input[type="number"][placeholder="0.00"]')
                ->pause(300);

            // Type discount (should be the only number input with that placeholder in Valores section)
            $browser->script("
                const inputs = document.querySelectorAll('input[type=\"number\"][placeholder=\"0.00\"]');
                inputs.forEach(input => {
                    if (input.closest('.space-y-3')) {
                        input.value = '10.00';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            ");
            $browser->pause(300);

            // Verify payment section is visible
            $browser->scrollIntoView('button:contains("Registrar Venda")')
                ->assertSee('Formas de Pagamento')
                ->pause(300);

            // Select payment method checkbox
            $browser->check('input[type="checkbox"]:first')
                ->pause(300);

            // Enter payment amount via JavaScript
            $browser->script("
                const checkboxes = document.querySelectorAll('input[type=\"checkbox\"]');
                const firstChecked = Array.from(checkboxes).find(cb => cb.checked);
                if (firstChecked) {
                    const parent = firstChecked.closest('div');
                    const amountInput = parent.querySelector('input[type=\"number\"]');
                    if (amountInput) {
                        amountInput.value = '180.00';
                        amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            ");
            $browser->pause(300);

            // Verify payment validation passes
            $browser->assertSee('Pagamentos conferem');

            // Submit form
            $this->findAndClickByText($browser, 'Registrar Venda');
            $browser->waitForRoute('sales.show')
                ->pause(500);
        });
    }

    /**
     * Helper to find and click button by text
     */
    private function findAndClickByText(Browser $browser, string $text): void
    {
        $browser->script("
            const buttons = document.querySelectorAll('button');
            const button = Array.from(buttons).find(b => b.textContent.includes('{$text}'));
            if (button) button.click();
        ");
    }

    /**
     * Test sales creation with payment validation error
     */
    public function testSalesCreationPaymentValidationError(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/sales/create')
                ->assertSee('Nova Venda');

            // Select client
            $browser->type('input[placeholder*="Pesquisar cliente"]', 'Test')
                ->pause(500)
                ->click('div.hover\\:bg-blue-100:first-child')
                ->pause(300);

            // Add item
            $browser->type('input[placeholder*="Descrição"]', 'Produto Test')
                ->press('Tab')
                ->type('input[type="number"][step="0.01"]', '1')
                ->press('Tab')
                ->type('input[type="number"][min="0.01"]', '100.00')
                ->pause(300);

            // Scroll to payment section
            $browser->scrollIntoView('input[type="checkbox"]:first')
                ->pause(300);

            // Select payment method but with wrong amount
            $browser->check('input[type="checkbox"]:first')
                ->pause(300);

            // Enter wrong payment amount via JavaScript
            $browser->script("
                const checkboxes = document.querySelectorAll('input[type=\"checkbox\"]');
                const firstChecked = Array.from(checkboxes).find(cb => cb.checked);
                if (firstChecked) {
                    const parent = firstChecked.closest('div');
                    const amountInput = parent.querySelector('input[type=\"number\"]');
                    if (amountInput) {
                        amountInput.value = '50.00';
                        amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            ");
            $browser->pause(300);

            // Verify error message
            $browser->assertSee('não corresponde ao total');

            // Verify submit button is disabled
            $browser->assertDisabled('button:contains("Registrar Venda")');
        });
    }

    /**
     * Test sales listing page loads correctly
     */
    public function testSalesListingPageLoads(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/sales')
                ->assertSee('Vendas')
                ->assertSee('Filtrar Vendas')
                ->assertSee('Nova Venda');
        });
    }

    /**
     * Test sales search functionality
     */
    public function testSalesSearchFunctionality(): void
    {
        $user = User::where('email', 'test-attendant@mail.com')->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/sales')
                ->assertSee('Filtrar')
                ->type('input[placeholder*="Pesquisar"]', 'test')
                ->press('Filtrar')
                ->pause(500);
            // Verify search was executed (page contains search field)
            $browser->assertPathIs('/sales');
        });
    }

    /**
     * Test that unauthenticated users are redirected
     */
    public function testUnauthenticatedUserRedirection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/sales')
                ->assertUrlIs('/login');
        });
    }

    /**
     * Seed payment methods for tests
     */
    private function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Dinheiro', 'code' => 'cash', 'description' => 'Pagamento em dinheiro'],
            ['name' => 'PIX', 'code' => 'pix', 'description' => 'Pagamento via PIX'],
            ['name' => 'Cartão Débito', 'code' => 'debit_card', 'description' => 'Cartão de débito'],
            ['name' => 'Cartão Crédito', 'code' => 'credit_card', 'description' => 'Cartão de crédito'],
            ['name' => 'Saldo', 'code' => 'balance', 'description' => 'Saldo pré-pago'],
            ['name' => 'Caderneta', 'code' => 'account', 'description' => 'Compra fiada'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create([
                ...$method,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create a test client
     */
    private function createTestClient(): void
    {
        Client::create([
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '(11) 99999-9999',
            'cpf_cnpj' => '12345678901',
        ]);
    }

    /**
     * Create a test user
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
}

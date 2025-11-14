# Guia de Implementação Passo a Passo

Este guia detalha como implementar o sistema completo do zero, seguindo as melhores práticas do Laravel 11 + Inertia.js + Vue 3.

---

## Fase 1: Configuração Inicial

### 1.1. Criar Projeto Laravel com Breeze

```bash
# Criar novo projeto Laravel 11
composer create-project laravel/laravel sales-system
cd sales-system

# Instalar Laravel Breeze com Inertia + Vue
composer require laravel/breeze --dev
php artisan breeze:install vue

# Instalar dependências
npm install
npm run build

# Configurar banco de dados no .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_system
DB_USERNAME=root
DB_PASSWORD=

# Rodar migrations do Breeze
php artisan migrate

# Testar servidor
php artisan serve
npm run dev
```

### 1.2. Instalar Dependências Extras

```bash
# Heroicons para ícones
npm install @heroicons/vue

# V-mask para máscaras de input (opcional)
npm install v-mask

# Axios já vem instalado com Laravel
```

---

## Fase 2: Banco de Dados

### 2.1. Criar Migrations

```bash
# Migration para adicionar campo type na tabela users
php artisan make:migration add_type_to_users_table

# Migrations das tabelas principais
php artisan make:migration create_clients_table
php artisan make:migration create_client_balances_table
php artisan make:migration create_payment_methods_table
php artisan make:migration create_sales_table
php artisan make:migration create_sale_payments_table
php artisan make:migration create_client_ledger_table
```

**Implementar conforme especificado em [DATABASE.md](./DATABASE.md)**

### 2.2. Criar Models

```bash
php artisan make:model Client
php artisan make:model ClientBalance
php artisan make:model PaymentMethod
php artisan make:model Sale
php artisan make:model SalePayment
php artisan make:model ClientLedger
```

**Exemplo: app/Models/Sale.php**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'user_id',
        'client_id',
        'total_amount',
        'status',
        'items',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'items' => 'array',
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(ClientLedger::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('code', 'like', '%'.$search.'%')
                      ->orWhereHas('client', function ($query) use ($search) {
                          $query->where('name', 'like', '%'.$search.'%');
                      });
            });
        });

        $query->when($filters['date_from'] ?? null, function ($query, $date) {
            $query->whereDate('created_at', '>=', $date);
        });

        $query->when($filters['date_to'] ?? null, function ($query, $date) {
            $query->whereDate('created_at', '<=', $date);
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });
    }
}
```

### 2.3. Criar Seeders

```bash
php artisan make:seeder PaymentMethodSeeder
php artisan make:seeder DefaultClientSeeder
php artisan make:seeder SuperAdminSeeder
```

**database/seeders/PaymentMethodSeeder.php:**

```php
<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Dinheiro',
                'code' => 'cash',
                'is_active' => true,
                'requires_client_balance' => false,
                'is_credit' => false,
                'display_order' => 1,
            ],
            [
                'name' => 'PIX',
                'code' => 'pix',
                'is_active' => true,
                'requires_client_balance' => false,
                'is_credit' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Cartão de Crédito',
                'code' => 'credit_card',
                'is_active' => true,
                'requires_client_balance' => false,
                'is_credit' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Cartão de Débito',
                'code' => 'debit_card',
                'is_active' => true,
                'requires_client_balance' => false,
                'is_credit' => false,
                'display_order' => 4,
            ],
            [
                'name' => 'Saldo',
                'code' => 'balance',
                'is_active' => true,
                'requires_client_balance' => true,
                'is_credit' => false,
                'display_order' => 5,
            ],
            [
                'name' => 'Caderneta',
                'code' => 'tab',
                'is_active' => true,
                'requires_client_balance' => false,
                'is_credit' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
```

**database/seeders/DefaultClientSeeder.php:**

```php
<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientBalance;
use Illuminate\Database\Seeder;

class DefaultClientSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['is_anonymous' => true],
            [
                'name' => 'Anônimo',
                'is_anonymous' => true,
            ]
        );

        // Criar saldo inicial
        if (!$client->balance) {
            ClientBalance::create([
                'client_id' => $client->id,
                'balance_amount' => 0,
                'tab_amount' => 0,
            ]);
        }
    }
}
```

**database/seeders/DatabaseSeeder.php:**

```php
public function run(): void
{
    $this->call([
        PaymentMethodSeeder::class,
        DefaultClientSeeder::class,
        // SuperAdminSeeder::class, // Descomentar para dev
    ]);
}
```

### 2.4. Executar Migrations e Seeds

```bash
php artisan migrate:fresh --seed
```

---

## Fase 3: Backend - Services

### 3.1. Criar Service Classes

```bash
mkdir app/Services
touch app/Services/SaleService.php
touch app/Services/PaymentService.php
touch app/Services/BalanceService.php
```

**app/Services/SaleService.php:**

```php
<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BalanceService $balanceService
    ) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // 1. Garantir cliente (usar anônimo se não informado)
            $clientId = $data['client_id'] ?? $this->getAnonymousClientId();

            // 2. Criar venda
            $sale = Sale::create([
                'code' => $this->generateCode(),
                'user_id' => auth()->id(),
                'client_id' => $clientId,
                'total_amount' => $data['total_amount'],
                'items' => $data['items'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            // 3. Processar pagamentos
            foreach ($data['payments'] as $payment) {
                $this->paymentService->processPayment($sale, $payment);
            }

            return $sale->load(['client', 'payments.paymentMethod']);
        });
    }

    public function cancelSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            // 1. Reverter todos os pagamentos
            foreach ($sale->payments as $payment) {
                $this->paymentService->reversePayment($sale, $payment);
            }

            // 2. Atualizar status
            $sale->update(['status' => 'cancelled']);

            // 3. Soft delete
            $sale->delete();
        });
    }

    protected function generateCode(): string
    {
        $year = date('Y');
        $lastSale = Sale::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $number = $lastSale ? (intval(substr($lastSale->code, -4)) + 1) : 1;

        return sprintf('VENDA-%s%04d', $year, $number);
    }

    protected function getAnonymousClientId(): int
    {
        return Client::where('is_anonymous', true)->value('id');
    }
}
```

**app/Services/PaymentService.php:**

```php
<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\PaymentMethod;

class PaymentService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    public function processPayment(Sale $sale, array $paymentData): SalePayment
    {
        $method = PaymentMethod::findOrFail($paymentData['payment_method_id']);

        // Criar registro de pagamento
        $salePayment = SalePayment::create([
            'sale_id' => $sale->id,
            'payment_method_id' => $method->id,
            'amount' => $paymentData['amount'],
            'metadata' => $paymentData['metadata'] ?? [],
        ]);

        // Processar movimentações específicas
        match ($method->code) {
            'balance' => $this->balanceService->debitBalance(
                $sale->client_id,
                $paymentData['amount'],
                "Pagamento venda #{$sale->code}",
                $sale->id
            ),
            'tab' => $this->balanceService->addTabDebit(
                $sale->client_id,
                $paymentData['amount'],
                "Venda #{$sale->code}",
                $sale->id
            ),
            'cash' => $this->processCashPayment($sale, $paymentData),
            default => null,
        };

        return $salePayment;
    }

    protected function processCashPayment(Sale $sale, array $paymentData): void
    {
        // Verificar se tem troco como saldo
        if (isset($paymentData['metadata']['change_as_balance'])) {
            $this->balanceService->addBalance(
                $sale->client_id,
                $paymentData['metadata']['change_as_balance'],
                "Troco venda #{$sale->code}"
            );
        }
    }

    public function reversePayment(Sale $sale, SalePayment $payment): void
    {
        $method = $payment->paymentMethod;

        match ($method->code) {
            'balance' => $this->balanceService->addBalance(
                $sale->client_id,
                $payment->amount,
                "Estorno venda #{$sale->code} cancelada",
                $sale->id
            ),
            'tab' => $this->balanceService->payTab(
                $sale->client_id,
                $payment->amount,
                "Estorno venda #{$sale->code} cancelada",
                $sale->id
            ),
            default => null,
        };
    }
}
```

**app/Services/BalanceService.php:**

```php
<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\ClientLedger;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function addBalance(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)->lockForUpdate()->first();
            $balance->increment('balance_amount', $amount);

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $balance->fresh()->balance_amount,
                'tab_after' => $balance->fresh()->tab_amount,
                'description' => $description,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function debitBalance(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)->lockForUpdate()->first();

            if ($balance->balance_amount < $amount) {
                throw new \Exception('Saldo insuficiente.');
            }

            $balance->decrement('balance_amount', $amount);

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $balance->fresh()->balance_amount,
                'tab_after' => $balance->fresh()->tab_amount,
                'description' => $description,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function addTabDebit(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)->lockForUpdate()->first();
            $balance->increment('tab_amount', $amount);

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'type' => 'tab_debit',
                'amount' => $amount,
                'balance_after' => $balance->fresh()->balance_amount,
                'tab_after' => $balance->fresh()->tab_amount,
                'description' => $description,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function payTab(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)->lockForUpdate()->first();

            if ($balance->tab_amount < $amount) {
                throw new \Exception('Valor maior que a dívida atual.');
            }

            $balance->decrement('tab_amount', $amount);

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'type' => 'tab_credit',
                'amount' => $amount,
                'balance_after' => $balance->fresh()->balance_amount,
                'tab_after' => $balance->fresh()->tab_amount,
                'description' => $description,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
```

---

## Fase 4: Backend - Controllers e Rotas

### 4.1. Criar Controllers

```bash
php artisan make:controller SaleController
php artisan make:controller ClientController
php artisan make:controller DashboardController
php artisan make:controller UserController
```

**Implementar conforme especificado em [ROUTES.md](./ROUTES.md)**

### 4.2. Criar Form Requests

```bash
php artisan make:request StoreSaleRequest
php artisan make:request StoreClientRequest
```

**app/Http/Requests/StoreSaleRequest.php:**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.metadata' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.subtotal' => 'required_with:items|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar soma dos pagamentos
            $totalPayments = collect($this->payments)->sum('amount');
            if (abs($totalPayments - $this->total_amount) > 0.01) {
                $validator->errors()->add(
                    'payments',
                    'A soma dos pagamentos deve ser igual ao total da venda.'
                );
            }

            // Validar soma dos itens se informados
            if ($this->items && count($this->items) > 0) {
                $totalItems = collect($this->items)->sum('subtotal');
                if (abs($totalItems - $this->total_amount) > 0.01) {
                    $validator->errors()->add(
                        'items',
                        'A soma dos itens deve ser igual ao total da venda.'
                    );
                }
            }
        });
    }
}
```

### 4.3. Criar Policies

**Seguir [PERMISSIONS.md](./PERMISSIONS.md)**

### 4.4. Definir Rotas

**routes/web.php - Implementar conforme [ROUTES.md](./ROUTES.md)**

---

## Fase 5: Frontend - Componentes Base

### 5.1. Criar Estrutura de Pastas

```bash
mkdir -p resources/js/Components/Sales
mkdir -p resources/js/Components/Clients
mkdir -p resources/js/Components/Common
mkdir -p resources/js/Composables
mkdir -p resources/js/Utils
```

### 5.2. Criar Helpers

**resources/js/Utils/helpers.js:**

```javascript
export function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

export function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

export function formatDateTime(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short'
    }).format(new Date(date));
}
```

### 5.3. Criar Composables

**Implementar conforme [COMPONENTS.md](./COMPONENTS.md)**

### 5.4. Criar Componentes Comuns

```bash
touch resources/js/Components/Common/Button.vue
touch resources/js/Components/Common/Input.vue
touch resources/js/Components/Common/Modal.vue
touch resources/js/Components/Common/Select.vue
```

**Exemplo: resources/js/Components/Common/Button.vue**

```vue
<template>
    <button
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
        v-bind="$attrs"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-3 h-5 w-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button'
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'ghost'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    disabled: Boolean,
    loading: Boolean,
});

const classes = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

    const variants = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 disabled:bg-blue-300',
        secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500 disabled:bg-gray-100',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
        ghost: 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-base',
        lg: 'px-6 py-3 text-lg',
    };

    return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>
```

---

## Fase 6: Frontend - Pages e Componentes Específicos

### 6.1. Criar Pages

```bash
mkdir -p resources/js/Pages/Sales
mkdir -p resources/js/Pages/Clients
mkdir -p resources/js/Pages/Users

touch resources/js/Pages/Dashboard.vue
touch resources/js/Pages/Sales/Index.vue
touch resources/js/Pages/Sales/Create.vue
touch resources/js/Pages/Sales/Show.vue
touch resources/js/Pages/Clients/Index.vue
touch resources/js/Pages/Clients/Create.vue
touch resources/js/Pages/Clients/Show.vue
```

**Implementar conforme [COMPONENTS.md](./COMPONENTS.md)**

### 6.2. Criar Componentes de Vendas

```bash
touch resources/js/Components/Sales/SaleForm.vue
touch resources/js/Components/Sales/PaymentSplit.vue
touch resources/js/Components/Sales/ItemsTable.vue
```

**Implementar conforme [COMPONENTS.md](./COMPONENTS.md)**

### 6.3. Criar Componentes de Clientes

```bash
touch resources/js/Components/Clients/ClientSelect.vue
touch resources/js/Components/Clients/ClientModal.vue
touch resources/js/Components/Clients/ClientBalance.vue
```

**Implementar conforme [COMPONENTS.md](./COMPONENTS.md)**

---

## Fase 7: Testes

### 7.1. Configurar Ambiente de Testes

```bash
# Criar banco de teste
DB_CONNECTION=mysql
DB_DATABASE=sales_system_test

# Configurar phpunit.xml (já vem configurado)
```

### 7.2. Criar Factories

```bash
php artisan make:factory ClientFactory
php artisan make:factory SaleFactory
php artisan make:factory SalePaymentFactory
```

### 7.3. Escrever Testes

```bash
php artisan make:test SaleTest
php artisan make:test ClientTest
php artisan make:test SalePolicyTest
```

**Exemplo: tests/Feature/SaleTest.php**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed payment methods and anonymous client
    }

    public function test_attendant_can_create_sale()
    {
        $attendant = User::factory()->create(['type' => 'attendant']);
        $client = Client::factory()->create();
        $paymentMethod = PaymentMethod::where('code', 'cash')->first();

        $this->actingAs($attendant);

        $response = $this->post(route('sales.store'), [
            'client_id' => $client->id,
            'total_amount' => 100.00,
            'payments' => [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => 100.00,
                ]
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sales', [
            'client_id' => $client->id,
            'total_amount' => 100.00,
        ]);
    }

    public function test_payments_must_equal_total()
    {
        $attendant = User::factory()->create(['type' => 'attendant']);
        $client = Client::factory()->create();
        $paymentMethod = PaymentMethod::where('code', 'cash')->first();

        $this->actingAs($attendant);

        $response = $this->postJson(route('sales.store'), [
            'client_id' => $client->id,
            'total_amount' => 100.00,
            'payments' => [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => 50.00, // Errado!
                ]
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payments');
    }
}
```

### 7.4. Executar Testes

```bash
php artisan test
```

---

## Fase 8: Deploy

### 8.1. Preparar para Produção

```bash
# Otimizar autoload
composer install --optimize-autoloader --no-dev

# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Build do frontend
npm run build
```

### 8.2. Configurar Servidor

**Requisitos:**
- PHP 8.2+
- MySQL 8.0+ ou PostgreSQL 14+
- Nginx ou Apache
- Node.js 18+ (para build)
- Composer

**Nginx config:**

```nginx
server {
    listen 80;
    server_name sales-system.com;
    root /var/www/sales-system/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 8.3. Configurar .env de Produção

```env
APP_NAME="Sales System"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://sales-system.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_system
DB_USERNAME=sales_user
DB_PASSWORD=strong_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 8.4. Executar Migrations em Produção

```bash
php artisan migrate --force
php artisan db:seed --force --class=PaymentMethodSeeder
php artisan db:seed --force --class=DefaultClientSeeder
```

---

## Checklist de Implementação

### Backend
- [ ] Migrations criadas e executadas
- [ ] Models com relacionamentos
- [ ] Seeders configurados
- [ ] Services implementados
- [ ] Controllers criados
- [ ] Form Requests validados
- [ ] Policies configuradas
- [ ] Middleware registrado
- [ ] Rotas definidas

### Frontend
- [ ] Componentes base criados
- [ ] Composables implementados
- [ ] Pages criadas
- [ ] Componentes específicos
- [ ] Validações client-side
- [ ] Responsividade testada
- [ ] Acessibilidade verificada

### Testes
- [ ] Factories criadas
- [ ] Testes unitários
- [ ] Testes de feature
- [ ] Testes de políticas
- [ ] Cobertura > 80%

### Deploy
- [ ] Otimizações aplicadas
- [ ] Servidor configurado
- [ ] SSL configurado
- [ ] Backups automáticos
- [ ] Monitoramento ativo

---

## Recursos Adicionais

- [Documentação Laravel 11](https://laravel.com/docs/11.x)
- [Documentação Inertia.js](https://inertiajs.com/)
- [Documentação Vue 3](https://vuejs.org/)
- [TailwindCSS](https://tailwindcss.com/)

## Suporte

Para dúvidas ou problemas, consulte:
1. Os documentos de arquitetura neste repositório
2. Issues no GitHub do projeto
3. Comunidade Laravel Brasil

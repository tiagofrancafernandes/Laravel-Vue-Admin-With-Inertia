# Sistema de Permissões e Autorização

## Tipos de Usuários

### 1. Super Admin
**Descrição:** Acesso completo ao sistema.

**Permissões:**
- ✅ Gerenciar usuários (criar, editar, excluir)
- ✅ Gerenciar métodos de pagamento
- ✅ Ver, criar e cancelar vendas
- ✅ Gerenciar clientes (CRUD completo)
- ✅ Adicionar saldo e receber pagamento de caderneta
- ✅ Acessar todas as vendas e relatórios

**Identificação:**
```php
$user->type === 'super_admin'
```

### 2. Atendente (Attendant)
**Descrição:** Usuário operacional que realiza vendas.

**Permissões:**
- ✅ Ver e criar vendas
- ✅ Gerenciar clientes (CRUD completo)
- ✅ Adicionar saldo e receber pagamento de caderneta
- ✅ Acessar dashboard com estatísticas
- ❌ Gerenciar usuários
- ❌ Cancelar vendas
- ❌ Gerenciar métodos de pagamento

**Identificação:**
```php
$user->type === 'attendant'
```

### 3. Cliente (Client)
**Descrição:** Cliente que acessa o sistema para ver suas compras e dívidas.

**Permissões:**
- ✅ Ver próprio perfil
- ✅ Ver próprias vendas
- ✅ Ver próprio saldo e caderneta
- ✅ Ver histórico de movimentações (ledger)
- ❌ Criar vendas
- ❌ Ver outros clientes
- ❌ Acessar área administrativa

**Identificação:**
```php
$user->type === 'client'
```

---

## Implementação

### 1. Model User

**app/Models/User.php:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relacionamentos
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    // Helpers de Autorização
    public function isSuperAdmin(): bool
    {
        return $this->type === 'super_admin';
    }

    public function isAttendant(): bool
    {
        return $this->type === 'attendant';
    }

    public function isClient(): bool
    {
        return $this->type === 'client';
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageSales(): bool
    {
        return $this->isSuperAdmin() || $this->isAttendant();
    }

    public function canManageClients(): bool
    {
        return $this->isSuperAdmin() || $this->isAttendant();
    }

    public function canCancelSales(): bool
    {
        return $this->isSuperAdmin();
    }
}
```

### 2. Middleware

#### 2.1. EnsureSuperAdmin

**app/Http/Middleware/EnsureSuperAdmin.php:**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Acesso negado. Apenas Super Admins podem acessar esta área.');
        }

        return $next($request);
    }
}
```

**Registrar middleware em `bootstrap/app.php`:**

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    // ...
```

#### 2.2. EnsureAttendantOrAbove

**app/Http/Middleware/EnsureAttendantOrAbove.php:**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttendantOrAbove
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->isSuperAdmin() && !$user->isAttendant())) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
```

**Registrar:**

```php
$middleware->alias([
    'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
    'attendant' => \App\Http\Middleware\EnsureAttendantOrAbove::class,
]);
```

### 3. Policies

Policies fornecem autorização granular por recurso.

#### 3.1. SalePolicy

**app/Policies/SalePolicy.php:**

```php
<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Visualizar qualquer venda
     */
    public function viewAny(User $user): bool
    {
        // Super admin e atendente veem todas
        // Cliente vê apenas as suas
        return $user->isSuperAdmin()
            || $user->isAttendant()
            || $user->isClient();
    }

    /**
     * Visualizar uma venda específica
     */
    public function view(User $user, Sale $sale): bool
    {
        // Super admin e atendente veem todas
        if ($user->isSuperAdmin() || $user->isAttendant()) {
            return true;
        }

        // Cliente vê apenas se for dele
        if ($user->isClient() && $user->client) {
            return $sale->client_id === $user->client->id;
        }

        return false;
    }

    /**
     * Criar venda
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    /**
     * Cancelar venda
     */
    public function cancel(User $user, Sale $sale): bool
    {
        // Apenas super admin pode cancelar
        if (!$user->isSuperAdmin()) {
            return false;
        }

        // Não pode cancelar venda já cancelada
        if ($sale->status === 'cancelled') {
            return false;
        }

        return true;
    }

    /**
     * Excluir venda (soft delete)
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin();
    }
}
```

**Registrar em `app/Providers/AppServiceProvider.php`:**

```php
use App\Models\Sale;
use App\Policies\SalePolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Sale::class, SalePolicy::class);
}
```

#### 3.2. ClientPolicy

**app/Policies/ClientPolicy.php:**

```php
<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function view(User $user, Client $client): bool
    {
        // Super admin e atendente veem todos
        if ($user->isSuperAdmin() || $user->isAttendant()) {
            return true;
        }

        // Cliente vê apenas o próprio perfil
        if ($user->isClient() && $user->client) {
            return $client->id === $user->client->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function delete(User $user, Client $client): bool
    {
        // Não permitir deletar cliente anônimo
        if ($client->is_anonymous) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    /**
     * Adicionar saldo ao cliente
     */
    public function addBalance(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    /**
     * Receber pagamento de caderneta
     */
    public function payTab(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }
}
```

#### 3.3. UserPolicy

**app/Policies/UserPolicy.php:**

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Super admin não pode editar a si mesmo (segurança)
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Não pode deletar a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isSuperAdmin();
    }
}
```

### 4. Uso nas Rotas

**routes/web.php:**

```php
// Rotas que requerem autenticação
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (todos autenticados)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Vendas (com policies)
    Route::get('/sales', [SaleController::class, 'index'])
        ->can('viewAny', Sale::class)
        ->name('sales.index');

    Route::get('/sales/create', [SaleController::class, 'create'])
        ->can('create', Sale::class)
        ->name('sales.create');

    Route::post('/sales', [SaleController::class, 'store'])
        ->can('create', Sale::class)
        ->name('sales.store');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->can('view', 'sale')
        ->name('sales.show');

    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->can('cancel', 'sale')
        ->name('sales.cancel');

    // Clientes
    Route::resource('clients', ClientController::class)
        ->middleware('attendant'); // Atendente ou acima

    Route::post('/clients/{client}/add-balance', [ClientController::class, 'addBalance'])
        ->can('addBalance', 'client')
        ->name('clients.add-balance');

    // Super Admin apenas
    Route::middleware('super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('payment-methods', PaymentMethodController::class);
    });
});
```

### 5. Uso nos Controllers

**app/Http/Controllers/SaleController.php:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $query = Sale::with(['client', 'user', 'payments.paymentMethod']);

        // Cliente vê apenas suas vendas
        if ($request->user()->isClient() && $request->user()->client) {
            $query->where('client_id', $request->user()->client->id);
        }

        $sales = $query->latest()->paginate(15);

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
        ]);
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        return Inertia::render('Sales/Show', [
            'sale' => $sale->load([
                'client',
                'user',
                'payments.paymentMethod',
            ]),
        ]);
    }

    public function cancel(Sale $sale)
    {
        $this->authorize('cancel', $sale);

        // Lógica de cancelamento...

        return redirect()->back()->with('success', 'Venda cancelada com sucesso.');
    }
}
```

### 6. Uso no Frontend (Vue)

**Compartilhar permissões com frontend via Inertia:**

**app/Http/Middleware/HandleInertiaRequests.php:**

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'type' => $request->user()->type,
                'can' => [
                    'manage_users' => $request->user()->canManageUsers(),
                    'manage_sales' => $request->user()->canManageSales(),
                    'manage_clients' => $request->user()->canManageClients(),
                    'cancel_sales' => $request->user()->canCancelSales(),
                ],
            ] : null,
        ],
    ];
}
```

**Usar no Vue:**

```vue
<template>
    <div>
        <!-- Botão visível apenas para super admin -->
        <Button
            v-if="$page.props.auth.user.can.cancel_sales"
            @click="cancelSale"
        >
            Cancelar Venda
        </Button>

        <!-- Link visível apenas para quem pode gerenciar usuários -->
        <Link
            v-if="$page.props.auth.user.can.manage_users"
            :href="route('users.index')"
        >
            Gerenciar Usuários
        </Link>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>
```

**Composable para permissões:**

```javascript
// resources/js/Composables/useAuth.js
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);

    const can = (permission) => {
        return user.value?.can?.[permission] || false;
    };

    const isSuperAdmin = computed(() => user.value?.type === 'super_admin');
    const isAttendant = computed(() => user.value?.type === 'attendant');
    const isClient = computed(() => user.value?.type === 'client');

    return {
        user,
        can,
        isSuperAdmin,
        isAttendant,
        isClient,
    };
}
```

**Usar no componente:**

```vue
<script setup>
import { useAuth } from '@/Composables/useAuth';

const { user, can, isSuperAdmin } = useAuth();
</script>

<template>
    <div>
        <h1>Olá, {{ user.name }}!</h1>

        <Button v-if="can('cancel_sales')">
            Cancelar Venda
        </Button>

        <div v-if="isSuperAdmin">
            Área exclusiva de administrador
        </div>
    </div>
</template>
```

---

## Matriz de Permissões

| Recurso | Super Admin | Atendente | Cliente |
|---------|-------------|-----------|---------|
| **Vendas** |
| Ver todas | ✅ | ✅ | ❌ (apenas suas) |
| Ver uma | ✅ | ✅ | ✅ (se for sua) |
| Criar | ✅ | ✅ | ❌ |
| Cancelar | ✅ | ❌ | ❌ |
| **Clientes** |
| Listar | ✅ | ✅ | ❌ |
| Ver | ✅ | ✅ | ✅ (apenas próprio) |
| Criar | ✅ | ✅ | ❌ |
| Editar | ✅ | ✅ | ❌ |
| Excluir | ✅ | ❌ | ❌ |
| Adicionar Saldo | ✅ | ✅ | ❌ |
| Pagar Caderneta | ✅ | ✅ | ❌ |
| **Usuários** |
| Listar | ✅ | ❌ | ❌ |
| Criar | ✅ | ❌ | ❌ |
| Editar | ✅ | ❌ | ❌ |
| Excluir | ✅ | ❌ | ❌ |
| **Métodos Pagamento** |
| Gerenciar | ✅ | ❌ | ❌ |
| **Dashboard** |
| Acessar | ✅ | ✅ | ✅ (limitado) |

---

## Testes de Autorização

**tests/Feature/SalePolicyTest.php:**

```php
<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_cancel_sale()
    {
        $superAdmin = User::factory()->create(['type' => 'super_admin']);
        $sale = Sale::factory()->create();

        $this->actingAs($superAdmin);

        $response = $this->post(route('sales.cancel', $sale));

        $response->assertSuccessful();
    }

    public function test_attendant_cannot_cancel_sale()
    {
        $attendant = User::factory()->create(['type' => 'attendant']);
        $sale = Sale::factory()->create();

        $this->actingAs($attendant);

        $response = $this->post(route('sales.cancel', $sale));

        $response->assertForbidden();
    }

    public function test_client_can_only_view_own_sales()
    {
        $client = User::factory()->create(['type' => 'client']);
        $clientModel = Client::factory()->create();
        $client->client()->associate($clientModel);

        $ownSale = Sale::factory()->create(['client_id' => $clientModel->id]);
        $otherSale = Sale::factory()->create();

        $this->actingAs($client);

        $response = $this->get(route('sales.show', $ownSale));
        $response->assertSuccessful();

        $response = $this->get(route('sales.show', $otherSale));
        $response->assertForbidden();
    }
}
```

---

## Segurança

### Pontos de Atenção

1. **Validar sempre no backend:** Frontend é apenas UX, backend é segurança
2. **Usar policies:** Não confiar apenas em middleware
3. **Não expor dados sensíveis:** Cliente não deve ver outras vendas
4. **Logs de auditoria:** Registrar ações sensíveis (cancelamentos, exclusões)
5. **Proteção contra CSRF:** Laravel protege automaticamente
6. **Rate limiting:** Limitar tentativas de login e ações críticas

### Rate Limiting

**routes/web.php:**

```php
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // 60 requisições por minuto
    Route::post('/sales', [SaleController::class, 'store']);
});
```

### Logs de Auditoria

**Registrar ações sensíveis:**

```php
use Illuminate\Support\Facades\Log;

public function cancel(Sale $sale)
{
    $this->authorize('cancel', $sale);

    DB::transaction(function () use ($sale) {
        // Cancelar venda...

        // Log
        Log::info('Sale cancelled', [
            'sale_id' => $sale->id,
            'sale_code' => $sale->code,
            'cancelled_by' => auth()->id(),
            'cancelled_by_name' => auth()->user()->name,
        ]);
    });
}
```

---

## Resumo

1. **3 tipos de usuário:** Super Admin, Atendente, Cliente
2. **Middleware:** Para rotas que requerem tipo específico
3. **Policies:** Para autorização granular por recurso
4. **Frontend:** Compartilhar permissões via Inertia
5. **Testes:** Cobrir todos os cenários de autorização
6. **Segurança:** Sempre validar no backend, logs de auditoria

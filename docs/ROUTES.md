# Estrutura de Rotas

## Convenções

- **API JSON:** Rotas com prefixo `/api` retornam JSON puro
- **Inertia:** Rotas sem prefixo usam Inertia (SSR)
- **Autenticação:** Todas as rotas (exceto login/register) requerem autenticação
- **Autorização:** Policies aplicadas automaticamente
- **Nomenclatura:** RESTful quando possível

## Middleware

```php
// web.php
Route::middleware(['auth', 'verified'])->group(function () {
    // Rotas autenticadas
});

Route::middleware(['auth', 'super_admin'])->group(function () {
    // Rotas exclusivas do super admin
});
```

## Grupos de Rotas

### 1. Dashboard

```php
// Página inicial após login
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
```

**Retorna (Inertia):**
```php
return Inertia::render('Dashboard', [
    'stats' => [
        'today_sales' => $todaySales,
        'today_revenue' => $todayRevenue,
        'pending_tabs' => $pendingTabs,
        'active_clients' => $activeClients,
    ],
    'recent_sales' => $recentSales,
]);
```

---

### 2. Vendas (Sales)

#### 2.1. Listar Vendas (Inertia)

```php
Route::get('/sales', [SaleController::class, 'index'])
    ->name('sales.index');
```

**Query Parameters:**
- `page` (int): Página atual
- `per_page` (int): Itens por página (padrão: 15)
- `search` (string): Busca por código, cliente
- `date_from` (date): Data inicial
- `date_to` (date): Data final
- `status` (string): completed, cancelled, pending

**Retorna (Inertia):**
```php
return Inertia::render('Sales/Index', [
    'sales' => Sale::with(['client', 'user', 'payments.paymentMethod'])
        ->filter($request->only(['search', 'date_from', 'date_to', 'status']))
        ->latest()
        ->paginate($request->per_page ?? 15),
    'filters' => $request->only(['search', 'date_from', 'date_to', 'status']),
]);
```

**Acesso:**
- Super Admin: todas as vendas
- Atendente: todas as vendas
- Cliente: apenas suas próprias vendas

#### 2.2. Criar Venda (Form - Inertia)

```php
Route::get('/sales/create', [SaleController::class, 'create'])
    ->name('sales.create');
```

**Retorna (Inertia):**
```php
return Inertia::render('Sales/Create', [
    'payment_methods' => PaymentMethod::where('is_active', true)
        ->orderBy('display_order')
        ->get(),
    'anonymous_client_id' => Client::where('is_anonymous', true)->value('id'),
]);
```

**Acesso:**
- Super Admin: ✅
- Atendente: ✅
- Cliente: ❌

#### 2.3. Armazenar Venda (Store)

```php
Route::post('/sales', [SaleController::class, 'store'])
    ->name('sales.store');
```

**Request Body:**
```json
{
    "client_id": 5,
    "total_amount": 150.50,
    "payments": [
        {
            "payment_method_id": 1,
            "amount": 100.00,
            "metadata": {}
        },
        {
            "payment_method_id": 2,
            "amount": 50.50,
            "metadata": {}
        }
    ],
    "items": [
        {
            "name": "Produto A",
            "quantity": 2,
            "unit_price": 50.00,
            "subtotal": 100.00
        },
        {
            "name": "Produto B",
            "quantity": 1,
            "unit_price": 50.50,
            "subtotal": 50.50
        }
    ],
    "notes": "Observação opcional"
}
```

**Validações:**
```php
$request->validate([
    'client_id' => 'nullable|exists:clients,id',
    'total_amount' => 'required|numeric|min:0',
    'payments' => 'required|array|min:1',
    'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
    'payments.*.amount' => 'required|numeric|min:0',
    'payments.*.metadata' => 'nullable|array',
    'items' => 'nullable|array',
    'items.*.name' => 'required|string|max:255',
    'items.*.quantity' => 'required|numeric|min:0.01',
    'items.*.unit_price' => 'required|numeric|min:0',
    'items.*.subtotal' => 'required|numeric|min:0',
    'notes' => 'nullable|string|max:1000',
]);

// Validação customizada: soma dos pagamentos = total
Rule::custom(function ($attribute, $value, $fail) use ($request) {
    $total = collect($request->payments)->sum('amount');
    if ($total != $request->total_amount) {
        $fail('A soma dos pagamentos deve ser igual ao total da venda.');
    }
});
```

**Response (Sucesso - 201):**
```json
{
    "message": "Venda criada com sucesso!",
    "sale": {
        "id": 123,
        "code": "VENDA-20250123",
        "total_amount": 150.50,
        "client": {
            "id": 5,
            "name": "João Silva"
        },
        "created_at": "2025-01-15T10:30:00"
    }
}
```

**Lógica (SaleService):**
```php
DB::transaction(function () use ($data) {
    // 1. Criar venda
    $sale = Sale::create([
        'code' => $this->generateCode(),
        'user_id' => auth()->id(),
        'client_id' => $data['client_id'] ?? $this->getAnonymousClientId(),
        'total_amount' => $data['total_amount'],
        'items' => $data['items'] ?? null,
        'notes' => $data['notes'] ?? null,
        'status' => 'completed',
    ]);

    // 2. Processar pagamentos
    foreach ($data['payments'] as $payment) {
        $this->processPayment($sale, $payment);
    }

    return $sale;
});
```

#### 2.4. Visualizar Venda (Show)

```php
Route::get('/sales/{sale}', [SaleController::class, 'show'])
    ->name('sales.show');
```

**Retorna (Inertia):**
```php
return Inertia::render('Sales/Show', [
    'sale' => $sale->load([
        'client',
        'user',
        'payments.paymentMethod',
        'ledgerEntries',
    ]),
]);
```

**Acesso:**
- Super Admin: todas as vendas
- Atendente: todas as vendas
- Cliente: apenas se `sale.client_id == auth()->user()->client_id`

#### 2.5. Cancelar Venda

```php
Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
    ->name('sales.cancel');
```

**Request Body:** (vazio)

**Response (Sucesso - 200):**
```json
{
    "message": "Venda cancelada com sucesso!",
    "sale": {
        "id": 123,
        "code": "VENDA-20250123",
        "status": "cancelled"
    }
}
```

**Acesso:**
- Super Admin: ✅
- Atendente: ❌ (apenas super admin pode cancelar)
- Cliente: ❌

---

### 3. Clientes (Clients)

#### 3.1. Listar Clientes (Inertia)

```php
Route::get('/clients', [ClientController::class, 'index'])
    ->name('clients.index');
```

**Query Parameters:**
- `page`, `per_page`, `search`

**Retorna (Inertia):**
```php
return Inertia::render('Clients/Index', [
    'clients' => Client::with('balance')
        ->where('is_anonymous', false)
        ->filter($request->only('search'))
        ->latest()
        ->paginate(15),
]);
```

**Acesso:**
- Super Admin: ✅
- Atendente: ✅
- Cliente: ❌

#### 3.2. Listar Clientes (JSON - para Select)

```php
Route::get('/api/clients/select', [ClientController::class, 'selectList'])
    ->name('api.clients.select');
```

**Query Parameters:**
- `search` (string): Busca por nome, email, telefone
- `limit` (int): Máximo de resultados (padrão: 20)

**Response (JSON):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "João Silva",
            "email": "joao@example.com",
            "phone": "(11) 98765-4321",
            "balance": {
                "balance_amount": 100.50,
                "tab_amount": 25.00
            }
        },
        {
            "id": 2,
            "name": "Maria Santos",
            "email": "maria@example.com",
            "phone": "(11) 98765-1234",
            "balance": {
                "balance_amount": 0.00,
                "tab_amount": 0.00
            }
        }
    ]
}
```

**Acesso:** Super Admin, Atendente

#### 3.3. Buscar Saldo do Cliente (JSON)

```php
Route::get('/api/clients/{client}/balance', [ClientController::class, 'balance'])
    ->name('api.clients.balance');
```

**Response (JSON):**
```json
{
    "client_id": 5,
    "balance_amount": 150.75,
    "tab_amount": 30.00,
    "updated_at": "2025-01-15T10:30:00"
}
```

**Acesso:**
- Super Admin: ✅
- Atendente: ✅
- Cliente: apenas o próprio

#### 3.4. Criar Cliente

```php
Route::get('/clients/create', [ClientController::class, 'create'])
    ->name('clients.create');

Route::post('/clients', [ClientController::class, 'store'])
    ->name('clients.store');
```

**Request Body (POST):**
```json
{
    "name": "Pedro Costa",
    "email": "pedro@example.com",
    "phone": "(11) 98765-0000",
    "document": "123.456.789-00",
    "notes": "Cliente VIP"
}
```

**Validações:**
```php
$request->validate([
    'name' => 'required|string|min:3|max:255',
    'email' => 'nullable|email|unique:clients,email',
    'phone' => 'nullable|string|max:20',
    'document' => 'nullable|string|max:20|unique:clients,document',
    'notes' => 'nullable|string|max:1000',
]);
```

**Response (Sucesso - 201):**
```json
{
    "message": "Cliente criado com sucesso!",
    "client": {
        "id": 10,
        "name": "Pedro Costa",
        "email": "pedro@example.com",
        "balance": {
            "balance_amount": 0.00,
            "tab_amount": 0.00
        }
    }
}
```

**Lógica:**
```php
DB::transaction(function () use ($data) {
    $client = Client::create($data);

    // Criar registro de saldo inicial
    ClientBalance::create([
        'client_id' => $client->id,
        'balance_amount' => 0,
        'tab_amount' => 0,
    ]);

    return $client;
});
```

**Acesso:** Super Admin, Atendente

#### 3.5. Visualizar Cliente

```php
Route::get('/clients/{client}', [ClientController::class, 'show'])
    ->name('clients.show');
```

**Retorna (Inertia):**
```php
return Inertia::render('Clients/Show', [
    'client' => $client->load('balance'),
    'recent_sales' => $client->sales()->latest()->take(10)->get(),
    'ledger' => $client->ledger()->latest()->paginate(15),
]);
```

**Acesso:**
- Super Admin: ✅
- Atendente: ✅
- Cliente: apenas o próprio perfil

#### 3.6. Adicionar Saldo

```php
Route::post('/clients/{client}/add-balance', [ClientController::class, 'addBalance'])
    ->name('clients.add-balance');
```

**Request Body:**
```json
{
    "amount": 100.00,
    "payment_method": "pix",
    "description": "Recarga de saldo"
}
```

**Response (Sucesso):**
```json
{
    "message": "Saldo adicionado com sucesso!",
    "balance": {
        "balance_amount": 250.00,
        "tab_amount": 0.00
    }
}
```

**Lógica:**
```php
DB::transaction(function () use ($client, $data) {
    // 1. Atualizar saldo
    $client->balance->increment('balance_amount', $data['amount']);

    // 2. Registrar no ledger
    ClientLedger::create([
        'client_id' => $client->id,
        'type' => 'credit',
        'amount' => $data['amount'],
        'balance_after' => $client->balance->balance_amount,
        'tab_after' => $client->balance->tab_amount,
        'description' => $data['description'] ?? 'Adição de saldo',
        'created_by' => auth()->id(),
    ]);
});
```

**Acesso:** Super Admin, Atendente

#### 3.7. Pagar Caderneta

```php
Route::post('/clients/{client}/pay-tab', [ClientController::class, 'payTab'])
    ->name('clients.pay-tab');
```

**Request Body:**
```json
{
    "amount": 50.00,
    "payment_method": "cash",
    "description": "Pagamento de dívida"
}
```

**Validações:**
```php
$request->validate([
    'amount' => [
        'required',
        'numeric',
        'min:0.01',
        Rule::max($client->balance->tab_amount), // Não pode pagar mais que deve
    ],
    'payment_method' => 'required|string',
    'description' => 'nullable|string|max:500',
]);
```

**Response (Sucesso):**
```json
{
    "message": "Pagamento registrado com sucesso!",
    "balance": {
        "balance_amount": 0.00,
        "tab_amount": 50.00
    }
}
```

**Acesso:** Super Admin, Atendente

---

### 4. Usuários (Users)

**Apenas Super Admin**

#### 4.1. Listar Usuários

```php
Route::get('/users', [UserController::class, 'index'])
    ->middleware('super_admin')
    ->name('users.index');
```

#### 4.2. Criar Usuário

```php
Route::get('/users/create', [UserController::class, 'create'])
    ->middleware('super_admin')
    ->name('users.create');

Route::post('/users', [UserController::class, 'store'])
    ->middleware('super_admin')
    ->name('users.store');
```

**Request Body:**
```json
{
    "name": "Novo Atendente",
    "email": "atendente@example.com",
    "password": "senha123",
    "password_confirmation": "senha123",
    "type": "attendant"
}
```

**Validações:**
```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:8|confirmed',
    'type' => 'required|in:super_admin,attendant,client',
]);
```

#### 4.3. Editar/Excluir Usuário

```php
Route::get('/users/{user}/edit', [UserController::class, 'edit'])
    ->middleware('super_admin')
    ->name('users.edit');

Route::put('/users/{user}', [UserController::class, 'update'])
    ->middleware('super_admin')
    ->name('users.update');

Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->middleware('super_admin')
    ->name('users.destroy');
```

---

### 5. Métodos de Pagamento (Payment Methods)

**Apenas Super Admin**

```php
Route::middleware('super_admin')->group(function () {
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
        ->name('payment-methods.index');

    Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])
        ->name('payment-methods.create');

    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])
        ->name('payment-methods.store');

    Route::get('/payment-methods/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])
        ->name('payment-methods.edit');

    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])
        ->name('payment-methods.update');

    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])
        ->name('payment-methods.destroy');
});
```

---

## Resumo de Rotas por Tipo de Usuário

### Super Admin

```
✅ Dashboard
✅ Vendas (CRUD completo + cancelar)
✅ Clientes (CRUD completo + saldo + caderneta)
✅ Usuários (CRUD completo)
✅ Métodos de Pagamento (CRUD completo)
```

### Atendente

```
✅ Dashboard
✅ Vendas (listar, criar, visualizar)
✅ Clientes (CRUD completo + saldo + caderneta)
❌ Usuários
❌ Métodos de Pagamento
```

### Cliente

```
✅ Dashboard (resumo pessoal)
✅ Vendas (apenas suas)
✅ Perfil (visualizar saldo e caderneta)
❌ Criar vendas
❌ Gerenciar outros clientes
❌ Usuários
```

## Arquivo de Rotas Completo

**routes/web.php:**

```php
<?php

use App\Http\Controllers\{
    DashboardController,
    SaleController,
    ClientController,
    UserController,
    PaymentMethodController,
};
use Illuminate\Support\Facades\Route;

// Rotas públicas (Breeze)
require __DIR__.'/auth.php';

// Rotas autenticadas
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Vendas
    Route::resource('sales', SaleController::class)
        ->except(['edit', 'update', 'destroy']);
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('can:cancel,sale')
        ->name('sales.cancel');

    // Clientes
    Route::resource('clients', ClientController::class)
        ->except(['destroy']);
    Route::post('clients/{client}/add-balance', [ClientController::class, 'addBalance'])
        ->name('clients.add-balance');
    Route::post('clients/{client}/pay-tab', [ClientController::class, 'payTab'])
        ->name('clients.pay-tab');

    // Super Admin
    Route::middleware('super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('payment-methods', PaymentMethodController::class);
    });
});

// API JSON
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('clients/select', [ClientController::class, 'selectList'])
        ->name('api.clients.select');
    Route::get('clients/{client}/balance', [ClientController::class, 'balance'])
        ->name('api.clients.balance');
});
```

## Tratamento de Erros

**Handler.php:**

```php
public function render($request, Throwable $exception)
{
    // Erros de autorização
    if ($exception instanceof AuthorizationException) {
        return $request->wantsJson()
            ? response()->json(['message' => 'Não autorizado.'], 403)
            : redirect()->back()->with('error', 'Você não tem permissão para esta ação.');
    }

    // Erros de validação (automático pelo Laravel)

    // Erros de negócio (customizados)
    if ($exception instanceof BusinessException) {
        return $request->wantsJson()
            ? response()->json(['message' => $exception->getMessage()], 422)
            : redirect()->back()->withErrors(['error' => $exception->getMessage()]);
    }

    return parent::render($request, $exception);
}
```

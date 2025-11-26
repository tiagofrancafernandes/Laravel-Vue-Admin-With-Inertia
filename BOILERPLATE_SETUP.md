# Laravel + Vue 3 + Inertia Admin Boilerplate

## Visão Geral

Este é um boilerplate pronto para uso que fornece uma base sólida para construir painéis administrativos e aplicações web com:

- **Backend**: Laravel 11 com autenticação via Sanctum
- **Frontend**: Vue 3 com Composition API
- **SSR/Routing**: Inertia.js para renderização server-side
- **Styling**: Tailwind CSS v3 com suporte a dark mode
- **ORM**: Eloquent com migrations
- **Autorização**: Sistema de Policies do Laravel
- **Testes**: Teste suite com exemplos de padrões

## Início Rápido

```bash
# 1. Instalar dependências
composer install && npm install

# 2. Configurar arquivo .env
cp .env.example .env
php artisan key:generate

# 3. Rodar migrations
php artisan migrate

# 4. Iniciar servidor de desenvolvimento
composer run dev
```

## Estrutura de Pastas

```
Laravel-Vue-Admin-Boilerplate/
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Controllers (organizados por recurso)
│   │   ├── Middleware/            # Middleware customizado
│   │   ├── Requests/              # Form Requests (validação)
│   │   └── Kernel.php
│   ├── Models/                    # Modelos Eloquent
│   ├── Policies/                  # Autorização (Policies)
│   └── Providers/                 # Service Providers
│
├── resources/
│   ├── js/
│   │   ├── Components/            # Componentes Vue reutilizáveis
│   │   │   ├── Common/            # Componentes genéricos (Modal, Button, etc)
│   │   │   ├── Forms/             # Componentes de formulário
│   │   │   └── UI/                # Componentes de interface
│   │   ├── Layouts/               # Layouts (Guest, Authenticated)
│   │   ├── Pages/                 # Páginas Inertia (uma por rota)
│   │   │   ├── Auth/              # Páginas de autenticação
│   │   │   ├── Profile/           # Páginas de perfil de usuário
│   │   │   └── Resources/         # CRUDs de recursos (ex: Users, Products)
│   │   ├── Composables/           # Vue 3 Composables (lógica reutilizável)
│   │   ├── Utils/                 # Funções utilitárias
│   │   └── app.js                 # Ponto de entrada Vue
│   └── css/
│       └── app.css                # Estilos (Tailwind)
│
├── database/
│   ├── migrations/                # Migrações de banco de dados
│   ├── seeders/                   # Seeds para dados iniciais
│   └── factories/                 # Model factories para testes
│
├── routes/
│   ├── web.php                    # Rotas web (Inertia)
│   ├── api.php                    # Rotas API JSON (opcional)
│   └── auth.php                   # Rotas de autenticação (incluído em web.php)
│
├── tests/
│   ├── Feature/                   # Testes de funcionalidades
│   │   └── Pages/                 # Testes de acesso a páginas
│   ├── Unit/                      # Testes unitários
│   └── TestCase.php               # Classe base para testes
│
├── CLAUDE.md                      # Instruções específicas do projeto
└── BOILERPLATE_SETUP.md           # Este arquivo
```

## Guia: Como Implementar um Novo Recurso (ex: Produtos)

### Passo 1: Criar Modelo e Migração

```bash
php artisan make:model Product -m
```

Edite a migração em `database/migrations/`:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->timestamps();
    $table->softDeletes();
});
```

Edite o modelo em `app/Models/Product.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'price'];

    // Relações
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Passo 2: Rodar Migração

```bash
php artisan migrate
```

### Passo 3: Criar Factory (para testes)

```bash
php artisan make:factory ProductFactory
```

Em `database/factories/ProductFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
```

### Passo 4: Criar Controller

```bash
php artisan make:controller ProductController
```

Em `app/Http/Controllers/ProductController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
    {
        $products = Product::paginate(15);

        return Inertia::render('Resources/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return Inertia::render('Resources/Products/Create');
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('message', 'Product created successfully');
    }

    public function show(Product $product)
    {
        return Inertia::render('Resources/Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        return Inertia::render('Resources/Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('message', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Product deleted successfully');
    }
}
```

### Passo 5: Criar Form Requests

```bash
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
```

Em `app/Http/Requests/StoreProductRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
        ];
    }
}
```

### Passo 6: Criar Policy (Autorização)

```bash
php artisan make:policy ProductPolicy --model=Product
```

Em `app/Policies/ProductPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos podem ver a lista
    }

    public function view(User $user, Product $product): bool
    {
        return true; // Todos podem ver
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isSuperAdmin();
    }
}
```

### Passo 7: Criar Rotas

Em `routes/web.php`, adicione:

```php
Route::middleware('auth')->group(function () {
    Route::resource('products', ProductController::class);
});
```

### Passo 8: Criar Páginas Vue

Crie a estrutura:
```
resources/js/Pages/Resources/Products/
├── Index.vue          # Listagem
├── Create.vue         # Formulário de criação
├── Edit.vue           # Formulário de edição
└── Show.vue           # Visualização detalhada
```

**Index.vue** (Listagem com paginação):

```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    products: Object,
});

const products_list = computed(() => props.products.data);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Products
                </h2>
                <Link :href="route('products.create')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Create Product
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Price</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products_list" :key="product.id" class="border-t dark:border-gray-700">
                                <td class="px-6 py-4">{{ product.name }}</td>
                                <td class="px-6 py-4">{{ product.price }}</td>
                                <td class="px-6 py-4 space-x-2">
                                    <Link :href="route('products.show', product)" class="text-blue-600 hover:text-blue-900">View</Link>
                                    <Link :href="route('products.edit', product)" class="text-green-600 hover:text-green-900">Edit</Link>
                                    <Link :href="route('products.destroy', product)" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginação -->
                    <div class="px-6 py-4 flex justify-between items-center border-t dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
                        </div>
                        <div class="space-x-2">
                            <Link v-if="products.prev_page_url" :href="products.prev_page_url" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">← Previous</Link>
                            <Link v-if="products.next_page_url" :href="products.next_page_url" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next →</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

### Passo 9: Criar Testes

```bash
php artisan make:test Feature/Pages/ProductsPagesTest
```

Em `tests/Feature/Pages/ProductsPagesTest.php`:

```php
<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanAccessProductsIndexPage(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertStatus(200);
    }

    public function testAdminCanCreateProduct(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'New Product',
            'price' => 99.99,
        ]);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
        $response->assertRedirect(route('products.index'));
    }

    public function testAttendantCannotCreateProduct(): void
    {
        $user = User::factory()->attendant()->create();

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'New Product',
            'price' => 99.99,
        ]);

        $response->assertForbidden();
    }
}
```

## Sistema de Páginas com `pageType`

Este boilerplate suporta um sistema flexível para definir como uma página deve ser exibida usando o campo `pageType`:

### Como Funciona

Cada recurso pode ter suas páginas exibidas de três formas diferentes:

1. **`pageType: 'modal'`** - A página é exibida como modal dentro da rota `index`
2. **`pageType: 'page'`** - A página é exibida em sua própria rota (comportamento padrão)
3. **`pageType: false`** - A página não é exibida

### Exemplo de Uso

No seu Controller, passe o `pageType` ao renderizar:

```php
public function create()
{
    return Inertia::render('Resources/Products/Create', [
        'pageType' => 'modal', // Exibe como modal
    ]);
}

public function show(Product $product)
{
    return Inertia::render('Resources/Products/Show', [
        'product' => $product,
        'pageType' => 'page', // Exibe como página
    ]);
}
```

Na sua página Vue, use o `pageType`:

```vue
<template>
    <div v-if="pageType === 'modal'" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <!-- Conteúdo da modal -->
    </div>
    <div v-else>
        <!-- Conteúdo da página normal -->
    </div>
</template>

<script setup>
defineProps({
    pageType: String,
});
</script>
```

## Comandos Úteis

```bash
# Desenvolvimento
composer run dev              # Inicia servidor com Vite, queue listener, etc

# Testes
composer run test             # Roda todos os testes
php artisan test tests/Feature/Pages/ProductsPagesTest.php
php artisan test --filter=testAdminCanCreateProduct

# Formatação
./vendor/bin/pint             # Formata código PHP (PSR-12)
npx prettier --write resources/js  # Formata código Vue/JS

# Migrations
php artisan migrate            # Roda migrations
php artisan migrate:rollback   # Desfaz última migration
php artisan make:migration create_products_table  # Cria nova migration
```

## Padrões de Código

### Controllers
- Use `authorizeResource()` no construtor
- Retorne Inertia Pages ao invés de views
- Use Form Requests para validação
- Redirecione com `with('message', 'Texto')`

### Models
- Use `SoftDeletes` para dados sensíveis
- Defina `$fillable` para mass assignment
- Use relacionamentos tipados (com tipos de retorno)
- Use Factories para testes

### Vue Components
- Use Composition API com `<script setup>`
- Use object syntax para classes condicionais
- Componentes genéricos em `Components/`
- Páginas em `Pages/Resources/`

### Testes
- Heredo de `Tests\TestCase`
- Use `RefreshDatabase` para isolamento
- Teste acesso (autenticação) antes de funcionalidade
- Teste autorização (policies) para operações sensíveis

## Segurança

- Todas as rotas requerem autenticação via `auth` middleware
- Use Policies para autorização de modelos
- Validação via Form Requests
- CSRF protection automaticamente ativada
- SQL injection prevenida pelo Eloquent

## Próximas Etapas

1. **Duplicar a estrutura do CRUD de Usuários** para seus próprios recursos
2. **Implementar relacionamentos** entre suas entidades
3. **Adicionar testes** para cada nova funcionalidade
4. **Customizar layouts** conforme necessário
5. **Expandir composables** para lógica reutilizável

## Troubleshooting

**Erro ao rodar `composer run dev`:**
```bash
composer install
npm install
php artisan migrate
```

**Migrations já rodadas:**
```bash
php artisan migrate:refresh --seed
```

**Limpar cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Suporte

Para dúvidas sobre Laravel, Vue 3, Inertia.js ou Tailwind CSS, consulte:
- [Laravel Docs](https://laravel.com/docs)
- [Vue 3 Docs](https://vuejs.org/)
- [Inertia.js Docs](https://inertiajs.com/)
- [Tailwind CSS Docs](https://tailwindcss.com/)

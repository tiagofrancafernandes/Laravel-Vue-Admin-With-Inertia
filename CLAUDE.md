# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 + Vue 3 + Inertia.js web application for managing sales transactions, client records, and payment processing with support for prepaid balances and ledger-based credit systems. The project uses TailwindCSS v3 for styling and follows PSR-12 PHP standards with Vue 3 Composition API best practices.

## Development Commands

### Setup
```bash
composer install && npm install
php artisan migrate
```

### Development Server
```bash
composer run dev
```
This command runs: Laravel dev server + queue listener + log pail + Vite dev server (concurrently)

### Building for Production
```bash
npm run build
```

### Code Formatting & Linting
```bash
# PHP formatting with Pint (PSR-12)
./vendor/bin/pint

# JavaScript formatting with Prettier
npx prettier --write resources/js
```

### Testing
```bash
# Run all tests
composer run test

# Run specific test file
php artisan test tests/Feature/SaleControllerTest.php

# Run specific test method
php artisan test tests/Feature/SaleControllerTest.php --filter=testStoreCreatesNewSale
```

## High-Level Architecture

### Backend Stack
- **Framework**: Laravel 12 with Sanctum authentication
- **ORM**: Eloquent with relationships
- **Validation**: Form Requests pattern
- **Authorization**: Gate/Policy system
- **Middleware**: Custom middleware for role-based access

### Frontend Stack
- **Framework**: Vue 3 Composition API
- **Routing/SSR**: Inertia.js (server-side routing)
- **Styling**: TailwindCSS v3 with dark mode support
- **Build tool**: Vite with Laravel Vite Plugin
- **Icons**: Heroicons Vue

### Database
SQLite for development, supports MySQL/PostgreSQL in production. Uses soft deletes for non-destructive operations.

### Key Packages
- `spatie/laravel-activitylog`: Activity auditing
- `spatie/laravel-permission`: Role and permission management
- `predis/predis`: Redis client for caching
- `tightenco/ziggy`: Frontend route generation
- `@inertiajs/vue3`: Vue 3 adapter for Inertia
- `@tailwindcss/forms`: Form styling plugin

## Data Flow & Architecture Patterns

### Request Lifecycle
```
HTTP Request → Middleware → Controller → Form Request Validation →
Service Layer (Business Logic) → Models (Data Access) →
Database → Response (JSON/Inertia)
```

### Key Architectural Principles
1. **Separation of Concerns**: Controllers orchestrate, Services contain business logic, Models handle data
2. **Transactional Integrity**: All financial operations use database transactions
3. **Authorization**: Policies gate access at the controller level
4. **Soft Deletes**: Sales and clients use `softDeletes()` for audit trails
5. **Activity Logging**: Significant actions logged via `spatie/laravel-activitylog`

### Core Models & Relationships
- **User**: Can be `attendant` (staff) or `super_admin` (owner)
- **Client**: Customer with optional prepaid balance and credit ledger
- **Sale**: Transaction with multiple payments and payment methods
- **ClientBalance**: Tracks prepaid amount per client
- **ClientLedger**: Audit trail of balance changes
- **PaymentMethod**: Transaction payment type (cash, credit card, prepaid, etc.)
- **SalePayment**: Breakdown of how a sale was paid

### Service Layer
Located in `app/Services/`, these handle complex business logic:
- `SaleService`: Sale creation, cancellation, payment split calculations
- `PaymentService`: Payment validation and processing
- `BalanceService`: Prepaid balance management and ledger updates

### Form Requests
Located in `app/Http/Requests/`, validate input before controllers process:
- `StoreSaleRequest`: Sale data validation (items, client, payments)
- `StoreClientRequest`: Client creation validation
- Custom field validation using Laravel's built-in rules

## Frontend Component Architecture

### Page Components (in `resources/js/Pages/`)
- **Dashboard**: Analytics and overview
- **Sales/**: Index (list), Create (form), Show (detail)
- **Clients/**: Index (list), Create (form), Show (detail with ledger)
- **Users/**: Admin management

### Reusable Components (in `resources/js/Components/`)
- **Common**: Modal, DataTable, Select, FormInput
- **Clients**: ClientSelect (dropdown), ClientBalance (display), ClientModal (inline creation)
- **Sales**: SaleForm (multi-step), PaymentSplit (calculator), ItemsTable (items list)
- **Layouts**: AuthenticatedLayout, GuestLayout

### Composables (in `resources/js/Composables/`)
- `useDarkMode()`: Light/dark theme management
- `usePaymentSplit()`: Payment allocation logic
- `useClientBalance()`: Balance fetching and updates
- `useFormValidation()`: Common form validation patterns
- `useClientSelection()`: Client search and selection

## Styling & Tailwind Configuration

### Configuration
- Uses Tailwind v3 with PSR-12 preset
- Dark mode enabled via `class` strategy (apply `dark` class to root)
- Custom color extensions (extended grays)
- Forms plugin enabled for styled form elements
- Print width: 120 characters (Prettier)

### Class Binding Best Practices
Use object syntax for conditional classes:
```vue
:class="{'bg-blue-600 text-white': isActive, 'bg-gray-200 text-gray-800': !isActive}"
```

For complex multi-condition states:
```vue
:class="[
    'base-classes',
    {
        'active-state': status === 'active',
        'inactive-light': status !== 'active' && !isDark,
        'inactive-dark': status !== 'active' && isDark,
    }
]"
```

## Authorization & Security

### User Types
- **Super Admin**: Full system access, user management
- **Attendant**: Can create sales, manage clients, view dashboard

### Authorization Checks
- Controllers use `$this->authorize('action', Model)` or Gate checks
- Policies located in `app/Policies/` define permissions
- Routes protected by `auth` and `verified` middleware
- Sensitive actions protected by role-specific middleware

### Security Practices
- All input validated via Form Requests before database operations
- Eloquent ORM prevents SQL injection
- CSRF protection via Laravel middleware
- Prepared statements for queries
- Soft deletes prevent data loss

## File Structure Quick Reference

```
app/
├── Http/Controllers/        # Controller classes
├── Http/Requests/           # Form request validation
├── Http/Middleware/         # Custom middleware
├── Models/                  # Eloquent models
├── Policies/                # Authorization policies
├── Services/                # Business logic services
└── Providers/               # Service providers

resources/
├── js/
│   ├── Components/          # Reusable Vue components
│   ├── Composables/         # Vue 3 composables
│   ├── Layouts/             # Page layouts
│   ├── Pages/               # Inertia page components
│   ├── Utils/               # Utility functions
│   └── app.js               # Vue app entry point
└── css/
    └── app.css              # Tailwind CSS entry

database/
├── migrations/              # Schema changes
├── seeders/                 # Seed data
└── factories/               # Model factories for testing

tests/
├── Feature/                 # Integration/controller tests
├── Unit/                    # Unit tests
└── Browser/                 # Laravel Dusk browser tests
```

## Common Development Tasks

### Adding a New Feature
1. Create migration for new database schema
2. Create Model with relationships
3. Create Service to handle business logic
4. Create Form Request for validation
5. Create Controller to orchestrate
6. Create Inertia Pages and Components
7. Write tests in `tests/Feature/`

### Creating New API Endpoints
- Use `Route::middleware('auth')->group()` in `routes/web.php`
- Return JSON responses from controllers
- Use same validation and authorization patterns

### Testing
- Feature tests inherit from `Tests\TestCase`
- Use database transactions for test isolation
- Mock external dependencies
- Test authorization via `actingAs(User)`

### Database Changes
1. Create migration: `php artisan make:migration migration_name`
2. Define schema in migration file
3. Run: `php artisan migrate`
4. Update Model relationships if needed

## Development Preferences

### PHP Code Style
- PSR-12 enforced via Pint
- 4-space indentation
- Arrow functions preferred (`fn =>`)
- Type hints required on all methods
- Nullable types for optional values

### JavaScript/Vue Style
- 4-space indentation (Prettier)
- Single quotes for strings
- Semicolons required
- Composition API for components
- TypeScript not currently enforced but recommended for new code

### Git Workflow
- Branch from `claude/sales-app-architecture-planning-*`
- **Make commits at each significant stage or milestone completed** - don't batch multiple features into one commit
- Write clear, descriptive commit messages that explain the _why_ behind changes
- **Omit any reference to Claude Code in commit messages** - commits should read as if written directly by the user
  - ❌ Bad: "Claude Code implemented user authentication"
  - ✅ Good: "Implement user authentication with Sanctum"
- Run tests before committing
- Use atomic commits (one feature/fix per commit)

## Important Notes

### Financial Operations
All money-related operations must use database transactions to ensure consistency:
```php
DB::transaction(function () {
    // Debit balance, create ledger entry, update sale
});
```

### N+1 Query Prevention
Always eager load relationships in controllers:
```php
$sales = Sale::with(['client', 'payments', 'payments.method'])->paginate();
```

### Dark Mode
The application includes dark mode support. Check `useDarkMode()` composable for implementation details.

### Activity Logging
Significant actions are logged automatically. Check `spatie/laravel-activitylog` package for querying activity history.

### Testing Access to Application
quando precisar testar acesso à aplicação principalmente no tocante ao navegador, crie um teste para tal funcionalidade e coloque ações como dd, dump, vardump etc para que possa coletar saídas. Use o arquivo @tests/Feature/LocalOnly/CodeDemoTest.php como modelo de teste. Para esse teste por exemplo, se quiser testar o teste por completo execute : `artisan test --stop-on-error --filter=CodeDemoTest` se quiser testar apenas um método: `artisan test --stop-on-error --filter=testCodeDemoTestAccessToDashboard`

Use o arquivo @tests/Feature/LocalOnly/CodeDemoTest.php como modelo para criar outros arquivos de teste

## Fluxo de Ações para Próximas Atividades

### 1. Melhorar Fluxo de Cadastro de Cliente na Tela de Vendas

**Objetivo**: Quando um novo cliente for criado diretamente da tela de vendas (Sales/Create), a aplicação não deve redirecionar para a página do cliente, mas sim exibir uma modal de sucesso/confirmação e manter o usuário na tela de vendas.

**Implementação**:
- Modificar `ClientController.store()` para detectar se a requisição vem de um contexto de vendas
- Retornar resposta JSON com o novo cliente ao invés de redirecionamento
- No frontend (ClientModal.vue), após criação bem-sucedida, fechar a modal e atualizar a lista de clientes
- Exemplo: Após cadastrar cliente "João Silva", a modal fecha automaticamente e "João Silva" aparece selecionado no campo de cliente

**Arquivos Afetados**:
- `app/Http/Controllers/ClientController.php` - método `store()`
- `resources/js/Components/Clients/ClientModal.vue` - tratamento de resposta
- `resources/js/Pages/Sales/Create.vue` - integração com modal

### 2. Melhorar Seleção e Listagem de Clientes

**Objetivo**: Quando pesquisar/selecionar um cliente, listar no formato `{nome} - {telefone}` para melhor identificação.

**Implementação**:
- Modificar `api.clients.select` endpoint para retornar campo de telefone
- No `ClientSelect.vue`, formatear a exibição como `nome - telefone` tanto na lista de seleção quanto no campo selecionado
- Exemplo: "João Silva - (11) 9999-8888" ao invés de apenas "João Silva"

**Arquivos Afetados**:
- `app/Http/Controllers/ClientController.php` - método `select()` (API endpoint)
- `resources/js/Components/Clients/ClientSelect.vue` - formatação da exibição
- `app/Models/Client.php` - adicionar acessor se necessário

### 3. Carregamento de Saldo do Cliente (Lazy Loading)

**Objetivo**: Quando um cliente é selecionado, seu saldo deve ser carregado em background, mas só exibido na tela de pagamento da venda (não na tela inicial de seleção).

**Implementação**:
- Ao selecionar cliente em ClientSelect, fazer requisição à `api.clients.{id}.balance`
- Armazenar saldo em estado local/contexto
- Exibir saldo APENAS no componente de pagamento (SalePayment ou equivalente)
- Usar composable `useClientBalance()` para gerenciar este estado

**Fluxo**:
1. Usuário seleciona cliente "João Silva"
2. Background: requisição GET `/api/clients/{id}/balance` é feita
3. Saldo fica disponível mas invisível na tela de criação
4. Ao chegar na etapa de pagamento: saldo é exibido (ex: "Saldo disponível: R$ 150,00")
5. Usuário pode usar saldo como forma de pagamento

**Arquivos Afetados**:
- `resources/js/Components/Clients/ClientSelect.vue` - dispara carregamento de saldo
- `resources/js/Composables/useClientBalance.js` - gerencia estado do saldo
- `resources/js/Pages/Sales/Create.vue` - acessa saldo na etapa de pagamento
- `app/Http/Controllers/ClientController.php` - endpoint `balance()`

### 4. Portal de Acesso do Cliente (Cliente Portal/Dashboard)

**Objetivo**: Criar área restrita onde clientes podem acessar suas informações financeiras, visualizar saldo e débitos, e enviar comprovantes de pagamento para análise pelos administradores.

**Implementação**:

#### Backend:
- Criar migration: `CreateClientProofsTable` (tabela para armazenar comprovantes)
  - Campos: id, client_id, sale_id (nullable), type (deposit/payment), amount, file_path, status (pending/approved/rejected), notes, admin_id (quem revisou), created_at, updated_at

- Criar Model: `ClientProof`
  - Relações: belongsTo(Client), belongsTo(Admin/User), belongsTo(Sale)
  - Scopes: pending(), approved(), rejected()

- Criar Controller: `ClientPortalController`
  - `dashboard()` - exibe resumo financeiro do cliente
  - `statement()` - lista histórico de transações e saldos
  - `submitProof()` - processa upload de comprovante
  - `proofHistory()` - lista comprovantes enviados com status

- Criar Form Request: `SubmitClientProofRequest`
  - Valida: file (imagem/PDF, max 5MB), amount, type, description

- Adicionar rotas autenticadas para cliente:
  ```php
  Route::middleware(['auth', 'verified'])->group(function () {
      Route::get('/client-portal', [ClientPortalController::class, 'dashboard'])->name('client.dashboard');
      Route::get('/client-portal/statement', [ClientPortalController::class, 'statement'])->name('client.statement');
      Route::post('/client-portal/proof', [ClientPortalController::class, 'submitProof'])->name('client.proof.store');
      Route::get('/client-portal/proofs', [ClientPortalController::class, 'proofHistory'])->name('client.proofs');
  });
  ```

#### Frontend:
- Criar layout: `ClientPortalLayout.vue` (parecido com AuthenticatedLayout mas para clientes)

- Criar páginas em `resources/js/Pages/ClientPortal/`:
  - `Dashboard.vue` - resumo com cards de:
    - Saldo disponível (se houver)
    - Total devido
    - Últimas transações (3-5 últimas)
    - CTA para enviar comprovante

  - `Statement.vue` - tabela com histórico completo:
    - Data, descrição, valor, saldo anterior, saldo atual
    - Filtros por período, tipo (venda, pagamento, ajuste)
    - Paginação

  - `SubmitProof.vue` - formulário para upload:
    - Upload de arquivo (imagem/PDF)
    - Tipo de comprovante (Depósito/Pagamento)
    - Valor do comprovante
    - Descrição (opcional)
    - Referência de venda (opcional, autocomplete)
    - Preview de arquivo antes de enviar

  - `ProofHistory.vue` - lista de comprovantes enviados:
    - Cards com status (Pendente, Aprovado, Rejeitado)
    - Data de submissão
    - Valor e tipo
    - Notas do admin
    - Download do arquivo
    - Badge de status com cores diferentes

#### Autenticação/Autorização:
- Middleware: `ClientPortalAccess` - garante que cliente só acesse seus próprios dados
- Policy: `ClientProofPolicy` - controla ações em proofs (criar, visualizar)
- Cada cliente só vê seus próprios dados/proofs

**Fluxo de Uso**:
1. Cliente faz login com suas credenciais
2. Acessa `/client-portal`
3. Vê resumo com saldo e débitos atuais
4. Clica em "Enviar Comprovante"
5. Faz upload de comprovante (imagem/PDF)
6. Preenche valor e descrição
7. Sistema salva com status "Pendente"
8. Admin recebe notificação/vê na área de admin
9. Admin aprova/rejeita com nota
10. Cliente vê atualização no status e na tabela de proofs
11. Se aprovado, saldo é atualizado automaticamente

**Arquivos a Criar**:
- Database: `database/migrations/XXXX_create_client_proofs_table.php`
- Models: `app/Models/ClientProof.php`
- Controllers: `app/Http/Controllers/ClientPortalController.php`
- Requests: `app/Http/Requests/SubmitClientProofRequest.php`
- Policies: `app/Policies/ClientProofPolicy.php`
- Middleware: `app/Http/Middleware/ClientPortalAccess.php`
- Composables: `resources/js/Composables/useClientPortal.js`
- Layouts: `resources/js/Layouts/ClientPortalLayout.vue`
- Pages: `resources/js/Pages/ClientPortal/{Dashboard,Statement,SubmitProof,ProofHistory}.vue`

**Arquivos a Modificar**:
- `routes/web.php` - adicionar rotas do cliente
- `app/Http/Kernel.php` - registrar middleware
- `app/Models/Client.php` - adicionar relação com proofs
- `app/Models/User.php` - adicionar relação com proofs revisados
- `app/Providers/AuthServiceProvider.php` - registrar policies

**Considerações**:
- Verificar se cliente já tem email verificado (segurança)
- Armazenar arquivos em disco seguro (`storage/app/client-proofs/`)
- Gerar URL assinada/temporária para download de arquivos
- Auditar todas as ações (criação, aprovação de proofs)
- Notificar cliente quando proof for aprovado/rejeitado
- Email para admin quando novo proof for enviado (opcional)
- Validar que arquivo é realmente imagem/PDF (não executável)

### Ordem de Prioridade
1. **Alta**: Melhorar seleção de cliente (listagem com telefone) - melhora UX imediata
2. **Alta**: Fluxo de modal para novo cliente - evita redirect desnecessário
3. **Média**: Lazy loading de saldo - otimização de performance
4. **Alta**: Portal de acesso do cliente - novo fluxo de negócio importante

### Cronograma de Implementação Sugerido
```
Fase 1 (Etapa 2): Fluxo Modal
  → Modifica apenas ClientController e components existentes
  → Impacto baixo, valor alto

Fase 2 (Etapa 1): Listagem com Telefone
  → Melhorias no ClientSelect.vue e API
  → Impacto baixo, valor médio

Fase 3 (Etapa 3): Lazy Loading de Saldo
  → Usa composable existente
  → Impacto médio, valor médio

Fase 4 (Etapa 4): Portal do Cliente
  → Nova estrutura grande
  → Impacto alto, valor muito alto
  → Recomenda-se fazer por partes:
    - 4.1: Banco de dados + Models (migrations, models)
    - 4.2: Backend (controllers, requests, policies)
    - 4.3: Frontend Dashboard (visualização)
    - 4.4: Frontend Upload (formulário)
    - 4.5: Admin Dashboard para revisar proofs
```

### Notas de Implementação
- Manter compatibilidade com fluxo de vendas existente
- Garantir que saldo carregado seja sempre atualizado (evitar cache stale)
- Testar com clientes que não têm saldo/telefone vazio
- Adicionar testes em `tests/Feature/LocalOnly/CodeDemoTest.php` para validar comportamentos
- Para portal do cliente:
  - Validar tamanho máximo de arquivo (5MB)
  - Aceitar apenas imagens (JPG, PNG) e PDF
  - Limitar a 20 uploads por cliente por mês
  - Manter histórico completo de todas as actions
  - Implementar soft delete para proofs (audit trail)

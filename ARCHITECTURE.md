# Arquitetura do Sistema de Vendas

## Visão Geral

Sistema de registro de vendas para estabelecimento comercial com suporte a múltiplos métodos de pagamento, gestão de saldo pré-pago e caderneta (fiado).

**Stack Tecnológica:**
- Laravel 11
- Inertia.js
- Vue 3 (Composition API)
- TailwindCSS (via Breeze)
- MySQL/PostgreSQL

## Princípios Fundamentais

1. **Clareza:** Código explícito e fácil de entender
2. **Previsibilidade:** Comportamento consistente e esperado
3. **Simplicidade:** Evitar over-engineering
4. **Segurança:** Validação rigorosa e controle de acesso
5. **Consistência:** Transações atômicas para operações financeiras

## Estrutura do Projeto

```
app/
├── Models/
│   ├── User.php
│   ├── Client.php
│   ├── Sale.php
│   ├── SalePayment.php
│   ├── PaymentMethod.php
│   ├── ClientBalance.php
│   └── ClientLedger.php
├── Http/
│   ├── Controllers/
│   │   ├── SaleController.php
│   │   ├── ClientController.php
│   │   ├── UserController.php
│   │   └── DashboardController.php
│   ├── Requests/
│   │   ├── StoreSaleRequest.php
│   │   ├── StoreClientRequest.php
│   │   └── StoreUserRequest.php
│   └── Middleware/
│       ├── EnsureSuperAdmin.php
│       └── EnsureAttendant.php
├── Policies/
│   ├── SalePolicy.php
│   ├── ClientPolicy.php
│   └── UserPolicy.php
└── Services/
    ├── SaleService.php
    ├── PaymentService.php
    └── BalanceService.php

resources/js/
├── Pages/
│   ├── Dashboard.vue
│   ├── Sales/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Show.vue
│   ├── Clients/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Show.vue
│   └── Users/
│       ├── Index.vue
│       └── Create.vue
├── Components/
│   ├── Sales/
│   │   ├── SaleForm.vue
│   │   ├── PaymentSplit.vue
│   │   └── ItemsTable.vue
│   ├── Clients/
│   │   ├── ClientSelect.vue
│   │   ├── ClientModal.vue
│   │   └── ClientBalance.vue
│   └── Common/
│       ├── Modal.vue
│       ├── DataTable.vue
│       └── Select.vue
└── Composables/
    ├── usePaymentSplit.js
    ├── useClientBalance.js
    └── useFormValidation.js
```

## Camadas da Aplicação

### 1. Camada de Dados (Models)
Representam as entidades do negócio e seus relacionamentos.

### 2. Camada de Negócio (Services)
Contém a lógica de negócio complexa, especialmente para operações financeiras.

### 3. Camada de Controle (Controllers)
Orquestram requisições HTTP, chamam services e retornam respostas.

### 4. Camada de Apresentação (Vue/Inertia)
Interface do usuário com componentes reutilizáveis.

### 5. Camada de Segurança (Policies/Middleware)
Controle de acesso e autorização.

## Fluxo de Dados

```
Request → Middleware → Controller → Service → Model → Database
                                      ↓
                                   Response
```

## Pontos de Atenção

### Segurança
- Sempre validar dados de entrada com Form Requests
- Usar Policies para autorização
- Proteger rotas com middleware apropriado
- Sanitizar dados antes de salvar no banco
- Usar transações para operações financeiras
- Evitar SQL injection usando Eloquent ORM

### Consistência de Dados
- **Transações DB:** Todas as operações de venda devem ser atômicas
- **Validação de saldo:** Verificar saldo disponível antes de debitar
- **Auditoria:** Registrar todas as movimentações no ClientLedger
- **Soft Deletes:** Usar soft deletes para vendas e clientes

### Performance
- Eager loading para evitar N+1 queries
- Cache de métodos de pagamento (raramente mudam)
- Paginação em todas as listagens
- Índices no banco de dados para campos frequentemente consultados

## Próximos Documentos

1. [DATABASE.md](./docs/DATABASE.md) - Estrutura completa do banco de dados
2. [BUSINESS_RULES.md](./docs/BUSINESS_RULES.md) - Regras de negócio detalhadas
3. [ROUTES.md](./docs/ROUTES.md) - Mapeamento de rotas
4. [COMPONENTS.md](./docs/COMPONENTS.md) - Especificação dos componentes Vue
5. [PERMISSIONS.md](./docs/PERMISSIONS.md) - Sistema de permissões
6. [IMPLEMENTATION_GUIDE.md](./docs/IMPLEMENTATION_GUIDE.md) - Guia de implementação passo a passo

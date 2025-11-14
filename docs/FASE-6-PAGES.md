# Fase 6 - Pages e Componentes Específicos

## Visão Geral

Implementação de páginas Inertia.js que utilizam os componentes base criados na Fase 5. Cada página representa uma view completa do sistema com integração de dados via props do Laravel.

## Páginas a Implementar

### 1. Dashboard (✅ Completada)
**Arquivo:** `resources/js/Pages/Dashboard.vue`
**Localização:** `/dashboard`
**Responsável:** DashboardController::index()

Exibe:
- 4 cards de estatísticas (vendas, receita, ticket médio, clientes ativos)
- Gráfico de receita mensal
- Métodos de pagamento com totais
- Últimas 10 vendas
- Top 5 clientes por gasto

### 2. Sales/Index
**Arquivo:** `resources/js/Pages/Sales/Index.vue`
**Localização:** `/sales`
**Responsável:** SaleController::index()

Exibe:
- Tabela com todas as vendas
- Filtro por número de venda ou cliente
- Paginação (15 por página)
- Botões para:
  - Nova venda (+)
  - Ver detalhes (link em sale_number)
  - Cancelar venda (com confirmação)

**Props do Controller:**
```php
return Inertia::render('Sales/Index', [
    'sales' => $sales,  // Paginado
    'filters' => $request->only(['search']),
]);
```

**Estrutura Esperada:**
```vue
<template>
  <AppLayout>
    <Card title="Vendas">
      <!-- Search -->
      <Input v-model="filters.search" placeholder="Pesquisar por venda ou cliente..." />

      <!-- Table -->
      <Table :columns="salesColumns" :rows="sales.data">
        <template #column-sale_number="{ row }">
          <Link :href="`/sales/${row.id}`">{{ row.sale_number }}</Link>
        </template>

        <template #actions="{ row }">
          <Link href="">Ver</Link>
          <button @click="cancelSale(row.id)">Cancelar</button>
        </template>
      </Table>

      <!-- Pagination -->
    </Card>
  </AppLayout>
</template>
```

### 3. Sales/Create
**Arquivo:** `resources/js/Pages/Sales/Create.vue`
**Localização:** `/sales/create`
**Responsável:** SaleController::create()

Formulário com:
- ClientSelect (autocomplete)
- Tabela de itens (dinâmica)
  - Descr ição do item
  - Quantidade
  - Valor unitário
  - Subtotal (calculado)
  - Botão remover
- Subtotal (calculado)
- Campo de desconto
- Total (calculado)
- PaymentMethodSelector (múltiplos métodos)
- Validação de pagamentos (deve somar total)
- Botão Salvar e Voltar

**Props do Controller:**
```php
return Inertia::render('Sales/Create', [
    'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('display_order')->get(),
    'anonymousClientId' => Client::where('name', 'Anônimo')->value('id'),
]);
```

**Estrutura Esperada:**
```vue
<script setup lang="ts">
// Reactive state
const form = ref({
  client_id: null,
  items: [],
  discount: 0,
  payments: [],
});

// Computed
const subtotal = computed(() => {
  return form.value.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
});

const total = computed(() => {
  return subtotal.value - form.value.discount;
});

// Methods
const addItem = () => {
  form.value.items.push({ description: '', quantity: 1, price: 0 });
};

const removeItem = (index: number) => {
  form.value.items.splice(index, 1);
};

const submitForm = async () => {
  // Validação: sum(payments) === total
  // POST /sales
  // Redirecionar para /sales/:id
};
</script>
```

### 4. Sales/Show
**Arquivo:** `resources/js/Pages/Sales/Show.vue`
**Localização:** `/sales/:id`
**Responsável:** SaleController::show()

Exibe:
- Número da venda
- Cliente (com link)
- Data/hora
- Detalhes dos itens
- Subtotal e desconto
- Total
- Detalhes dos pagamentos
- Status (completed/cancelled)
- Botões:
  - Editar (desabilitado)
  - Cancelar (se não cancelada)
  - Voltar

**Props do Controller:**
```php
return Inertia::render('Sales/Show', [
    'sale' => $sale->load(['client', 'user', 'payments.paymentMethod']),
]);
```

### 5. Clients/Index
**Arquivo:** `resources/js/Pages/Clients/Index.vue`
**Localização:** `/clients`
**Responsável:** ClientController::index()

Exibe:
- Tabela com clientes
- Filtro por nome, email ou telefone
- Paginação (15 por página)
- Botões:
  - Novo cliente (+)
  - Ver detalhes
  - Editar (desabilitado)

**Props do Controller:**
```php
return Inertia::render('Clients/Index', [
    'clients' => $clients,  // Paginado
    'filters' => $request->only(['search']),
]);
```

### 6. Clients/Create
**Arquivo:** `resources/js/Pages/Clients/Create.vue`
**Localização:** `/clients/create`
**Responsável:** ClientController::create()

Formulário com:
- Nome (obrigatório)
- E-mail (opcional, único)
- Telefone (opcional)
- CPF/CNPJ (opcional, único, com validação)
- Saldo inicial (opcional, padrão 0)
- Botões: Salvar e Voltar

**Props do Controller:**
```php
return Inertia::render('Clients/Create');
```

**Validação Customizada:**
- CPF: 11 dígitos, algoritmo de validação
- CNPJ: 14 dígitos, algoritmo de validação
- E-mail: formato válido, único

### 7. Clients/Show
**Arquivo:** `resources/js/Pages/Clients/Show.vue`
**Localização:** `/clients/:id`
**Responsável:** ClientController::show()

Exibe:
- Dados do cliente
- BalanceDisplay (saldo e caderneta)
- Últimas 10 transações (ledger)
- Últimas vendas
- Estatísticas:
  - Total gasto
  - Número de compras
  - Última compra
  - Status (ativo/inativo)
- Botões:
  - Editar (desabilitado)
  - Voltar

**Props do Controller:**
```php
return Inertia::render('Clients/Show', [
    'client' => $client->load('balance'),
    'balance' => $client->balance,
    'recentTransactions' => $client->ledger()->latest()->take(10)->get(),
    'recentSales' => $client->sales()->latest()->take(10)->get(),
    'stats' => [
        'total_spent' => $client->sales()->sum('total'),
        'sales_count' => $client->sales()->count(),
        'last_sale' => $client->sales()->latest()->first(),
    ],
]);
```

## Estrutura de Arquivos

```
resources/js/Pages/
├── Dashboard.vue ✅
├── Sales/
│   ├── Index.vue
│   ├── Create.vue
│   └── Show.vue
├── Clients/
│   ├── Index.vue
│   ├── Create.vue
│   └── Show.vue
└── Auth/
    └── (Mantenha como está - Breeze)
```

## Padrões Comuns

### Form Handling
```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  // ...
});

const submit = () => {
  form.post('/resource', {
    onSuccess: () => {
      // Redireciona automaticamente
    },
    onError: () => {
      // Erros em form.errors
    },
  });
};
</script>

<template>
  <Input
    v-model="form.name"
    :error="form.errors.name"
    @input="form.clearErrors('name')"
  />
</template>
```

### Table Columns Pattern
```typescript
const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Nome' },
  { key: 'created_at', label: 'Criado em' },
];
```

### Computed Formatting
```typescript
const formatCurrency = (value: string | number): string => {
  const num = typeof value === 'string' ? parseFloat(value) : value;
  return `R$ ${num.toFixed(2).replace('.', ',')}`;
};
```

## Validações do Lado do Cliente

### CPF Validation
```typescript
const validateCPF = (cpf: string): boolean => {
  const cleaned = cpf.replace(/\D/g, '');
  if (cleaned.length !== 11) return false;

  // Verificar dígitos repetidos
  if (/^(\d)\1{10}$/.test(cleaned)) return false;

  // Calcular primeiro dígito verificador
  let sum = 0;
  for (let i = 0; i < 9; i++) {
    sum += parseInt(cleaned[i]) * (10 - i);
  }
  let digit1 = 11 - (sum % 11);
  digit1 = digit1 > 9 ? 0 : digit1;

  if (parseInt(cleaned[9]) !== digit1) return false;

  // Calcular segundo dígito verificador
  sum = 0;
  for (let i = 0; i < 10; i++) {
    sum += parseInt(cleaned[i]) * (11 - i);
  }
  let digit2 = 11 - (sum % 11);
  digit2 = digit2 > 9 ? 0 : digit2;

  return parseInt(cleaned[10]) === digit2;
};
```

### Form Validation Example
```vue
<script setup>
const errors = ref({});

const validateForm = () => {
  errors.value = {};

  if (!form.name) errors.value.name = 'Nome é obrigatório';
  if (form.email && !isValidEmail(form.email)) errors.value.email = 'E-mail inválido';
  if (form.cpf && !validateCPF(form.cpf)) errors.value.cpf = 'CPF inválido';

  return Object.keys(errors.value).length === 0;
};
</script>
```

## Integração com Inertia.js

### Props Typing
```typescript
interface Props {
  sales: {
    data: Array<Sale>;
    links: PaginationLinks;
    meta: PaginationMeta;
  };
  filters: {
    search?: string;
  };
}

defineProps<Props>();
```

### Link Navigation
```vue
<template>
  <Link href="/dashboard" class="...">Dashboard</Link>
  <Link :href="`/sales/${sale.id}`" method="post">Delete</Link>
</template>
```

### Form Methods
```javascript
form.post('/sales', {
  preserveScroll: true,
  onSuccess: () => {
    router.visit('/sales');
  },
});
```

## Próximos Passos

1. Criar todos os arquivos de pages acima
2. Implementar validações do lado do cliente
3. Adicionar feedback visual (loading, disabled states)
4. Implementar paginação e filtros funcionais
5. Adicionar confirmações para ações destrutivas
6. Implementar dark mode em todas as páginas

## Notas Importantes

- Todas as datas devem ser formatadas para locale "pt-BR"
- Valores monetários sempre em formato "R$ X,XX"
- Usar `@inertiajs/vue3` para navegação
- Props devem ser tipadas com TypeScript
- Componentes de Fase 5 devem ser reutilizados maximamente
- Validações duplas: servidor (StoreSaleRequest) + cliente (páginas)

## Estatísticas Esperadas

- **Arquivos:** 7 páginas
- **Linhas de código:** ~3.000 (TypeScript/Vue/Template)
- **Componentes reutilizados:** 12 (de Fase 5)
- **Rotas integradas:** 10
- **Validações:** 20+

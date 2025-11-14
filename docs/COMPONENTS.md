# Componentes Vue + Inertia

## Estrutura de Pastas

```
resources/js/
├── Pages/                      # Páginas completas (Inertia)
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
│
├── Components/                 # Componentes reutilizáveis
│   ├── Sales/
│   │   ├── SaleForm.vue
│   │   ├── PaymentSplit.vue
│   │   ├── ItemsTable.vue
│   │   └── SaleCard.vue
│   ├── Clients/
│   │   ├── ClientSelect.vue
│   │   ├── ClientModal.vue
│   │   ├── ClientBalance.vue
│   │   └── ClientCard.vue
│   ├── Common/
│   │   ├── Modal.vue
│   │   ├── DataTable.vue
│   │   ├── Select.vue
│   │   ├── Input.vue
│   │   ├── Button.vue
│   │   └── Badge.vue
│   └── Layout/
│       ├── AuthenticatedLayout.vue
│       ├── Sidebar.vue
│       └── Navbar.vue
│
└── Composables/                # Lógica reutilizável
    ├── usePaymentSplit.js
    ├── useClientBalance.js
    ├── useFormValidation.js
    ├── useDebounce.js
    └── useAuth.js
```

---

## 1. Pages (Páginas)

### 1.1. Dashboard.vue

**Descrição:** Página inicial com estatísticas e resumos.

**Props:**
```typescript
interface Props {
    stats: {
        today_sales: number;
        today_revenue: number;
        pending_tabs: number;
        active_clients: number;
    };
    recent_sales: Sale[];
}
```

**Template:**
```vue
<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cards de Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <StatCard
                        title="Vendas Hoje"
                        :value="stats.today_sales"
                        icon="shopping-cart"
                    />
                    <StatCard
                        title="Faturamento Hoje"
                        :value="formatCurrency(stats.today_revenue)"
                        icon="dollar"
                    />
                    <StatCard
                        title="Cadernetas Pendentes"
                        :value="stats.pending_tabs"
                        icon="clipboard"
                    />
                    <StatCard
                        title="Clientes Ativos"
                        :value="stats.active_clients"
                        icon="users"
                    />
                </div>

                <!-- Vendas Recentes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Vendas Recentes</h2>
                        <SalesList :sales="recent_sales" :compact="true" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

**Responsividade:**
- Desktop: Grid 4 colunas
- Tablet: Grid 2 colunas
- Mobile: Grid 1 coluna

---

### 1.2. Sales/Create.vue

**Descrição:** Formulário completo de criação de venda.

**Props:**
```typescript
interface Props {
    payment_methods: PaymentMethod[];
    anonymous_client_id: number;
}
```

**Template:**
```vue
<template>
    <AuthenticatedLayout>
        <Head title="Nova Venda" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-6">Nova Venda</h2>

                        <SaleForm
                            :payment-methods="payment_methods"
                            :anonymous-client-id="anonymous_client_id"
                            @submit="handleSubmit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    payment_methods: Array,
    anonymous_client_id: Number,
});

const handleSubmit = (formData) => {
    router.post(route('sales.store'), formData, {
        onSuccess: () => {
            router.visit(route('sales.index'));
        },
    });
};
</script>
```

---

## 2. Components (Componentes Reutilizáveis)

### 2.1. Sales/SaleForm.vue

**Descrição:** Formulário completo de venda com todos os campos e validações.

**Props:**
```typescript
interface Props {
    paymentMethods: PaymentMethod[];
    anonymousClientId: number;
    initialData?: Partial<SaleFormData>;
}
```

**Template:**
```vue
<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Cliente -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Cliente
            </label>
            <ClientSelect
                v-model="form.client_id"
                :default-value="anonymousClientId"
                @update:modelValue="onClientChange"
            />

            <!-- Saldo do Cliente -->
            <ClientBalance
                v-if="form.client_id && form.client_id !== anonymousClientId"
                :client-id="form.client_id"
                class="mt-2"
            />
        </div>

        <!-- Valor Total -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Valor Total *
            </label>
            <Input
                v-model="form.total_amount"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                required
                :error="form.errors.total_amount"
            />
        </div>

        <!-- Itens (Opcional) -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">
                    Itens da Venda (Opcional)
                </label>
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    @click="toggleItems"
                >
                    {{ showItems ? 'Ocultar Itens' : 'Adicionar Itens' }}
                </Button>
            </div>

            <ItemsTable
                v-if="showItems"
                v-model="form.items"
                :total="form.total_amount"
            />
        </div>

        <!-- Métodos de Pagamento -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Pagamento *
            </label>
            <PaymentSplit
                v-model="form.payments"
                :payment-methods="paymentMethods"
                :total-amount="form.total_amount"
                :client-balance="clientBalance"
                :client-id="form.client_id"
            />
        </div>

        <!-- Observações -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Observações
            </label>
            <textarea
                v-model="form.notes"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Observações opcionais..."
            />
        </div>

        <!-- Botões -->
        <div class="flex items-center justify-end gap-4">
            <Button
                type="button"
                variant="secondary"
                @click="$emit('cancel')"
            >
                Cancelar
            </Button>
            <Button
                type="submit"
                :disabled="!isValid || form.processing"
                :loading="form.processing"
            >
                Finalizar Venda
            </Button>
        </div>
    </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ClientSelect from '@/Components/Clients/ClientSelect.vue';
import ClientBalance from '@/Components/Clients/ClientBalance.vue';
import ItemsTable from '@/Components/Sales/ItemsTable.vue';
import PaymentSplit from '@/Components/Sales/PaymentSplit.vue';
import Input from '@/Components/Common/Input.vue';
import Button from '@/Components/Common/Button.vue';

const props = defineProps({
    paymentMethods: Array,
    anonymousClientId: Number,
    initialData: Object,
});

const emit = defineEmits(['submit', 'cancel']);

const form = useForm({
    client_id: props.anonymousClientId,
    total_amount: 0,
    payments: [],
    items: [],
    notes: '',
});

const showItems = ref(false);
const clientBalance = ref(null);

const isValid = computed(() => {
    const totalPayments = form.payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
    return form.total_amount > 0
        && form.payments.length > 0
        && Math.abs(totalPayments - form.total_amount) < 0.01;
});

const toggleItems = () => {
    showItems.value = !showItems.value;
};

const onClientChange = async (clientId) => {
    if (clientId && clientId !== props.anonymousClientId) {
        // Buscar saldo do cliente
        try {
            const response = await axios.get(route('api.clients.balance', clientId));
            clientBalance.value = response.data;
        } catch (error) {
            console.error('Erro ao buscar saldo:', error);
        }
    } else {
        clientBalance.value = null;
    }
};

const submit = () => {
    emit('submit', form.data());
};

// Recalcular total quando itens mudarem
watch(() => form.items, (items) => {
    if (items && items.length > 0) {
        form.total_amount = items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
    }
}, { deep: true });
</script>
```

**Emits:**
- `submit(formData)`: Quando formulário é válido e submetido
- `cancel()`: Quando usuário cancela

**Validações:**
- Valor total > 0
- Pelo menos um método de pagamento
- Soma dos pagamentos = valor total

---

### 2.2. Sales/PaymentSplit.vue

**Descrição:** Interface para dividir pagamento entre múltiplos métodos.

**Props:**
```typescript
interface Props {
    modelValue: Payment[];
    paymentMethods: PaymentMethod[];
    totalAmount: number;
    clientBalance?: ClientBalance | null;
    clientId?: number | null;
}
```

**Template:**
```vue
<template>
    <div class="space-y-4">
        <!-- Lista de Pagamentos -->
        <div
            v-for="(payment, index) in payments"
            :key="index"
            class="flex items-end gap-2 p-4 border border-gray-200 rounded-lg"
        >
            <!-- Método -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Método
                </label>
                <select
                    v-model="payment.payment_method_id"
                    class="w-full border-gray-300 rounded-md"
                    @change="onMethodChange(index)"
                >
                    <option value="">Selecione...</option>
                    <option
                        v-for="method in availableMethods(index)"
                        :key="method.id"
                        :value="method.id"
                        :disabled="isMethodDisabled(method, index)"
                    >
                        {{ method.name }}
                        <template v-if="method.code === 'balance' && clientBalance">
                            (Disponível: {{ formatCurrency(clientBalance.balance_amount) }})
                        </template>
                    </option>
                </select>
            </div>

            <!-- Valor -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Valor
                </label>
                <input
                    v-model.number="payment.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    :max="getMaxAmount(payment, index)"
                    class="w-full border-gray-300 rounded-md"
                    placeholder="0.00"
                    @input="updatePayments"
                />
            </div>

            <!-- Remover -->
            <Button
                v-if="payments.length > 1"
                type="button"
                variant="danger"
                size="sm"
                @click="removePayment(index)"
            >
                <TrashIcon class="h-4 w-4" />
            </Button>
        </div>

        <!-- Adicionar Método -->
        <Button
            type="button"
            variant="secondary"
            size="sm"
            @click="addPayment"
            :disabled="payments.length >= paymentMethods.length"
        >
            + Adicionar Método
        </Button>

        <!-- Resumo -->
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center text-sm">
                <span>Total da Venda:</span>
                <span class="font-semibold">{{ formatCurrency(totalAmount) }}</span>
            </div>
            <div class="flex justify-between items-center text-sm mt-2">
                <span>Total Pago:</span>
                <span
                    class="font-semibold"
                    :class="{
                        'text-green-600': totalPaid === totalAmount && totalAmount > 0,
                        'text-red-600': totalPaid !== totalAmount,
                    }"
                >
                    {{ formatCurrency(totalPaid) }}
                </span>
            </div>
            <div
                v-if="remaining !== 0"
                class="flex justify-between items-center text-sm mt-2"
            >
                <span>{{ remaining > 0 ? 'Faltam:' : 'Troco:' }}</span>
                <span class="font-semibold" :class="remaining > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ formatCurrency(Math.abs(remaining)) }}
                </span>
            </div>
        </div>

        <!-- Troco como Saldo -->
        <div v-if="showChangeOption" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <label class="flex items-center">
                <input
                    v-model="changeAsBalance"
                    type="checkbox"
                    class="rounded border-gray-300"
                />
                <span class="ml-2 text-sm">
                    Adicionar troco de {{ formatCurrency(Math.abs(remaining)) }} ao saldo do cliente
                </span>
            </label>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { formatCurrency } from '@/Utils/helpers';
import Button from '@/Components/Common/Button.vue';
import { TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: Array,
    paymentMethods: Array,
    totalAmount: Number,
    clientBalance: Object,
    clientId: Number,
});

const emit = defineEmits(['update:modelValue']);

const payments = ref(props.modelValue.length > 0 ? [...props.modelValue] : [
    { payment_method_id: '', amount: props.totalAmount, metadata: {} }
]);

const changeAsBalance = ref(false);

const totalPaid = computed(() => {
    return payments.value.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const remaining = computed(() => {
    return props.totalAmount - totalPaid.value;
});

const showChangeOption = computed(() => {
    return remaining.value < 0
        && props.clientId
        && props.clientId !== props.anonymousClientId
        && payments.value.some(p => {
            const method = props.paymentMethods.find(m => m.id === p.payment_method_id);
            return method && method.code === 'cash';
        });
});

const availableMethods = (currentIndex) => {
    return props.paymentMethods;
};

const isMethodDisabled = (method, currentIndex) => {
    // Desabilita saldo se não houver saldo disponível
    if (method.code === 'balance' && (!props.clientBalance || props.clientBalance.balance_amount <= 0)) {
        return true;
    }
    return false;
};

const getMaxAmount = (payment, index) => {
    const method = props.paymentMethods.find(m => m.id === payment.payment_method_id);

    // Se for saldo, limitar ao saldo disponível
    if (method && method.code === 'balance' && props.clientBalance) {
        return props.clientBalance.balance_amount;
    }

    return null; // Sem limite
};

const addPayment = () => {
    payments.value.push({
        payment_method_id: '',
        amount: Math.max(0, remaining.value),
        metadata: {}
    });
    updatePayments();
};

const removePayment = (index) => {
    payments.value.splice(index, 1);
    updatePayments();
};

const onMethodChange = (index) => {
    updatePayments();
};

const updatePayments = () => {
    emit('update:modelValue', payments.value);
};

watch(changeAsBalance, (value) => {
    if (value && remaining.value < 0) {
        // Adicionar metadata ao pagamento em dinheiro
        const cashPayment = payments.value.find(p => {
            const method = props.paymentMethods.find(m => m.id === p.payment_method_id);
            return method && method.code === 'cash';
        });

        if (cashPayment) {
            cashPayment.metadata = {
                ...cashPayment.metadata,
                change_as_balance: Math.abs(remaining.value)
            };
        }
    }
});
</script>
```

**Emits:**
- `update:modelValue(payments)`: Atualiza array de pagamentos

**Validações:**
- Soma dos valores = total da venda
- Métodos não podem se repetir (opcional)
- Saldo não pode exceder disponível

---

### 2.3. Clients/ClientSelect.vue

**Descrição:** Select com autocomplete para buscar clientes.

**Props:**
```typescript
interface Props {
    modelValue?: number | null;
    defaultValue?: number;
    placeholder?: string;
}
```

**Template:**
```vue
<template>
    <div class="relative">
        <div class="flex gap-2">
            <!-- Select/Autocomplete -->
            <div class="flex-1 relative">
                <input
                    v-model="search"
                    type="text"
                    class="w-full border-gray-300 rounded-md"
                    :placeholder="placeholder"
                    @input="onSearch"
                    @focus="showDropdown = true"
                />

                <!-- Dropdown -->
                <div
                    v-if="showDropdown && clients.length > 0"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                    <button
                        v-for="client in clients"
                        :key="client.id"
                        type="button"
                        class="w-full px-4 py-2 text-left hover:bg-gray-100"
                        @click="selectClient(client)"
                    >
                        <div class="font-medium">{{ client.name }}</div>
                        <div class="text-sm text-gray-500">
                            <template v-if="client.email">{{ client.email }}</template>
                            <template v-if="client.phone"> • {{ client.phone }}</template>
                        </div>
                        <div v-if="client.balance" class="text-xs text-gray-400 mt-1">
                            Saldo: {{ formatCurrency(client.balance.balance_amount) }}
                            <template v-if="client.balance.tab_amount > 0">
                                • Deve: {{ formatCurrency(client.balance.tab_amount) }}
                            </template>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Botão Novo Cliente -->
            <Button
                type="button"
                variant="secondary"
                @click="openNewClientModal"
            >
                + Novo
            </Button>
        </div>

        <!-- Cliente Selecionado -->
        <div v-if="selectedClient" class="mt-2 p-3 bg-gray-50 rounded-md">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium">{{ selectedClient.name }}</div>
                    <div class="text-sm text-gray-500">{{ selectedClient.email }}</div>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="clearSelection"
                >
                    Remover
                </Button>
            </div>
        </div>

        <!-- Modal Novo Cliente -->
        <ClientModal
            v-model="showModal"
            @created="onClientCreated"
        />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useDebounce } from '@/Composables/useDebounce';
import { formatCurrency } from '@/Utils/helpers';
import Button from '@/Components/Common/Button.vue';
import ClientModal from './ClientModal.vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Number,
    defaultValue: Number,
    placeholder: {
        type: String,
        default: 'Buscar cliente...'
    },
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const clients = ref([]);
const selectedClient = ref(null);
const showDropdown = ref(false);
const showModal = ref(false);

const onSearch = useDebounce(async () => {
    if (search.value.length < 2) {
        clients.value = [];
        return;
    }

    try {
        const response = await axios.get(route('api.clients.select'), {
            params: { search: search.value }
        });
        clients.value = response.data.data;
    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
    }
}, 300);

const selectClient = (client) => {
    selectedClient.value = client;
    emit('update:modelValue', client.id);
    showDropdown.value = false;
    search.value = '';
};

const clearSelection = () => {
    selectedClient.value = null;
    emit('update:modelValue', props.defaultValue);
};

const openNewClientModal = () => {
    showModal.value = true;
};

const onClientCreated = (client) => {
    selectClient(client);
    showModal.value = false;
};

// Fechar dropdown ao clicar fora
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
```

**Emits:**
- `update:modelValue(clientId)`: Quando cliente é selecionado

**Features:**
- Busca com debounce (300ms)
- Autocomplete com dropdown
- Botão para criar novo cliente inline
- Mostra saldo e dívida do cliente

---

### 2.4. Clients/ClientModal.vue

**Descrição:** Modal para criar cliente sem sair da tela.

**Template:**
```vue
<template>
    <Modal :show="modelValue" @close="close">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Novo Cliente</h3>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Nome -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nome *
                    </label>
                    <Input
                        v-model="form.name"
                        required
                        :error="form.errors.name"
                    />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <Input
                        v-model="form.email"
                        type="email"
                        :error="form.errors.email"
                    />
                </div>

                <!-- Telefone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Telefone
                    </label>
                    <Input
                        v-model="form.phone"
                        v-mask="'(##) #####-####'"
                        :error="form.errors.phone"
                    />
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="close"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        :loading="form.processing"
                    >
                        Salvar
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import Input from '@/Components/Common/Input.vue';
import Button from '@/Components/Common/Button.vue';

const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'created']);

const form = useForm({
    name: '',
    email: '',
    phone: '',
});

const submit = () => {
    form.post(route('clients.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            const client = page.props.client;
            emit('created', client);
            form.reset();
        },
    });
};

const close = () => {
    form.reset();
    emit('update:modelValue', false);
};
</script>
```

---

## 3. Composables (Lógica Reutilizável)

### 3.1. useDebounce.js

```javascript
import { ref } from 'vue';

export function useDebounce(fn, delay = 300) {
    let timeoutId = null;

    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}
```

### 3.2. usePaymentSplit.js

```javascript
import { ref, computed } from 'vue';

export function usePaymentSplit(totalAmount) {
    const payments = ref([]);

    const totalPaid = computed(() => {
        return payments.value.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
    });

    const remaining = computed(() => {
        return totalAmount.value - totalPaid.value;
    });

    const isValid = computed(() => {
        return Math.abs(remaining.value) < 0.01 && payments.value.length > 0;
    });

    const addPayment = (paymentMethodId = '', amount = 0) => {
        payments.value.push({
            payment_method_id: paymentMethodId,
            amount,
            metadata: {}
        });
    };

    const removePayment = (index) => {
        payments.value.splice(index, 1);
    };

    const reset = () => {
        payments.value = [];
    };

    return {
        payments,
        totalPaid,
        remaining,
        isValid,
        addPayment,
        removePayment,
        reset,
    };
}
```

### 3.3. useClientBalance.js

```javascript
import { ref } from 'vue';
import axios from 'axios';

export function useClientBalance() {
    const balance = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchBalance = async (clientId) => {
        if (!clientId) {
            balance.value = null;
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(route('api.clients.balance', clientId));
            balance.value = response.data;
        } catch (err) {
            error.value = err.message;
            console.error('Erro ao buscar saldo:', err);
        } finally {
            loading.value = false;
        }
    };

    const reset = () => {
        balance.value = null;
        error.value = null;
    };

    return {
        balance,
        loading,
        error,
        fetchBalance,
        reset,
    };
}
```

---

## 4. Responsividade

### Breakpoints (TailwindCSS)

```javascript
// tailwind.config.js
module.exports = {
    theme: {
        screens: {
            'sm': '640px',   // Mobile grande
            'md': '768px',   // Tablet
            'lg': '1024px',  // Desktop
            'xl': '1280px',  // Desktop grande
        }
    }
}
```

### Padrões de Responsividade

#### Cards
```vue
<!-- Desktop: 4 colunas, Tablet: 2 colunas, Mobile: 1 coluna -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <Card />
</div>
```

#### Tabelas
```vue
<!-- Desktop: Tabela normal, Mobile: Cards empilhados -->
<div class="hidden md:block">
    <table>...</table>
</div>
<div class="md:hidden space-y-4">
    <Card v-for="item in items">...</Card>
</div>
```

#### Modais
```vue
<!-- Desktop: Modal centralizado, Mobile: Tela cheia -->
<div class="fixed inset-0 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md md:max-w-2xl max-h-[90vh] overflow-y-auto">
        ...
    </div>
</div>
```

---

## 5. Boas Práticas

### 5.1. Composição de Componentes

✅ **Bom:** Componentes pequenos e focados
```vue
<SaleForm>
    <ClientSelect />
    <PaymentSplit />
    <ItemsTable />
</SaleForm>
```

❌ **Ruim:** Componente gigante fazendo tudo

### 5.2. Props e Events

✅ **Bom:** Props para entrada, Events para saída
```vue
<PaymentSplit
    :total-amount="100"
    @update:modelValue="handleUpdate"
/>
```

### 5.3. Validações

✅ **Bom:** Validar no cliente E no servidor
```javascript
// Cliente (UX imediata)
const isValid = computed(() => form.total > 0);

// Servidor (Segurança)
$request->validate(['total' => 'required|numeric|min:0']);
```

### 5.4. Loading States

✅ **Sempre** mostrar feedback visual
```vue
<Button :loading="form.processing" :disabled="form.processing">
    Salvar
</Button>
```

### 5.5. Tratamento de Erros

```vue
<div v-if="form.errors.total" class="text-red-600 text-sm mt-1">
    {{ form.errors.total }}
</div>
```

---

## 6. Acessibilidade

- Labels para todos os inputs
- Aria-labels em botões de ícone
- Foco visível em elementos interativos
- Cores com contraste adequado (WCAG AA)
- Formulários navegáveis por teclado

```vue
<button aria-label="Remover item" @click="remove">
    <TrashIcon />
</button>
```

---

## Resumo

- **Pages:** Páginas completas do Inertia
- **Components:** Reutilizáveis e compostos
- **Composables:** Lógica compartilhada
- **Responsivo:** Mobile-first com TailwindCSS
- **Acessível:** WCAG AA compliance
- **Performático:** Lazy loading e debounce

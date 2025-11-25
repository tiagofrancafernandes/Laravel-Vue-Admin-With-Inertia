<template>
    <Head title="Nova Venda" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100">Nova Venda</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm md:text-base">
                        Registre uma nova transação
                    </p>
                </div>
                <Link href="/sales" class="w-full md:w-auto">
                    <Button variant="secondary" class="w-full md:w-auto">← Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Client Selection -->
                <Card title="Cliente">
                    <ClientSelect
                        v-model="form.client_id"
                        :error="form.errors.client_id"
                        required
                        @balance-loaded="onBalanceLoaded"
                    />
                </Card>

                <!-- Client Balance Info -->
                <Card v-if="clientBalance && form.client_id" title="Informações do Cliente">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Saldo Disponível</p>
                                <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                                    R$ {{ formatCurrency(clientBalance.balance) }}
                                </p>
                            </div>
                            <div v-if="clientBalance.credit_limit > 0">
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Limite de Crédito Total</p>
                                <p class="text-lg font-semibold text-blue-600 dark:text-blue-400 mt-1">
                                    R$ {{ formatCurrency(clientBalance.credit_limit) }}
                                </p>
                            </div>
                        </div>

                        <!-- Credit Availability for this Sale -->
                        <div v-if="paymentDeficit > 0" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-3">
                                Crédito Disponível para esta Venda (R$ {{ formatCurrency(paymentDeficit) }} a pagar)
                            </p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium">
                                        Crédito Disponível: R$ {{ formatCurrency(availableCreditForSale) }}
                                    </span>
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            {
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                    creditPercentage >= 30,
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                    creditPercentage >= 10 && creditPercentage < 30,
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200':
                                                    creditPercentage < 10 && creditPercentage > 0,
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                    creditPercentage <= 0,
                                            },
                                        ]"
                                    >
                                        {{ creditPercentage.toFixed(0) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div
                                        :style="{ width: Math.max(0, Math.min(100, creditPercentage)) + '%' }"
                                        :class="[
                                            'h-2 rounded-full transition-all',
                                            {
                                                'bg-green-500': creditPercentage >= 30,
                                                'bg-yellow-500': creditPercentage >= 10 && creditPercentage < 30,
                                                'bg-orange-500': creditPercentage < 10 && creditPercentage > 0,
                                                'bg-red-500': creditPercentage <= 0,
                                            },
                                        ]"
                                    />
                                </div>
                                <p
                                    :class="[
                                        'text-xs mt-2',
                                        {
                                            'text-green-600 dark:text-green-400': creditPercentage >= 30,
                                            'text-yellow-600 dark:text-yellow-400': creditPercentage >= 10 && creditPercentage < 30,
                                            'text-orange-600 dark:text-orange-400': creditPercentage < 10 && creditPercentage > 0,
                                            'text-red-600 dark:text-red-400': creditPercentage <= 0,
                                        },
                                    ]"
                                >
                                    <span v-if="creditPercentage >= 30"> ✓ Crédito suficiente</span>
                                    <span v-else-if="creditPercentage >= 10 && creditPercentage < 30">
                                        ⚠️ Crédito baixo
                                    </span>
                                    <span v-else-if="creditPercentage < 10 && creditPercentage > 0">
                                        ⚠️ Crédito quase acabando
                                    </span>
                                    <span v-else> ✗ Crédito insuficiente</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Items Section -->
                <Card title="Itens">
                    <div class="space-y-4">
                        <!-- Product Selector -->
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Selecione um Produto
                            </p>
                            <ProductSelect @select="onProductSelected" />
                        </div>

                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3"
                        >
                            <div class="flex justify-between items-start">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Item {{ index + 1 }}</h4>
                                <button
                                    v-if="form.items.length"
                                    type="button"
                                    @click="removeItem(index)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm"
                                >
                                    Remover
                                </button>
                            </div>

                            <Input
                                v-model="item.description"
                                label="Descrição"
                                placeholder="Ex: Produto XYZ"
                                required
                            />

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <Input
                                    v-model="item.quantity"
                                    label="Quantidade"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0"
                                    required
                                />
                                <Input
                                    v-model="item.price"
                                    label="Valor Unitário"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    required
                                />
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Subtotal
                                    </label>
                                    <div
                                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-900 dark:text-gray-100 font-medium"
                                    >
                                        R$ {{ formatCurrency(item.quantity * item.price) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Button type="button" variant="secondary" @click="addItem">+ Adicionar Item Manualmente</Button>
                    </div>
                </Card>

                <!-- Totals Section -->
                <Card title="Valores">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                R$ {{ formatCurrency(subtotal) }}
                            </span>
                        </div>

                        <Input
                            v-model.number="form.discount"
                            label="Desconto"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />

                        <div
                            class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between items-center"
                        >
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Total</span>
                            <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                R$ {{ formatCurrency(total) }}
                            </span>
                        </div>
                    </div>
                </Card>

                <!-- Payment Methods -->
                <Card title="Formas de Pagamento">
                    <PaymentMethodSelector
                        v-model="form.payments"
                        :payment-methods="paymentMethods"
                        :total="total"
                        :client-id="form.client_id"
                        :client-balance="clientBalance"
                        :error="form.errors.payments"
                    />
                    <div
                        v-if="paymentTotal < total && form.payments.length > 0"
                        class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg"
                    >
                        <p class="text-red-800 dark:text-red-200 text-sm">
                            ⚠️ Soma dos pagamentos (R$ {{ formatCurrency(paymentTotal) }}) não corresponde ao total (R$
                            {{ formatCurrency(total) }})
                        </p>
                    </div>
                    <div
                        v-else-if="form.payments.length > 0"
                        class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg"
                    >
                        <p class="text-green-800 dark:text-green-200 text-sm">
                            ✓ Pagamentos conferem: R$ {{ formatCurrency(paymentTotal) }}
                        </p>
                    </div>
                </Card>

                <!-- Sale Notes -->
                <Card title="Observações da Venda">
                    <textarea
                        v-model="form.notes"
                        placeholder="Adicione observações sobre esta venda (opcional)"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100 resize-none"
                        rows="3"
                    />
                </Card>

                <!-- Form Actions -->
                <div class="flex gap-4">
                    <Link href="/sales" class="flex-1">
                        <Button type="button" variant="secondary" class="w-full">Cancelar</Button>
                    </Link>
                    <Button
                        type="submit"
                        variant="primary"
                        class="flex-1"
                        :disabled="form.processing || paymentTotal < total"
                    >
                        {{ form.processing ? 'Registrando...' : 'Registrar Venda' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import {
    computed,
    ref,
    watch,
} from 'vue';

import ClientSelect from '@/Components/Clients/ClientSelect.vue';
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import ProductSelect from '@/Components/Products/ProductSelect.vue';
import PaymentMethodSelector from '@/Components/Sales/PaymentMethodSelector.vue';
import Card from '@/Components/UI/Card.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
    description?: string;
    is_active: boolean;
}

interface Item {
    description: string;
    quantity: number;
    price: number;
}

interface Payment {
    method_id: number;
    amount: number;
}

interface Props {
    paymentMethods: PaymentMethod[];
    anonymousClientId: number;
}

const props = defineProps<Props>();

const clientBalance = ref<any>(null);

const onBalanceLoaded = (balance: any) => {
    clientBalance.value = balance;
};

const form = useForm({
    client_id: null as number | null,
    items: [
        // { description: '', quantity: 1, price: 0 },
    ] as Item[],
    discount: 0,
    payments: [] as Payment[],
    notes: '',
});

// Reset client balance when client is deselected
watch(
    () => form.client_id,
    (newClientId) => {
        if (!newClientId) {
            clientBalance.value = null;
        }
    }
);

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value - form.discount);
});

const paymentTotal = computed(() => {
    return form.payments.reduce((sum, payment) => sum + payment.amount, 0);
});

const paymentDeficit = computed(() => {
    // How much more needs to be paid or collected
    const deficit = total.value - paymentTotal.value;
    return Math.max(0, deficit);
});

const availableCreditForSale = computed(() => {
    if (!clientBalance.value) return 0;
    // Calculate how much credit is needed
    const neededCredit = total.value - paymentTotal.value;
    // Client's available credit is their limit minus any amount they owe
    // For this display, we just show their credit limit (the backend validates actual usage)
    return Math.max(0, clientBalance.value.credit_limit - neededCredit);
});

const creditPercentage = computed(() => {
    if (!clientBalance.value || clientBalance.value.credit_limit <= 0) return 0;
    const needed = total.value - paymentTotal.value;
    const percentage = ((clientBalance.value.credit_limit - needed) / clientBalance.value.credit_limit) * 100;
    return Math.max(0, percentage);
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, price: 0 });
};

const onProductSelected = (product: Item) => {
    form.items.push(product);
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const formatCurrency = (value: number): string => {
    return value.toFixed(2).replace('.', ',');
};

const submitForm = () => {
    form.post('/sales', {
        onSuccess: () => {
            // Redirect handled by Inertia
        },
    });
};
</script>

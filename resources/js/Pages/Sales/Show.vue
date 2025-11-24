<template>
    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Venda {{ sale.sale_number }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Detalhes da transação</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/sales" class="inline-block">
                        <Button variant="secondary">← Voltar</Button>
                    </Link>
                    <Button v-if="sale.status === 'completed'" variant="danger" @click="showCancelModal = true">
                        Cancelar Venda
                    </Button>
                </div>
            </div>

            <!-- Sale Status -->
            <div
                :class="[
                    'rounded-lg p-4',
                    sale.status === 'completed' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20',
                ]"
            >
                <p
                    :class="[
                        'font-medium',
                        sale.status === 'completed'
                            ? 'text-green-800 dark:text-green-200'
                            : 'text-red-800 dark:text-red-200',
                    ]"
                >
                    {{ sale.status === 'completed' ? '✓ Venda Finalizada' : '✗ Venda Cancelada' }}
                </p>
            </div>

            <!-- Main Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Client Info -->
                <Card title="Cliente">
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ sale.client?.name || 'Anônimo' }}
                        </p>
                        <p v-if="sale.client?.email" class="text-sm text-gray-600 dark:text-gray-400">
                            {{ sale.client.email }}
                        </p>
                        <p v-if="sale.client?.phone" class="text-sm text-gray-600 dark:text-gray-400">
                            {{ sale.client.phone }}
                        </p>
                        <Link
                            v-if="sale.client"
                            :href="`/clients/${sale.client.id}`"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-block mt-2"
                        >
                            Ver perfil do cliente →
                        </Link>
                    </div>
                </Card>

                <!-- Date & User Info -->
                <Card title="Informações">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Data de Criação</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ formatDateTime(sale.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Responsável</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ sale.user?.name }}
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Totals -->
                <Card title="Resumo">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                R$ {{ formatCurrency(sale.subtotal) }}
                            </span>
                        </div>
                        <div v-if="parseFloat(sale.discount) > 0" class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Desconto</span>
                            <span class="font-medium text-red-600 dark:text-red-400">
                                -R$ {{ formatCurrency(sale.discount) }}
                            </span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Total</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                R$ {{ formatCurrency(sale.total) }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Items -->
            <Card title="Itens da Venda">
                <div v-if="sale.items && sale.items.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Descrição
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Qtd
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Valor Unit.
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="(item, index) in sale.items"
                                :key="index"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ item.description }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                    R$ {{ formatCurrency(item.price) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                    R$ {{ formatCurrency(item.quantity * item.price) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">Nenhum item registrado</div>
            </Card>

            <!-- Payments -->
            <Card title="Pagamentos">
                <div v-if="sale.payments && sale.payments.length > 0" class="space-y-3">
                    <div
                        v-for="payment in sale.payments"
                        :key="payment.id"
                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ payment.paymentMethod?.name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ formatDate(payment.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                R$ {{ formatCurrency(payment.amount) }}
                            </p>
                            <p
                                v-if="payment.change_amount && parseFloat(payment.change_amount) > 0"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Troco: R$ {{ formatCurrency(payment.change_amount) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">Nenhum pagamento registrado</div>
            </Card>

            <!-- Notes -->
            <Card v-if="sale.notes" title="Observações">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ sale.notes }}
                </p>
            </Card>
        </div>

        <!-- Cancel Sale Modal -->
        <Modal :open="showCancelModal" title="Cancelar Venda" @close="showCancelModal = false">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Tem certeza que deseja cancelar a venda {{ sale.sale_number }}?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                Esta ação é irreversível. Todos os pagamentos serão estornados e os saldos do cliente serão restaurados.
            </p>

            <template #footer>
                <Button variant="secondary" @click="showCancelModal = false">Não, manter venda</Button>
                <Button variant="danger" @click="confirmCancel" :disabled="cancelLoading">
                    {{ cancelLoading ? 'Cancelando...' : 'Sim, cancelar venda' }}
                </Button>
            </template>
        </Modal>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import Button from '@/Components/Forms/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import { Link, useForm } from '@inertiajs/vue3';

interface SaleItem {
    description: string;
    quantity: number;
    price: string | number;
}

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
}

interface Payment {
    id: number;
    amount: string;
    change_amount?: string;
    created_at: string;
    paymentMethod?: PaymentMethod;
}

interface Client {
    id: number;
    name: string;
    email?: string;
    phone?: string;
}

interface User {
    id: number;
    name: string;
}

interface Sale {
    id: number;
    sale_number: string;
    client_id?: number;
    client?: Client;
    user?: User;
    items: SaleItem[];
    subtotal: string;
    discount: string;
    total: string;
    status: 'completed' | 'cancelled';
    notes?: string;
    payments: Payment[];
    created_at: string;
    updated_at: string;
}

interface Props {
    sale: Sale;
}

const props = defineProps<Props>();

const showCancelModal = ref(false);
const cancelLoading = ref(false);

const confirmCancel = () => {
    cancelLoading.value = true;

    const form = useForm({});
    form.post(`/sales/${props.sale.id}/cancel`, {
        onSuccess: () => {
            showCancelModal.value = false;
        },
        onFinish: () => {
            cancelLoading.value = false;
        },
    });
};

const formatCurrency = (value: string | number): string => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return num.toFixed(2).replace('.', ',');
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const formatDateTime = (date: string): string => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

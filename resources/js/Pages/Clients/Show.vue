<template>
    <Head :title="`${client.name} - Cliente`" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ client.name }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Perfil do cliente</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/clients/${client.id}/edit`" class="inline-block">
                        <Button variant="primary">✎ Editar</Button>
                    </Link>
                    <Link href="/clients" class="inline-block">
                        <Button variant="secondary">← Voltar</Button>
                    </Link>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card title="Contato">
                    <div class="space-y-2">
                        <div v-if="client.email">
                            <p class="text-sm text-gray-600 dark:text-gray-400">E-mail</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ client.email }}</p>
                        </div>
                        <div v-if="client.phone">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Telefone</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ client.phone }}</p>
                        </div>
                        <div v-if="client.cpf_cnpj">
                            <p class="text-sm text-gray-600 dark:text-gray-400">CPF/CNPJ</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ formatCPFCNPJ(client.cpf_cnpj) }}
                            </p>
                        </div>
                        <div v-if="!client.email && !client.phone && !client.cpf_cnpj" class="text-sm text-gray-500">
                            Nenhuma informação adicional
                        </div>
                    </div>
                </Card>

                <!-- Account Status -->
                <Card title="Informações da Conta">
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Desde</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ formatDate(client.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total de Compras</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ stats?.sales_count || 0 }}
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Account Totals -->
                <Card title="Gasto Total">
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Gasto</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                             {{ formatCurrency(stats?.total_spent || 0) }}
                        </p>
                        <p v-if="stats?.last_sale" class="text-sm text-gray-600 dark:text-gray-400 mt-3">
                            Última compra: {{ formatDate(stats.last_sale.created_at) }}
                        </p>
                        <p v-else class="text-sm text-gray-500">Nenhuma compra registrada</p>
                    </div>
                </Card>
            </div>

            <!-- Balance Display -->
            <BalanceDisplay :balance="balance?.balance || 0" :credit-limit="balance?.credit_limit || 0" />

            <!-- Add Balance Form -->
            <AddBalanceForm :client-id="client.id" />

            <!-- Add Credit Item Form -->
            <AddCreditItemForm :client-id="client.id" />

            <!-- Recent Sales -->
            <Card title="Últimas Compras">
                <div v-if="recentSales && recentSales.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Venda
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Valor
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Data
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Ação
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="sale in recentSales"
                                :key="sale.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ sale.sale_number }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                     {{ formatCurrency(sale.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(sale.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            {
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                    sale.status === 'completed',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                    sale.status !== 'completed',
                                            },
                                        ]"
                                    >
                                        {{ sale.status === 'completed' ? 'OK' : 'Cancelada' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <Link
                                        :href="`/sales/${sale.id}`"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">Nenhuma compra registrada</div>
            </Card>

            <!-- Transaction History -->
            <Card title="Histórico de Movimentações">
                <div v-if="recentTransactions && recentTransactions.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Valor
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Saldo Anterior
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Saldo Novo
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Descrição
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Data
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium whitespace-nowrap',
                                            getTransactionTypeClass(transaction.type),
                                        ]"
                                    >
                                        {{ getTransactionTypeLabel(transaction.type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                     {{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                     {{ formatCurrency(transaction.balance_before) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                     {{ formatCurrency(transaction.balance_after) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ transaction.description }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(transaction.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">Nenhuma movimentação registrada</div>
            </Card>
        </div>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import AddBalanceForm from '@/Components/Clients/AddBalanceForm.vue';
import AddCreditItemForm from '@/Components/Clients/AddCreditItemForm.vue';
import BalanceDisplay from '@/Components/Clients/BalanceDisplay.vue';
import Button from '@/Components/Forms/Button.vue';
import Card from '@/Components/UI/Card.vue';
import {
    formatCurrency,
    formatDate,
} from '@/Utils/helpers';
import {
    Head,
    Link,
} from '@inertiajs/vue3';

interface Client {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    cpf_cnpj?: string;
    created_at: string;
}

interface ClientBalance {
    balance: number;
    credit_limit: number;
}

interface Sale {
    id: number;
    sale_number: string;
    client_id?: number;
    client?: {
        id: number;
        name: string;
    };
    total_amount: string;
    status: 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
}

interface Transaction {
    id: number;
    type: 'credit' | 'debit' | 'tab_debit' | 'tab_credit';
    amount: string;
    balance_before: string;
    balance_after: string;
    description: string;
    created_at: string;
}

interface Stats {
    total_spent: string;
    sales_count: number;
    last_sale?: {
        created_at: string;
    };
}

interface Props {
    client: Client;
    balance?: ClientBalance;
    recentSales?: Sale[];
    recentTransactions?: Transaction[];
    stats?: Stats;
}

defineProps<Props>();

const formatCPFCNPJ = (value?: string): string => {
    if (!value) return '';

    const cleaned = value.replace(/\D/g, '');

    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    if (cleaned.length === 14) {
        return cleaned.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }

    return value;
};

const getTransactionTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        credit: 'Crédito',
        debit: 'Débito',
        tab_debit: 'Caderneta ↑',
        tab_credit: 'Caderneta ↓',
    };
    return labels[type] || type;
};

const getTransactionTypeClass = (type: string): string => {
    const classes: Record<string, string> = {
        credit: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        debit: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        tab_debit: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        tab_credit: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};
</script>

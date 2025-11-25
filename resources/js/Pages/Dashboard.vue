<script setup lang="ts">
import Card from '@/Components/UI/Card.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import Table from '@/Components/UI/Table.vue';
import {
    formatCurrency,
    formatDate,
    formatDateFull,
} from '@/Utils/helpers';
import {
    Head,
    Link,
} from '@inertiajs/vue3';

interface Props {
    todayStats: {
        count: number;
        total: string;
        average: string;
        cash: string;
    };
    clientStats: {
        total: number;
        active: number;
        with_debt: number;
    };
    paymentStats: Array<{
        name: string;
        total: string;
    }>;
    recentSales: any[];
    topClients: any[];
}

defineProps<Props>();

const salesColumns = [
    { key: 'sale_number', label: 'Venda' },
    { key: 'client.name', label: 'Cliente' },
    { key: 'total', label: 'Total' },
    { key: 'created_at', label: 'Data' },
];

const clientColumns = [
    { key: 'name', label: 'Cliente' },
    { key: 'email', label: 'E-mail' },
    { key: 'total_spent', label: 'Gasto Total' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Bem-vindo de volta, {{ $page.props.auth.user?.name }}!
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatsCard
                    label="Vendas Hoje"
                    :value="todayStats.count"
                    type="number"
                    color="blue"
                    :subtext="`Total: R$ ${todayStats.total}`"
                />
                <StatsCard label="Receita Hoje" :value="todayStats.total" type="currency" color="green" />
                <StatsCard label="Ticket Médio" :value="todayStats.average" type="currency" color="yellow" />
                <StatsCard label="Clientes Ativos" :value="clientStats.active" type="number" color="purple" />
            </div>

            <!-- Charts and Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Monthly Revenue Chart -->
                <Card class="lg:col-span-2" title="Receita Mensal">
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        Gráfico de receita mensal (implementar com Chart.js)
                    </div>
                </Card>

                <!-- Payment Methods -->
                <Card title="Métodos de Pagamento">
                    <div class="space-y-3">
                        <div
                            v-for="method in paymentStats"
                            :key="method.name"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded"
                        >
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ method.name }}
                            </span>
                            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                R$ {{ method.total }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Recent Sales -->
            <Card title="Últimas Vendas">
                <Table v-if="recentSales.length > 0" :columns="salesColumns" :rows="recentSales">
                    <template #column-sale_number="{ row }">
                        <Link
                            :href="`/sales/${row.id}`"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                        >
                            {{ row.sale_number }}
                        </Link>
                    </template>

                    <template #column-total="{ row }"> {{ formatCurrency(row.total) }}</template>

                    <template #column-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <template #actions="{ row }">
                        <Link
                            :href="`/sales/${row.id}`"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Ver
                        </Link>
                    </template>
                </Table>
                <div v-else class="text-center py-8 text-gray-500">Nenhuma venda registrada hoje</div>
            </Card>

            <!-- Top Clients -->
            <Card title="Clientes Principais">
                <Table v-if="topClients.length > 0" :columns="clientColumns" :rows="topClients">
                    <template #column-total_spent="{ row }"> {{ formatCurrency(row.total_spent) }}</template>

                    <template #actions="{ row }">
                        <Link
                            :href="`/clients/${row.id}`"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Ver
                        </Link>
                    </template>
                </Table>
                <div v-else class="text-center py-8 text-gray-500">Nenhum cliente ainda</div>
            </Card>
        </div>
    </AdminAppLayout>
</template>

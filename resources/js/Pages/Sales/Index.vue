<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Vendas</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie todas as vendas registradas</p>
                </div>
                <Link href="/sales/create" class="inline-block">
                    <Button variant="primary" size="lg">+ Nova Venda</Button>
                </Link>
            </div>

            <!-- Search Card -->
            <Card title="Filtrar Vendas">
                <form @submit.prevent="search" class="flex gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="form.search"
                            label="Pesquisar"
                            placeholder="Número da venda ou cliente..."
                            type="text"
                        />
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" variant="primary">Filtrar</Button>
                    </div>
                </form>
            </Card>

            <!-- Sales Table -->
            <Card title="Vendas Registradas">
                <div v-if="sales.data && sales.data.length > 0" class="space-y-4">
                    <Table :columns="columns" :rows="sales.data">
                        <template #column-sale_number="{ row }">
                            <Link
                                :href="`/sales/${row.id}`"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                {{ row.sale_number }}
                            </Link>
                        </template>

                        <template #column-client.name="{ row }">
                            <span>{{ row.client?.name || 'Anônimo' }}</span>
                        </template>

                        <template #column-total="{ row }">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                R$ {{ formatCurrency(row.total) }}
                            </span>
                        </template>

                        <template #column-status="{ row }">
                            <span
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-medium',
                                    row.status === 'completed'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                ]"
                            >
                                {{ row.status === 'completed' ? 'Finalizada' : 'Cancelada' }}
                            </span>
                        </template>

                        <template #column-created_at="{ row }">
                            {{ formatDate(row.created_at) }}
                        </template>

                        <template #actions="{ row }">
                            <div class="flex gap-2">
                                <Link
                                    :href="`/sales/${row.id}`"
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Ver
                                </Link>
                                <button
                                    v-if="row.status === 'completed'"
                                    @click="openCancelModal(row)"
                                    class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </template>
                    </Table>

                    <!-- Pagination -->
                    <div v-if="sales.links" class="mt-6 flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Exibindo {{ sales.from }} a {{ sales.to }} de {{ sales.total }} vendas
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in sales.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 rounded text-sm',
                                    link.active
                                        ? 'bg-blue-600 text-white dark:bg-blue-500'
                                        : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                                :disabled="!link.url"
                            >
                                {{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">Nenhuma venda encontrada</div>
            </Card>
        </div>

        <!-- Cancel Sale Modal -->
        <Modal :open="showCancelModal" title="Cancelar Venda" @close="closeCancelModal">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Tem certeza que deseja cancelar a venda {{ selectedSale?.sale_number }}?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                Esta ação é irreversível e todas as transações serão estornadas.
            </p>

            <template #footer>
                <Button variant="secondary" @click="closeCancelModal">Não, manter</Button>
                <Button variant="danger" @click="confirmCancel" :disabled="cancelLoading">
                    {{ cancelLoading ? 'Cancelando...' : 'Sim, cancelar venda' }}
                </Button>
            </template>
        </Modal>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Components/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Modal from '@/Components/UI/Modal.vue';

interface Sale {
    id: number;
    sale_number: string;
    client_id?: number;
    client?: {
        id: number;
        name: string;
    };
    total: string;
    status: 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    data: Sale[];
    from: number;
    to: number;
    total: number;
    per_page: number;
    current_page: number;
    links: PaginationLink[];
}

interface Props {
    sales: PaginationData;
    filters: {
        search?: string;
    };
}

defineProps<Props>();

const columns = [
    { key: 'sale_number', label: 'Venda' },
    { key: 'client.name', label: 'Cliente' },
    { key: 'total', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Data' },
];

const form = useForm({
    search: '',
});

const showCancelModal = ref(false);
const selectedSale = ref<Sale | null>(null);
const cancelLoading = ref(false);

const search = () => {
    form.get('/sales', {
        preserveScroll: true,
    });
};

const openCancelModal = (sale: Sale) => {
    selectedSale.value = sale;
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    selectedSale.value = null;
};

const confirmCancel = async () => {
    if (!selectedSale.value) return;

    cancelLoading.value = true;

    const cancelForm = useForm({});
    cancelForm.post(`/sales/${selectedSale.value.id}/cancel`, {
        onSuccess: () => {
            closeCancelModal();
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
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Vendas" />

    <AdminAppLayout>
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
                <form @submit.prevent="search" class="flex flex-col md:flex-row gap-3 md:gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="form.search"
                            label="Pesquisar"
                            placeholder="Número da venda ou cliente..."
                            type="text"
                        />
                    </div>
                    <div class="flex items-end w-full md:w-auto">
                        <Button type="submit" variant="primary" class="w-full md:w-auto">Filtrar</Button>
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

                        <template #column-total_amount="{ row }">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                 {{ formatCurrency(row.total_amount) }}
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
                                    v-if="showCancelButton && row.status === 'completed'"
                                    @click="openCancelModal(row as Sale)"
                                    class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </template>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="sales.links"
                        class="mt-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Exibindo {{ sales.from }} a {{ sales.to }} de {{ sales.total }} vendas
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="link in sales.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 md:py-1 rounded text-sm font-medium',
                                    link.active
                                        ? 'bg-blue-600 text-white dark:bg-blue-500'
                                        : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                                :disabled="!link.url"
                                v-html="(link?.label || '').replace('&laquo;', '«').replace('&raquo;', '»')"
                            />
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
    </AdminAppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import Table from '@/Components/UI/Table.vue';
import {
    formatCurrency,
    formatDate,
    formatDateFull,
} from '@/Utils/helpers';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

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
    { key: 'total_amount', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Data' },
];

const form = useForm({
    search: '',
});

const showCancelButton = false; // Por hora não vamos mostrar até ter a lógica de adicionar como vaoucer/saldo para o cliente e se não for o anônimo
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
</script>

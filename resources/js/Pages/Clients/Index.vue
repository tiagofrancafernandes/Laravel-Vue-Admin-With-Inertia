<template>
    <Head title="Clientes" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Clientes</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie os clientes cadastrados</p>
                </div>
                <Link href="/clients/create" class="inline-block">
                    <Button variant="primary" size="lg">+ Novo Cliente</Button>
                </Link>
            </div>

            <!-- Search Card -->
            <Card title="Filtrar Clientes">
                <form @submit.prevent="search" class="flex flex-col md:flex-row gap-3 md:gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="form.search"
                            label="Pesquisar"
                            placeholder="Nome, e-mail ou telefone..."
                            type="text"
                        />
                    </div>
                    <div class="flex items-end w-full md:w-auto">
                        <Button type="submit" variant="primary" class="w-full md:w-auto">Filtrar</Button>
                    </div>
                </form>
            </Card>

            <!-- Clients Table -->
            <Card title="Clientes Cadastrados">
                <div v-if="clients.data && clients.data.length > 0" class="space-y-4">
                    <Table :columns="columns" :rows="clients.data">
                        <template #column-name="{ row }">
                            <Link
                                :href="`/clients/${row.id}`"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                {{ row.name }}
                            </Link>
                        </template>

                        <template #column-email="{ row }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ row.email || '—' }}
                            </span>
                        </template>

                        <template #column-phone="{ row }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ row.phone || '—' }}
                            </span>
                        </template>

                        <template #column-cpf_cnpj="{ row }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ formatCPFCNPJ(row.cpf_cnpj) || '—' }}
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <Link
                                :href="`/clients/${row.id}`"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Ver
                            </Link>
                        </template>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="clients.links"
                        class="mt-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Exibindo {{ clients.from }} a {{ clients.to }} de {{ clients.total }} clientes
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="link in clients.links"
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
                            >
                                {{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">Nenhum cliente encontrado</div>
            </Card>
        </div>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Client {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    cpf_cnpj?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    data: Client[];
    from: number;
    to: number;
    total: number;
    per_page: number;
    current_page: number;
    links: PaginationLink[];
}

interface Props {
    clients: PaginationData;
    filters: {
        search?: string;
    };
}

defineProps<Props>();

const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'email', label: 'E-mail' },
    { key: 'phone', label: 'Telefone' },
    { key: 'cpf_cnpj', label: 'CPF/CNPJ' },
];

const form = useForm({
    search: '',
});

const search = () => {
    form.get('/clients', {
        preserveScroll: true,
    });
};

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
</script>

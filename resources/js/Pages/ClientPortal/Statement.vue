<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Extrato Bancário</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="transactions.data.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold">Data</th>
                                        <th class="px-4 py-2 text-left font-semibold">Descrição</th>
                                        <th class="px-4 py-2 text-right font-semibold">Valor</th>
                                        <th class="px-4 py-2 text-right font-semibold">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="transaction in transactions.data"
                                        :key="transaction.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-4 py-2">{{ formatDate(transaction.created_at) }}</td>
                                        <td class="px-4 py-2">{{ transaction.description }}</td>
                                        <td class="px-4 py-2 text-right font-semibold">
                                            {{ formatCurrency(transaction.amount) }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-semibold">
                                            {{ formatCurrency(transaction.balance_after) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center text-gray-500 dark:text-gray-400 py-8">
                            <p>Nenhuma transação registrada</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="transactions.links" class="mt-6">
                            <nav class="flex justify-center gap-2">
                                <Link
                                    v-for="link in transactions.links"
                                    :key="link.url"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-1 rounded text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                              ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'
                                              : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>

                        <div class="mt-6">
                            <Link href="/client-portal" class="text-blue-600 dark:text-blue-400 hover:underline">
                                ← Voltar ao Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    transactions: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (date) => {
    return new Intl.DateTimeFormat('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(date));
};
</script>

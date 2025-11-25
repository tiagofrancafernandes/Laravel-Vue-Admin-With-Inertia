<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Portal do Cliente - Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Financial Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Current Balance Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Saldo Disponível</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                    </div>

                    <!-- Credit Limit Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Limite de Crédito</h3>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                {{ formatCurrency(creditLimit) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Due Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Devido</h3>
                            <p
                                :class="[
                                    'text-3xl font-bold',
                                    totalDue > 0
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-green-600 dark:text-green-400',
                                ]"
                            >
                                {{ formatCurrency(totalDue) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <Link
                        href="/client-portal/proof/submit"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Enviar Comprovante
                    </Link>
                    <Link
                        href="/client-portal/proofs"
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Histórico de Comprovantes
                    </Link>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Últimas Transações</h3>

                        <div v-if="recentTransactions.length > 0" class="overflow-x-auto">
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
                                        v-for="transaction in recentTransactions"
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

                        <div class="mt-4">
                            <Link
                                href="/client-portal/statement"
                                class="text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                Ver todas as transações →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    formatCurrency,
    formatDate,
} from '@/Utils/helpers';
import { Link } from '@inertiajs/vue3';

defineProps({
    balance: {
        type: Number,
        required: true,
    },
    creditLimit: {
        type: Number,
        required: true,
    },
    totalDue: {
        type: Number,
        required: true,
    },
    recentTransactions: {
        type: Array,
        required: true,
    },
});
</script>

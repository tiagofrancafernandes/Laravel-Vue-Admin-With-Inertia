<template>
    <div
        v-if="balance"
        class="rounded-lg border border-gray-200 bg-gray-50 p-4"
    >
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700">
                    Saldo Disponível
                </p>
                <p class="mt-1 text-2xl font-semibold text-green-600">
                    {{ formatCurrency(balance.balance_amount) }}
                </p>
            </div>

            <div v-if="balance.tab_amount > 0" class="flex-1 text-right">
                <p class="text-sm font-medium text-gray-700">Deve</p>
                <p class="mt-1 text-2xl font-semibold text-red-600">
                    {{ formatCurrency(balance.tab_amount) }}
                </p>
            </div>
        </div>

        <div v-if="loading" class="mt-2 text-xs text-gray-500">
            Atualizando...
        </div>
    </div>

    <div
        v-else-if="loading"
        class="rounded-lg border border-gray-200 bg-gray-50 p-4 animate-pulse"
    >
        <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
    </div>

    <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4">
        <p class="text-sm text-red-600">Erro ao carregar saldo: {{ error }}</p>
    </div>
</template>

<script setup>
import { watch, onMounted } from 'vue';
import { useClientBalance } from '@/Composables/useClientBalance';
import { formatCurrency } from '@/Utils/helpers';

const props = defineProps({
    clientId: {
        type: Number,
        required: true,
    },
});

const { balance, loading, error, fetchBalance } = useClientBalance();

onMounted(() => {
    if (props.clientId) {
        fetchBalance(props.clientId);
    }
});

watch(
    () => props.clientId,
    (newId) => {
        if (newId) {
            fetchBalance(newId);
        }
    }
);
</script>

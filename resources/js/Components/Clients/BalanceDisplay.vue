<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Saldo (Balance) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Saldo</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            R$ {{ formatCurrency(balance) }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Saldo pré-pago disponível
          </p>
        </div>
        <div class="text-4xl">💰</div>
      </div>
    </div>

    <!-- Caderneta (Credit) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Caderneta (Crédito)</p>
          <p :class="[
            'text-2xl font-bold',
            creditLimit > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'
          ]">
            R$ {{ formatCurrency(creditLimit) }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            {{ creditLimit > 0 ? 'Débito pendente' : 'Sem débitos' }}
          </p>
        </div>
        <div class="text-4xl">📔</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  balance: number;
  creditLimit: number;
}

defineProps<Props>();

const formatCurrency = (value: number): string => {
  return value.toFixed(2).replace(".", ",");
};
</script>

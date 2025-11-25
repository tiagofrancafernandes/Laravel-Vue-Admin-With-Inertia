<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Gerenciar Comprovantes
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="proofs.data.length > 0" class="space-y-4">
          <div
            v-for="proof in proofs.data"
            :key="proof.id"
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
          >
            <div class="p-6 text-gray-900 dark:text-gray-100">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h3 class="text-lg font-semibold">
                    Cliente #{{ proof.client_id }}
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDate(proof.created_at) }}
                  </p>
                </div>
                <span :class="[
                  'px-3 py-1 rounded-full text-sm font-semibold',
                  proof.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '',
                  proof.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '',
                  proof.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ''
                ]">
                  {{ getStatusLabel(proof.status) }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Tipo</p>
                  <p class="text-lg font-semibold">
                    {{ proof.type === 'deposit' ? 'Depósito' : 'Pagamento' }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Valor</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(proof.amount) }}</p>
                </div>
              </div>

              <div class="flex gap-4">
                <button
                  @click="approveProof(proof.id)"
                  class="text-green-600 dark:text-green-400 hover:underline"
                >
                  Aprovar
                </button>
                <button
                  @click="rejectProof(proof.id)"
                  class="text-red-600 dark:text-red-400 hover:underline"
                >
                  Rejeitar
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-center text-gray-500 dark:text-gray-400">
            <p>Nenhum comprovante para revisar</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  proofs: {
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

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pendente',
    approved: 'Aprovado',
    rejected: 'Rejeitado',
  };
  return labels[status] || status;
};

const approveProof = async (proofId) => {
  // Implement approval logic
};

const rejectProof = async (proofId) => {
  // Implement rejection logic
};
</script>

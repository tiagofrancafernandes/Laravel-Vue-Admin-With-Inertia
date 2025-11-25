<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Histórico de Comprovantes
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-end mb-6">
          <Link
            href="/client-portal/proof/submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            + Enviar Novo Comprovante
          </Link>
        </div>

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
                    {{ proof.type === 'deposit' ? 'Depósito' : 'Pagamento' }}
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
                  <p class="text-sm text-gray-500 dark:text-gray-400">Valor</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(proof.amount) }}</p>
                </div>
                <div v-if="proof.sale_id">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Venda ID</p>
                  <p class="text-lg font-semibold">#{{ proof.sale_id }}</p>
                </div>
              </div>

              <div v-if="proof.notes" class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                  Nota do Administrador:
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ proof.notes }}</p>
              </div>

              <div class="flex gap-4">
                <button
                  @click="downloadProof(proof.id)"
                  class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                  Baixar Arquivo
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="proofs.links" class="flex justify-center gap-2 mt-6">
            <Link
              v-for="link in proofs.links"
              :key="link.url"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1 rounded text-sm',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                  ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </div>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-center text-gray-500 dark:text-gray-400">
            <p class="mb-4">Você ainda não enviou nenhum comprovante.</p>
            <Link
              href="/client-portal/proof/submit"
              class="text-blue-600 dark:text-blue-400 hover:underline"
            >
              Enviar primeiro comprovante →
            </Link>
          </div>
        </div>

        <div class="mt-6">
          <Link
            href="/client-portal"
            class="text-blue-600 dark:text-blue-400 hover:underline"
          >
            ← Voltar ao Dashboard
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
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

const downloadProof = async (proofId) => {
  try {
    const response = await fetch(`/client-portal/proof/${proofId}/download`);
    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `comprovante-${proofId}`;
      document.body.appendChild(link);
      link.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(link);
    }
  } catch (error) {
    console.error('Error downloading proof:', error);
  }
};
</script>

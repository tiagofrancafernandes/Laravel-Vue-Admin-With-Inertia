<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatCurrency } from '@/Utils/helpers';

defineProps({
    proof: Object,
});

const approveNotes = ref('');
const rejectNotes = ref('');
const approvingInProgress = ref(false);
const rejectingInProgress = ref(false);
const activeTab = ref('details');

const getStatusBadge = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    };

    const labels = {
        pending: 'Pendente',
        approved: 'Aprovado',
        rejected: 'Rejeitado',
    };

    return {
        class: colors[status] || 'bg-gray-100 text-gray-800',
        label: labels[status] || status,
    };
};

const getTypeLabel = (type) => {
    return type === 'deposit' ? 'Depósito' : 'Pagamento';
};

const getTypeIcon = (type) => {
    if (type === 'deposit') {
        return 'M12 4v16m8-8H4'; // Plus icon
    } else {
        return 'M12 4v16m8-8H4'; // Minus icon
    }
};

const approveProof = () => {
    approvingInProgress.value = true;
    router.post(route('admin.proofs.approve', proof.id), {
        notes: approveNotes.value,
    });
};

const rejectProof = () => {
    if (!rejectNotes.value.trim()) {
        alert('Por favor, adicione uma nota de rejeição');
        return;
    }
    rejectingInProgress.value = true;
    router.post(route('admin.proofs.reject', proof.id), {
        notes: rejectNotes.value,
    });
};
</script>

<template>
    <Head :title="`Admin - Comprovante #${proof.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Comprovante #{{ proof.id }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Revisar e gerenciar comprovante de cliente
                    </p>
                </div>
                <Link
                    :href="route('admin.proofs.index')"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
                >
                    ← Voltar
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Status and Quick Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Status</p>
                    <div class="mt-2">
                        <span :class="['inline-block px-3 py-1 rounded-full text-xs font-medium', getStatusBadge(proof.status).class]">
                            {{ getStatusBadge(proof.status).label }}
                        </span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Tipo</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-2">
                        {{ getTypeLabel(proof.type) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Valor</p>
                    <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-2">
                        {{ formatCurrency(proof.amount) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Data Enviada</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-2">
                        {{ proof.created_at }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- File Preview -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Arquivo
                        </h3>

                        <div v-if="proof.file_available" class="space-y-4">
                            <!-- File info -->
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                    <svg
                                        v-if="proof.file_path.endsWith('.pdf')"
                                        class="w-6 h-6 text-red-600 dark:text-red-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-6 h-6 text-blue-600 dark:text-blue-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ proof.file_path.split('/').pop() }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ proof.file_path.endsWith('.pdf') ? 'Arquivo PDF' : 'Imagem' }}
                                    </p>
                                </div>
                                <a
                                    :href="route('admin.proofs.download', proof.id)"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors whitespace-nowrap"
                                >
                                    ⬇ Baixar
                                </a>
                            </div>

                            <!-- Image preview for images -->
                            <div
                                v-if="!proof.file_path.endsWith('.pdf')"
                                class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                            >
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Preview:
                                </p>
                                <div class="max-h-96 flex items-center justify-center">
                                    <img
                                        :src="route('admin.proofs.download', proof.id)"
                                        alt="Proof preview"
                                        class="max-w-full max-h-96 rounded-lg"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-else class="flex items-center justify-center p-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-gray-600 dark:text-gray-400">
                                Arquivo não disponível
                            </p>
                        </div>
                    </div>

                    <!-- Proof Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Detalhes do Comprovante
                        </h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                        Tipo
                                    </p>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ getTypeLabel(proof.type) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                        Valor
                                    </p>
                                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(proof.amount) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                        Enviado em
                                    </p>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ proof.created_at }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                        Última atualização
                                    </p>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ proof.updated_at }}
                                    </p>
                                </div>
                            </div>

                            <!-- Sale info if available -->
                            <div v-if="proof.sale" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-2">
                                    Venda Relacionada
                                </p>
                                <Link
                                    :href="route('sales.show', proof.sale.id)"
                                    class="block p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        Venda #{{ proof.sale.code }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Valor: {{ formatCurrency(proof.sale.total_amount) }}
                                    </p>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Client Info and Actions -->
                <div class="space-y-6">
                    <!-- Client Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Informações do Cliente
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                    Nome
                                </p>
                                <Link
                                    :href="route('clients.show', proof.client.id)"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                                >
                                    {{ proof.client.name }}
                                </Link>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                    Email
                                </p>
                                <a
                                    :href="`mailto:${proof.client.email}`"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium break-all"
                                >
                                    {{ proof.client.email }}
                                </a>
                            </div>

                            <div v-if="proof.client.phone">
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                    Telefone
                                </p>
                                <a
                                    :href="`tel:${proof.client.phone}`"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                                >
                                    {{ proof.client.phone }}
                                </a>
                            </div>

                            <a
                                :href="route('clients.show', proof.client.id)"
                                class="block mt-4 px-4 py-2 text-center bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-900/50 font-medium transition-colors"
                            >
                                Ver Perfil do Cliente
                            </a>
                        </div>
                    </div>

                    <!-- Admin Action / History -->
                    <div v-if="proof.status !== 'pending'" class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Histórico da Ação
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                    Revisor
                                </p>
                                <p class="text-gray-900 dark:text-gray-100 font-medium">
                                    {{ proof.admin.name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">
                                    Ação
                                </p>
                                <p :class="['inline-block px-3 py-1 rounded-full text-xs font-medium', getStatusBadge(proof.status).class]">
                                    {{ getStatusBadge(proof.status).label }}
                                </p>
                            </div>

                            <div v-if="proof.notes" class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-2">
                                    Observações
                                </p>
                                <p class="text-gray-900 dark:text-gray-100 text-sm">
                                    {{ proof.notes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons (if pending) -->
                    <div v-if="proof.status === 'pending'" class="space-y-4">
                        <!-- Approve Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Aprovar Comprovante
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Observações (Opcional)
                                    </label>
                                    <textarea
                                        v-model="approveNotes"
                                        rows="3"
                                        placeholder="Adicione uma nota sobre a aprovação..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                                    />
                                </div>

                                <button
                                    @click="approveProof"
                                    :disabled="approvingInProgress"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
                                >
                                    {{ approvingInProgress ? 'Aprovando...' : '✓ Aprovar Comprovante' }}
                                </button>
                            </div>
                        </div>

                        <!-- Reject Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Rejeitar Comprovante
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Motivo da Rejeição <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="rejectNotes"
                                        rows="3"
                                        placeholder="Explique por que está rejeitando este comprovante..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                                    />
                                </div>

                                <button
                                    @click="rejectProof"
                                    :disabled="rejectingInProgress"
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
                                >
                                    {{ rejectingInProgress ? 'Rejeitando...' : '✕ Rejeitar Comprovante' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

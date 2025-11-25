<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Enviar Comprovante
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submitProof">
              <!-- Type Selection -->
              <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tipo de Comprovante <span class="text-red-500">*</span>
                </label>
                <select
                  id="type"
                  v-model="form.type"
                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                  <option value="">Selecione um tipo</option>
                  <option value="deposit">Depósito</option>
                  <option value="payment">Pagamento</option>
                </select>
                <InputError :message="errors.type" class="mt-2" />
              </div>

              <!-- Amount -->
              <div class="mb-6">
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Valor do Comprovante <span class="text-red-500">*</span>
                </label>
                <input
                  id="amount"
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0,00"
                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <InputError :message="errors.amount" class="mt-2" />
              </div>

              <!-- File Upload -->
              <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Arquivo (JPEG, PNG, PDF - máx 5MB) <span class="text-red-500">*</span>
                </label>
                <input
                  id="file"
                  ref="fileInput"
                  type="file"
                  accept=".jpg,.jpeg,.png,.pdf"
                  class="mt-1 block w-full"
                  @change="onFileSelected"
                />
                <InputError :message="errors.file" class="mt-2" />

                <!-- File Preview -->
                <div v-if="filePreview" class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    Arquivo selecionado: <strong>{{ filePreview }}</strong>
                  </p>
                </div>
              </div>

              <!-- Sale ID (Optional) -->
              <div class="mb-6">
                <label for="sale_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  ID da Venda (Opcional)
                </label>
                <input
                  id="sale_id"
                  v-model="form.sale_id"
                  type="text"
                  placeholder="ID da venda"
                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <InputError :message="errors.sale_id" class="mt-2" />
              </div>

              <!-- Description (Optional) -->
              <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Descrição (Opcional)
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  placeholder="Adicione detalhes sobre o comprovante..."
                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <InputError :message="errors.description" class="mt-2" />
              </div>

              <!-- Buttons -->
              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="processing"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ processing ? 'Enviando...' : 'Enviar Comprovante' }}
                </button>
                <Link
                  href="/client-portal"
                  class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-700"
                >
                  Cancelar
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const fileInput = ref(null);
const filePreview = ref('');
const processing = ref(false);

const form = ref({
  type: '',
  amount: '',
  file: null,
  sale_id: '',
  description: '',
});

const errors = ref({});

const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.value.file = file;
    filePreview.value = file.name;
  }
};

const submitProof = async () => {
  processing.value = true;
  errors.value = {};

  const formData = new FormData();
  formData.append('type', form.value.type);
  formData.append('amount', form.value.amount);
  if (form.value.file) {
    formData.append('file', form.value.file);
  }
  if (form.value.sale_id) {
    formData.append('sale_id', form.value.sale_id);
  }
  if (form.value.description) {
    formData.append('description', form.value.description);
  }

  try {
    const response = await fetch('/client-portal/proof/submit', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      const data = await response.json();
      if (data.errors) {
        errors.value = data.errors;
      }
      return;
    }

    const data = await response.json();
    if (data.success) {
      router.visit('/client-portal/proofs', { preserveState: false });
    }
  } catch (error) {
    console.error('Error:', error);
    errors.value.general = 'Erro ao enviar comprovante. Tente novamente.';
  } finally {
    processing.value = false;
  }
};
</script>

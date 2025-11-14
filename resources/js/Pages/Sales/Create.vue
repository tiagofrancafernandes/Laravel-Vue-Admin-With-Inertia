<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Nova Venda</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Registre uma nova transação</p>
        </div>
        <Link href="/sales" class="inline-block">
          <Button variant="secondary">← Voltar</Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Client Selection -->
        <Card title="Cliente">
          <ClientSelect
            v-model="form.client_id"
            :error="form.errors.client_id"
            required
          />
        </Card>

        <!-- Items Section -->
        <Card title="Itens">
          <div class="space-y-4">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3"
            >
              <div class="flex justify-between items-start">
                <h4 class="font-medium text-gray-900 dark:text-gray-100">Item {{ index + 1 }}</h4>
                <button
                  v-if="form.items.length > 1"
                  type="button"
                  @click="removeItem(index)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm"
                >
                  Remover
                </button>
              </div>

              <Input
                v-model="item.description"
                label="Descrição"
                placeholder="Ex: Produto XYZ"
                required
              />

              <div class="grid grid-cols-3 gap-4">
                <Input
                  v-model="item.quantity"
                  label="Quantidade"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0"
                  required
                />
                <Input
                  v-model="item.price"
                  label="Valor Unitário"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  required
                />
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Subtotal
                  </label>
                  <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-900 dark:text-gray-100 font-medium">
                    R$ {{ formatCurrency(item.quantity * item.price) }}
                  </div>
                </div>
              </div>
            </div>

            <Button
              type="button"
              variant="secondary"
              @click="addItem"
            >
              + Adicionar Item
            </Button>
          </div>
        </Card>

        <!-- Totals Section -->
        <Card title="Valores">
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">
                R$ {{ formatCurrency(subtotal) }}
              </span>
            </div>

            <Input
              v-model.number="form.discount"
              label="Desconto"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
            />

            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between items-center">
              <span class="font-semibold text-gray-900 dark:text-gray-100">Total</span>
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                R$ {{ formatCurrency(total) }}
              </span>
            </div>
          </div>
        </Card>

        <!-- Payment Methods -->
        <Card title="Formas de Pagamento">
          <PaymentMethodSelector
            v-model="form.payments"
            :payment-methods="paymentMethods"
            :error="form.errors.payments"
          />
          <div v-if="paymentTotal !== total && form.payments.length > 0" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-red-800 dark:text-red-200 text-sm">
              ⚠️ Soma dos pagamentos (R$ {{ formatCurrency(paymentTotal) }}) não corresponde ao total (R$ {{ formatCurrency(total) }})
            </p>
          </div>
          <div v-else-if="form.payments.length > 0" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-green-800 dark:text-green-200 text-sm">
              ✓ Pagamentos conferem: R$ {{ formatCurrency(paymentTotal) }}
            </p>
          </div>
        </Card>

        <!-- Form Actions -->
        <div class="flex gap-4">
          <Link href="/sales" class="flex-1">
            <Button type="button" variant="secondary" class="w-full">
              Cancelar
            </Button>
          </Link>
          <Button
            type="submit"
            variant="primary"
            class="flex-1"
            :disabled="form.processing || paymentTotal !== total"
          >
            {{ form.processing ? 'Registrando...' : 'Registrar Venda' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Components/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import ClientSelect from '@/Components/Clients/ClientSelect.vue';
import PaymentMethodSelector from '@/Components/Sales/PaymentMethodSelector.vue';

interface PaymentMethod {
  id: number;
  name: string;
  code: string;
  description?: string;
  is_active: boolean;
}

interface Item {
  description: string;
  quantity: number;
  price: number;
}

interface Payment {
  method_id: number;
  amount: number;
}

interface Props {
  paymentMethods: PaymentMethod[];
  anonymousClientId: number;
}

const props = defineProps<Props>();

const form = useForm({
  client_id: null as number | null,
  items: [{ description: '', quantity: 1, price: 0 }] as Item[],
  discount: 0,
  payments: [] as Payment[],
});

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
});

const total = computed(() => {
  return Math.max(0, subtotal.value - form.discount);
});

const paymentTotal = computed(() => {
  return form.payments.reduce((sum, payment) => sum + payment.amount, 0);
});

const addItem = () => {
  form.items.push({ description: '', quantity: 1, price: 0 });
};

const removeItem = (index: number) => {
  form.items.splice(index, 1);
};

const formatCurrency = (value: number): string => {
  return value.toFixed(2).replace('.', ',');
};

const submitForm = () => {
  form.post('/sales', {
    onSuccess: () => {
      // Redirect handled by Inertia
    },
  });
};
</script>

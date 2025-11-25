<template>
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Selecione o(s) Método(s) de Pagamento
            <span class="text-red-500">*</span>
        </label>

        <div class="space-y-4">
            <div
                v-for="method in paymentMethods"
                :key="method.id"
                class="flex flex-col md:flex-row md:items-start gap-3"
            >
                <div class="flex items-start flex-1">
                    <input
                        :id="`method-${method.id}`"
                        type="checkbox"
                        :value="method.id"
                        :checked="isSelected(method.id)"
                        @change="toggleMethod(method.id)"
                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label :for="`method-${method.id}`" class="ml-3 flex-1 cursor-pointer">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ method.name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ method.description }}
                        </div>
                    </label>
                </div>

                <!-- Payment input for selected methods -->
                <div v-if="isSelected(method.id)" class="w-full md:w-40">
                    <input
                        :value="getPaymentAmount(method.id)"
                        @input="setPaymentAmount(method.id, $event.target.value)"
                        type="number"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        :class="[
                            'w-full px-3 py-2 border rounded text-right',
                            'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                            'border-gray-300 dark:border-gray-600 focus:ring-blue-500',
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Special handling for change -->
        <div
            v-if="shouldShowChangeOptions()"
            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3"
        >
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Opções para troco:
            </p>
            <label class="flex items-center space-x-2">
                <input
                    v-model="addChangeAsBalance"
                    type="checkbox"
                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Adicionar troco como saldo do cliente</span>
            </label>
            <label class="flex items-center space-x-2">
                <input
                    v-model="useChangeForCredit"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Usar troco para quitar crédito do cliente</span>
            </label>
        </div>

        <div v-if="error" class="mt-2 text-sm text-red-500 dark:text-red-400">
            {{ error }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
    description?: string;
}

interface Props {
    paymentMethods: PaymentMethod[];
    modelValue: Array<any>;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: Array<any>];
}>();

const selectedMethods = ref(new Set<number>());
const paymentAmounts = ref(new Map<number, number>());
const addChangeAsBalance = ref(false);
const useChangeForCredit = ref(false);

const isSelected = (methodId: number): boolean => {
    return selectedMethods.value.has(methodId);
};

const toggleMethod = (methodId: number) => {
    if (selectedMethods.value.has(methodId)) {
        selectedMethods.value.delete(methodId);
        paymentAmounts.value.delete(methodId);
    } else {
        selectedMethods.value.add(methodId);
        paymentAmounts.value.set(methodId, 0);
    }
    emitUpdate();
};

const getPaymentAmount = (methodId: number): number => {
    return paymentAmounts.value.get(methodId) || 0;
};

const setPaymentAmount = (methodId: number, value: string) => {
    const amount = parseFloat(value) || 0;
    paymentAmounts.value.set(methodId, amount);
    emitUpdate();
};

const getCashMethodId = (): number => {
    return props.paymentMethods.find((m) => m.code === 'cash')?.id || 0;
};

const shouldShowChangeOptions = (): boolean => {
    // Show change options when cash method is selected
    return selectedMethods.value.has(getCashMethodId());
};

const emitUpdate = () => {
    const payments = Array.from(selectedMethods.value).map((methodId) => ({
        payment_method_id: methodId,
        amount: paymentAmounts.value.get(methodId) || 0,
        add_change_as_balance: addChangeAsBalance.value,
        use_change_for_credit: useChangeForCredit.value,
    }));
    emit('update:modelValue', payments);
};

// Watch for changes in the checkboxes
watch([addChangeAsBalance, useChangeForCredit], () => {
    emitUpdate();
});
</script>

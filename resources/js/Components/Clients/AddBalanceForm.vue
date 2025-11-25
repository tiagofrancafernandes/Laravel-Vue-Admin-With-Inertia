<template>
    <Card title="Adicionar Saldo">
        <form @submit.prevent="submitForm" class="space-y-4">
            <Input
                v-model.number="form.amount"
                label="Valor a Adicionar"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                :error="form.errors.amount"
                required
            />

            <Input
                v-model="form.description"
                label="Descrição (Opcional)"
                type="text"
                placeholder="Ex: Depósito em conta"
                :error="form.errors.description"
            />

            <div class="flex gap-3">
                <Button
                    type="submit"
                    variant="primary"
                    :disabled="form.processing"
                    class="flex-1"
                >
                    {{ form.processing ? 'Adicionando...' : 'Adicionar Saldo' }}
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    @click="resetForm"
                    :disabled="form.processing"
                    class="flex-1"
                >
                    Limpar
                </Button>
            </div>

            <div v-if="successMessage" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-green-800 dark:text-green-200 text-sm">{{ successMessage }}</p>
            </div>
        </form>
    </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Card from '@/Components/UI/Card.vue';

interface Props {
    clientId: number;
}

const props = defineProps<Props>();

const successMessage = ref('');

const form = useForm({
    amount: null as number | null,
    description: '',
});

const submitForm = () => {
    successMessage.value = '';

    form.post(route('clients.add-balance', props.clientId), {
        onSuccess: () => {
            successMessage.value = 'Saldo adicionado com sucesso!';
            resetForm();
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        },
    });
};

const resetForm = () => {
    form.reset();
    successMessage.value = '';
};
</script>

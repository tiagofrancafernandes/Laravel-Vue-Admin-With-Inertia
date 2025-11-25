<template>
    <Head title="Editar Cliente" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Editar Cliente</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ client.name }}</p>
                </div>
                <Link :href="`/clients/${client.id}`" class="inline-block">
                    <Button variant="secondary">← Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6 max-w-2xl">
                <!-- Personal Information -->
                <Card title="Dados Pessoais">
                    <div class="space-y-4">
                        <Input
                            v-model="form.name"
                            label="Nome Completo"
                            placeholder="Ex: João Silva"
                            :error="form.errors.name"
                            required
                        />

                        <Input
                            v-model="form.email"
                            label="E-mail"
                            type="email"
                            placeholder="joao@email.com"
                            :error="form.errors.email"
                        />

                        <Input
                            v-model="form.phone"
                            label="Telefone"
                            placeholder="(11) 9999-9999"
                            :error="form.errors.phone"
                        />
                    </div>
                </Card>

                <!-- Document Information -->
                <Card title="Identificação">
                    <div class="space-y-4">
                        <Input
                            v-model="form.cpf_cnpj"
                            label="CPF ou CNPJ"
                            placeholder="123.456.789-10 ou 12.345.678/0001-90"
                            :error="form.errors.cpf_cnpj"
                        />
                    </div>
                </Card>

                <!-- Credit Limit -->
                <Card title="Limite de Crédito">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Limite de Crédito Disponível
                            </label>
                            <div class="flex gap-2">
                                <Input
                                    v-model.number="creditForm.credit_limit"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    :error="creditForm.errors.credit_limit"
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    @click="updateCreditLimit"
                                    :disabled="creditForm.processing"
                                    variant="primary"
                                >
                                    {{ creditForm.processing ? 'Atualizando...' : 'Atualizar' }}
                                </Button>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Limite de crédito (caderneta) disponível para este cliente
                            </p>
                        </div>

                        <div v-if="client.balance" class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Saldo Atual</p>
                                <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                                    R$ {{ formatCurrency(client.balance.balance) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Limite Total</p>
                                <p class="text-lg font-semibold text-blue-600 dark:text-blue-400 mt-1">
                                    R$ {{ formatCurrency(client.balance.credit_limit) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Form Actions -->
                <div class="flex gap-4">
                    <Link :href="`/clients/${client.id}`" class="flex-1">
                        <Button type="button" variant="secondary" class="w-full">Cancelar</Button>
                    </Link>
                    <Button type="submit" variant="primary" class="flex-1" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import AdminAppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Card from '@/Components/UI/Card.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface ClientData {
    id: number;
    name: string;
    email: string;
    phone: string;
    cpf_cnpj: string;
    balance?: {
        balance: number;
        credit_limit: number;
    };
}

interface Props {
    client: ClientData;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    phone: props.client.phone,
    cpf_cnpj: props.client.cpf_cnpj,
});

const creditForm = useForm({
    credit_limit: props.client.balance?.credit_limit || 0,
});

const formatCurrency = (value: number | string): string => {
    const numValue = typeof value === 'string' ? parseFloat(value) : value;
    return numValue.toFixed(2).replace('.', ',');
};

const submitForm = () => {
    form.put(`/clients/${props.client.id}`, {
        onSuccess: () => {
            // Redirect handled by Inertia
        },
    });
};

const updateCreditLimit = () => {
    creditForm.put(`/clients/${props.client.id}/credit-limit`);
};
</script>

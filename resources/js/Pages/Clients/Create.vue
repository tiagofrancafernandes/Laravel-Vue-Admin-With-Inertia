<template>
    <Head title="Novo Cliente" />

    <AdminAppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Novo Cliente</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Cadastre um novo cliente</p>
                </div>
                <Link href="/clients" class="inline-block">
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                CPF ou CNPJ
                            </label>
                            <div class="flex gap-2">
                                <Input
                                    v-model="form.cpf_cnpj"
                                    label=""
                                    placeholder="123.456.789-10 ou 12.345.678/0001-90"
                                    :error="form.errors.cpf_cnpj"
                                    class="flex-1"
                                />
                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        @click="validateDocument"
                                        class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Validar
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="documentValidation"
                                :class="[
                                    'mt-2 p-2 rounded text-sm',
                                    documentValidation.valid
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                                        : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200',
                                ]"
                            >
                                {{ documentValidation.message }}
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Initial Balance -->
                <Card title="Saldo Inicial">
                    <Input
                        v-model.number="form.initial_balance"
                        label="Saldo Pré-pago Inicial"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        :error="form.errors.initial_balance"
                    />
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Deixe em branco ou zero para não adicionar saldo inicial
                    </p>
                </Card>

                <!-- Form Actions -->
                <div class="flex gap-4">
                    <Link href="/clients" class="flex-1">
                        <Button type="button" variant="secondary" class="w-full">Cancelar</Button>
                    </Link>
                    <Button type="submit" variant="primary" class="flex-1" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar Cliente' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminAppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import Button from '@/Components/Forms/Button.vue';
import Input from '@/Components/Forms/Input.vue';
import Card from '@/Components/UI/Card.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface DocumentValidation {
    valid: boolean;
    message: string;
}

const form = useForm({
    name: '',
    email: '',
    phone: '',
    cpf_cnpj: '',
    initial_balance: 0,
});

const documentValidation = ref<DocumentValidation | null>(null);

const validateDocument = () => {
    const value = form.cpf_cnpj.replace(/\D/g, '');

    if (!value) {
        documentValidation.value = {
            valid: false,
            message: 'Digite um CPF ou CNPJ válido',
        };
        return;
    }

    if (value.length === 11) {
        const isValid = validateCPF(value);
        documentValidation.value = {
            valid: isValid,
            message: isValid ? '✓ CPF válido' : '✗ CPF inválido',
        };
    } else if (value.length === 14) {
        const isValid = validateCNPJ(value);
        documentValidation.value = {
            valid: isValid,
            message: isValid ? '✓ CNPJ válido' : '✗ CNPJ inválido',
        };
    } else {
        documentValidation.value = {
            valid: false,
            message: 'CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos',
        };
    }
};

const validateCPF = (cpf: string): boolean => {
    // Rejeitar sequências iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    // Validar primeiro dígito
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf[i]) * (10 - i);
    }
    let digit1 = 11 - (sum % 11);
    digit1 = digit1 > 9 ? 0 : digit1;

    if (parseInt(cpf[9]) !== digit1) return false;

    // Validar segundo dígito
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf[i]) * (11 - i);
    }
    let digit2 = 11 - (sum % 11);
    digit2 = digit2 > 9 ? 0 : digit2;

    return parseInt(cpf[10]) === digit2;
};

const validateCNPJ = (cnpj: string): boolean => {
    // Rejeitar sequências iguais
    if (/^(\d)\1{13}$/.test(cnpj)) return false;

    // Validar primeiro dígito
    let sum = 0;
    const weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    for (let i = 0; i < 12; i++) {
        sum += parseInt(cnpj[i]) * weights1[i];
    }

    let digit1 = 11 - (sum % 11);
    digit1 = digit1 > 9 ? 0 : digit1;

    if (parseInt(cnpj[12]) !== digit1) return false;

    // Validar segundo dígito
    sum = 0;
    const weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    for (let i = 0; i < 13; i++) {
        sum += parseInt(cnpj[i]) * weights2[i];
    }

    let digit2 = 11 - (sum % 11);
    digit2 = digit2 > 9 ? 0 : digit2;

    return parseInt(cnpj[13]) === digit2;
};

const submitForm = () => {
    form.post('/clients', {
        onSuccess: () => {
            // Redirect handled by Inertia
        },
    });
};
</script>

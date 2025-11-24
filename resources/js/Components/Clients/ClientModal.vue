<template>
    <Modal :show="modelValue" @close="close" max-width="md">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Novo Cliente</h3>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        :error="form.errors.name"
                        placeholder="Nome do cliente"
                    />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :error="form.errors.email"
                        placeholder="email@exemplo.com"
                    />
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <Input id="phone" v-model="form.phone" :error="form.errors.phone" placeholder="(11) 98765-4321" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <Button type="button" variant="secondary" @click="close">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" :loading="form.processing">Salvar</Button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Common/Modal.vue';
import Input from '@/Components/Common/Input.vue';
import Button from '@/Components/Common/Button.vue';

const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'created']);

const form = ref({
    name: '',
    email: '',
    phone: '',
});

const errors = ref({});
const loading = ref(false);

const submit = async () => {
    errors.value = {};
    loading.value = true;

    try {
        const response = await axios.post(route('clients.store'), form.value, {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (response.data.success) {
            // Emite o cliente criado
            emit('created', response.data.client);
            resetForm();
            close();
        }
    } catch (error) {
        if (error.response?.status === 422) {
            // Validation errors
            errors.value = error.response.data.errors || {};
        } else {
            errors.value.general = error.response?.data?.message || 'Erro ao criar cliente';
        }
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        phone: '',
    };
    errors.value = {};
};

const close = () => {
    resetForm();
    emit('update:modelValue', false);
};
</script>

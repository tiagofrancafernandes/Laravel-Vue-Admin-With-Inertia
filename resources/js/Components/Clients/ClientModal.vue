<template>
    <Modal :show="modelValue" @close="close" max-width="md">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                Novo Cliente
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Nome *
                    </label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        :error="form.errors.name"
                        placeholder="Nome do cliente"
                    />
                </div>

                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Email
                    </label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :error="form.errors.email"
                        placeholder="email@exemplo.com"
                    />
                </div>

                <div>
                    <label
                        for="phone"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Telefone
                    </label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        :error="form.errors.phone"
                        placeholder="(11) 98765-4321"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="close"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        :loading="form.processing"
                    >
                        Salvar
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import Input from '@/Components/Common/Input.vue';
import Button from '@/Components/Common/Button.vue';

const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'created']);

const form = useForm({
    name: '',
    email: '',
    phone: '',
});

const submit = () => {
    form.post(route('clients.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Emite o cliente criado
            const client = page.props.flash?.client || null;
            if (client) {
                emit('created', client);
            }
            form.reset();
            close();
        },
    });
};

const close = () => {
    form.reset();
    emit('update:modelValue', false);
};
</script>

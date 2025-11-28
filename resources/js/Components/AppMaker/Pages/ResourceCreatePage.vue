<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

import AppMakerForm from '../Form/Form.vue';

const props = defineProps({
    resource: String,
    resourceConfig: Object,
    form: Object,
});

const formData = useForm({});

// Initialize form data with defaults
props.form.schema.forEach((field) => {
    formData[field.name] = field.default ?? null;
});

function handleSubmit() {
    formData.post(route(`${props.resource}.store`), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #headerActions>
            <!-- actions here -->
        </template>

        <template #headerTitle>Create {{ resourceConfig.singularLabel }}</template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <AppMakerForm :form="form" :form-data="formData" :resource="resource" @submit="handleSubmit" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

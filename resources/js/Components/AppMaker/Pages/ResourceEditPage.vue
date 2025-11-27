<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppMakerForm from '../Form/Form.vue';

const props = defineProps({
    resource: String,
    resourceConfig: Object,
    form: Object,
    record: Object,
});

const formData = useForm({ ...props.record });

function handleSubmit() {
    formData.put(route(`${props.resource}.update`, props.record.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit {{ resourceConfig.singularLabel }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <AppMakerForm
                            :form="form"
                            :form-data="formData"
                            :resource="resource"
                            :is-editing="true"
                            @submit="handleSubmit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

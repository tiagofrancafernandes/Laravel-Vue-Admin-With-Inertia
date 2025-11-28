<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

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
    <Head :title="`Edit ${resourceConfig.singularLabel}`" />
    <AuthenticatedLayout>
        <template #headerTitle>Edit {{ resourceConfig.singularLabel }}</template>

        <template #headerActions>
            <!-- actions here -->
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

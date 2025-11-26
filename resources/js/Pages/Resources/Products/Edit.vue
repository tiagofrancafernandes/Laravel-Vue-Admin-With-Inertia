<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from './ProductForm.vue';

const props = defineProps({
    product: Object,
});

const handleCancel = () => {
    router.visit(`/products/${props.product.id}`);
};
</script>

<template>
    <Head title="Edit Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Product: {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <ProductForm :product="product" :is-edit="true" @cancel="handleCancel" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

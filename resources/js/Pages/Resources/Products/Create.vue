<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

import ProductForm from './ProductForm.vue';

const handleCancel = () => {
    router.visit('/products');
};
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #headerTitle>Create Product</template>

        <template #headerActions>
            <!-- actions here -->
            <Link
                href="/products"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
                Back to List
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <ProductForm :is-edit="false" @cancel="handleCancel" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

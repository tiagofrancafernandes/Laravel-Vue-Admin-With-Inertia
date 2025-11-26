<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    product: Object,
});

const deleteProduct = () => {
    if (confirm(`Are you sure you want to delete "${props.product.name}"?`)) {
        router.delete(`/products/${props.product.id}`);
    }
};
</script>

<template>
    <Head :title="`Product: ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Product Details</h2>
                <div class="flex gap-2">
                    <Link
                        :href="`/products/${product.id}/edit`"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                    >
                        Edit
                    </Link>
                    <button
                        @click="deleteProduct"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                    >
                        Delete
                    </button>
                    <Link
                        href="/products"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Product Name -->
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ product.name }}
                            </h3>
                        </div>

                        <!-- Product Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Price
                                </label>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    ${{ parseFloat(product.price).toFixed(2) }}
                                </p>
                            </div>

                            <!-- Stock -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Stock
                                </label>
                                <span
                                    :class="[
                                        'inline-flex px-3 py-1 text-sm rounded-full font-semibold',
                                        {
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                product.stock > 0,
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                product.stock === 0,
                                        },
                                    ]"
                                >
                                    {{ product.stock }} units
                                </span>
                            </div>

                            <!-- Created By -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Created By
                                </label>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{ product.creator?.name || 'N/A' }}
                                </p>
                            </div>

                            <!-- Created At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Created At
                                </label>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{ new Date(product.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="product.description">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                Description
                            </label>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                    {{ product.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                        Last Updated
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ new Date(product.updated_at).toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

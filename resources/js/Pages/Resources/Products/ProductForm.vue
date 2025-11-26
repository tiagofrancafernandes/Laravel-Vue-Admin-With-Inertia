<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        default: () => ({
            name: '',
            description: '',
            price: '',
            stock: 0,
        }),
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['cancel']);

const form = useForm({
    name: props.product.name || '',
    description: props.product.description || '',
    price: props.product.price || '',
    stock: props.product.stock || 0,
});

const submit = () => {
    if (props.isEdit) {
        form.put(`/products/${props.product.id}`);
    } else {
        form.post('/products');
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Product Name <span class="text-red-500">*</span>
            </label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100"
                :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.name }}
            </p>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description
            </label>
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100"
                :class="{ 'border-red-500': form.errors.description }"
            ></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.description }}
            </p>
        </div>

        <!-- Price -->
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Price <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                </div>
                <input
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                    class="block w-full pl-7 pr-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100"
                    :class="{ 'border-red-500': form.errors.price }"
                />
            </div>
            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.price }}
            </p>
        </div>

        <!-- Stock -->
        <div>
            <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Stock <span class="text-red-500">*</span>
            </label>
            <input
                id="stock"
                v-model="form.stock"
                type="number"
                min="0"
                required
                class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100"
                :class="{ 'border-red-500': form.errors.stock }"
            />
            <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.stock }}
            </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <button
                type="button"
                @click="emit('cancel')"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-md transition-colors"
                :class="{
                    'hover:bg-blue-700': !form.processing,
                    'opacity-50 cursor-not-allowed': form.processing
                }"
            >
                {{ form.processing ? 'Saving...' : (isEdit ? 'Update Product' : 'Create Product') }}
            </button>
        </div>
    </form>
</template>

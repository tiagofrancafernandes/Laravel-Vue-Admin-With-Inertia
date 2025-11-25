<template>
    <div class="space-y-3">
        <!-- Popular Products Tags -->
        <div v-if="popularProducts.length > 0" class="space-y-2">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Mais Vendidos</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="product in popularProducts"
                    :key="product.id"
                    type="button"
                    @click="selectProduct(product)"
                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
                >
                    {{ product.name }}
                    <span class="text-xs text-blue-600 dark:text-blue-300 ml-1">({{ product.sales_count }})</span>
                </button>
            </div>
        </div>

        <!-- Search Input -->
        <div class="relative">
            <Input
                v-model="search"
                type="text"
                placeholder="Buscar produto..."
                @input="onSearch"
                @focus="showDropdown = true"
            />

            <!-- Dropdown -->
            <div
                v-if="showDropdown && filteredProducts.length > 0"
                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
            >
                <button
                    v-for="product in filteredProducts"
                    :key="product.id"
                    type="button"
                    class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors"
                    @click="selectProduct(product)"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ product.name }}
                            </div>
                            <div v-if="product.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ product.description }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                R$ {{ formatCurrency(product.price) }}
                            </div>
                            <div v-if="product.sales_count > 0" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ product.sales_count }} venda{{ product.sales_count !== 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useDebounce } from '@/Composables/useDebounce';
import { formatCurrency } from '@/Utils/helpers';
import Input from '@/Components/Common/Input.vue';

interface Product {
    id: number;
    name: string;
    description?: string;
    price: number;
    sales_count: number;
    created_at: string;
}

const emit = defineEmits(['select']);

const search = ref('');
const products = ref<Product[]>([]);
const showDropdown = ref(false);

const filteredProducts = computed(() => {
    if (!search.value) {
        return products.value;
    }
    return products.value.filter(
        (p) =>
            p.name.toLowerCase().includes(search.value.toLowerCase()) ||
            (p.description && p.description.toLowerCase().includes(search.value.toLowerCase()))
    );
});

const popularProducts = computed(() => {
    return products.value
        .filter((p) => p.sales_count > 0)
        .sort((a, b) => b.sales_count - a.sales_count)
        .slice(0, 5);
});

const onSearch = useDebounce(async () => {
    try {
        const response = await axios.get(route('api.products.select'));
        products.value = response.data.data || [];
    } catch (error) {
        console.error('Erro ao buscar produtos:', error);
        products.value = [];
    }
}, 300);

const selectProduct = (product: Product) => {
    emit('select', {
        description: product.name,
        quantity: 1,
        price: Number(product.price),
    });
    search.value = '';
    showDropdown.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (!event.target || !(event.target as HTMLElement).closest('.relative')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    onSearch();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

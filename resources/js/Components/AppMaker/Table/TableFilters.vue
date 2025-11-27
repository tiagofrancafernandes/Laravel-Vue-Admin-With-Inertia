<script setup>
import { ref } from 'vue';

defineProps({
    filters: Array,
});

const emit = defineEmits(['filter']);

const filterValues = ref({});

function handleFilterChange(filterName, value) {
    filterValues.value[filterName] = value;
    emit('filter', filterName, value);
}
</script>

<template>
    <div class="flex flex-wrap gap-4">
        <div v-for="filter in filters" :key="filter.name" class="flex-1 min-w-[200px]">
            <!-- Select Filter -->
            <div v-if="filter.type === 'select'">
                <label :for="filter.name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ filter.label }}
                </label>
                <select
                    :id="filter.name"
                    v-model="filterValues[filter.name]"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    @change="handleFilterChange(filter.name, filterValues[filter.name])"
                >
                    <option value="">All</option>
                    <option v-for="(label, value) in filter.options" :key="value" :value="value">
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Date Filter -->
            <div v-else-if="filter.type === 'date'">
                <label :for="filter.name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ filter.label }}
                </label>
                <input
                    :id="filter.name"
                    v-model="filterValues[filter.name]"
                    type="date"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    @change="handleFilterChange(filter.name, filterValues[filter.name])"
                />
            </div>

            <!-- Boolean Filter -->
            <div v-else-if="filter.type === 'boolean'">
                <label :for="filter.name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ filter.label }}
                </label>
                <select
                    :id="filter.name"
                    v-model="filterValues[filter.name]"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    @change="handleFilterChange(filter.name, filterValues[filter.name])"
                >
                    <option value="">All</option>
                    <option :value="true">{{ filter.trueLabel }}</option>
                    <option :value="false">{{ filter.falseLabel }}</option>
                </select>
            </div>
        </div>
    </div>
</template>

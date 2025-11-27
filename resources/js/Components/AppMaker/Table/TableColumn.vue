<script setup>
import { computed } from 'vue';

const props = defineProps({
    column: Object,
    record: Object,
});

const value = computed(() => {
    const keys = props.column.name.split('.');
    let val = props.record;

    for (const key of keys) {
        val = val?.[key];
    }

    return val;
});

const displayValue = computed(() => {
    if (value.value === null || value.value === undefined) {
        return '-';
    }

    return value.value;
});
</script>

<template>
    <td class="px-6 py-4">
        <!-- Text Column -->
        <div
            v-if="column.type === 'text'"
            class="text-sm text-gray-900 dark:text-gray-200"
            :class="{ truncate: column.limit }"
            :style="{ maxWidth: column.limit ? `${column.limit}ch` : 'auto' }"
        >
            {{ displayValue }}
        </div>

        <!-- Icon Column -->
        <div v-else-if="column.type === 'icon'" class="flex items-center">
            <span
                v-if="column.boolean"
                class="inline-flex rounded-full p-1"
                :class="{
                    'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400':
                        value && column.trueIcon.color === 'green',
                    'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400':
                        !value && column.falseIcon.color === 'red',
                }"
            >
                {{ value ? '✓' : '✗' }}
            </span>
        </div>

        <!-- Badge Column -->
        <div v-else-if="column.type === 'badge'">
            <span
                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                :class="{
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': column.colors[value] === 'gray',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                        column.colors[value] === 'green',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300':
                        column.colors[value] === 'yellow',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': column.colors[value] === 'red',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': column.colors[value] === 'blue',
                }"
            >
                {{ displayValue }}
            </span>
        </div>

        <!-- Image Column -->
        <div v-else-if="column.type === 'image'">
            <img
                :src="value"
                :alt="column.label"
                :class="{ 'rounded-full': column.rounded }"
                :style="{
                    width: column.width ? `${column.width}px` : 'auto',
                    height: column.height ? `${column.height}px` : 'auto',
                }"
                class="object-cover"
            />
        </div>

        <!-- Default -->
        <div v-else class="text-sm text-gray-900 dark:text-gray-200">
            {{ displayValue }}
        </div>
    </td>
</template>

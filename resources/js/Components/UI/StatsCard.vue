<template>
    <div :class="['bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6', 'border-l-4', borderColorClass]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ label }}
                </p>
                <p :class="['text-2xl font-bold mt-2', valueColorClass]">
                    {{ formattedValue }}
                </p>
                <p v-if="subtext" class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ subtext }}
                </p>
            </div>
            <div :class="['p-3 rounded-lg', iconBackgroundClass]">
                <slot name="icon">
                    <div :class="['w-8 h-8', iconColorClass]">📊</div>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    label: string;
    value: number | string;
    type?: 'currency' | 'percentage' | 'number';
    color?: 'blue' | 'green' | 'red' | 'yellow' | 'purple';
    subtext?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'number',
    color: 'blue',
});

const formattedValue = computed(() => {
    if (props.type === 'currency') {
        return `R$ ${parseFloat(String(props.value)).toFixed(2).replace('.', ',')}`;
    } else if (props.type === 'percentage') {
        return `${parseFloat(String(props.value)).toFixed(2).replace('.', ',')}%`;
    }
    return props.value;
});

const borderColorClass = computed(() => {
    switch (props.color) {
        case 'green':
            return 'border-green-500';
        case 'red':
            return 'border-red-500';
        case 'yellow':
            return 'border-yellow-500';
        case 'purple':
            return 'border-purple-500';
        default:
            return 'border-blue-500';
    }
});

const valueColorClass = computed(() => {
    switch (props.color) {
        case 'green':
            return 'text-green-600 dark:text-green-400';
        case 'red':
            return 'text-red-600 dark:text-red-400';
        case 'yellow':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'purple':
            return 'text-purple-600 dark:text-purple-400';
        default:
            return 'text-blue-600 dark:text-blue-400';
    }
});

const iconBackgroundClass = computed(() => {
    switch (props.color) {
        case 'green':
            return 'bg-green-100 dark:bg-green-900';
        case 'red':
            return 'bg-red-100 dark:bg-red-900';
        case 'yellow':
            return 'bg-yellow-100 dark:bg-yellow-900';
        case 'purple':
            return 'bg-purple-100 dark:bg-purple-900';
        default:
            return 'bg-blue-100 dark:bg-blue-900';
    }
});

const iconColorClass = computed(() => {
    switch (props.color) {
        case 'green':
            return 'text-green-600 dark:text-green-400';
        case 'red':
            return 'text-red-600 dark:text-red-400';
        case 'yellow':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'purple':
            return 'text-purple-600 dark:text-purple-400';
        default:
            return 'text-blue-600 dark:text-blue-400';
    }
});
</script>

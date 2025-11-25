<template>
    <button
        :type="type"
        :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors duration-200',
            'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
            variantClasses,
            sizeClasses,
            { 'opacity-50 cursor-not-allowed': disabled },
        ]"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'danger' | 'success';
    size?: 'sm' | 'md' | 'lg';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    size: 'md',
    disabled: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600';
        case 'secondary':
            return 'bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100';
        case 'danger':
            return 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600';
        case 'success':
            return 'bg-green-600 hover:bg-green-700 text-white dark:bg-green-500 dark:hover:bg-green-600';
        default:
            return '';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-2 md:py-1 text-sm';
        case 'lg':
            return 'px-6 py-3 md:py-3 text-lg';
        default:
            return 'px-4 py-3 md:py-2 text-base';
    }
});
</script>

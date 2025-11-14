<template>
    <span :class="classes">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) =>
            [
                'default',
                'primary',
                'success',
                'warning',
                'danger',
                'info',
            ].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
});

const classes = computed(() => {
    const base =
        'inline-flex items-center font-medium rounded-full px-2.5 py-0.5';

    const variants = {
        default: 'bg-gray-100 text-gray-800',
        primary: 'bg-indigo-100 text-indigo-800',
        success: 'bg-green-100 text-green-800',
        warning: 'bg-yellow-100 text-yellow-800',
        danger: 'bg-red-100 text-red-800',
        info: 'bg-blue-100 text-blue-800',
    };

    const sizes = {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
    };

    return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

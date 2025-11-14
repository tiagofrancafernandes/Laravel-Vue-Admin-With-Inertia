<template>
    <button
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
        v-bind="$attrs"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) =>
            ['primary', 'secondary', 'danger', 'success', 'ghost'].includes(
                value
            ),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    disabled: Boolean,
    loading: Boolean,
});

const classes = computed(() => {
    const base =
        'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    const variants = {
        primary:
            'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        secondary:
            'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        success:
            'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        ghost: 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-base',
        lg: 'px-6 py-3 text-lg',
    };

    return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

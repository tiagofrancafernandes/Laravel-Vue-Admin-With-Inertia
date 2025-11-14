<template>
    <div class="mb-4">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            @change="$emit('update:modelValue', $event.target.value)"
            :class="[
                'w-full px-3 py-2 border rounded-lg',
                'bg-white dark:bg-gray-800',
                'text-gray-900 dark:text-gray-100',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                'transition-colors duration-200',
                error ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600',
                { 'opacity-50 cursor-not-allowed': disabled },
            ]"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-500 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Option {
    value: string | number;
    label: string;
}

interface Props {
    modelValue: string | number;
    options: Option[];
    label?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    error?: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    id: () => Math.random().toString(36).substr(2, 9),
    disabled: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();
</script>

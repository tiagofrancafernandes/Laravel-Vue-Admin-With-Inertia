<template>
    <div class="mb-4">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :step="step"
            :min="min"
            :max="max"
            @input="$emit('update:modelValue', $event.target.value)"
            :class="[
                'w-full px-3 py-2 border rounded-lg',
                'bg-white dark:bg-gray-800',
                'text-gray-900 dark:text-gray-100',
                'placeholder-gray-400 dark:placeholder-gray-500',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                'transition-colors duration-200',
                error ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600',
                { 'opacity-50 cursor-not-allowed': disabled },
            ]"
        />
        <p v-if="error" class="mt-1 text-sm text-red-500 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string | number;
    label?: string;
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    error?: string;
    id?: string;
    step?: string | number;
    min?: string | number;
    max?: string | number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    id: () => Math.random().toString(36).substr(2, 9),
    disabled: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();
</script>

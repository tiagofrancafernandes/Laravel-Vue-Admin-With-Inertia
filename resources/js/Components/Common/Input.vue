<template>
    <div>
        <input
            :type="type"
            :value="modelValue"
            :class="inputClasses"
            :disabled="disabled"
            :placeholder="placeholder"
            :required="required"
            :min="min"
            :max="max"
            :step="step"
            @input="$emit('update:modelValue', $event.target.value)"
            v-bind="$attrs"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    error: {
        type: String,
        default: null,
    },
    disabled: Boolean,
    required: Boolean,
    placeholder: String,
    min: [String, Number],
    max: [String, Number],
    step: [String, Number],
});

defineEmits(['update:modelValue']);

const inputClasses = computed(() => {
    const base =
        'block w-full rounded-lg border px-3 py-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed';

    if (props.error) {
        return `${base} border-red-300 focus:border-red-500 focus:ring-red-500`;
    }

    return `${base} border-gray-300 focus:border-indigo-500 focus:ring-indigo-500`;
});
</script>

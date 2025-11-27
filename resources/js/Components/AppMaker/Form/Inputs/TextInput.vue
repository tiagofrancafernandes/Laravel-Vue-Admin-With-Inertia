<script setup>
defineProps({
    field: Object,
    modelValue: [String, Number],
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :id="field.name"
        :type="field.inputType || 'text'"
        :value="modelValue"
        :placeholder="field.placeholder"
        :required="field.required"
        :disabled="field.disabled"
        :maxlength="field.maxLength"
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
        @input="emit('update:modelValue', $event.target.value)"
    />
</template>

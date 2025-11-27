<script setup>
defineProps({
    field: Object,
    modelValue: String,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <textarea
        :id="field.name"
        :value="modelValue"
        :placeholder="field.placeholder"
        :required="field.required"
        :disabled="field.disabled"
        :rows="field.rows || 4"
        :maxlength="field.maxLength"
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
        @input="emit('update:modelValue', $event.target.value)"
    />
</template>

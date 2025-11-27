<script setup>
defineProps({
    field: Object,
    modelValue: [String, Number, Array],
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <select
        :id="field.name"
        :value="modelValue"
        :required="field.required"
        :disabled="field.disabled"
        :multiple="field.multiple"
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
        @change="emit('update:modelValue', $event.target.value)"
    >
        <option value="">Select an option</option>
        <option v-for="(label, value) in field.options" :key="value" :value="value">
            {{ label }}
        </option>
    </select>
</template>

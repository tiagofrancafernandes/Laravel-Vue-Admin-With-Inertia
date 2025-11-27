<script setup>
defineProps({
    field: Object,
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex items-center">
        <input
            :id="field.name"
            type="checkbox"
            :checked="modelValue"
            :disabled="field.disabled"
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
            @change="emit('update:modelValue', $event.target.checked)"
        />
        <label v-if="field.helperText" :for="field.name" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
            {{ field.helperText }}
        </label>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import TextInput from './Inputs/TextInput.vue';
import Textarea from './Inputs/Textarea.vue';
import Select from './Inputs/Select.vue';
import Checkbox from './Inputs/Checkbox.vue';

const props = defineProps({
    field: Object,
    modelValue: [String, Number, Boolean, Array, Object],
    error: String,
});

const emit = defineEmits(['update:modelValue']);

const component = computed(() => {
    const componentMap = {
        'text-input': TextInput,
        textarea: Textarea,
        select: Select,
        checkbox: Checkbox,
    };

    return componentMap[props.field.type] || TextInput;
});
</script>

<template>
    <div v-if="field.visible">
        <label :for="field.name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ field.label }}
            <span v-if="field.required" class="text-red-500">*</span>
        </label>

        <component
            :is="component"
            :id="field.name"
            :field="field"
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <p v-if="field.helperText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ field.helperText }}
        </p>

        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>

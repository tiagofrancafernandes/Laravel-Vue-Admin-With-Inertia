<script setup>
import { Link } from '@inertiajs/vue3';
import FormField from './FormField.vue';

const props = defineProps({
    form: Object,
    formData: Object,
    resource: String,
    isEditing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit']);

function handleSubmit() {
    emit('submit');
}
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <h3 v-if="form.heading" class="mb-6 text-lg font-semibold text-gray-800 dark:text-gray-200">
            {{ form.heading }}
        </h3>

        <div
            class="grid gap-6"
            :class="{
                'grid-cols-1': form.columns[0] === 1,
                'grid-cols-2': form.columns[0] === 2,
                'grid-cols-3': form.columns[0] === 3,
            }"
        >
            <FormField
                v-for="field in form.schema"
                :key="field.name"
                :field="field"
                :model-value="formData[field.name]"
                :error="formData.errors[field.name]"
                :class="{
                    'col-span-1': field.columnSpan === 1,
                    'col-span-2': field.columnSpan === 2,
                    'col-span-3': field.columnSpan === 3,
                    'col-span-full': field.columnSpan > 3,
                }"
                @update:model-value="formData[field.name] = $event"
            />
        </div>

        <div class="mt-6 flex items-center justify-end gap-4">
            <Link
                :href="route(`${resource}.index`)"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
            >
                {{ form.cancelLabel }}
            </Link>

            <button
                type="submit"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                :disabled="formData.processing"
            >
                {{ formData.processing ? 'Saving...' : form.submitLabel }}
            </button>
        </div>
    </form>
</template>

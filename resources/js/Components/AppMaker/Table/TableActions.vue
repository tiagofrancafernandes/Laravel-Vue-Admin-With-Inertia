<template>
    <div class="flex items-center justify-end gap-2">
        <!-- Se não tem actions configuradas, usa padrão (View, Edit) -->
        <template v-if="!actions || actions.length === 0">
            <Link
                :href="route(`${resource}.show`, record.id)"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium"
            >
                View
            </Link>

            <Link
                :href="route(`${resource}.edit`, record.id)"
                class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 text-sm font-medium"
            >
                Edit
            </Link>
        </template>

        <!-- Actions customizadas usando ActionGroup -->
        <ActionGroup
            v-else
            :actions="actions"
            :record="record"
            :resource="resource"
            :item-name-key="itemNameKey"
            @action="handleAction"
        />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ActionGroup from '../Actions/ActionGroup.vue';

defineProps({
    actions: {
        type: Array,
        default: () => [],
    },
    record: {
        type: Object,
        required: true,
    },
    resource: {
        type: String,
        required: true,
    },
    itemNameKey: {
        type: String,
        default: 'name',
    },
});

const emit = defineEmits(['action']);

const handleAction = (action, recordId) => {
    emit('action', action, recordId);
};
</script>

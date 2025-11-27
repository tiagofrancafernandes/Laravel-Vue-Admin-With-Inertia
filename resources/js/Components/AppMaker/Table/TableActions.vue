<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    actions: Array,
    record: Object,
    resource: String,
});

const emit = defineEmits(['action']);
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <!-- View -->
        <Link
            :href="route(`${resource}.show`, record.id)"
            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
        >
            View
        </Link>

        <!-- Edit -->
        <Link
            :href="route(`${resource}.edit`, record.id)"
            class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
        >
            Edit
        </Link>

        <!-- Custom Actions -->
        <button
            v-for="action in actions"
            :key="action.name"
            type="button"
            class="text-sm font-medium"
            :class="{
                'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300': action.color === 'red',
                'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300':
                    action.color === 'blue',
                'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300':
                    action.color === 'yellow',
                'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300': !action.color,
            }"
            @click="emit('action', action)"
        >
            {{ action.label }}
        </button>
    </div>
</template>

<template>
    <div
        :class="[
            'bg-white dark:bg-gray-800 rounded-lg shadow-sm',
            noBorder ? '' : 'border border-gray-200 dark:border-gray-700',
        ]"
    >
        <div v-if="title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
            </h3>
        </div>
        <div :class="['px-6 py-4', noPadding && 'p-0']">
            <slot />
        </div>
        <div
            v-if="$slots.footer"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg"
        >
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title?: string;
    noBorder?: boolean;
    noPadding?: boolean;
}

defineProps<Props>();
</script>

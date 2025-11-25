<template>
    <div
        :class="[
            'bg-white dark:bg-gray-800 rounded-lg shadow-sm',
            noBorder ? '' : 'border border-gray-200 dark:border-gray-700',
        ]"
    >
        <div :class="['px-6 py-2 border-gray-200 dark:border-gray-700', { 'border-b': !hideBody }]">
            <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
            </h3>
            <template v-else-if="$slots.title">
                <slot name="title"/>
            </template>
        </div>
        <div
            :class="[
                'px-6',
                {
                    'py-4': !(noPadding || hideBody),
                    'p-0': (noPadding || hideBody),
                },
            ]"
        >
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
    hideBody?: boolean;
}

defineProps<Props>();
</script>

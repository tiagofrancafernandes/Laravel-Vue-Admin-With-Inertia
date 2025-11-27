<script setup>
import { computed } from 'vue';

const props = defineProps({
    infoList: Object,
    record: Object,
});

function getValue(entry) {
    const keys = entry.name.split('.');
    let value = props.record;

    for (const key of keys) {
        value = value?.[key];
    }

    return value ?? '-';
}
</script>

<template>
    <div
        class="grid gap-6"
        :class="{
            'grid-cols-1': infoList.columns === 1,
            'grid-cols-2': infoList.columns === 2,
            'grid-cols-3': infoList.columns === 3,
        }"
    >
        <div
            v-for="entry in infoList.schema"
            :key="entry.name"
            class="border-b border-gray-200 pb-3 dark:border-gray-700"
            :class="{
                'col-span-1': entry.columnSpan === 1,
                'col-span-2': entry.columnSpan === 2,
                'col-span-3': entry.columnSpan === 3,
                'col-span-full': entry.columnSpan > 3,
            }"
        >
            <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ entry.label }}
            </dt>

            <!-- Text Entry -->
            <dd v-if="entry.type === 'text'" class="text-sm text-gray-900 dark:text-gray-200">
                <span
                    v-if="entry.badge"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="{
                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300':
                            entry.colors[getValue(entry)] === 'gray',
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                            entry.colors[getValue(entry)] === 'green',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300':
                            entry.colors[getValue(entry)] === 'red',
                    }"
                >
                    {{ getValue(entry) }}
                </span>
                <span v-else>{{ getValue(entry) }}</span>
            </dd>

            <!-- Icon Entry -->
            <dd v-else-if="entry.type === 'icon'">
                <span
                    v-if="entry.boolean"
                    class="inline-flex items-center rounded-full px-2 py-1 text-sm font-medium"
                    :class="{
                        'bg-green-100 text-green-800': getValue(entry),
                        'bg-red-100 text-red-800': !getValue(entry),
                    }"
                >
                    {{ getValue(entry) ? '✓ Yes' : '✗ No' }}
                </span>
            </dd>

            <!-- Image Entry -->
            <dd v-else-if="entry.type === 'image'">
                <img
                    :src="getValue(entry)"
                    :alt="entry.label"
                    :class="{ 'rounded-full': entry.rounded }"
                    :style="{
                        width: entry.width ? `${entry.width}px` : 'auto',
                        height: entry.height ? `${entry.height}px` : 'auto',
                    }"
                    class="object-cover"
                />
            </dd>

            <!-- Default -->
            <dd v-else class="text-sm text-gray-900 dark:text-gray-200">
                {{ getValue(entry) }}
            </dd>
        </div>
    </div>
</template>

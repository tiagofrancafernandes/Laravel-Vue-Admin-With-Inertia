<template>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <th v-for="column in columns" :key="column.key" class="px-6 py-3 text-left">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ column.label }}
                        </span>
                    </th>
                    <th v-if="$slots.actions" class="px-6 py-3 text-right">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Ações</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100"
                    >
                        <slot :name="`column-${column.key}`" :row="row">
                            {{ getNestedValue(row, column.key) }}
                        </slot>
                    </td>
                    <td v-if="$slots.actions" class="px-6 py-4 text-right">
                        <slot name="actions" :row="row" />
                    </td>
                </tr>
                <tr v-if="rows.length === 0">
                    <td
                        :colspan="columns.length + ($slots.actions ? 1 : 0)"
                        class="px-6 py-4 text-center text-gray-500 dark:text-gray-400"
                    >
                        Nenhum registro encontrado
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
interface Column {
    key: string;
    label: string;
}

interface Row {
    id: number | string;
    [key: string]: any;
}

interface Props {
    columns: Column[];
    rows: Row[];
}

defineProps<Props>();

const getNestedValue = (obj: any, path: string): any => {
    return path.split('.').reduce((current, prop) => current?.[prop], obj);
};
</script>

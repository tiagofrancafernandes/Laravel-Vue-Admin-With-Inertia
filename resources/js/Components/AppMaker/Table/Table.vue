<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import TableColumn from './TableColumn.vue';
import TableFilters from './TableFilters.vue';
import TablePagination from './TablePagination.vue';
import TableActions from './TableActions.vue';

const props = defineProps({
    table: Object,
    records: Object,
    resource: String,
});

const emit = defineEmits(['search', 'filter', 'sort', 'action', 'bulk-action']);

const searchQuery = ref('');
const selectedRows = ref([]);
const sortColumn = ref(null);
const sortDirection = ref('asc');

const allSelected = computed({
    get() {
        return props.records.data.length > 0 && selectedRows.value.length === props.records.data.length;
    },
    set(value) {
        if (value) {
            selectedRows.value = props.records.data.map((r) => r.id);
        } else {
            selectedRows.value = [];
        }
    },
});

function handleSearch() {
    emit('search', searchQuery.value);
}

function handleSort(column) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }

    emit('sort', column, sortDirection.value);
}
</script>

<template>
    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <!-- Header -->
        <div class="border-b border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    {{ table.heading }}
                </h3>

                <!-- Search -->
                <div v-if="table.search.enabled" class="w-64">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full rounded-lg border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        @input="handleSearch"
                    />
                </div>
            </div>

            <!-- Filters -->
            <TableFilters
                v-if="table.filters.length > 0"
                :filters="table.filters"
                class="mt-4"
                @filter="(name, value) => emit('filter', name, value)"
            />
        </div>

        <!-- Bulk Actions -->
        <div
            v-if="selectedRows.length > 0 && table.actions.bulk.length > 0"
            class="border-b border-gray-200 bg-blue-50 p-4 dark:border-gray-700 dark:bg-blue-900/20"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ selectedRows.length }} selected</span>

                <div class="flex gap-2">
                    <button
                        v-for="action in table.actions.bulk"
                        :key="action.name"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium"
                        :class="{
                            'bg-red-600 text-white hover:bg-red-700': action.color === 'red',
                            'bg-blue-600 text-white hover:bg-blue-700': action.color === 'blue',
                            'bg-gray-600 text-white hover:bg-gray-700': !action.color,
                        }"
                        @click="emit('bulk-action', action, selectedRows)"
                    >
                        {{ action.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th v-if="table.selectable" class="px-6 py-3 text-left">
                            <input
                                v-model="allSelected"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                        </th>

                        <th
                            v-for="column in table.columns"
                            :key="column.name"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            :class="{ 'cursor-pointer': column.sortable }"
                            @click="column.sortable && handleSort(column.name)"
                        >
                            <div class="flex items-center gap-1">
                                {{ column.label }}
                                <span v-if="column.sortable && sortColumn === column.name" class="text-gray-400">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </div>
                        </th>

                        <th
                            v-if="table.actions.record.length > 0"
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <tr
                        v-for="record in records.data"
                        :key="record.id"
                        :class="{
                            'bg-gray-50 dark:bg-gray-900': table.striped,
                        }"
                    >
                        <td v-if="table.selectable" class="px-6 py-4">
                            <input
                                v-model="selectedRows"
                                type="checkbox"
                                :value="record.id"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                        </td>

                        <TableColumn
                            v-for="column in table.columns"
                            :key="column.name"
                            :column="column"
                            :record="record"
                        />

                        <td v-if="table.actions.record.length > 0" class="px-6 py-4 text-right">
                            <TableActions
                                :actions="table.actions.record"
                                :record="record"
                                :resource="resource"
                                @action="(action) => emit('action', action, record.id)"
                            />
                        </td>
                    </tr>

                    <tr v-if="records.data.length === 0">
                        <td
                            :colspan="
                                table.columns.length +
                                (table.selectable ? 1 : 0) +
                                (table.actions.record.length > 0 ? 1 : 0)
                            "
                            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
                        >
                            No records found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <TablePagination v-if="table.pagination.enabled" :records="records" :config="table.pagination" />
    </div>
</template>

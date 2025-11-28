<script setup>
import { computed, ref } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

import AppMakerTable from '../Table/Table.vue';

const props = defineProps({
    resource: String,
    resourceConfig: Object,
    table: Object,
    records: Object,
});

const filters = ref({});
const search = ref('');

function handleSearch(value) {
    router.get(
        route(`${props.resource}.index`),
        {
            search: value,
            filters: filters.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['records'],
        }
    );
}

function handleFilter(filterName, value) {
    filters.value[filterName] = value;

    router.get(
        route(`${props.resource}.index`),
        {
            search: search.value,
            filters: filters.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['records'],
        }
    );
}

function handleSort(column, direction) {
    router.get(
        route(`${props.resource}.index`),
        {
            sort_by: column,
            sort_direction: direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['records'],
        }
    );
}

function handleAction(action, recordId) {
    router.post(
        route('appmaker.action', {
            resource: props.resource,
            action: action.name,
            record: recordId,
        }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['records'] });
            },
        }
    );
}

function handleBulkAction(action, ids) {
    router.post(
        route('appmaker.bulk-action', {
            resource: props.resource,
            action: action.name,
        }),
        { ids },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['records'] });
            },
        }
    );
}
</script>

<template>
    <Head :title="`Create ${resourceConfig.singularLabel}`" />

    <AuthenticatedLayout>
        <template #headerTitle>
            {{ resourceConfig.title }}
        </template>

        <template #headerActions>
            <!-- actions here -->
            <Link
                v-if="table.actions.header.length > 0"
                :href="route(`${resource}.create`)"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Create {{ resourceConfig.singularLabel }}
            </Link>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <AppMakerTable
                    :table="table"
                    :records="records"
                    :resource="resource"
                    @search="handleSearch"
                    @filter="handleFilter"
                    @sort="handleSort"
                    @action="handleAction"
                    @bulk-action="handleBulkAction"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

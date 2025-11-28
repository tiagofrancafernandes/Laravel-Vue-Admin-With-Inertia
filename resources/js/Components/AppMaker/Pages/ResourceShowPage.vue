<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

import AppMakerInfoList from '../InfoList/InfoList.vue';

const props = defineProps({
    resource: String,
    resourceConfig: Object,
    infoList: Object,
    record: Object,
});
</script>

<template>
    <Head :title="`${resourceConfig.singularLabel} Details`" />
    <AuthenticatedLayout>
        <template #headerTitle>{{ resourceConfig.title }} Details</template>

        <template #headerActions>
            <!-- actions here -->

            <Link
                :href="route(`${resource}.edit`, record.id)"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Edit
            </Link>

            <Link
                :href="route(`${resource}.index`)"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
            >
                Back to List
            </Link>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <AppMakerInfoList v-if="infoList" :info-list="infoList" :record="record" />

                        <!-- Fallback if no infoList -->
                        <div v-else>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div
                                    v-for="(value, key) in record"
                                    :key="key"
                                    class="border-b border-gray-200 pb-3 dark:border-gray-700"
                                >
                                    <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ key }}
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-200">
                                        {{ value }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

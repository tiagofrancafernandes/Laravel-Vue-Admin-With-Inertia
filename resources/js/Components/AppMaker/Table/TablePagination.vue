<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    records: Object,
    config: Object,
});
</script>

<template>
    <div
        class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-800"
    >
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="records.prev_page_url"
                :href="records.prev_page_url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Previous
            </Link>
            <Link
                v-if="records.next_page_url"
                :href="records.next_page_url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Next
            </Link>
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing
                    <span class="font-medium">{{ records.from }}</span>
                    to
                    <span class="font-medium">{{ records.to }}</span>
                    of
                    <span class="font-medium">{{ records.total }}</span>
                    results
                </p>
            </div>

            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <Link
                        v-for="link in records.links"
                        :key="link.label"
                        :href="link.url"
                        :class="{
                            'relative inline-flex items-center px-4 py-2 text-sm font-medium': true,
                            'z-10 bg-blue-600 text-white': link.active,
                            'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                !link.active,
                            'pointer-events-none opacity-50': !link.url,
                        }"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </div>
</template>

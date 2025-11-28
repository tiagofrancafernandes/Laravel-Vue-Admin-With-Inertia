<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import UserForm from './UserForm.vue';

defineProps({
    user: Object,
    pageType: {
        type: String,
        default: 'page',
    },
    availableRoles: {
        type: Array,
        default: [],
    },
});
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AuthenticatedLayout>
        <template #headerTitle>Edit User: {{ user.name }}</template>

        <template #headerActions>
            <!-- actions here -->
            <Link
                href="/users"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
                Back to List
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <UserForm :user="user" :availableRoles="availableRoles" :is-editing="true" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

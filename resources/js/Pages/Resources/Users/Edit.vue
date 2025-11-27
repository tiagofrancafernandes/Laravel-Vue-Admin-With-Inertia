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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit User: {{ user.name }}
            </h2>
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

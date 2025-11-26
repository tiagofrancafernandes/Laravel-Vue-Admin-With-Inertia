<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
    pageType: {
        type: String,
        default: 'page',
    },
});
</script>

<template>
    <Head :title="user.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ user.name }}
                </h2>
                <Link :href="route('users.edit', user)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Edit User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- User Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Basic Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Basic Information
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Name
                                    </label>
                                    <p class="text-gray-900 dark:text-white">{{ user.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Email
                                    </label>
                                    <p class="text-gray-900 dark:text-white">{{ user.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Role
                                    </label>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium inline-block',
                                        {
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': user.role === 'admin',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': user.role === 'user',
                                        }
                                    ]">
                                        {{ user.role }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Account Information
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Created At
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ new Date(user.created_at).toLocaleString() }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Last Updated
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ new Date(user.updated_at).toLocaleString() }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Email Verified
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ user.email_verified_at ? 'Yes' : 'No' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t dark:border-gray-700 flex gap-4">
                        <Link
                            :href="route('users.edit', user)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Edit User
                        </Link>
                        <Link :href="route('users.index')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            Back to Users
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

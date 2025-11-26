<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

interface Props {
    stats: {
        totalUsers: number;
        adminUsers: number;
        regularUsers: number;
        verifiedUsers: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        created_at: string;
    }>;
}

defineProps<Props>();

const page = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Welcome back, {{ page.props.auth.user.name }}!
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Here's an overview of your application.
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Users -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Total Users
                                </p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.totalUsers }}
                                </p>
                            </div>
                            <div class="text-4xl text-blue-500 opacity-10">
                                👥
                            </div>
                        </div>
                    </div>

                    <!-- Admin Users -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Admin Users
                                </p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.adminUsers }}
                                </p>
                            </div>
                            <div class="text-4xl text-red-500 opacity-10">
                                🔐
                            </div>
                        </div>
                    </div>

                    <!-- Regular Users -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Regular Users
                                </p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.regularUsers }}
                                </p>
                            </div>
                            <div class="text-4xl text-green-500 opacity-10">
                                ✓
                            </div>
                        </div>
                    </div>

                    <!-- Verified Users -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Verified Users
                                </p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.verifiedUsers }}
                                </p>
                            </div>
                            <div class="text-4xl text-yellow-500 opacity-10">
                                ✉️
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Recent Users
                        </h3>
                        <Link :href="route('users.index')" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 text-sm font-medium">
                            View all →
                        </Link>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Email</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Role</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in recentUsers" :key="user.id" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    <Link :href="route('users.show', user.id)" class="text-blue-600 hover:underline">
                                        {{ user.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium',
                                        {
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': user.role === 'admin',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': user.role === 'user',
                                        }
                                    ]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Getting Started Section -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-4">
                        Getting Started
                    </h3>
                    <ul class="space-y-2 text-blue-800 dark:text-blue-300">
                        <li class="flex items-center">
                            <span class="mr-3">📚</span>
                            <span>Read the <a href="#" class="underline hover:no-underline">BOILERPLATE_SETUP.md</a> guide to learn how to extend this boilerplate</span>
                        </li>
                        <li class="flex items-center">
                            <span class="mr-3">👥</span>
                            <span>Manage users by visiting the <Link :href="route('users.index')" class="underline hover:no-underline">Users Management</Link> page</span>
                        </li>
                        <li class="flex items-center">
                            <span class="mr-3">⚙️</span>
                            <span>Check the CLAUDE.md file for project-specific development guidelines</span>
                        </li>
                        <li class="flex items-center">
                            <span class="mr-3">✨</span>
                            <span>Use the Users CRUD as a template for implementing new resources</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

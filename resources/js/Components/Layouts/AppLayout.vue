<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center">
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">Ledger</div>
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-8">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                                isActive(item.href)
                                    ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600'
                                    : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400',
                            ]"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- User Menu -->
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $page.props.auth.user.name }}
                            </span>
                            <form method="POST" action="/logout">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <button
                                    type="submit"
                                    class="text-sm text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                >
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4">
                <Alert type="success" :message="$page.props.flash.success" />
            </div>

            <div v-if="$page.props.flash.error" class="mb-4">
                <Alert type="error" :message="$page.props.flash.error" />
            </div>

            <!-- Page Content -->
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Alert from '../UI/Alert.vue';

const page = usePage();

const navItems = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Vendas', href: '/sales' },
    { name: 'Clientes', href: '/clients' },
];

const currentRoute = computed(() => page.url);

const isActive = (href: string): boolean => {
    return currentRoute.value.startsWith(href);
};
</script>

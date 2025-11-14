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

                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                :title="`Alternar para ${isDarkMode ? 'modo claro' : 'modo escuro'}`"
                                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                aria-label="Toggle dark mode"
                            >
                                <!-- Sun Icon (Light Mode) -->
                                <svg
                                    v-if="isDarkMode"
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zm4.293 2.293a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414L14.707 5.707a1 1 0 010-1.414zm2.828 2.828a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414l-1.414-1.414a1 1 0 010-1.414zM10 7a1 1 0 011 1v2a1 1 0 11-2 0V8a1 1 0 011-1zm-4.293 2.293a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414L5.293 10.707a1 1 0 010-1.414zM10 12a2 2 0 100-4 2 2 0 000 4zm4.293 2.293a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414l-1.414-1.414a1 1 0 010-1.414zM14.707 14.707a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414l-1.414-1.414a1 1 0 010-1.414zm-4.707 2.828a1 1 0 011 1v2a1 1 0 11-2 0v-2a1 1 0 011-1zm-4.293-2.293a1 1 0 011.414 0l1.414 1.414a1 1 0 11-1.414 1.414L5.293 15.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>

                                <!-- Moon Icon (Dark Mode) -->
                                <svg
                                    v-else
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                                    />
                                </svg>
                            </button>

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
import { useDarkMode } from '../../composables/useDarkMode';

const page = usePage();
const { isDarkMode, toggleDarkMode } = useDarkMode();

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

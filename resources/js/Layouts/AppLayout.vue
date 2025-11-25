<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-12">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm fixed top-0 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link :href="route('dashboard')" class="flex align-center w-full md:gap-2">
                            <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />

                            <span class="sm:pt-1 text-xl font-bold text-blue-600 dark:text-blue-400">Ledger</span>
                        </Link>
                    </div>

                    <!-- Navigation Links (Desktop) -->
                    <div class="hidden md:flex items-center space-x-8">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                'px-3 py-2 rounded-sm text-sm font-medium transition-colors',
                                {
                                    'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600': isActive(item?.href),
                                    'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400':
                                        !isActive(item?.href),
                                },
                            ]"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- User Menu -->
                        <div class="flex items-center space-x-4">
                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                :title="`Alternar para ${isDarkMode ? 'modo claro' : 'modo escuro'}`"
                                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                aria-label="Toggle dark mode"
                            >
                                <svg v-if="isDarkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user?.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center space-x-2">
                        <button
                            @click="toggleDarkMode"
                            :title="`Alternar para ${isDarkMode ? 'modo claro' : 'modo escuro'}`"
                            class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            aria-label="Toggle dark mode"
                        >
                            <svg v-if="isDarkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                        </button>
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out"
                            aria-label="Toggle navigation"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    v-if="!showingNavigationDropdown"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div v-if="showingNavigationDropdown" class="md:hidden border-t border-gray-200 dark:border-gray-700">
                    <div class="space-y-1 px-2 pb-3 pt-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                'block px-3 py-2 rounded-md text-base font-medium transition-colors w-full text-left',
                                {
                                    'bg-blue-600 text-white': isActive(item?.href),
                                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700':
                                        !isActive(item?.href),
                                },
                            ]"
                            @click="showingNavigationDropdown = false"
                        >
                            {{ item.name }}
                        </Link>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 px-2 py-3 space-y-2">
                        <div class="text-sm font-medium text-gray-600 dark:text-gray-400 px-3">
                            {{ $page.props.auth.user?.name }}
                        </div>
                        <Link
                            :href="route('profile.edit')"
                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            @click="showingNavigationDropdown = false"
                        >
                            Perfil
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            @click="showingNavigationDropdown = false"
                        >
                            Sair
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 pb-12">
            <!-- Flash Messages -->
            <div v-if="flashMessages?.success" class="mb-4">
                <Alert type="success" :message="flashMessages.success" />
            </div>

            <div v-if="flashMessages?.error" class="mb-4">
                <Alert type="error" :message="flashMessages.error" />
            </div>

            <!-- Page Content -->
            <slot />

            <div class="pt-6">
                <template v-for="(error, errorIndex) in errors" :key="errorIndex">
                    <div v-if="Object.keys(errors).length" class="mb-4">
                        <Alert type="error" :message="error" />
                    </div>
                </template>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useDarkMode } from '@/composables/useDarkMode';
import { route, strEndsWith } from '@/Utils/helpers';
import { PageProps } from '@inertiajs/core';
import { Link, usePage } from '@inertiajs/vue3';

interface FlashMessages {
    danger?: string;
    info?: string;
    success?: string;
    error?: string;
}
interface CustomProps extends PageProps {
    props: PageProps & {
        flash?: FlashMessages;
    };
}

const page = usePage<CustomProps & PageProps>();
const pageProps = page?.props;
const flashMessages = computed<FlashMessages>(() => pageProps.flash || {});
const { isDarkMode, toggleDarkMode } = useDarkMode();

type ValidationErrors = Record<string, string>;

const errors = computed<ValidationErrors>(() => pageProps?.errors || {});

const navItems = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Vendas', href: '/sales' },
    { name: 'Clientes', href: '/clients' },
];

const currentRoute = computed(() => page.url || null);

const isActive = (href: string): boolean => {
    return strEndsWith(currentRoute.value || null, href);
};

const showingNavigationDropdown = ref(false);
</script>

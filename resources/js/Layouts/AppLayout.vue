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

                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-8">
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
                                <!-- Sun Icon (Light Mode) -->
                                <svg
                                    v-if="isDarkMode"
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                >
                                    <!-- Icon from Material Line Icons by Vjacheslav Trushkin - https://github.com/cyberalien/line-md/blob/master/license.txt -->
                                    <g
                                        fill="none"
                                        stroke="#888888"
                                        stroke-dasharray="4"
                                        stroke-dashoffset="4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M13 4h1.5M13 4h-1.5M13 4v1.5M13 4v-1.5">
                                            <animate
                                                id="SVGjUNXVaqx"
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="0.7s;SVGjUNXVaqx.begin+6s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGjUNXVaqx.begin+2s;SVGjUNXVaqx.begin+4s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGjUNXVaqx.begin+1.2s;SVGjUNXVaqx.begin+3.2s;SVGjUNXVaqx.begin+5.2s"
                                                dur="0.4s"
                                                values="0;4"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGjUNXVaqx.begin+1.8s"
                                                to="M12 5h1.5M12 5h-1.5M12 5v1.5M12 5v-1.5"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGjUNXVaqx.begin+3.8s"
                                                to="M12 4h1.5M12 4h-1.5M12 4v1.5M12 4v-1.5"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGjUNXVaqx.begin+5.8s"
                                                to="M13 4h1.5M13 4h-1.5M13 4v1.5M13 4v-1.5"
                                            />
                                        </path>
                                        <path d="M19 11h1.5M19 11h-1.5M19 11v1.5M19 11v-1.5">
                                            <animate
                                                id="SVGO88gQckN"
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="1.1s;SVGO88gQckN.begin+6s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGO88gQckN.begin+2s;SVGO88gQckN.begin+4s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGO88gQckN.begin+1.2s;SVGO88gQckN.begin+3.2s;SVGO88gQckN.begin+5.2s"
                                                dur="0.4s"
                                                values="0;4"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGO88gQckN.begin+1.8s"
                                                to="M17 11h1.5M17 11h-1.5M17 11v1.5M17 11v-1.5"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGO88gQckN.begin+3.8s"
                                                to="M18 12h1.5M18 12h-1.5M18 12v1.5M18 12v-1.5"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGO88gQckN.begin+5.8s"
                                                to="M19 11h1.5M19 11h-1.5M19 11v1.5M19 11v-1.5"
                                            />
                                        </path>
                                        <path d="M19 4h1.5M19 4h-1.5M19 4v1.5M19 4v-1.5">
                                            <animate
                                                id="SVGPXuakc7A"
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="2s;SVGPXuakc7A.begin+6s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGPXuakc7A.begin+2s"
                                                dur="0.4s"
                                                values="4;0"
                                            />
                                            <animate
                                                fill="freeze"
                                                attributeName="stroke-dashoffset"
                                                begin="SVGPXuakc7A.begin+1.2s;SVGPXuakc7A.begin+3.2s"
                                                dur="0.4s"
                                                values="0;4"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGPXuakc7A.begin+1.8s"
                                                to="M20 5h1.5M20 5h-1.5M20 5v1.5M20 5v-1.5"
                                            />
                                            <set
                                                fill="freeze"
                                                attributeName="d"
                                                begin="SVGPXuakc7A.begin+5.8s"
                                                to="M19 4h1.5M19 4h-1.5M19 4v1.5M19 4v-1.5"
                                            />
                                        </path>
                                    </g>
                                    <path
                                        fill="none"
                                        stroke="#888888"
                                        stroke-dasharray="56"
                                        stroke-dashoffset="56"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 6 C7 12.08 11.92 17 18 17 C18.53 17 19.05 16.96 19.56 16.89 C17.95 19.36 15.17 21 12 21 C7.03 21 3 16.97 3 12 C3 8.83 4.64 6.05 7.11 4.44 C7.04 4.95 7 5.47 7 6 Z"
                                    >
                                        <animate
                                            fill="freeze"
                                            attributeName="stroke-dashoffset"
                                            dur="0.6s"
                                            values="56;0"
                                        />
                                    </path>
                                </svg>

                                <!-- Moon Icon (Dark Mode) -->
                                <svg
                                    v-else
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                >
                                    <!-- Icon from Material Line Icons by Vjacheslav Trushkin - https://github.com/cyberalien/line-md/blob/master/license.txt -->
                                    <g
                                        fill="none"
                                        stroke="#888888"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="32" r="6" fill="#888888">
                                            <animate fill="freeze" attributeName="cy" dur="0.6s" values="32;12" />
                                        </circle>
                                        <g>
                                            <path
                                                stroke-dasharray="2"
                                                stroke-dashoffset="2"
                                                d="M12 19v1M19 12h1M12 5v-1M5 12h-1"
                                            >
                                                <animate
                                                    fill="freeze"
                                                    attributeName="d"
                                                    begin="0.7s"
                                                    dur="0.2s"
                                                    values="M12 19v1M19 12h1M12 5v-1M5 12h-1;M12 21v1M21 12h1M12 3v-1M3 12h-1"
                                                />
                                                <animate
                                                    fill="freeze"
                                                    attributeName="stroke-dashoffset"
                                                    begin="0.7s"
                                                    dur="0.2s"
                                                    values="2;0"
                                                />
                                            </path>
                                            <path
                                                stroke-dasharray="2"
                                                stroke-dashoffset="2"
                                                d="M17 17l0.5 0.5M17 7l0.5 -0.5M7 7l-0.5 -0.5M7 17l-0.5 0.5"
                                            >
                                                <animate
                                                    fill="freeze"
                                                    attributeName="d"
                                                    begin="0.9s"
                                                    dur="0.2s"
                                                    values="M17 17l0.5 0.5M17 7l0.5 -0.5M7 7l-0.5 -0.5M7 17l-0.5 0.5;M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"
                                                />
                                                <animate
                                                    fill="freeze"
                                                    attributeName="stroke-dashoffset"
                                                    begin="0.9s"
                                                    dur="0.2s"
                                                    values="2;0"
                                                />
                                            </path>
                                            <animateTransform
                                                attributeName="transform"
                                                dur="30s"
                                                repeatCount="indefinite"
                                                type="rotate"
                                                values="0 12 12;360 12 12"
                                            />
                                        </g>
                                    </g>
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
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 pb-12">
            <!-- Flash Messages -->
            <div v-if="flashMessages?.success" class="mb-4">
                <Alert type="success" :message="flashMessages.success" />
            </div>

            <div v-if="flashMessages?.error" class="mb-4">
                <Alert type="error" :message="flashMessages.error" />
            </div>

            <!-- Page Content -->
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import {
    computed,
    ref,
} from 'vue';

import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useDarkMode } from '@/composables/useDarkMode';
import { User } from '@/types/app-types.d';
import {
    route,
    strEndsWith,
} from '@/Utils/helpers';
import {
    PageProps,
    router,
} from '@inertiajs/core';
import {
    Link,
    usePage,
} from '@inertiajs/vue3';

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
console.log('route', route, route('dashboard'));
</script>

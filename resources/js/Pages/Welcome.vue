<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useDarkMode } from '@/composables/useDarkMode';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

const { isDarkMode, toggleDarkMode } = useDarkMode();
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <ApplicationLogo class="h-12 w-auto" />
                    </div>

                    <nav class="-mx-3 flex flex-1 justify-end">
                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            :title="`Alternar para ${isDarkMode ? 'modo claro' : 'modo escuro'}`"
                            class="p-2 rounded-lg bg-gray-100 dark:bg-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800/50 transition-colors"
                            aria-label="Toggle dark mode"
                        >
                            <!-- Sun Icon (Light Mode) -->
                            <svg v-if="isDarkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            <!-- Moon Icon (Dark Mode) -->
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                        </button>

                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white hover:bg-gray-200 dark:hover:bg-gray-800/50 transition-colors"
                            >
                                Dashboard
                            </Link>

                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white hover:bg-gray-200 dark:hover:bg-gray-800/50 transition-colors"
                                >
                                    Log in
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white hover:bg-gray-200 dark:hover:bg-gray-800/50 transition-colors"
                                >
                                    Register
                                </Link>
                            </template>
                        </template>
                    </nav>
                </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <a
                            href="https://laravel.com/docs"
                            id="docs-card"
                            class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div id="screenshot-container" class="relative flex w-full flex-1 items-stretch">
                                <img
                                    src="@root/public/dash-screen-light.png"
                                    alt="Dashboard screenshot"
                                    class="aspect-video h-full w-full flex-1 rounded-[10px] object-cover object-top drop-shadow-[0px_4px_34px_rgba(0,0,0,0.06)] dark:hidden"
                                    @error="handleImageError"
                                />
                                <img
                                    src="@root/public/dash-screen.png"
                                    alt="Dashboard screenshot"
                                    class="hidden aspect-video h-full w-full flex-1 rounded-[10px] object-cover object-top drop-shadow-[0px_4px_34px_rgba(0,0,0,0.25)] dark:block"
                                />
                                <div
                                    class="absolute -bottom-16 -left-16 h-40 w-[calc(100%+8rem)] bg-gradient-to-b from-transparent via-white to-white dark:via-zinc-900 dark:to-zinc-900"
                                ></div>
                            </div>

                            <div class="relative flex items-center gap-6 lg:items-end">
                                <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                                    <div
                                        class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16"
                                    >
                                        <svg
                                            class="size-5 sm:size-6"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                fill="#FF2D20"
                                                d="M23 4a1 1 0 0 0-1.447-.894L12.224 7.77a.5.5 0 0 1-.448 0L2.447 3.106A1 1 0 0 0 1 4v13.382a1.99 1.99 0 0 0 1.105 1.79l9.448 4.728c.14.065.293.1.447.1.154-.005.306-.04.447-.105l9.453-4.724a1.99 1.99 0 0 0 1.1-1.789V4ZM3 6.023a.25.25 0 0 1 .362-.223l7.5 3.75a.251.251 0 0 1 .138.223v11.2a.25.25 0 0 1-.362.224l-7.5-3.75a.25.25 0 0 1-.138-.22V6.023Zm18 11.2a.25.25 0 0 1-.138.224l-7.5 3.75a.249.249 0 0 1-.329-.099.249.249 0 0 1-.033-.12V9.772a.251.251 0 0 1 .138-.224l7.5-3.75a.25.25 0 0 1 .362.224v11.2Z"
                                            />
                                            <path
                                                fill="#FF2D20"
                                                d="m3.55 1.893 8 4.048a1.008 1.008 0 0 0 .9 0l8-4.048a1 1 0 0 0-.9-1.785l-7.322 3.706a.506.506 0 0 1-.452 0L4.454.108a1 1 0 0 0-.9 1.785H3.55Z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="pt-3 sm:pt-5 lg:pt-0">
                                        <h2 class="text-xl font-semibold text-black dark:text-white">Documentation</h2>

                                        <p class="mt-4 text-sm/relaxed">
                                            Laravel has wonderful documentation covering every aspect of the framework.
                                            Whether you are a newcomer or have prior experience with Laravel, we
                                            recommend reading our documentation from beginning to end.
                                        </p>
                                    </div>
                                </div>

                                <svg
                                    class="size-6 shrink-0 stroke-[#FF2D20]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                    />
                                </svg>
                            </div>
                        </a>

                        <Link
                            href="/dashboard"
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16"
                            >
                                <svg
                                    class="size-5 sm:size-6"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                >
                                    <!-- Icon from Ultimate color icons by Streamline - https://creativecommons.org/licenses/by/4.0/ -->
                                    <g fill="none">
                                        <path
                                            fill="#66e1ff"
                                            d="M18.216 8.174c-1.333 2.254-4.47 2.563-6.217.613c-1.747 1.948-4.883 1.639-6.216-.613a2.71 2.71 0 0 1-2.87.802v12.582c0 .528.429.956.957.956h16.258a.956.956 0 0 0 .957-.956V8.98a2.71 2.71 0 0 1-2.87-.806"
                                        />
                                        <path
                                            fill="#c2f3ff"
                                            d="M12 8.787c-1.748 1.948-4.884 1.639-6.217-.613a2.71 2.71 0 0 1-2.87.802v12.582a.94.94 0 0 0 .409.765l12.33-12.331A3.74 3.74 0 0 1 12 8.787"
                                        />
                                        <path
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M21.085 21.086v.478a.956.956 0 0 1-.957.956H3.87a.957.957 0 0 1-.956-.956v-.478M2.91 8.975v2.069m18.17-2.062v2.062"
                                        />
                                        <path
                                            fill="#fff"
                                            d="M6.74 1.48H3.506a.96.96 0 0 0-.855.529L1.002 5.305h4.782z"
                                        />
                                        <path fill="#ffbfc5" d="M12 5.305V1.48H6.74l-.956 3.825z" />
                                        <path fill="#fff" d="M18.216 5.305L17.26 1.48H12v3.825z" />
                                        <path
                                            fill="#ffbfc5"
                                            d="m22.998 5.305l-1.65-3.296a.96.96 0 0 0-.854-.53h-3.235l.957 3.826z"
                                        />
                                        <path
                                            fill="#ff808c"
                                            d="M5.784 5.305v2.87c1.333 2.252 4.47 2.56 6.216.611v-3.48zm12.432 0v2.87c1.397 1.576 3.977 1.048 4.643-.95c.103-.31.15-.637.139-.963v-.957z"
                                        />
                                        <path
                                            fill="#e3e3e3"
                                            d="M12 5.305v3.481c1.746 1.95 4.884 1.642 6.216-.612V5.305zm-10.998 0v.957C.93 8.368 3.167 9.762 5.026 8.77a2.7 2.7 0 0 0 .758-.596V5.305z"
                                        />
                                        <path
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M21.35 2.009a.96.96 0 0 0-.855-.53H3.505a.96.96 0 0 0-.854.53L1.002 5.305h21.997zm1.648 4.253c.073 2.105-2.16 3.5-4.02 2.511a2.7 2.7 0 0 1-.762-.599c-1.332 2.254-4.47 2.563-6.216.612c-1.746 1.95-4.884 1.642-6.217-.612c-1.4 1.575-3.981 1.042-4.644-.959a2.7 2.7 0 0 1-.137-.953v-.957h21.996z"
                                        />
                                        <path
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5.784 8.174V5.305L6.74 1.48M12 8.791V1.48m6.216 6.694V5.305l-.957-3.825M7.696 18.695V14.39a1.435 1.435 0 0 1 2.869 0v4.304m-2.874-2.869h2.87m9.567 2.87h-1.912a.956.956 0 0 1-.957-.957v-3.825c0-.528.428-.957.957-.957h1.912m-2.873 2.869h1.913m-6.69-2.869v4.782c0 .528.428.957.956.957h1.913m-9.564-5.739H4.157a1.243 1.243 0 0 0-.691 2.283l1.76 1.173a1.243 1.243 0 0 1-.694 2.283H2.914"
                                        />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-black dark:text-white">Admin Dashboard</h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Access your admin dashboard to manage your application.
                                </p>
                            </div>

                            <svg
                                class="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                />
                            </svg>
                        </Link>

                        <Link
                            href="/customer/dashboard"
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16"
                            >
                                <svg
                                    class="size-5 sm:size-6"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                >
                                    <!-- Icon from Ultimate color icons by Streamline - https://creativecommons.org/licenses/by/4.0/ -->
                                    <g fill="none">
                                        <path
                                            fill="#c77f67"
                                            d="M1.12 22.522A.48.48 0 0 0 1.6 23h20.8a.48.48 0 0 0 .479-.478v-9.087H1.599a.48.48 0 0 0-.478.478z"
                                        />
                                        <path
                                            fill="#e3bfb3"
                                            d="M22.88 16.304v-2.87H1.599a.48.48 0 0 0-.478.479v2.391z"
                                        />
                                        <path
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M11.396 13.435H22.88"
                                        />
                                        <path
                                            fill="#e3e3e3"
                                            d="m8.535 23l.478-5.74h2.392v-3.347a4.783 4.783 0 0 0-9.566 0v3.348h2.392L4.709 23z"
                                        />
                                        <path
                                            fill="#fff"
                                            d="M6.622 9.13a4.783 4.783 0 0 0-4.783 4.783v2.633a4.783 4.783 0 0 1 9.566 0v-2.633A4.783 4.783 0 0 0 6.622 9.13"
                                        />
                                        <path
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m8.535 23l.478-5.74h2.392v-3.347a4.783 4.783 0 0 0-9.566 0v3.348h2.392L4.709 23z"
                                        />
                                        <path
                                            fill="#e3e3e3"
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6.622 7.696a3.348 3.348 0 1 0 0-6.696a3.348 3.348 0 0 0 0 6.696"
                                        />
                                        <path
                                            fill="#ffdda1"
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M17.622 9.13a2.152 2.152 0 1 0 0-4.304a2.152 2.152 0 0 0 0 4.304"
                                        />
                                        <path
                                            fill="#ff808c"
                                            stroke="#191919"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M21.927 13.435a4.443 4.443 0 0 0-8.61 0z"
                                        />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-black dark:text-white">Customer</h2>

                                <p class="mt-4 text-sm/relaxed">Customer painel.</p>
                            </div>

                            <svg
                                class="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                />
                            </svg>
                        </Link>

                        <div
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16"
                            >
                                <svg
                                    class="size-5 sm:size-6"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <g fill="#FF2D20">
                                        <path
                                            d="M16.597 12.635a.247.247 0 0 0-.08-.237 2.234 2.234 0 0 1-.769-1.68c.001-.195.03-.39.084-.578a.25.25 0 0 0-.09-.267 8.8 8.8 0 0 0-4.826-1.66.25.25 0 0 0-.268.181 2.5 2.5 0 0 1-2.4 1.824.045.045 0 0 0-.045.037 12.255 12.255 0 0 0-.093 3.86.251.251 0 0 0 .208.214c2.22.366 4.367 1.08 6.362 2.118a.252.252 0 0 0 .32-.079 10.09 10.09 0 0 0 1.597-3.733ZM13.616 17.968a.25.25 0 0 0-.063-.407A19.697 19.697 0 0 0 8.91 15.98a.25.25 0 0 0-.287.325c.151.455.334.898.548 1.328.437.827.981 1.594 1.619 2.28a.249.249 0 0 0 .32.044 29.13 29.13 0 0 0 2.506-1.99ZM6.303 14.105a.25.25 0 0 0 .265-.274 13.048 13.048 0 0 1 .205-4.045.062.062 0 0 0-.022-.07 2.5 2.5 0 0 1-.777-.982.25.25 0 0 0-.271-.149 11 11 0 0 0-5.6 2.815.255.255 0 0 0-.075.163c-.008.135-.02.27-.02.406.002.8.084 1.598.246 2.381a.25.25 0 0 0 .303.193 19.924 19.924 0 0 1 5.746-.438ZM9.228 20.914a.25.25 0 0 0 .1-.393 11.53 11.53 0 0 1-1.5-2.22 12.238 12.238 0 0 1-.91-2.465.248.248 0 0 0-.22-.187 18.876 18.876 0 0 0-5.69.33.249.249 0 0 0-.179.336c.838 2.142 2.272 4 4.132 5.353a.254.254 0 0 0 .15.048c1.41-.01 2.807-.282 4.117-.802ZM18.93 12.957l-.005-.008a.25.25 0 0 0-.268-.082 2.21 2.21 0 0 1-.41.081.25.25 0 0 0-.217.2c-.582 2.66-2.127 5.35-5.75 7.843a.248.248 0 0 0-.09.299.25.25 0 0 0 .065.091 28.703 28.703 0 0 0 2.662 2.12.246.246 0 0 0 .209.037c2.579-.701 4.85-2.242 6.456-4.378a.25.25 0 0 0 .048-.189 13.51 13.51 0 0 0-2.7-6.014ZM5.702 7.058a.254.254 0 0 0 .2-.165A2.488 2.488 0 0 1 7.98 5.245a.093.093 0 0 0 .078-.062 19.734 19.734 0 0 1 3.055-4.74.25.25 0 0 0-.21-.41 12.009 12.009 0 0 0-10.4 8.558.25.25 0 0 0 .373.281 12.912 12.912 0 0 1 4.826-1.814ZM10.773 22.052a.25.25 0 0 0-.28-.046c-.758.356-1.55.635-2.365.833a.25.25 0 0 0-.022.48c1.252.43 2.568.65 3.893.65.1 0 .2 0 .3-.008a.25.25 0 0 0 .147-.444c-.526-.424-1.1-.917-1.673-1.465ZM18.744 8.436a.249.249 0 0 0 .15.228 2.246 2.246 0 0 1 1.352 2.054c0 .337-.08.67-.23.972a.25.25 0 0 0 .042.28l.007.009a15.016 15.016 0 0 1 2.52 4.6.25.25 0 0 0 .37.132.25.25 0 0 0 .096-.114c.623-1.464.944-3.039.945-4.63a12.005 12.005 0 0 0-5.78-10.258.25.25 0 0 0-.373.274c.547 2.109.85 4.274.901 6.453ZM9.61 5.38a.25.25 0 0 0 .08.31c.34.24.616.561.8.935a.25.25 0 0 0 .3.127.631.631 0 0 1 .206-.034c2.054.078 4.036.772 5.69 1.991a.251.251 0 0 0 .267.024c.046-.024.093-.047.141-.067a.25.25 0 0 0 .151-.23A29.98 29.98 0 0 0 15.957.764a.25.25 0 0 0-.16-.164 11.924 11.924 0 0 0-2.21-.518.252.252 0 0 0-.215.076A22.456 22.456 0 0 0 9.61 5.38Z"
                                        />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-black dark:text-white">App Ecosystem</h2>

                                <p class="mt-4 text-sm/relaxed">
                                    <!--  -->
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    App v{{ laravelVersion }}-{{ phpVersion }}
                </footer>
            </div>
        </div>
    </div>
</template>

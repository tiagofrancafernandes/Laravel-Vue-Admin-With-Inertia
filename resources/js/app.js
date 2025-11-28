import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { setupDarkMode } from '@/composables/useDarkMode';
import AdminAppLayout from '@/Layouts/AppLayout.vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from '@vendor/tightenco/ziggy';
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Inicializa dark mode antes de criar a aplicação Vue
setupDarkMode();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: 'top-right',
                theme: 'auto',
                transition: 'slide',
                closeButton: true,
                pauseOnHover: true,
                pauseOnFocusLoss: true,
            })
            .component('AdminAppLayout', AdminAppLayout)
            .component('AppLayout', AdminAppLayout)
            .component('AuthenticatedLayout', AdminAppLayout)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/appmaker.js'),
            name: 'AppMaker',
            fileName: (format) => `appmaker.${format}.js`,
        },
        rollupOptions: {
            external: ['vue', '@inertiajs/vue3'],
            output: {
                globals: {
                    vue: 'Vue',
                    '@inertiajs/vue3': 'InertiaVue3',
                },
            },
        },
    },
});

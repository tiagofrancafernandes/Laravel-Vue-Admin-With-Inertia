import {
    fileURLToPath,
    URL,
} from 'node:url'; // Use 'node:url' for Node.js built-in module

import laravel from 'laravel-vite-plugin';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';
import { defineConfig } from 'vite';

import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        AutoImport({
            imports: [
                'vue',
                'pinia',
                {
                    '@inertiajs/vue3': [
                        'Head',
                        'Link',
                        'router',
                    ],
                },
            ],
            dts: 'resources/js/auto-imports.d.ts'
        }),
        // auto-import para componentes
        Components({
            dirs: [], // sem diretórios próprios
            resolvers: [
                {
                    type: 'component',
                    resolve: (name) => {
                        if (['Head', 'Link', ].includes(name)) {
                            return { name, from: '@inertiajs/vue3' };
                        }
                    }
                }
            ],
            dts: 'resources/js/auto-components.d.ts'
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Chunk size configuration
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/png|jpe?g|gif|svg|webp|ico/.test(ext)) {
                        return `images/[name]-[hash][extname]`;
                    } else if (/woff|woff2|eot|ttf|otf/.test(ext)) {
                        return `fonts/[name]-[hash][extname]`;
                    } else if (ext === 'css') {
                        return `css/[name]-[hash][extname]`;
                    }
                    return `[name]-[hash][extname]`;
                },
                // Manual chunks for better code splitting
                manualChunks(id) {
                    // Vendor chunks
                    if (id.includes('node_modules')) {
                        if (id.includes('@inertiajs')) {
                            return 'vendor-inertia';
                        }
                        if (id.includes('@vue')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('tailwindcss')) {
                            return 'vendor-tailwind';
                        }
                        return 'vendor-other';
                    }
                    // Component chunks
                    if (id.includes('/Components/')) {
                        return 'components';
                    }
                    // Layout chunks
                    if (id.includes('/Layouts/')) {
                        return 'layouts';
                    }
                },
            },
        },
        // Chunk size warnings
        chunkSizeWarningLimit: 600,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['@inertiajs/vue3', '@headlessui/vue'],
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@root': fileURLToPath(new URL('./', import.meta.url)),
            '@components': fileURLToPath(new URL('./resources/js/Components', import.meta.url)),
            '@vendor': fileURLToPath(new URL('./vendor', import.meta.url)),
            '@nm': fileURLToPath(new URL('./node_modules', import.meta.url)),
        },
    },
});

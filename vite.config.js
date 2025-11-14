import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
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
        include: [
            '@inertiajs/vue3',
            '@headlessui/vue',
        ],
    },
});

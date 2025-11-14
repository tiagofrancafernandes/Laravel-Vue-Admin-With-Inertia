/**
 * Composable for preloading Vue components
 * Improves perceived performance by loading components before they're needed
 */

import { onMounted } from 'vue';

/**
 * Preload components by importing them
 * @param loaders - Array of dynamic import functions
 */
export const usePreloadComponents = (loaders: Array<() => Promise<any>>) => {
    onMounted(() => {
        // Use requestIdleCallback if available, otherwise fallback to setTimeout
        const callback = () => {
            loaders.forEach((loader) => {
                loader().catch((error) => {
                    console.warn('Failed to preload component:', error);
                });
            });
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(callback, { timeout: 2000 });
        } else {
            setTimeout(callback, 1000);
        }
    });
};

/**
 * Preload a single component
 * @param loader - Dynamic import function
 */
export const usePreloadComponent = (loader: () => Promise<any>) => {
    usePreloadComponents([loader]);
};

/**
 * Batch preload multiple component sets
 * Useful for preloading related components
 */
export const usePreloadComponentSet = (sets: Record<string, Array<() => Promise<any>>>) => {
    onMounted(() => {
        const allLoaders = Object.values(sets).flat();

        if ('requestIdleCallback' in window) {
            allLoaders.forEach((loader, index) => {
                window.requestIdleCallback(
                    () => {
                        loader().catch((error) => {
                            console.warn('Failed to preload component set:', error);
                        });
                    },
                    { timeout: 5000 + index * 500 }
                );
            });
        } else {
            allLoaders.forEach((loader, index) => {
                setTimeout(
                    () => {
                        loader().catch((error) => {
                            console.warn('Failed to preload component set:', error);
                        });
                    },
                    500 + index * 300
                );
            });
        }
    });
};

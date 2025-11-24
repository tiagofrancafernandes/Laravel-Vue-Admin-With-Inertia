/**
 * Lazy load utility for Vue 3 components
 * Provides helpers for code splitting and lazy loading
 */

import { defineAsyncComponent, AsyncComponentLoader } from 'vue';

/**
 * Create a lazy-loaded component with loading and error states
 * @param importStatement - The dynamic import statement
 * @param delayMs - Delay in milliseconds before showing loading component
 */
export const lazyComponent = (importStatement: () => Promise<any>, delayMs: number = 200) => {
    return defineAsyncComponent({
        loader: importStatement,
        delay: delayMs,
        timeout: 10000,
        errorComponent: {
            template: '<div class="p-4 text-red-600 dark:text-red-400">Erro ao carregar componente</div>',
        },
        loadingComponent: {
            template: '<div class="p-4 text-gray-500 dark:text-gray-400">Carregando...</div>',
        },
    });
};

/**
 * Create multiple lazy-loaded components at once
 * Useful for component registrations
 */
export const createLazyComponents = (components: Record<string, () => Promise<any>>) => {
    const lazyComponents: Record<string, any> = {};

    for (const [name, loader] of Object.entries(components)) {
        lazyComponents[name] = lazyComponent(loader);
    }

    return lazyComponents;
};

/**
 * Track when components are mounted/unmounted for analytics
 */
export const trackComponentLoad = (componentName: string) => {
    if (typeof window !== 'undefined' && window.__COMPONENT_LOADS) {
        window.__COMPONENT_LOADS.push({
            name: componentName,
            timestamp: Date.now(),
        });
    }
};

declare global {
    interface Window {
        __COMPONENT_LOADS?: Array<{ name: string; timestamp: number }>;
    }
}

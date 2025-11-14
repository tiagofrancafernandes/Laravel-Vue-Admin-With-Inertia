/**
 * Performance monitoring utilities
 * Tracks and reports component loading times and other performance metrics
 */

/**
 * Measure the time it takes to load a component
 */
export class ComponentLoadTimer {
    private startTime: number = 0;
    private componentName: string;

    constructor(name: string) {
        this.componentName = name;
        this.startTime = performance.now();
    }

    /**
     * Complete the measurement and log the result
     */
    end() {
        const endTime = performance.now();
        const duration = endTime - this.startTime;

        if (duration > 100) {
            console.warn(`[Performance] ${this.componentName} took ${duration.toFixed(2)}ms to load`);
        } else {
            console.log(`[Performance] ${this.componentName} loaded in ${duration.toFixed(2)}ms`);
        }

        return duration;
    }
}

/**
 * Report Web Vitals to analytics
 * Tracks Core Web Vitals: LCP, FID, CLS
 */
export const reportWebVitals = (onReport: (metric: PerformanceMetric) => void) => {
    if (typeof window === 'undefined') return;

    // Largest Contentful Paint (LCP)
    if ('PerformanceObserver' in window) {
        try {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                onReport({
                    name: 'LCP',
                    value: lastEntry.renderTime || lastEntry.loadTime,
                    timestamp: lastEntry.startTime,
                });
            });
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        } catch (e) {
            console.error('Failed to observe LCP:', e);
        }

        // First Input Delay (FID)
        try {
            const fidObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry: any) => {
                    onReport({
                        name: 'FID',
                        value: entry.processingDuration,
                        timestamp: entry.startTime,
                    });
                });
            });
            fidObserver.observe({ entryTypes: ['first-input'] });
        } catch (e) {
            console.error('Failed to observe FID:', e);
        }

        // Cumulative Layout Shift (CLS)
        try {
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if ((entry as any).hadRecentInput) continue;
                    clsValue += (entry as any).value;
                    onReport({
                        name: 'CLS',
                        value: clsValue,
                        timestamp: entry.startTime,
                    });
                }
            });
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        } catch (e) {
            console.error('Failed to observe CLS:', e);
        }
    }
};

/**
 * Get current memory usage (if available)
 */
export const getMemoryUsage = () => {
    if (typeof (performance as any).memory === 'undefined') {
        return null;
    }

    const memory = (performance as any).memory;
    return {
        usedJSHeapSize: memory.usedJSHeapSize,
        totalJSHeapSize: memory.totalJSHeapSize,
        jsHeapSizeLimit: memory.jsHeapSizeLimit,
        percentUsed: (memory.usedJSHeapSize / memory.jsHeapSizeLimit) * 100,
    };
};

/**
 * Mark and measure custom performance entries
 */
export const markPerformance = {
    start: (name: string) => {
        if ('performance' in window) {
            window.performance.mark(`${name}-start`);
        }
    },
    end: (name: string) => {
        if ('performance' in window) {
            try {
                window.performance.mark(`${name}-end`);
                window.performance.measure(name, `${name}-start`, `${name}-end`);

                const measure = window.performance.getEntriesByName(name)[0];
                console.log(`[Measure] ${name}: ${(measure as any).duration.toFixed(2)}ms`);
            } catch (e) {
                console.error(`Failed to measure ${name}:`, e);
            }
        }
    },
};

export interface PerformanceMetric {
    name: string;
    value: number;
    timestamp: number;
}

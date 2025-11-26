import Toastify from 'toastify-js';

export type ToastType = 'success' | 'error' | 'info' | 'warning';

interface ToastOptions {
    duration?: number;
    position?: 'top-left' | 'top-center' | 'top-right' | 'bottom-left' | 'bottom-center' | 'bottom-right';
    newWindow?: boolean;
    close?: boolean;
    gravity?: 'top' | 'bottom';
}

const getBackgroundColor = (type: ToastType): string => {
    const colors: Record<ToastType, string> = {
        success: '#10b981', // green-500
        error: '#ef4444', // red-500
        info: '#3b82f6', // blue-500
        warning: '#f59e0b', // amber-500
    };
    return colors[type];
};

export const useToast = () => {
    const show = (message: string, type: ToastType = 'info', options: ToastOptions = {}) => {
        const defaultOptions = {
            duration: 3000,
            position: 'top-right' as const,
            newWindow: true,
            close: true,
            gravity: 'top' as const,
        };

        const finalOptions = { ...defaultOptions, ...options };

        Toastify({
            text: message,
            duration: finalOptions.duration,
            gravity: finalOptions.gravity,
            position: finalOptions.position,
            newWindow: finalOptions.newWindow,
            close: finalOptions.close,
            backgroundColor: getBackgroundColor(type),
            className: 'toastify-toast',
            style: {
                fontFamily: 'inherit',
                fontSize: '14px',
                padding: '12px 16px',
                borderRadius: '6px',
            },
        }).showToast();
    };

    const success = (message: string, options?: ToastOptions) => show(message, 'success', options);
    const error = (message: string, options?: ToastOptions) => show(message, 'error', options);
    const info = (message: string, options?: ToastOptions) => show(message, 'info', options);
    const warning = (message: string, options?: ToastOptions) => show(message, 'warning', options);

    return {
        show,
        success,
        error,
        info,
        warning,
    };
};

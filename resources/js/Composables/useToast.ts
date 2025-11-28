import { toast, type ToastOptions } from 'vue3-toastify';

export type ToastType = 'success' | 'error' | 'info' | 'warning';

interface CustomToastOptions extends Omit<ToastOptions, 'type'> {
    duration?: number;
}

export const useToast = () => {
    const show = (message: string, type: ToastType = 'info', options: CustomToastOptions = {}) => {
        const defaultOptions: ToastOptions = {
            autoClose: options.duration || 3000,
            position: 'top-right',
            theme: 'auto',
            transition: 'slide',
            closeButton: true,
            pauseOnHover: true,
            pauseOnFocusLoss: true,
            ...options,
        };

        switch (type) {
            case 'success':
                toast.success(message, defaultOptions);
                break;
            case 'error':
                toast.error(message, defaultOptions);
                break;
            case 'warning':
                toast.warning(message, defaultOptions);
                break;
            case 'info':
            default:
                toast.info(message, defaultOptions);
                break;
        }
    };

    const success = (message: string, options?: CustomToastOptions) => show(message, 'success', options);
    const error = (message: string, options?: CustomToastOptions) => show(message, 'error', options);
    const info = (message: string, options?: CustomToastOptions) => show(message, 'info', options);
    const warning = (message: string, options?: CustomToastOptions) => show(message, 'warning', options);

    return {
        show,
        success,
        error,
        info,
        warning,
    };
};

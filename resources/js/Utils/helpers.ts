import { route as _route } from '@root/vendor/tightenco/ziggy';

export type AnyObject = Record<string, unknown>;
export interface GenericObject extends AnyObject {}

export const debugIsOn = function () {
    if (typeof localStorage === 'undefined' || !localStorage) {
        return false;
    }

    return Boolean(Number(localStorage?.getItem('debug_on')));
};

export const globalErrorCatcher = function (error: unknown) {
    if (debugIsOn()) {
        console.error('Error:', error);
    }
};

export const isString = function (value: unknown) {
    return typeof value === 'string' || value instanceof String;
};

export const isNumber = function (value: unknown) {
    return (typeof value === 'number' || value instanceof Number) && !isNaN(value as number);
};

export const isNumeric = function (value: unknown) {
    return !isNaN(Number(value));
};

export const ifNumeric = function (value: unknown) {
    return isNumeric(value) ? Number(value) : null;
};

export const isFilledString = function (value: unknown) {
    return isString(value) && value.trim()?.length > 0;
};

export function isValidDate(value: unknown): boolean {
    try {
        value = ['string', 'number'].includes(typeof value) || value instanceof Date ? value : null;

        if (value === null || !value || typeof value === 'object') {
            return false;
        }

        const d = new Date(value as string | number | Date);

        // Data inválida não lança exceção, mas gera NaN
        if (isNaN(d.getTime())) {
            return false;
        }

        return true;
    } catch (_) {
        return false;
    }
}

export function ifValidDate(value: unknown): null | Date {
    try {
        if (isValidDate(value)) {
            return null;
        }

        const d = new Date(value as string | number | Date);

        if (isNaN(d.getTime())) {
            return null;
        }

        return d;
    } catch (_) {
        return null;
    }
}

/**
 * Format number as Brazilian currency
 */
export function formatCurrency(value: string | number) {
    value = isNumeric(value) ? Number(value) : 0;

    if (typeof value !== 'number') {
        value = 0;
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

/**
 * Format date as Brazilian format
 */
export function formatDate(date: any): string {
    return (
        ifValidDate(date)?.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }) || ''
    );
}

export function formatDateFull(date: any): string {
    return (
        ifValidDate(date)?.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }) || ''
    );
}

export function formatPtBrDate(date: any) {
    date = ifValidDate(date);

    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('pt-BR').format(date);
}

/**
 * Format datetime as Brazilian format
 */
export function formatDateTime(date: string | number | Date) {
    try {
        if (!date) {
            return '';
        }

        date = date instanceof Date ? date : new Date(date);

        return new Intl.DateTimeFormat('pt-BR', {
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(date);
    } catch (error) {
        globalErrorCatcher(error);
        return '';
    }
}

/**
 * Format phone number
 */
export function formatPhone(phone: string) {
    if (!isFilledString(phone)) {
        return '';
    }

    const cleaned = phone.replace(/\D/g, '');

    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }

    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    return phone;
}

/**
 * Format document (CPF/CNPJ)
 */
export function formatDocument(document: string) {
    if (!isFilledString(document)) {
        return '';
    }

    const cleaned = document.replace(/\D/g, '');

    if (cleaned.length === 11) {
        // CPF
        return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    if (cleaned.length === 14) {
        // CNPJ
        return cleaned.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }

    return document;
}

/**
 * Get sale status label
 */
export function getSaleStatusLabel(status: string) {
    const labels: AnyObject = {
        completed: 'Concluída',
        pending: 'Pendente',
        cancelled: 'Cancelada',
    };

    return labels[status] || status;
}

/**
 * Get sale status color class
 */
export function getSaleStatusColor(status: string) {
    const colors: AnyObject = {
        completed: 'text-green-600 bg-green-50',
        pending: 'text-yellow-600 bg-yellow-50',
        cancelled: 'text-red-600 bg-red-50',
    };

    return colors[status] || 'text-gray-600 bg-gray-50';
}

/**
 * Get payment method label
 */
export function getPaymentMethodLabel(code: string) {
    const labels: AnyObject = {
        cash: 'Dinheiro',
        pix: 'PIX',
        credit_card: 'Cartão de Crédito',
        debit_card: 'Cartão de Débito',
        balance: 'Saldo',
        tab: 'Caderneta',
    };

    return labels[code] || code;
}

/**
 * Get ledger type label
 */
export function getLedgerTypeLabel(type: string) {
    const labels: AnyObject = {
        credit: 'Crédito (Saldo)',
        debit: 'Débito (Saldo)',
        tab_credit: 'Pagamento (Caderneta)',
        tab_debit: 'Dívida (Caderneta)',
    };

    if (type in labels) {
        return labels[type] || type;
    }

    return type;
}

/**
 * Get ledger type color
 */
export function getLedgerTypeColor(type: string) {
    const colors: AnyObject = {
        credit: 'text-green-600',
        debit: 'text-red-600',
        tab_credit: 'text-blue-600',
        tab_debit: 'text-orange-600',
    };

    return colors[type] || 'text-gray-600';
}

/**
 * Truncate text
 */
export function truncate(text: string, length: number = 50) {
    if (!text) {
        return '';
    }

    if (text.length <= length) {
        return text;
    }

    return text.substring(0, length) + '...';
}

/**
 * Parse number from Brazilian format
 */
export function parseNumber(value: unknown) {
    if (typeof value === 'number') {
        return value;
    }

    if (!value) {
        return 0;
    }

    // Remove currency symbol and spaces
    const cleaned = value
        .toString()
        .replace(/[R$\s]/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    return parseFloat(cleaned) || 0;
}

export const strEndsWith = function (value: any, toCheck: any): boolean {
    if (typeof value !== 'string' || typeof toCheck !== 'string') {
        return false;
    }

    return value.endsWith(toCheck);
};

/**
 * @param {string} name
 * @param {?(unknown | undefined)} [params]
 * @param {?boolean} [absolute]
 * @param {?AnyObject} [config]
 *
 * @returns {string}
 */
export const route = function (
    name: string,
    params?: unknown | undefined,
    absolute?: boolean,
    config?: AnyObject
): string {
    // @ts-ignore
    return _route(name, params, absolute, config);
};

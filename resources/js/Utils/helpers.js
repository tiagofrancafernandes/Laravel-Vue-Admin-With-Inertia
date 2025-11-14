/**
 * Format number as Brazilian currency
 */
export function formatCurrency(value) {
    if (value === null || value === undefined) {
        return 'R$ 0,00';
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

/**
 * Format date as Brazilian format
 */
export function formatDate(date) {
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

/**
 * Format datetime as Brazilian format
 */
export function formatDateTime(date) {
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(date));
}

/**
 * Format phone number
 */
export function formatPhone(phone) {
    if (!phone) {
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
export function formatDocument(document) {
    if (!document) {
        return '';
    }

    const cleaned = document.replace(/\D/g, '');

    if (cleaned.length === 11) {
        // CPF
        return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    if (cleaned.length === 14) {
        // CNPJ
        return cleaned.replace(
            /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
            '$1.$2.$3/$4-$5'
        );
    }

    return document;
}

/**
 * Get sale status label
 */
export function getSaleStatusLabel(status) {
    const labels = {
        completed: 'Concluída',
        pending: 'Pendente',
        cancelled: 'Cancelada',
    };

    return labels[status] || status;
}

/**
 * Get sale status color class
 */
export function getSaleStatusColor(status) {
    const colors = {
        completed: 'text-green-600 bg-green-50',
        pending: 'text-yellow-600 bg-yellow-50',
        cancelled: 'text-red-600 bg-red-50',
    };

    return colors[status] || 'text-gray-600 bg-gray-50';
}

/**
 * Get payment method label
 */
export function getPaymentMethodLabel(code) {
    const labels = {
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
export function getLedgerTypeLabel(type) {
    const labels = {
        credit: 'Crédito (Saldo)',
        debit: 'Débito (Saldo)',
        tab_credit: 'Pagamento (Caderneta)',
        tab_debit: 'Dívida (Caderneta)',
    };

    return labels[type] || type;
}

/**
 * Get ledger type color
 */
export function getLedgerTypeColor(type) {
    const colors = {
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
export function truncate(text, length = 50) {
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
export function parseNumber(value) {
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

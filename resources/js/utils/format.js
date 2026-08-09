export function formatCurrency(amount, options = {}) {
    const value = Number(amount ?? 0);

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
        ...options,
    }).format(Number.isFinite(value) ? value : 0);
}

export function formatDate(value, options = {}) {
    if (!value) return '—';

    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        ...options,
    }).format(date);
}

export function formatDateTime(value) {
    return formatDate(value, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

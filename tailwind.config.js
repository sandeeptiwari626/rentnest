import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                rn: {
                    bg: '#F7F8FA',
                    surface: '#FFFFFF',
                    border: '#E5E7EB',
                    text: '#111827',
                    muted: '#6B7280',
                    accent: '#0F766E',
                    'accent-soft': '#CCFBF1',
                    success: '#059669',
                    'success-soft': '#D1FAE5',
                    warning: '#D97706',
                    'warning-soft': '#FEF3C7',
                    danger: '#DC2626',
                    'danger-soft': '#FEE2E2',
                    info: '#0284C7',
                    'info-soft': '#E0F2FE',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                brand: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            borderRadius: {
                rn: 'var(--rn-radius)',
            },
            boxShadow: {
                rn: 'var(--rn-shadow)',
            },
        },
    },

    plugins: [forms],
};

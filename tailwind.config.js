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
                    bg: '#F4F1EA',
                    surface: '#FFFCF7',
                    border: '#E6E0D4',
                    text: '#14110E',
                    muted: '#6F675C',
                    accent: '#0D4F4A',
                    'accent-soft': '#DCEEE8',
                    gold: '#C4A574',
                    ink: '#0C1614',
                    success: '#047857',
                    'success-soft': '#D1FAE5',
                    warning: '#B45309',
                    'warning-soft': '#FEF3C7',
                    danger: '#B91C1C',
                    'danger-soft': '#FEE2E2',
                    info: '#0E7490',
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
                'rn-lg': '0 24px 60px rgb(12 22 20 / 0.18)',
            },
        },
    },

    plugins: [forms],
};

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
    size: {
        type: String,
        default: 'md',
    },
    type: {
        type: String,
        default: 'button',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    const base =
        'inline-flex items-center justify-center gap-2 rounded-xl font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-rn-accent/30 disabled:cursor-not-allowed disabled:opacity-50';

    const sizes = {
        sm: 'px-3 py-1.5 text-xs',
        md: 'px-4 py-2 text-sm',
        lg: 'px-5 py-2.5 text-sm',
    };

    const variants = {
        primary:
            'bg-rn-accent text-white shadow-sm hover:bg-teal-800',
        secondary:
            'border border-rn-border bg-rn-surface text-rn-text hover:bg-rn-bg',
        ghost: 'text-rn-muted hover:bg-rn-bg hover:text-rn-text',
        danger: 'bg-rn-danger text-white shadow-sm hover:bg-red-700',
        soft: 'bg-rn-accent-soft text-rn-accent hover:bg-teal-100',
    };

    return [base, sizes[props.size] || sizes.md, variants[props.variant] || variants.primary];
});
</script>

<template>
    <button
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot />
    </button>
</template>

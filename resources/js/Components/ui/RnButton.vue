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
        'inline-flex items-center justify-center gap-2 rounded-2xl font-semibold tracking-tight transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-rn-gold/40 disabled:cursor-not-allowed disabled:opacity-50';

    const sizes = {
        sm: 'px-3.5 py-1.5 text-xs',
        md: 'px-4 py-2.5 text-sm',
        lg: 'px-6 py-3 text-sm',
    };

    const variants = {
        primary:
            'bg-rn-accent text-white shadow-sm hover:bg-rn-ink',
        secondary:
            'border border-rn-border bg-rn-surface text-rn-text hover:border-rn-gold/50 hover:bg-white',
        ghost: 'text-rn-muted hover:bg-white/70 hover:text-rn-text',
        danger: 'bg-rn-danger text-white shadow-sm hover:bg-red-800',
        soft: 'bg-rn-accent-soft text-rn-accent hover:bg-teal-100',
        gold: 'bg-rn-gold text-rn-ink hover:bg-[#b39062]',
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

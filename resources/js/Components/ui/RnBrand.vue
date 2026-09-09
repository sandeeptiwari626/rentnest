<script setup>
defineProps({
    size: {
        type: String,
        default: 'md',
    },
    variant: {
        type: String,
        default: 'default',
    },
    markOnly: {
        type: Boolean,
        default: false,
    },
});

const sizeClass = {
    sm: 'text-lg',
    md: 'text-xl',
    lg: 'text-3xl sm:text-4xl',
};

const markSize = {
    sm: 'h-8 w-8',
    md: 'h-9 w-9',
    lg: 'h-11 w-11',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-2.5 font-brand font-semibold tracking-tight"
        :class="[
            sizeClass[size] || sizeClass.md,
            variant === 'light' ? 'text-rn-surface' : 'text-rn-text',
        ]"
    >
        <span
            class="relative inline-flex items-center justify-center rounded-2xl"
            :class="[
                markSize[size] || markSize.md,
                variant === 'light' ? 'bg-white/10 ring-1 ring-white/15' : 'bg-rn-ink text-rn-gold',
            ]"
            aria-hidden="true"
        >
            <svg viewBox="0 0 32 32" class="h-[58%] w-[58%]" fill="none">
                <path
                    d="M6 15.2 16 7l10 8.2V25a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V15.2Z"
                    :stroke="variant === 'light' ? '#C4A574' : 'currentColor'"
                    stroke-width="1.7"
                    stroke-linejoin="round"
                />
                <path
                    d="M13 27v-7h6v7"
                    :stroke="variant === 'light' ? '#FFFCF7' : '#F4F1EA'"
                    stroke-width="1.7"
                    stroke-linejoin="round"
                />
            </svg>
        </span>
        <span v-if="!markOnly">RentNest</span>
    </span>
</template>

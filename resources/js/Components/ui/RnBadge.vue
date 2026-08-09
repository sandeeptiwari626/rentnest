<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: {
        type: String,
        default: 'neutral',
    },
    size: {
        type: String,
        default: 'md',
    },
});

const classes = computed(() => {
    const sizes = {
        sm: 'px-2 py-0.5 text-[11px]',
        md: 'px-2.5 py-1 text-xs',
        lg: 'px-3 py-1 text-sm',
    };

    const colors = {
        success: 'bg-rn-success-soft text-rn-success',
        warning: 'bg-rn-warning-soft text-rn-warning',
        danger: 'bg-rn-danger-soft text-rn-danger',
        info: 'bg-rn-info-soft text-rn-info',
        neutral: 'bg-rn-bg text-rn-muted border border-rn-border',
    };

    return [
        'inline-flex items-center rounded-full font-medium',
        sizes[props.size] || sizes.md,
        colors[props.color] || colors.neutral,
    ];
});
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>

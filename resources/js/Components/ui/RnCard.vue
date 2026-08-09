<script setup>
const props = defineProps({
    padding: {
        type: String,
        default: 'md',
    },
    flush: {
        type: Boolean,
        default: false,
    },
});

const paddingClass = {
    none: '',
    sm: 'p-4',
    md: 'p-5 sm:p-6',
    lg: 'p-6 sm:p-8',
};
</script>

<template>
    <div class="rn-card overflow-hidden">
        <div
            v-if="$slots.title || $slots.subtitle || $slots.actions"
            class="flex items-start justify-between gap-4 border-b border-rn-border px-5 py-4 sm:px-6"
        >
            <div class="min-w-0">
                <div v-if="$slots.title" class="text-base font-semibold text-rn-text">
                    <slot name="title" />
                </div>
                <div v-if="$slots.subtitle" class="mt-0.5 text-sm text-rn-muted">
                    <slot name="subtitle" />
                </div>
            </div>
            <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
        <div :class="flush ? '' : (paddingClass[props.padding] || paddingClass.md)">
            <slot />
        </div>
    </div>
</template>

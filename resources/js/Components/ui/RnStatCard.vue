<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    trend: {
        type: String,
        default: '',
    },
    trendPositive: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="rn-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-rn-muted">{{ label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-rn-text">
                    {{ value }}
                </p>
            </div>
            <div
                v-if="$slots.icon"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-rn-accent-soft text-rn-accent"
            >
                <slot name="icon" />
            </div>
        </div>
        <div v-if="hint || trend" class="mt-3 flex items-center gap-2 text-xs">
            <span
                v-if="trend"
                class="font-medium"
                :class="trendPositive ? 'text-rn-success' : 'text-rn-danger'"
            >
                {{ trend }}
            </span>
            <span v-if="hint" class="text-rn-muted">{{ hint }}</span>
        </div>
    </div>
</template>

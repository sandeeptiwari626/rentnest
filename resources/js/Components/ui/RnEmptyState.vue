<script setup>
defineProps({
    title: {
        type: String,
        default: 'Nothing here yet',
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
        <div
            v-if="$slots.icon"
            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-rn-accent-soft text-rn-accent"
        >
            <slot name="icon" />
        </div>
        <h3 class="text-base font-semibold text-rn-text">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-rn-muted">
            {{ description }}
        </p>
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>

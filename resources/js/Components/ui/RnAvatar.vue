<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: {
        type: String,
        default: '',
    },
    src: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
    },
});

const sizeClass = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-12 w-12 text-base',
    xl: 'h-14 w-14 text-lg',
};

const initials = computed(() => {
    if (!props.name) {
        return '?';
    }

    return props.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || '')
        .join('');
});
</script>

<template>
    <div
        class="inline-flex shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-rn-ink font-semibold text-rn-gold ring-1 ring-rn-gold/20"
        :class="sizeClass[size] || sizeClass.md"
    >
        <img
            v-if="src"
            :src="src"
            :alt="name || 'Avatar'"
            class="h-full w-full object-cover"
        />
        <span v-else>{{ initials }}</span>
    </div>
</template>

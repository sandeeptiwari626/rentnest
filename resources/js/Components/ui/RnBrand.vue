<script setup>
defineProps({
    size: {
        type: String,
        default: 'md',
    },
});

const sizeClass = {
    sm: 'text-lg',
    md: 'text-xl',
    lg: 'text-3xl sm:text-4xl',
};
</script>

<template>
    <span
        class="font-brand font-semibold tracking-tight text-rn-text"
        :class="sizeClass[size] || sizeClass.md"
    >
        RentNest
    </span>
</template>

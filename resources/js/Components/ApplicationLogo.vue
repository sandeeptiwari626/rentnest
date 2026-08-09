<script setup>
import RnBrand from '@/Components/ui/RnBrand.vue';

defineProps({
    size: {
        type: String,
        default: 'md',
    },
});
</script>

<template>
    <RnBrand :size="size" />
</template>

<script setup>
defineOptions({ inheritAttrs: false });
</script>

<template>
    <div
        class="animate-pulse rounded-xl bg-slate-200/80"
        v-bind="$attrs"
        :class="[$attrs.class || 'h-4 w-full']"
        aria-hidden="true"
    />
</template>

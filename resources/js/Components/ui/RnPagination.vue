<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap items-center justify-center gap-1"
        aria-label="Pagination"
    >
        <template v-for="(link, index) in links" :key="index">
            <component
                :is="link.url ? Link : 'span'"
                :href="link.url || undefined"
                class="inline-flex min-w-9 items-center justify-center rounded-lg px-2.5 py-1.5 text-sm transition"
                :class="[
                    link.active
                        ? 'bg-rn-accent font-semibold text-white'
                        : 'text-rn-muted hover:bg-rn-bg hover:text-rn-text',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
                v-html="
                    link.label
                        .replace('&laquo; Previous', '‹')
                        .replace('Next &raquo;', '›')
                        .replace('Previous', '‹')
                        .replace('Next', '›')
                "
            />
        </template>
    </nav>
</template>

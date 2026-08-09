<script setup>
defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const statusDot = {
    success: 'bg-rn-success',
    warning: 'bg-rn-warning',
    danger: 'bg-rn-danger',
    info: 'bg-rn-info',
    pending: 'bg-rn-muted',
    default: 'bg-rn-accent',
};
</script>

<template>
    <ol class="relative space-y-0">
        <li
            v-for="(item, index) in items"
            :key="index"
            class="relative flex gap-4 pb-8 last:pb-0"
        >
            <div class="flex flex-col items-center">
                <span
                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-rn-surface"
                    :class="statusDot[item.status] || statusDot.default"
                />
                <span
                    v-if="index < items.length - 1"
                    class="mt-1 w-px flex-1 bg-rn-border"
                />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h4 class="text-sm font-semibold text-rn-text">
                        {{ item.title }}
                    </h4>
                    <time
                        v-if="item.date"
                        class="text-xs text-rn-muted"
                    >
                        {{ item.date }}
                    </time>
                </div>
                <p
                    v-if="item.description"
                    class="mt-1 text-sm text-rn-muted"
                >
                    {{ item.description }}
                </p>
            </div>
        </li>
    </ol>
</template>

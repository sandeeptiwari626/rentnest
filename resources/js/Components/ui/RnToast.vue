<script setup>
defineProps({
    type: {
        type: String,
        default: 'success',
    },
    message: {
        type: String,
        default: '',
    },
    show: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['close']);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="show && message"
            class="rounded-2xl border bg-rn-surface px-4 py-3 text-sm font-medium text-rn-text shadow-rn"
            :class="
                type === 'error' || type === 'danger'
                    ? 'border-rn-danger/20'
                    : 'border-rn-success/20'
            "
            role="status"
        >
            <div class="flex items-start justify-between gap-3">
                <p>{{ message }}</p>
                <button
                    type="button"
                    class="text-rn-muted hover:text-rn-text"
                    @click="$emit('close')"
                >
                    ×
                </button>
            </div>
        </div>
    </Transition>
</template>

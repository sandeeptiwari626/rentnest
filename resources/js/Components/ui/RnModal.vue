<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: 'lg',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const onKeydown = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

watch(
    () => props.show,
    (value) => {
        document.body.style.overflow = value ? 'hidden' : '';
    },
);

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px]"
                    @click="close"
                />

                <Transition
                    enter-active-class="ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-3 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-3 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="relative z-10 w-full overflow-hidden rounded-2xl border border-rn-border bg-rn-surface shadow-rn"
                        :class="maxWidthClass"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            v-if="title || closeable"
                            class="flex items-center justify-between gap-3 border-b border-rn-border px-5 py-4"
                        >
                            <h3 class="text-base font-semibold text-rn-text">
                                {{ title }}
                            </h3>
                            <button
                                v-if="closeable"
                                type="button"
                                class="rounded-lg p-1.5 text-rn-muted transition hover:bg-rn-bg hover:text-rn-text"
                                @click="close"
                            >
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                        <div class="px-5 py-5">
                            <slot />
                        </div>
                        <div
                            v-if="$slots.footer"
                            class="flex items-center justify-end gap-2 border-t border-rn-border bg-rn-bg/60 px-5 py-4"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

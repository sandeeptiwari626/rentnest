<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timer = null;

const flash = computed(() => page.props.flash || {});

const show = (nextType, nextMessage) => {
    if (!nextMessage) {
        return;
    }

    type.value = nextType;
    message.value = nextMessage;
    visible.value = true;

    clearTimeout(timer);
    timer = setTimeout(() => {
        visible.value = false;
    }, 4500);
};

watch(
    () => [flash.value.success, flash.value.error],
    ([success, error]) => {
        if (success) {
            show('success', success);
        } else if (error) {
            show('error', error);
        }
    },
);

onMounted(() => {
    if (flash.value.success) {
        show('success', flash.value.success);
    } else if (flash.value.error) {
        show('error', flash.value.error);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="visible"
                class="fixed bottom-4 right-4 z-[60] w-[min(100%-2rem,24rem)]"
                role="status"
            >
                <div
                    class="flex items-start gap-3 rounded-2xl border bg-rn-surface px-4 py-3 shadow-rn"
                    :class="
                        type === 'success'
                            ? 'border-rn-success/20'
                            : 'border-rn-danger/20'
                    "
                >
                    <CheckCircleIcon
                        v-if="type === 'success'"
                        class="mt-0.5 h-5 w-5 shrink-0 text-rn-success"
                    />
                    <XCircleIcon
                        v-else
                        class="mt-0.5 h-5 w-5 shrink-0 text-rn-danger"
                    />
                    <p class="flex-1 text-sm font-medium text-rn-text">
                        {{ message }}
                    </p>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-rn-muted hover:bg-rn-bg hover:text-rn-text"
                        @click="visible = false"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

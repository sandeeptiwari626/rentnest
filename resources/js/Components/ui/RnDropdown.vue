<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: 'w-56',
    },
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const alignment = {
    left: 'left-0 origin-top-left',
    right: 'right-0 origin-top-right',
};
</script>

<template>
    <div class="relative inline-block text-left">
        <div @click="open = !open">
            <slot name="trigger" :open="open" />
        </div>

        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        />

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 rounded-xl border border-rn-border bg-rn-surface py-1 shadow-rn"
                :class="[width, alignment[align] || alignment.right]"
                @click="open = false"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>

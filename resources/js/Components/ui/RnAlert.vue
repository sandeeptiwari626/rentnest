<script setup>
import { computed } from 'vue';
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
    },
    title: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
});

const styles = computed(() => {
    return {
        success: {
            wrap: 'border-rn-success/20 bg-rn-success-soft text-rn-success',
            icon: CheckCircleIcon,
        },
        warning: {
            wrap: 'border-rn-warning/20 bg-rn-warning-soft text-rn-warning',
            icon: ExclamationTriangleIcon,
        },
        danger: {
            wrap: 'border-rn-danger/20 bg-rn-danger-soft text-rn-danger',
            icon: XCircleIcon,
        },
        info: {
            wrap: 'border-rn-info/20 bg-rn-info-soft text-rn-info',
            icon: InformationCircleIcon,
        },
    }[props.type] || {
        wrap: 'border-rn-info/20 bg-rn-info-soft text-rn-info',
        icon: InformationCircleIcon,
    };
});
</script>

<template>
    <div
        class="flex gap-3 rounded-xl border px-4 py-3"
        :class="styles.wrap"
        role="alert"
    >
        <component :is="styles.icon" class="mt-0.5 h-5 w-5 shrink-0" />
        <div class="min-w-0">
            <p v-if="title" class="text-sm font-semibold">{{ title }}</p>
            <p v-if="message" class="text-sm" :class="{ 'mt-0.5': title }">
                {{ message }}
            </p>
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    form: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        v-if="Object.keys(form.errors).length"
        class="rounded-2xl border border-rn-danger/30 bg-rn-danger/5 px-4 py-3 text-sm text-rn-danger"
    >
        <p class="font-medium">Please fix the highlighted fields and try again.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
        </ul>
    </div>
</template>

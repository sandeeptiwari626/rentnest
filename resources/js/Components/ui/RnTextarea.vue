<script setup>
defineProps({
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    rows: {
        type: [Number, String],
        default: 4,
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const model = defineModel({
    type: String,
    default: '',
});
</script>

<template>
    <div>
        <label
            v-if="label"
            :for="id || undefined"
            class="rn-label"
        >
            {{ label }}
            <span v-if="required" class="text-rn-danger">*</span>
        </label>
        <textarea
            :id="id || undefined"
            v-model="model"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            class="rn-input"
            :class="{ 'border-rn-danger focus:border-rn-danger focus:ring-rn-danger/20': error }"
        />
        <p v-if="error" class="mt-1.5 text-sm text-rn-danger">{{ error }}</p>
    </div>
</template>

<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    id: {
        type: String,
        default: '',
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
    autocomplete: {
        type: String,
        default: '',
    },
});

const model = defineModel({
    type: [String, Number],
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
        <input
            :id="id || undefined"
            v-model="model"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :autocomplete="autocomplete || undefined"
            class="rn-input"
            :class="{ 'border-rn-danger focus:border-rn-danger focus:ring-rn-danger/20': error }"
            v-bind="$attrs"
        />
        <p v-if="error" class="mt-1.5 text-sm text-rn-danger">{{ error }}</p>
    </div>
</template>

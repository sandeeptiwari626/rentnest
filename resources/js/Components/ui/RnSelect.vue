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
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select…',
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
        <select
            :id="id || undefined"
            v-model="model"
            :disabled="disabled"
            :required="required"
            class="rn-input"
            :class="{ 'border-rn-danger focus:border-rn-danger focus:ring-rn-danger/20': error }"
        >
            <option v-if="placeholder" disabled value="">
                {{ placeholder }}
            </option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1.5 text-sm text-rn-danger">{{ error }}</p>
    </div>
</template>

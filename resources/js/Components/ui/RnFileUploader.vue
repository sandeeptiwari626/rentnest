<script setup>
import { ref } from 'vue';
import { CloudArrowUpIcon, DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    accepts: {
        type: String,
        default: '',
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: 'Upload files',
    },
    hint: {
        type: String,
        default: 'Drag and drop, or click to browse',
    },
});

const emit = defineEmits(['update:files', 'files']);

const inputRef = ref(null);
const files = ref([]);
const dragging = ref(false);

const setFiles = (fileList) => {
    const next = Array.from(fileList || []);
    files.value = props.multiple ? next : next.slice(0, 1);
    emit('update:files', files.value);
    emit('files', files.value);
};

const onChange = (event) => {
    setFiles(event.target.files);
};

const onDrop = (event) => {
    dragging.value = false;
    setFiles(event.dataTransfer.files);
};

const removeAt = (index) => {
    files.value = files.value.filter((_, i) => i !== index);
    emit('update:files', files.value);
    emit('files', files.value);
};
</script>

<template>
    <div>
        <div
            class="rounded-2xl border border-dashed border-rn-border bg-rn-bg/70 px-6 py-8 text-center transition"
            :class="{ 'border-rn-accent bg-rn-accent-soft/40': dragging }"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <CloudArrowUpIcon class="mx-auto h-8 w-8 text-rn-accent" />
            <p class="mt-3 text-sm font-medium text-rn-text">{{ label }}</p>
            <p class="mt-1 text-xs text-rn-muted">{{ hint }}</p>
            <button
                type="button"
                class="mt-4 inline-flex items-center rounded-xl border border-rn-border bg-rn-surface px-3 py-1.5 text-sm font-medium text-rn-text shadow-sm transition hover:bg-white"
                @click="inputRef?.click()"
            >
                Choose files
            </button>
            <input
                ref="inputRef"
                type="file"
                class="hidden"
                :accept="accepts || undefined"
                :multiple="multiple"
                @change="onChange"
            />
        </div>

        <ul v-if="files.length" class="mt-3 space-y-2">
            <li
                v-for="(file, index) in files"
                :key="`${file.name}-${index}`"
                class="flex items-center justify-between gap-3 rounded-xl border border-rn-border bg-rn-surface px-3 py-2"
            >
                <div class="flex min-w-0 items-center gap-2">
                    <DocumentIcon class="h-4 w-4 shrink-0 text-rn-muted" />
                    <span class="truncate text-sm text-rn-text">{{ file.name }}</span>
                    <span class="shrink-0 text-xs text-rn-muted">
                        {{ Math.max(1, Math.round(file.size / 1024)) }} KB
                    </span>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-rn-muted hover:bg-rn-bg hover:text-rn-text"
                    @click="removeAt(index)"
                >
                    <XMarkIcon class="h-4 w-4" />
                </button>
            </li>
        </ul>
    </div>
</template>

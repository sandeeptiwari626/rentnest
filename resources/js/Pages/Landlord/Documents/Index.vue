<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { DocumentTextIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ documents: Object, filters: Object, typeOptions: Array });
const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
const sizeLabel = (bytes) => (bytes ? `${Math.max(1, Math.round(bytes / 1024))} KB` : '—');

watch([search, type], () => {
    router.get(route('landlord.documents.index'), {
        search: search.value || undefined,
        type: type.value || undefined,
    }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Delete this document?')) router.delete(route('landlord.documents.destroy', id));
};
</script>

<template>
    <Head title="Documents" />
    <LandlordLayout>
        <RnPageHeader title="Documents" subtitle="Private files — downloaded only after auth checks.">
            <template #actions>
                <Link :href="route('landlord.documents.create')"><RnButton><PlusIcon class="h-4 w-4" />Upload</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <RnInput v-model="search" placeholder="Search title or filename…" class="sm:col-span-2" />
            <RnSelect v-model="type" :options="typeOptions" placeholder="All types" />
        </div>

        <RnCard v-if="!documents.data?.length" flush>
            <RnEmptyState title="No documents" description="Upload lease agreements, IDs, and property papers.">
                <template #icon><DocumentTextIcon class="h-6 w-6" /></template>
                <template #action><Link :href="route('landlord.documents.create')"><RnButton>Upload document</RnButton></Link></template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="space-y-3">
            <div v-for="doc in documents.data" :key="doc.id" class="rn-card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="font-semibold text-rn-text">{{ doc.title }}</p>
                    <p class="mt-1 text-sm text-rn-muted">
                        {{ doc.original_name }} · {{ sizeLabel(doc.file_size) }} · {{ dateLabel(doc.created_at) }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <RnBadge color="neutral">{{ doc.type_label }}</RnBadge>
                        <RnBadge v-if="doc.visible_to_tenant" color="info">Visible to tenant</RnBadge>
                        <RnBadge v-if="doc.documentable_type" color="neutral">{{ doc.documentable_type }}</RnBadge>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a :href="route('landlord.documents.download', doc.id)"><RnButton variant="secondary" size="sm">Download</RnButton></a>
                    <RnButton variant="danger" size="sm" @click="destroy(doc.id)">Delete</RnButton>
                </div>
            </div>
        </div>
        <div class="mt-6"><RnPagination :links="documents.links || []" /></div>
    </LandlordLayout>
</template>

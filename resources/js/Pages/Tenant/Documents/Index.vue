<script setup>
import { Head } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { formatDate } from '@/utils/format';

defineProps({
    documents: { type: Object, required: true },
});
</script>

<template>
    <Head title="Documents" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <RnPageHeader
                title="Documents"
                subtitle="Agreements and files shared with you"
            />

            <RnCard flush>
                <div v-if="documents.data?.length" class="divide-y divide-rn-border">
                    <div
                        v-for="doc in documents.data"
                        :key="doc.id"
                        class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            <div
                                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rn-accent-soft text-rn-accent"
                            >
                                <DocumentTextIcon class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-rn-text">
                                    {{ doc.title }}
                                </p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <RnBadge color="neutral" size="sm">
                                        {{ doc.type_label }}
                                    </RnBadge>
                                    <span class="text-xs text-rn-muted">
                                        {{ doc.file_size_label }} · {{ formatDate(doc.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <a
                            :href="route('tenant.documents.download', doc.id)"
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-rn-border bg-rn-surface px-3 py-1.5 text-xs font-medium text-rn-text transition hover:bg-rn-bg"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" />
                            <span class="hidden sm:inline">Download</span>
                        </a>
                    </div>
                </div>

                <RnEmptyState
                    v-else
                    title="No documents yet"
                    description="Lease agreements and other shared files will appear here when your landlord uploads them."
                >
                    <template #icon>
                        <DocumentTextIcon class="h-6 w-6" />
                    </template>
                </RnEmptyState>

                <div v-if="documents.links?.length" class="border-t border-rn-border px-4 py-4">
                    <RnPagination :links="documents.links" />
                </div>
            </RnCard>
        </div>
    </TenantLayout>
</template>

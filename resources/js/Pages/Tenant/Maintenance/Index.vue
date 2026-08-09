<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { PlusIcon, WrenchScrewdriverIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { formatDate } from '@/utils/format';

defineProps({
    requests: { type: Object, required: true },
});
</script>

<template>
    <Head title="Maintenance" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <RnPageHeader
                title="Maintenance"
                subtitle="Track and submit repair requests"
            >
                <template #actions>
                    <Link
                        :href="route('tenant.maintenance.create')"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-rn-accent px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New request
                    </Link>
                </template>
            </RnPageHeader>

            <RnCard flush>
                <div v-if="requests.data?.length" class="divide-y divide-rn-border">
                    <Link
                        v-for="item in requests.data"
                        :key="item.id"
                        :href="route('tenant.maintenance.show', item.id)"
                        class="block px-5 py-4 transition hover:bg-rn-bg/70 sm:px-6"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-rn-text">
                                    {{ item.title }}
                                </p>
                                <p class="mt-1 text-sm text-rn-muted">
                                    {{ item.category_label }} · {{ formatDate(item.created_at) }}
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                <RnBadge :color="item.status_color" size="sm">
                                    {{ item.status_label }}
                                </RnBadge>
                                <RnBadge :color="item.priority_color" size="sm">
                                    {{ item.priority_label }}
                                </RnBadge>
                            </div>
                        </div>
                    </Link>
                </div>

                <RnEmptyState
                    v-else
                    title="No maintenance requests"
                    description="Something broken? Submit a request and your landlord will get notified."
                >
                    <template #icon>
                        <WrenchScrewdriverIcon class="h-6 w-6" />
                    </template>
                    <template #action>
                        <Link
                            :href="route('tenant.maintenance.create')"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-rn-accent px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800"
                        >
                            Create request
                        </Link>
                    </template>
                </RnEmptyState>

                <div v-if="requests.links?.length" class="border-t border-rn-border px-4 py-4">
                    <RnPagination :links="requests.links" />
                </div>
            </RnCard>
        </div>
    </TenantLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRightIcon, MegaphoneIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { formatDate } from '@/utils/format';

defineProps({
    notices: { type: Object, required: true },
});
</script>

<template>
    <Head title="Notices" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <RnPageHeader
                title="Notices"
                subtitle="Announcements from your landlord"
            />

            <RnCard flush>
                <div v-if="notices.data?.length" class="divide-y divide-rn-border">
                    <Link
                        v-for="notice in notices.data"
                        :key="notice.id"
                        :href="route('tenant.notices.show', notice.id)"
                        class="flex min-h-16 items-center justify-between gap-3 px-5 py-4 transition hover:bg-rn-bg/70 sm:px-6"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="truncate font-semibold"
                                    :class="notice.is_read ? 'text-rn-text' : 'text-rn-text'"
                                >
                                    {{ notice.title }}
                                </p>
                                <RnBadge
                                    v-if="!notice.is_read"
                                    color="info"
                                    size="sm"
                                >
                                    New
                                </RnBadge>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm text-rn-muted">
                                {{ notice.message_preview }}
                            </p>
                            <p class="mt-1 text-xs text-rn-muted">
                                {{ formatDate(notice.publish_date) }}
                            </p>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 shrink-0 text-rn-muted" />
                    </Link>
                </div>

                <RnEmptyState
                    v-else
                    title="No notices"
                    description="When your landlord posts an announcement, it’ll show up here."
                >
                    <template #icon>
                        <MegaphoneIcon class="h-6 w-6" />
                    </template>
                </RnEmptyState>

                <div v-if="notices.links?.length" class="border-t border-rn-border px-4 py-4">
                    <RnPagination :links="notices.links" />
                </div>
            </RnCard>
        </div>
    </TenantLayout>
</template>

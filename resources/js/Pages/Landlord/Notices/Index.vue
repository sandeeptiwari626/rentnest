<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { MegaphoneIcon, PlusIcon } from '@heroicons/vue/24/outline';

defineProps({ notices: Object });

const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const destroy = (id) => {
    if (confirm('Delete this notice?')) router.delete(route('landlord.notices.destroy', id));
};
</script>

<template>
    <Head title="Notices" />
    <LandlordLayout>
        <RnPageHeader title="Notices" subtitle="Announcements for tenants and properties.">
            <template #actions>
                <Link :href="route('landlord.notices.create')"><RnButton><PlusIcon class="h-4 w-4" />New notice</RnButton></Link>
            </template>
        </RnPageHeader>

        <RnCard v-if="!notices.data?.length" flush>
            <RnEmptyState title="No notices yet" description="Publish building updates, reminders, and announcements.">
                <template #icon><MegaphoneIcon class="h-6 w-6" /></template>
                <template #action><Link :href="route('landlord.notices.create')"><RnButton>Create notice</RnButton></Link></template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="space-y-3">
            <div
                v-for="notice in notices.data"
                :key="notice.id"
                class="rn-card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <Link :href="route('landlord.notices.show', notice.id)" class="font-semibold text-rn-text hover:text-rn-accent">
                        {{ notice.title }}
                    </Link>
                    <p class="mt-1 text-sm text-rn-muted">
                        Published {{ dateLabel(notice.publish_date) }}
                        <span v-if="notice.expiry_date"> · Expires {{ dateLabel(notice.expiry_date) }}</span>
                        <span v-if="notice.property"> · {{ notice.property }}</span>
                        <span v-if="notice.tenant"> · {{ notice.tenant }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <RnBadge :color="notice.is_active ? 'success' : 'neutral'">{{ notice.is_active ? 'Active' : 'Inactive' }}</RnBadge>
                    <RnButton variant="danger" size="sm" @click="destroy(notice.id)">Delete</RnButton>
                </div>
            </div>
        </div>
        <div class="mt-6"><RnPagination :links="notices.links || []" /></div>
    </LandlordLayout>
</template>

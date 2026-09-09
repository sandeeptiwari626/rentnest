<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';

defineProps({
    notices: { type: Array, default: () => [] },
    acknowledgements: { type: Array, default: () => [] },
    activeNoticeId: { type: Number, default: null },
});

const dateLabel = (value) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const activate = (id) => {
    if (!confirm('Activate this version? Tenants who have not acknowledged it will be asked to review it at next login. Earlier acknowledgements are kept.')) {
        return;
    }

    router.post(route('landlord.portal-notice.activate', id));
};
</script>

<template>
    <Head title="Tenant Portal Notice" />
    <LandlordLayout>
        <RnPageHeader
            title="Tenant Portal Notice"
            subtitle="Versioned notice tenants must read before using the portal. This is not a lease."
        >
            <template #actions>
                <Link :href="route('landlord.portal-notice.create')">
                    <RnButton>New version</RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <div class="grid gap-6 xl:grid-cols-5">
            <RnCard class="xl:col-span-2" flush>
                <template #title>Versions</template>
                <div v-if="!notices.length" class="p-2">
                    <RnEmptyState title="No notices yet" />
                </div>
                <ul v-else class="divide-y divide-rn-border">
                    <li
                        v-for="notice in notices"
                        :key="notice.id"
                        class="flex items-start justify-between gap-3 px-5 py-4"
                    >
                        <div>
                            <p class="font-medium text-rn-text">{{ notice.display_title }}</p>
                            <p class="mt-1 text-xs text-rn-muted">
                                {{ notice.acknowledgements_count }} acknowledgement{{ notice.acknowledgements_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <RnBadge v-if="notice.is_active" color="success">Active</RnBadge>
                            <RnButton
                                v-else
                                size="sm"
                                variant="secondary"
                                type="button"
                                @click="activate(notice.id)"
                            >
                                Activate
                            </RnButton>
                        </div>
                    </li>
                </ul>
            </RnCard>

            <RnCard class="xl:col-span-3" flush>
                <template #title>Acknowledgements</template>
                <template #subtitle>Each accepted version is kept. Tenants only need to accept the currently active version to use the portal.</template>
                <div v-if="!acknowledgements.length" class="p-2">
                    <RnEmptyState title="No acknowledgements yet" description="When a tenant accepts the notice, it will appear here." />
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-rn-border bg-rn-bg/60 text-xs uppercase tracking-wide text-rn-muted">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Tenant</th>
                                <th class="px-5 py-3 font-semibold">Version</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Acknowledged</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rn-border">
                            <tr v-for="row in acknowledgements" :key="row.id">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-rn-text">{{ row.tenant }}</p>
                                    <p class="text-xs text-rn-muted">{{ row.tenant_email || row.user_email }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    v{{ row.notice_version }}
                                    <span v-if="row.is_current" class="ml-1 text-xs text-rn-accent">current</span>
                                </td>
                                <td class="px-5 py-3.5 capitalize">{{ row.status }}</td>
                                <td class="px-5 py-3.5 text-rn-muted">{{ dateLabel(row.acknowledged_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </RnCard>
        </div>
    </LandlordLayout>
</template>

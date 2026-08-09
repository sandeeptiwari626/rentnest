<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { ClipboardDocumentListIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ leases: Object, filters: Object, statusOptions: Array });
const status = ref(props.filters.status || '');
const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

watch(status, () => {
    router.get(route('landlord.leases.index'), { status: status.value || undefined }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Leases" />
    <LandlordLayout>
        <RnPageHeader title="Leases" subtitle="Agreements between tenants and properties.">
            <template #actions>
                <Link :href="route('landlord.leases.create')"><RnButton><PlusIcon class="h-4 w-4" />New lease</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 max-w-xs"><RnSelect v-model="status" :options="statusOptions" placeholder="All statuses" /></div>

        <RnCard v-if="!leases.data?.length" flush>
            <RnEmptyState title="No leases yet" description="Create a lease to connect a tenant with a unit.">
                <template #icon><ClipboardDocumentListIcon class="h-6 w-6" /></template>
                <template #action><Link :href="route('landlord.leases.create')"><RnButton>Create lease</RnButton></Link></template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="space-y-3">
            <Link
                v-for="lease in leases.data"
                :key="lease.id"
                :href="route('landlord.leases.show', lease.id)"
                class="rn-card flex flex-col gap-3 p-5 transition hover:border-rn-accent/40 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="font-semibold text-rn-text">{{ lease.property }} · {{ lease.unit }}</p>
                    <p class="mt-1 text-sm text-rn-muted">{{ lease.tenant }} · {{ dateLabel(lease.start_date) }} → {{ dateLabel(lease.end_date) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-semibold">{{ money(lease.monthly_rent) }}</span>
                    <RnBadge :color="lease.status_color">{{ lease.status_label }}</RnBadge>
                    <RnBadge v-if="lease.expiring_soon" color="warning">Expiring soon</RnBadge>
                </div>
            </Link>
        </div>
        <div class="mt-6"><RnPagination :links="leases.links || []" /></div>
    </LandlordLayout>
</template>

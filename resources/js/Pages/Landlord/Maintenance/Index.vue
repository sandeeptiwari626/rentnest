<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { WrenchScrewdriverIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    requests: Object,
    filters: Object,
    statusOptions: Array,
    priorityOptions: Array,
});

const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

watch([status, priority], () => {
    router.get(route('landlord.maintenance.index'), {
        status: status.value || undefined,
        priority: priority.value || undefined,
    }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Maintenance" />
    <LandlordLayout>
        <RnPageHeader title="Maintenance" subtitle="Track and resolve repair requests." />

        <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:max-w-lg">
            <RnSelect v-model="status" :options="statusOptions" placeholder="All statuses" />
            <RnSelect v-model="priority" :options="priorityOptions" placeholder="All priorities" />
        </div>

        <RnCard v-if="!requests.data?.length" flush>
            <RnEmptyState title="No maintenance requests" description="When tenants report issues, they’ll show up here.">
                <template #icon><WrenchScrewdriverIcon class="h-6 w-6" /></template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="space-y-3">
            <Link
                v-for="item in requests.data"
                :key="item.id"
                :href="route('landlord.maintenance.show', item.id)"
                class="rn-card flex flex-col gap-3 p-5 transition hover:border-rn-accent/40 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="font-semibold text-rn-text">{{ item.title }}</p>
                    <p class="mt-1 text-sm text-rn-muted">
                        {{ item.property }}<span v-if="item.unit"> · {{ item.unit }}</span> · {{ item.tenant }} · {{ dateLabel(item.created_at) }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <RnBadge :color="item.priority_color">{{ item.priority_label }}</RnBadge>
                    <RnBadge :color="item.status_color">{{ item.status_label }}</RnBadge>
                </div>
            </Link>
        </div>
        <div class="mt-6"><RnPagination :links="requests.links || []" /></div>
    </LandlordLayout>
</template>

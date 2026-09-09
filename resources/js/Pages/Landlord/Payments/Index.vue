<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { CurrencyDollarIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ payments: Object, filters: Object, statusOptions: Array });
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const selected = ref([]);
const deleting = ref(false);

const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const pageIds = computed(() => (props.payments.data || []).map((payment) => payment.id));
const selectedCount = computed(() => selected.value.length);
const allSelected = computed(
    () => pageIds.value.length > 0 && pageIds.value.every((id) => selected.value.includes(id)),
);
const someSelected = computed(
    () => selectedCount.value > 0 && !allSelected.value,
);

watch([search, status], () => {
    selected.value = [];
    router.get(route('landlord.payments.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
});

watch(
    () => props.payments.data,
    () => {
        const ids = new Set(pageIds.value);
        selected.value = selected.value.filter((id) => ids.has(id));
    },
);

const toggleOne = (id) => {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter((item) => item !== id);
        return;
    }
    selected.value = [...selected.value, id];
};

const toggleAll = () => {
    selected.value = allSelected.value ? [] : [...pageIds.value];
};

const bulkDelete = () => {
    if (!selectedCount.value || deleting.value) {
        return;
    }

    const label = selectedCount.value === 1 ? '1 payment' : `${selectedCount.value} payments`;
    if (!confirm(`Delete ${label}? This cannot be undone.`)) {
        return;
    }

    deleting.value = true;
    router.delete(route('landlord.payments.bulk-destroy'), {
        data: { ids: selected.value },
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            selected.value = [];
        },
    });
};
</script>

<template>
    <Head title="Payments" />
    <LandlordLayout>
        <RnPageHeader title="Payments" subtitle="Track rent dues and collections.">
            <template #actions>
                <RnButton
                    v-if="selectedCount"
                    variant="danger"
                    :loading="deleting"
                    @click="bulkDelete"
                >
                    <TrashIcon class="h-4 w-4" />
                    Delete selected ({{ selectedCount }})
                </RnButton>
                <Link :href="route('landlord.payments.create')">
                    <RnButton><PlusIcon class="h-4 w-4" />Record payment</RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <RnInput v-model="search" placeholder="Search tenant, receipt, period…" class="sm:col-span-2" />
            <RnSelect v-model="status" :options="statusOptions" placeholder="All statuses" />
        </div>

        <div
            v-if="selectedCount"
            class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-rn-border bg-white px-4 py-3 shadow-sm"
        >
            <p class="text-sm text-rn-text">
                <span class="font-semibold">{{ selectedCount }}</span>
                payment{{ selectedCount === 1 ? '' : 's' }} selected on this page
            </p>
            <div class="flex items-center gap-2">
                <RnButton variant="ghost" size="sm" @click="selected = []">Clear</RnButton>
                <RnButton variant="danger" size="sm" :loading="deleting" @click="bulkDelete">
                    <TrashIcon class="h-4 w-4" />
                    Delete
                </RnButton>
            </div>
        </div>

        <RnCard v-if="!payments.data?.length" flush>
            <RnEmptyState title="No payments yet" description="Record rent manually or generate dues from leases.">
                <template #icon><CurrencyDollarIcon class="h-6 w-6" /></template>
                <template #action><Link :href="route('landlord.payments.create')"><RnButton>Record payment</RnButton></Link></template>
            </RnEmptyState>
        </RnCard>

        <RnCard v-else flush>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-rn-border bg-rn-bg/60 text-xs uppercase tracking-wide text-rn-muted">
                        <tr>
                            <th class="w-12 px-5 py-3">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-rn-border text-rn-accent focus:ring-rn-accent/30"
                                    :checked="allSelected"
                                    :indeterminate.prop="someSelected"
                                    aria-label="Select all payments on this page"
                                    @change="toggleAll"
                                >
                            </th>
                            <th class="px-5 py-3 font-semibold">Tenant</th>
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Due</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rn-border">
                        <tr
                            v-for="payment in payments.data"
                            :key="payment.id"
                            class="hover:bg-rn-bg/50"
                            :class="{ 'bg-rn-accent-soft/40': selected.includes(payment.id) }"
                        >
                            <td class="px-5 py-3.5">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-rn-border text-rn-accent focus:ring-rn-accent/30"
                                    :checked="selected.includes(payment.id)"
                                    :aria-label="`Select payment for ${payment.tenant}`"
                                    @change="toggleOne(payment.id)"
                                >
                            </td>
                            <td class="px-5 py-3.5">
                                <Link :href="route('landlord.payments.show', payment.id)" class="font-medium hover:text-rn-accent">{{ payment.tenant }}</Link>
                                <p class="text-xs text-rn-muted">{{ payment.property }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-rn-muted">{{ payment.period_label || '—' }}</td>
                            <td class="px-5 py-3.5 text-rn-muted">{{ dateLabel(payment.due_date) }}</td>
                            <td class="px-5 py-3.5 font-semibold">{{ money(payment.amount) }}</td>
                            <td class="px-5 py-3.5"><RnBadge :color="payment.status_color">{{ payment.status_label }}</RnBadge></td>
                            <td class="px-5 py-3.5 text-right">
                                <Link :href="route('landlord.payments.edit', payment.id)" class="text-sm font-medium text-rn-accent hover:underline">Edit</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </RnCard>
        <div class="mt-6"><RnPagination :links="payments.links || []" /></div>
    </LandlordLayout>
</template>

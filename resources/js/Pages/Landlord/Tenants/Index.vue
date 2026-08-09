<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { PlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ tenants: Object, filters: Object });
const search = ref(props.filters.search || '');
const money = (n) => (n == null ? '—' : `₹${Number(n).toLocaleString('en-IN')}`);

watch(search, () => {
    router.get(route('landlord.tenants.index'), { search: search.value || undefined }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Tenants" />
    <LandlordLayout>
        <RnPageHeader title="Tenants" subtitle="People living in your properties.">
            <template #actions>
                <Link :href="route('landlord.tenants.create')"><RnButton><PlusIcon class="h-4 w-4" />Add tenant</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 max-w-md"><RnInput v-model="search" placeholder="Search name, email, phone…" /></div>

        <RnCard v-if="!tenants.data?.length" flush>
            <RnEmptyState title="No tenants yet" description="Add a tenant profile, optionally with a login account.">
                <template #icon><UserGroupIcon class="h-6 w-6" /></template>
                <template #action>
                    <Link :href="route('landlord.tenants.create')"><RnButton>Add tenant</RnButton></Link>
                </template>
            </RnEmptyState>
        </RnCard>

        <RnCard v-else flush>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-rn-border bg-rn-bg/60 text-xs uppercase tracking-wide text-rn-muted">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tenant</th>
                            <th class="px-5 py-3 font-semibold">Property</th>
                            <th class="px-5 py-3 font-semibold">Lease</th>
                            <th class="px-5 py-3 font-semibold">Rent</th>
                            <th class="px-5 py-3 font-semibold">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rn-border">
                        <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-rn-bg/50">
                            <td class="px-5 py-3.5">
                                <Link :href="route('landlord.tenants.show', tenant.id)" class="font-medium text-rn-text hover:text-rn-accent">
                                    {{ tenant.name }}
                                </Link>
                                <p class="text-xs text-rn-muted">{{ tenant.email || tenant.phone || '—' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-rn-muted">{{ tenant.property || '—' }}<span v-if="tenant.unit"> · {{ tenant.unit }}</span></td>
                            <td class="px-5 py-3.5"><RnBadge v-if="tenant.lease_status" :color="tenant.lease_status_color">{{ tenant.lease_status_label }}</RnBadge><span v-else class="text-rn-muted">—</span></td>
                            <td class="px-5 py-3.5 font-medium">{{ money(tenant.monthly_rent) }}</td>
                            <td class="px-5 py-3.5"><RnBadge v-if="tenant.payment_status" :color="tenant.payment_status_color">{{ tenant.payment_status_label }}</RnBadge><span v-else class="text-rn-muted">—</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </RnCard>
        <div class="mt-6"><RnPagination :links="tenants.links || []" /></div>
    </LandlordLayout>
</template>

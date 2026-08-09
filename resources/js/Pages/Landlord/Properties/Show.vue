<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';

const props = defineProps({ property: Object });

const money = (n) => (n == null ? '—' : `₹${Number(n).toLocaleString('en-IN')}`);
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const destroy = () => {
    if (confirm('Delete this property?')) {
        router.delete(route('landlord.properties.destroy', props.property.id));
    }
};
</script>

<template>
    <Head :title="property.name" />
    <LandlordLayout>
        <RnPageHeader :title="property.name" :subtitle="`${property.address}, ${property.city}, ${property.state} ${property.postal_code}`">
            <template #actions>
                <Link :href="route('landlord.properties.edit', property.id)"><RnButton variant="secondary">Edit</RnButton></Link>
                <RnButton variant="danger" @click="destroy">Delete</RnButton>
            </template>
        </RnPageHeader>

        <div class="mb-6 flex flex-wrap gap-2">
            <RnBadge :color="property.status_color">{{ property.status_label }}</RnBadge>
            <RnBadge color="neutral">{{ property.type_label }}</RnBadge>
            <RnBadge v-if="property.bedrooms != null" color="neutral">{{ property.bedrooms }} bed</RnBadge>
            <RnBadge v-if="property.bathrooms != null" color="neutral">{{ property.bathrooms }} bath</RnBadge>
            <RnBadge v-if="property.area" color="neutral">{{ property.area }} {{ property.area_unit }}</RnBadge>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <RnCard>
                    <template #title>Description</template>
                    <p class="text-sm leading-relaxed text-rn-muted">
                        {{ property.description || 'No description added.' }}
                    </p>
                </RnCard>

                <RnCard flush>
                    <template #title>Units</template>
                    <div v-if="!property.units?.length" class="p-2">
                        <RnEmptyState title="No units" description="Units will appear here once added." />
                    </div>
                    <div v-else class="divide-y divide-rn-border">
                        <div v-for="unit in property.units" :key="unit.id" class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div>
                                <p class="font-medium text-rn-text">{{ unit.name }}</p>
                                <p class="text-sm text-rn-muted">
                                    {{ unit.bedrooms ?? '—' }} bed · {{ unit.bathrooms ?? '—' }} bath · {{ money(unit.rent_amount) }}
                                </p>
                            </div>
                            <RnBadge :color="unit.status_color">{{ unit.status_label }}</RnBadge>
                        </div>
                    </div>
                </RnCard>

                <RnCard v-if="property.photos?.length">
                    <template #title>Photos</template>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <a
                            v-for="(photo, index) in property.photos"
                            :key="index"
                            :href="photo.url"
                            target="_blank"
                            class="overflow-hidden rounded-xl border border-rn-border bg-rn-bg"
                        >
                            <img :src="photo.url" :alt="`Photo ${index + 1}`" class="h-32 w-full object-cover" />
                        </a>
                    </div>
                </RnCard>
            </div>

            <div class="space-y-6">
                <RnCard>
                    <template #title>Active lease</template>
                    <div v-if="!property.active_lease" class="text-sm text-rn-muted">No active lease on this property.</div>
                    <div v-else class="space-y-3">
                        <div class="flex items-center justify-between">
                            <RnBadge :color="property.active_lease.status_color">{{ property.active_lease.status_label }}</RnBadge>
                            <span class="font-semibold">{{ money(property.active_lease.monthly_rent) }}/mo</span>
                        </div>
                        <p class="text-sm text-rn-text">Unit: {{ property.active_lease.unit }}</p>
                        <p class="text-sm text-rn-muted">
                            {{ dateLabel(property.active_lease.start_date) }}
                            →
                            {{ dateLabel(property.active_lease.end_date) }}
                        </p>
                        <div v-if="property.active_lease.tenant" class="rounded-xl bg-rn-bg p-3">
                            <p class="font-medium text-rn-text">{{ property.active_lease.tenant.name }}</p>
                            <p class="text-sm text-rn-muted">{{ property.active_lease.tenant.email }}</p>
                            <p class="text-sm text-rn-muted">{{ property.active_lease.tenant.phone }}</p>
                            <Link
                                :href="route('landlord.tenants.show', property.active_lease.tenant.id)"
                                class="mt-2 inline-block text-sm font-medium text-rn-accent hover:underline"
                            >
                                View tenant
                            </Link>
                        </div>
                        <Link :href="route('landlord.leases.show', property.active_lease.id)">
                            <RnButton variant="soft" size="sm" class="mt-2">View lease</RnButton>
                        </Link>
                    </div>
                </RnCard>
            </div>
        </div>
    </LandlordLayout>
</template>

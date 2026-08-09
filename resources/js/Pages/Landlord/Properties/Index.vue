<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { BuildingOffice2Icon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    properties: Object,
    filters: Object,
    statusOptions: Array,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    router.get(route('landlord.properties.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
});

const money = (n) => (n == null ? '—' : `₹${Number(n).toLocaleString('en-IN')}`);
</script>

<template>
    <Head title="Properties" />
    <LandlordLayout>
        <RnPageHeader title="Properties" subtitle="Manage buildings, units, and occupancy.">
            <template #actions>
                <Link :href="route('landlord.properties.create')">
                    <RnButton>
                        <PlusIcon class="h-4 w-4" />
                        Add property
                    </RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <RnInput v-model="search" placeholder="Search name, city, address…" />
            <RnSelect v-model="status" :options="statusOptions" placeholder="All statuses" />
        </div>

        <RnCard v-if="!properties.data?.length" flush>
            <RnEmptyState
                title="No properties yet"
                description="Add your first property to start tracking units, tenants, and rent."
            >
                <template #icon><BuildingOffice2Icon class="h-6 w-6" /></template>
                <template #action>
                    <Link :href="route('landlord.properties.create')"><RnButton>Add property</RnButton></Link>
                </template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="property in properties.data"
                :key="property.id"
                :href="route('landlord.properties.show', property.id)"
                class="rn-card block p-5 transition hover:border-rn-accent/40 hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-semibold text-rn-text">{{ property.name }}</h3>
                        <p class="mt-1 text-sm text-rn-muted">{{ property.address }}, {{ property.city }}</p>
                    </div>
                    <RnBadge :color="property.status_color">{{ property.status_label }}</RnBadge>
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-sm text-rn-muted">
                    <span>{{ property.type_label }}</span>
                    <span>·</span>
                    <span>{{ property.occupied_units_count }}/{{ property.units_count }} occupied</span>
                    <span>·</span>
                    <span>From {{ money(property.rent_from) }}</span>
                </div>
            </Link>
        </div>

        <div class="mt-6">
            <RnPagination :links="properties.links || []" />
        </div>
    </LandlordLayout>
</template>

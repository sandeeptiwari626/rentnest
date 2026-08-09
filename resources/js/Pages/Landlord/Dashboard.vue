<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnStatCard from '@/Components/ui/RnStatCard.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import {
    BuildingOffice2Icon,
    CurrencyRupeeIcon,
    HomeModernIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    greeting: String,
    stats: Object,
    propertyOverview: Array,
    recentPayments: Array,
    recentMaintenance: Array,
    upcoming: Object,
});

const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
</script>

<template>
    <Head title="Dashboard" />

    <LandlordLayout>
        <RnPageHeader :title="greeting" subtitle="Here’s what’s happening across your properties today.">
            <template #actions>
                <Link :href="route('landlord.properties.create')">
                    <RnButton>Add property</RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <RnStatCard label="Properties" :value="stats.total_properties">
                <template #icon><BuildingOffice2Icon class="h-5 w-5" /></template>
            </RnStatCard>
            <RnStatCard label="Occupied units" :value="stats.occupied_units">
                <template #icon><HomeModernIcon class="h-5 w-5" /></template>
            </RnStatCard>
            <RnStatCard label="Monthly rent" :value="money(stats.monthly_rent)">
                <template #icon><CurrencyRupeeIcon class="h-5 w-5" /></template>
            </RnStatCard>
            <RnStatCard label="Pending rent" :value="money(stats.pending_rent)" hint="Outstanding balance" />
            <RnStatCard label="Open maintenance" :value="stats.open_maintenance">
                <template #icon><WrenchScrewdriverIcon class="h-5 w-5" /></template>
            </RnStatCard>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-3">
            <RnCard class="xl:col-span-2" flush>
                <template #title>Property overview</template>
                <template #subtitle>Active leases and next rent due</template>
                <template #actions>
                    <Link :href="route('landlord.leases.index')" class="text-sm font-medium text-rn-accent hover:underline">View all</Link>
                </template>

                <div v-if="!propertyOverview.length" class="p-2">
                    <RnEmptyState title="No active leases yet" description="Create a property and lease to see occupancy here.">
                        <template #action>
                            <Link :href="route('landlord.leases.create')"><RnButton size="sm">Create lease</RnButton></Link>
                        </template>
                    </RnEmptyState>
                </div>
                <div v-else class="divide-y divide-rn-border">
                    <Link
                        v-for="item in propertyOverview"
                        :key="item.id"
                        :href="route('landlord.leases.show', item.id)"
                        class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-rn-bg sm:px-6"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium text-rn-text">{{ item.property }} · {{ item.unit }}</p>
                            <p class="mt-0.5 text-sm text-rn-muted">{{ item.tenant }} · {{ item.city }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="font-semibold text-rn-text">{{ money(item.monthly_rent) }}</p>
                            <p class="mt-0.5 text-xs text-rn-muted">Due {{ dateLabel(item.next_due) }}</p>
                        </div>
                    </Link>
                </div>
            </RnCard>

            <RnCard flush>
                <template #title>Upcoming</template>
                <div class="space-y-5 px-5 py-4 sm:px-6">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-rn-muted">Rent dues (14 days)</p>
                        <div v-if="!upcoming.rent_dues.length" class="text-sm text-rn-muted">Nothing due soon.</div>
                        <ul v-else class="space-y-2">
                            <li v-for="item in upcoming.rent_dues" :key="'due-'+item.id" class="flex justify-between gap-2 text-sm">
                                <span class="truncate text-rn-text">{{ item.label }}</span>
                                <span class="shrink-0 text-rn-muted">{{ dateLabel(item.date) }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-rn-muted">Lease expiries (60 days)</p>
                        <div v-if="!upcoming.lease_expiries.length" class="text-sm text-rn-muted">No expiries coming up.</div>
                        <ul v-else class="space-y-2">
                            <li v-for="item in upcoming.lease_expiries" :key="'lease-'+item.id" class="flex justify-between gap-2 text-sm">
                                <span class="truncate text-rn-text">{{ item.label }}</span>
                                <span class="shrink-0 text-rn-muted">{{ dateLabel(item.date) }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-rn-muted">Scheduled maintenance</p>
                        <div v-if="!upcoming.scheduled_maintenance.length" class="text-sm text-rn-muted">No visits scheduled.</div>
                        <ul v-else class="space-y-2">
                            <li v-for="item in upcoming.scheduled_maintenance" :key="'m-'+item.id" class="flex justify-between gap-2 text-sm">
                                <span class="truncate text-rn-text">{{ item.label }}</span>
                                <span class="shrink-0 text-rn-muted">{{ dateLabel(item.date) }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-rn-muted">Notices</p>
                        <div v-if="!upcoming.notices.length" class="text-sm text-rn-muted">No active notices.</div>
                        <ul v-else class="space-y-2">
                            <li v-for="item in upcoming.notices" :key="'n-'+item.id" class="flex justify-between gap-2 text-sm">
                                <Link :href="route('landlord.notices.show', item.id)" class="truncate text-rn-accent hover:underline">{{ item.title }}</Link>
                                <span class="shrink-0 text-rn-muted">{{ dateLabel(item.date) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </RnCard>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <RnCard flush>
                <template #title>Recent payments</template>
                <template #actions>
                    <Link :href="route('landlord.payments.index')" class="text-sm font-medium text-rn-accent hover:underline">View all</Link>
                </template>
                <div v-if="!recentPayments.length" class="p-2">
                    <RnEmptyState title="No payments yet" description="Record a rent payment to see it here." />
                </div>
                <div v-else class="divide-y divide-rn-border">
                    <Link
                        v-for="payment in recentPayments"
                        :key="payment.id"
                        :href="route('landlord.payments.show', payment.id)"
                        class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-rn-bg sm:px-6"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-rn-text">{{ payment.tenant }}</p>
                            <p class="text-xs text-rn-muted">{{ payment.property }} · {{ dateLabel(payment.date) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold">{{ money(payment.amount) }}</span>
                            <RnBadge :color="payment.status_color">{{ payment.status_label }}</RnBadge>
                        </div>
                    </Link>
                </div>
            </RnCard>

            <RnCard flush>
                <template #title>Recent maintenance</template>
                <template #actions>
                    <Link :href="route('landlord.maintenance.index')" class="text-sm font-medium text-rn-accent hover:underline">View all</Link>
                </template>
                <div v-if="!recentMaintenance.length" class="p-2">
                    <RnEmptyState title="All quiet" description="No maintenance requests reported yet." />
                </div>
                <div v-else class="divide-y divide-rn-border">
                    <Link
                        v-for="item in recentMaintenance"
                        :key="item.id"
                        :href="route('landlord.maintenance.show', item.id)"
                        class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-rn-bg sm:px-6"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-rn-text">{{ item.title }}</p>
                            <p class="text-xs text-rn-muted">{{ item.property }} · {{ item.tenant }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <RnBadge :color="item.status_color">{{ item.status_label }}</RnBadge>
                            <RnBadge :color="item.priority_color" size="sm">{{ item.priority_label }}</RnBadge>
                        </div>
                    </Link>
                </div>
            </RnCard>
        </div>
    </LandlordLayout>
</template>

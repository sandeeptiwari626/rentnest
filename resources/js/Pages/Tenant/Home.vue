<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    BanknotesIcon,
    BellAlertIcon,
    ChevronRightIcon,
    HomeIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnAlert from '@/Components/ui/RnAlert.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnStatCard from '@/Components/ui/RnStatCard.vue';
import RnTimeline from '@/Components/ui/RnTimeline.vue';
import { formatDate } from '@/utils/format';

const props = defineProps({
    greeting: { type: String, default: 'Hello' },
    userName: { type: String, default: '' },
    home: { type: Object, default: null },
    nextPayment: { type: Object, default: null },
    alerts: {
        type: Object,
        default: () => ({
            unpaid_count: 0,
            open_maintenance_count: 0,
            unread_notices_count: 0,
        }),
    },
    activity: { type: Array, default: () => [] },
});

const timelineItems = computed(() =>
    props.activity.map((item) => ({
        title: item.title,
        description: item.description,
        date: formatDate(item.date),
        status: item.status || 'default',
    })),
);

const firstName = computed(() => props.userName?.split(' ')[0] || 'there');
</script>

<template>
    <Head title="Home" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl space-y-5">
            <div>
                <p class="text-sm font-medium text-rn-accent">
                    {{ greeting }}
                </p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight text-rn-text">
                    {{ firstName }}
                </h1>
                <p class="mt-1 text-sm text-rn-muted">
                    Here’s what’s happening with your home.
                </p>
            </div>

            <RnAlert
                v-if="alerts.unpaid_count > 0"
                type="warning"
                title="Rent attention needed"
                :message="`${alerts.unpaid_count} payment${alerts.unpaid_count === 1 ? '' : 's'} pending or overdue.`"
            >
                <Link
                    :href="route('tenant.payments.index')"
                    class="mt-2 inline-flex text-sm font-semibold underline underline-offset-2"
                >
                    View payments
                </Link>
            </RnAlert>

            <RnCard v-if="home" flush>
                <Link
                    :href="route('tenant.my-home')"
                    class="block p-5 transition hover:bg-rn-bg/60 sm:p-6"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-rn-muted">
                                Your home
                            </p>
                            <h2 class="mt-1 text-xl font-semibold text-rn-text">
                                {{ home.property_name }}
                            </h2>
                            <p v-if="home.unit_name" class="mt-0.5 text-sm text-rn-muted">
                                Unit {{ home.unit_name }}
                            </p>
                            <p v-if="home.address" class="mt-2 text-sm text-rn-muted">
                                {{ home.address }}
                            </p>
                        </div>
                        <ChevronRightIcon class="mt-1 h-5 w-5 shrink-0 text-rn-muted" />
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-rn-bg px-4 py-3">
                            <p class="text-xs text-rn-muted">Monthly rent</p>
                            <p class="mt-1 text-lg font-semibold text-rn-text">
                                {{ home.monthly_rent_formatted }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-rn-bg px-4 py-3">
                            <p class="text-xs text-rn-muted">Next due</p>
                            <p class="mt-1 text-lg font-semibold text-rn-text">
                                {{ nextPayment ? formatDate(nextPayment.due_date) : `Day ${home.rent_due_day}` }}
                            </p>
                        </div>
                    </div>
                </Link>
            </RnCard>

            <RnCard v-else>
                <RnEmptyState
                    title="No home linked yet"
                    description="Once your landlord activates a lease, your property details will show up here."
                >
                    <template #icon>
                        <HomeIcon class="h-6 w-6" />
                    </template>
                </RnEmptyState>
            </RnCard>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <RnStatCard
                    label="Open requests"
                    :value="alerts.open_maintenance_count"
                    hint="Maintenance"
                >
                    <template #icon>
                        <WrenchScrewdriverIcon class="h-5 w-5" />
                    </template>
                </RnStatCard>
                <RnStatCard
                    label="Unread notices"
                    :value="alerts.unread_notices_count"
                    hint="From landlord"
                >
                    <template #icon>
                        <BellAlertIcon class="h-5 w-5" />
                    </template>
                </RnStatCard>
                <RnStatCard
                    class="col-span-2 sm:col-span-1"
                    label="Payments due"
                    :value="alerts.unpaid_count"
                    hint="Pending / late"
                >
                    <template #icon>
                        <BanknotesIcon class="h-5 w-5" />
                    </template>
                </RnStatCard>
            </div>

            <div v-if="nextPayment" class="rn-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-rn-muted">Upcoming rent</p>
                        <p class="mt-1 text-2xl font-semibold text-rn-text">
                            {{ nextPayment.amount_formatted }}
                        </p>
                        <p class="mt-1 text-sm text-rn-muted">
                            {{ nextPayment.period_label || 'Rent' }} · due {{ formatDate(nextPayment.due_date) }}
                        </p>
                    </div>
                    <RnBadge :color="nextPayment.status_color">
                        {{ nextPayment.status_label }}
                    </RnBadge>
                </div>
                <div class="mt-4">
                    <Link
                        :href="route('tenant.payments.show', nextPayment.id)"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-rn-accent-soft px-4 py-2 text-sm font-medium text-rn-accent transition hover:bg-teal-100 sm:w-auto"
                    >
                        View payment
                    </Link>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    :href="route('tenant.maintenance.create')"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-rn-accent px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800"
                >
                    Request maintenance
                </Link>
                <Link
                    :href="route('tenant.notices.index')"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-rn-border bg-rn-surface px-5 py-2.5 text-sm font-medium text-rn-text transition hover:bg-rn-bg"
                >
                    Notices
                </Link>
            </div>

            <RnCard>
                <template #title>Recent activity</template>
                <RnTimeline v-if="timelineItems.length" :items="timelineItems" />
                <RnEmptyState
                    v-else
                    title="All quiet for now"
                    description="Payments, maintenance updates, and notices will appear here."
                />
            </RnCard>
        </div>
    </TenantLayout>
</template>

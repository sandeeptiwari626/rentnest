<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnAlert from '@/Components/ui/RnAlert.vue';
import RnTimeline from '@/Components/ui/RnTimeline.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';

const props = defineProps({
    lease: Object,
    expiringSoon: Boolean,
    timeline: Array,
});

const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Open-ended');

const isLive = () => ['active', 'expiring'].includes(props.lease.status);

const activate = () => router.post(route('landlord.leases.activate', props.lease.id));
const terminate = () => {
    if (confirm('Terminate this lease? The unit will be marked vacant if no other active lease exists.')) {
        router.post(route('landlord.leases.terminate', props.lease.id));
    }
};
const expire = () => {
    if (confirm('Mark this lease as expired?')) {
        router.post(route('landlord.leases.expire', props.lease.id));
    }
};
const destroy = () => {
    if (isLive()) {
        alert('Terminate or expire this lease first, then you can delete it.');
        return;
    }
    if (confirm('Delete this lease? This cannot be undone from the list view.')) {
        router.delete(route('landlord.leases.destroy', props.lease.id));
    }
};
</script>

<template>
    <Head :title="`Lease · ${lease.property?.name}`" />
    <LandlordLayout>
        <RnPageHeader
            :title="`${lease.property?.name} · ${lease.unit}`"
            :subtitle="lease.tenant?.name"
        >
            <template #actions>
                <Link :href="route('landlord.leases.edit', lease.id)"><RnButton variant="secondary">Edit</RnButton></Link>
                <RnButton v-if="lease.status === 'draft'" @click="activate">Activate lease</RnButton>
                <RnButton
                    v-if="['active', 'expiring', 'draft'].includes(lease.status)"
                    variant="secondary"
                    @click="terminate"
                >
                    Terminate
                </RnButton>
                <RnButton
                    v-if="['active', 'expiring'].includes(lease.status)"
                    variant="ghost"
                    @click="expire"
                >
                    Mark expired
                </RnButton>
                <RnButton
                    variant="danger"
                    :disabled="['active', 'expiring'].includes(lease.status)"
                    @click="destroy"
                >
                    Delete
                </RnButton>
            </template>
        </RnPageHeader>

        <RnAlert
            v-if="expiringSoon"
            type="warning"
            title="Lease expiring soon"
            :message="`This lease ends on ${dateLabel(lease.end_date)}. Consider renewing or sending notice.`"
            class="mb-6"
        />

        <RnAlert
            v-if="['active', 'expiring'].includes(lease.status)"
            type="info"
            title="Delete is locked"
            message="Terminate or mark this lease as expired before deleting. That keeps rent history accurate and frees the unit."
            class="mb-6"
        />

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <RnCard>
                    <div class="flex flex-wrap items-center gap-2">
                        <RnBadge :color="lease.status_color" size="lg">{{ lease.status_label }}</RnBadge>
                        <span class="text-2xl font-semibold text-rn-text">{{ money(lease.monthly_rent) }}<span class="text-sm font-normal text-rn-muted">/mo</span></span>
                    </div>
                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-rn-muted">Period</dt>
                            <dd class="mt-1 text-sm text-rn-text">{{ dateLabel(lease.start_date) }} → {{ dateLabel(lease.end_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-rn-muted">Security deposit</dt>
                            <dd class="mt-1 text-sm text-rn-text">{{ money(lease.security_deposit) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-rn-muted">Rent due day</dt>
                            <dd class="mt-1 text-sm text-rn-text">{{ lease.rent_due_day }} of each month</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-rn-muted">Notice period</dt>
                            <dd class="mt-1 text-sm text-rn-text">{{ lease.notice_period_days }} days</dd>
                        </div>
                    </dl>
                    <p v-if="lease.notes" class="mt-4 rounded-xl bg-rn-bg p-4 text-sm text-rn-muted">{{ lease.notes }}</p>
                </RnCard>

                <RnCard>
                    <template #title>Lease timeline</template>
                    <RnTimeline :items="timeline" />
                </RnCard>

                <RnCard flush>
                    <template #title>Payments on this lease</template>
                    <div v-if="!lease.payments?.length" class="p-2"><RnEmptyState title="No payments recorded" /></div>
                    <div v-else class="divide-y divide-rn-border">
                        <Link
                            v-for="p in lease.payments"
                            :key="p.id"
                            :href="route('landlord.payments.show', p.id)"
                            class="flex items-center justify-between px-5 py-3.5 hover:bg-rn-bg sm:px-6"
                        >
                            <div>
                                <p class="text-sm font-medium">{{ p.period_label || dateLabel(p.due_date) }}</p>
                                <p class="text-xs text-rn-muted">Due {{ dateLabel(p.due_date) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold">{{ money(p.amount) }}</span>
                                <RnBadge :color="p.status_color">{{ p.status_label }}</RnBadge>
                            </div>
                        </Link>
                    </div>
                </RnCard>
            </div>

            <div class="space-y-6">
                <RnCard>
                    <template #title>Tenant</template>
                    <p class="font-medium text-rn-text">{{ lease.tenant?.name }}</p>
                    <p class="mt-1 text-sm text-rn-muted">{{ lease.tenant?.email }}</p>
                    <p class="text-sm text-rn-muted">{{ lease.tenant?.phone }}</p>
                    <Link
                        v-if="lease.tenant?.id"
                        :href="route('landlord.tenants.show', lease.tenant.id)"
                        class="mt-3 inline-block text-sm font-medium text-rn-accent hover:underline"
                    >
                        View profile
                    </Link>
                </RnCard>
                <RnCard>
                    <template #title>Property</template>
                    <p class="font-medium text-rn-text">{{ lease.property?.name }}</p>
                    <p class="mt-1 text-sm text-rn-muted">{{ lease.property?.address }}, {{ lease.property?.city }}</p>
                    <p class="mt-2 text-sm text-rn-muted">Unit: {{ lease.unit }}</p>
                    <Link
                        v-if="lease.property?.id"
                        :href="route('landlord.properties.show', lease.property.id)"
                        class="mt-3 inline-block text-sm font-medium text-rn-accent hover:underline"
                    >
                        View property
                    </Link>
                </RnCard>
            </div>
        </div>
    </LandlordLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnAvatar from '@/Components/ui/RnAvatar.vue';

const props = defineProps({ tenant: Object });
const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const destroy = () => {
    if (confirm('Delete this tenant?')) router.delete(route('landlord.tenants.destroy', props.tenant.id));
};
</script>

<template>
    <Head :title="tenant.name" />
    <LandlordLayout>
        <RnPageHeader :title="tenant.name" :subtitle="tenant.email || tenant.phone || 'Tenant profile'">
            <template #actions>
                <Link :href="route('landlord.tenants.edit', tenant.id)"><RnButton variant="secondary">Edit</RnButton></Link>
                <RnButton variant="danger" @click="destroy">Delete</RnButton>
            </template>
        </RnPageHeader>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <RnCard>
                    <div class="flex items-center gap-4">
                        <RnAvatar :name="tenant.name" size="lg" />
                        <div>
                            <p class="text-lg font-semibold text-rn-text">{{ tenant.name }}</p>
                            <p class="text-sm text-rn-muted">{{ tenant.email || 'No email' }} · {{ tenant.phone || 'No phone' }}</p>
                            <RnBadge class="mt-2" :color="tenant.has_account ? 'success' : 'neutral'">
                                {{ tenant.has_account ? 'Has login account' : 'No login account' }}
                            </RnBadge>
                        </div>
                    </div>
                    <div v-if="tenant.notes" class="mt-4 rounded-xl bg-rn-bg p-4 text-sm text-rn-muted">{{ tenant.notes }}</div>
                    <div v-if="tenant.emergency_contact_name" class="mt-4 text-sm">
                        <span class="text-rn-muted">Emergency:</span>
                        <span class="ml-1 text-rn-text">{{ tenant.emergency_contact_name }} · {{ tenant.emergency_contact_phone }}</span>
                    </div>
                </RnCard>

                <RnCard flush>
                    <template #title>Payments</template>
                    <div v-if="!tenant.payments?.length" class="p-2"><RnEmptyState title="No payments" description="Payment history will appear here." /></div>
                    <div v-else class="divide-y divide-rn-border">
                        <Link v-for="p in tenant.payments" :key="p.id" :href="route('landlord.payments.show', p.id)" class="flex items-center justify-between px-5 py-3.5 hover:bg-rn-bg sm:px-6">
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

                <RnCard flush>
                    <template #title>Maintenance</template>
                    <div v-if="!tenant.maintenance?.length" class="p-2"><RnEmptyState title="No requests" /></div>
                    <div v-else class="divide-y divide-rn-border">
                        <Link v-for="m in tenant.maintenance" :key="m.id" :href="route('landlord.maintenance.show', m.id)" class="flex items-center justify-between px-5 py-3.5 hover:bg-rn-bg sm:px-6">
                            <div>
                                <p class="text-sm font-medium">{{ m.title }}</p>
                                <p class="text-xs text-rn-muted">{{ m.property }} · {{ dateLabel(m.created_at) }}</p>
                            </div>
                            <RnBadge :color="m.status_color">{{ m.status_label }}</RnBadge>
                        </Link>
                    </div>
                </RnCard>
            </div>

            <div class="space-y-6">
                <RnCard>
                    <template #title>Lease</template>
                    <div v-if="!tenant.lease" class="text-sm text-rn-muted">No lease linked.</div>
                    <div v-else class="space-y-2 text-sm">
                        <RnBadge :color="tenant.lease.status_color">{{ tenant.lease.status_label }}</RnBadge>
                        <p class="font-medium text-rn-text">{{ tenant.lease.property }} · {{ tenant.lease.unit }}</p>
                        <p class="text-rn-muted">{{ money(tenant.lease.monthly_rent) }}/mo</p>
                        <p class="text-rn-muted">{{ dateLabel(tenant.lease.start_date) }} → {{ dateLabel(tenant.lease.end_date) }}</p>
                        <Link :href="route('landlord.leases.show', tenant.lease.id)" class="inline-block font-medium text-rn-accent hover:underline">View lease</Link>
                    </div>
                </RnCard>

                <RnCard flush>
                    <template #title>Documents</template>
                    <div v-if="!tenant.documents?.length" class="p-2"><RnEmptyState title="No documents" /></div>
                    <div v-else class="divide-y divide-rn-border">
                        <Link v-for="d in tenant.documents" :key="d.id" :href="route('landlord.documents.download', d.id)" class="block px-5 py-3 text-sm hover:bg-rn-bg sm:px-6">
                            <p class="font-medium text-rn-text">{{ d.title }}</p>
                            <p class="text-xs text-rn-muted">{{ d.type_label }} · {{ dateLabel(d.created_at) }}</p>
                        </Link>
                    </div>
                </RnCard>
            </div>
        </div>
    </LandlordLayout>
</template>

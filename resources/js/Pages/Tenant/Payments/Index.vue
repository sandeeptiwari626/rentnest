<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BanknotesIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { formatDate } from '@/utils/format';

defineProps({
    currentRent: { type: Object, default: () => ({}) },
    nextDue: { type: Object, default: null },
    payments: { type: Object, required: true },
});
</script>

<template>
    <Head title="Rent & Payments" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <RnPageHeader
                title="Rent & Payments"
                subtitle="Current rent, due dates, and payment history"
            />

            <div class="space-y-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rn-card p-5">
                        <p class="text-sm text-rn-muted">Current rent</p>
                        <p class="mt-2 text-2xl font-semibold text-rn-text">
                            {{ currentRent.monthly_rent_formatted || '—' }}
                        </p>
                        <p v-if="currentRent.property_name" class="mt-1 text-sm text-rn-muted">
                            {{ currentRent.property_name }}
                        </p>
                        <p v-if="currentRent.rent_due_day" class="mt-3 text-xs text-rn-muted">
                            Due every month on day {{ currentRent.rent_due_day }}
                        </p>
                    </div>

                    <div class="rn-card p-5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm text-rn-muted">Next due</p>
                                <p class="mt-2 text-2xl font-semibold text-rn-text">
                                    {{ nextDue?.amount_formatted || '—' }}
                                </p>
                            </div>
                            <RnBadge v-if="nextDue" :color="nextDue.status_color">
                                {{ nextDue.status_label }}
                            </RnBadge>
                        </div>
                        <p class="mt-1 text-sm text-rn-muted">
                            <template v-if="nextDue">
                                {{ nextDue.period_label || 'Rent' }} · {{ formatDate(nextDue.due_date) }}
                            </template>
                            <template v-else>
                                You’re all caught up
                            </template>
                        </p>
                        <Link
                            v-if="nextDue"
                            :href="route('tenant.payments.show', nextDue.id)"
                            class="mt-4 inline-flex text-sm font-semibold text-rn-accent"
                        >
                            View details
                        </Link>
                    </div>
                </div>

                <RnCard flush>
                    <template #title>Payment history</template>

                    <div v-if="payments.data?.length" class="divide-y divide-rn-border">
                        <Link
                            v-for="payment in payments.data"
                            :key="payment.id"
                            :href="route('tenant.payments.show', payment.id)"
                            class="flex min-h-16 items-center justify-between gap-3 px-5 py-4 transition hover:bg-rn-bg/70 sm:px-6"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium text-rn-text">
                                    {{ payment.period_label || 'Rent payment' }}
                                </p>
                                <p class="mt-0.5 text-sm text-rn-muted">
                                    Due {{ formatDate(payment.due_date) }}
                                    <span v-if="payment.payment_date">
                                        · Paid {{ formatDate(payment.payment_date) }}
                                    </span>
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <div class="text-right">
                                    <p class="font-semibold text-rn-text">
                                        {{ payment.amount_formatted }}
                                    </p>
                                    <RnBadge class="mt-1" :color="payment.status_color" size="sm">
                                        {{ payment.status_label }}
                                    </RnBadge>
                                </div>
                                <ChevronRightIcon class="h-5 w-5 text-rn-muted" />
                            </div>
                        </Link>
                    </div>

                    <RnEmptyState
                        v-else
                        title="No payments yet"
                        description="Your rent payment history will show up here once payments are recorded."
                    >
                        <template #icon>
                            <BanknotesIcon class="h-6 w-6" />
                        </template>
                    </RnEmptyState>

                    <div v-if="payments.links?.length" class="border-t border-rn-border px-4 py-4">
                        <RnPagination :links="payments.links" />
                    </div>
                </RnCard>
            </div>
        </div>
    </TenantLayout>
</template>

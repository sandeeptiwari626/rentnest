<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import { formatDate } from '@/utils/format';

defineProps({
    payment: { type: Object, required: true },
});
</script>

<template>
    <Head :title="payment.period_label || 'Payment'" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <div class="mb-4">
                <Link
                    :href="route('tenant.payments.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-rn-muted hover:text-rn-text"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to payments
                </Link>
            </div>

            <RnPageHeader :title="payment.period_label || 'Rent payment'">
                <template #subtitle>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <RnBadge :color="payment.status_color">
                            {{ payment.status_label }}
                        </RnBadge>
                        <span class="text-sm text-rn-muted">
                            Due {{ formatDate(payment.due_date) }}
                        </span>
                    </div>
                </template>
                <template v-if="payment.can_download_receipt" #actions>
                    <a
                        :href="route('tenant.payments.receipt', payment.id)"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-rn-border bg-rn-surface px-4 py-2 text-sm font-medium text-rn-text transition hover:bg-rn-bg"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" />
                        Download receipt
                    </a>
                </template>
            </RnPageHeader>

            <div class="space-y-5">
                <div class="rn-card p-5 sm:p-6">
                    <p class="text-sm text-rn-muted">Amount</p>
                    <p class="mt-1 text-3xl font-semibold tracking-tight text-rn-text">
                        {{ payment.amount_formatted }}
                    </p>
                    <p
                        v-if="payment.amount_paid > 0 && payment.amount_paid !== payment.amount"
                        class="mt-2 text-sm text-rn-muted"
                    >
                        Paid so far: {{ payment.amount_paid_formatted }}
                    </p>
                </div>

                <RnCard>
                    <template #title>Details</template>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-rn-muted">Property</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ payment.property_name || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Unit</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ payment.unit_name || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Payment date</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ formatDate(payment.payment_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Method</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ payment.payment_method_label || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Reference</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ payment.reference_number || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Receipt no.</dt>
                            <dd class="mt-1 text-sm font-medium text-rn-text">
                                {{ payment.receipt_number || '—' }}
                            </dd>
                        </div>
                    </dl>

                    <p
                        v-if="payment.property_address"
                        class="mt-5 border-t border-rn-border pt-4 text-sm text-rn-muted"
                    >
                        {{ payment.property_address }}
                    </p>

                    <p
                        v-if="payment.notes"
                        class="mt-4 rounded-xl bg-rn-bg px-4 py-3 text-sm text-rn-text"
                    >
                        {{ payment.notes }}
                    </p>
                </RnCard>

                <RnCard v-if="payment.proof_url">
                    <template #title>Payment proof</template>
                    <img
                        v-if="payment.proof_is_image"
                        :src="payment.proof_url"
                        alt="Payment screenshot"
                        class="max-h-96 w-full rounded-xl border border-rn-border bg-rn-bg object-contain"
                    >
                    <a
                        v-else
                        :href="payment.proof_url"
                        class="text-sm font-medium text-rn-accent hover:underline"
                        target="_blank"
                    >
                        Download proof
                    </a>
                </RnCard>
            </div>
        </div>
    </TenantLayout>
</template>

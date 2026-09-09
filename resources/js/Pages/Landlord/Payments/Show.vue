<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';

const props = defineProps({
    payment: Object,
    statusOptions: Array,
    methodOptions: Array,
});

const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const form = useForm({
    status: props.payment.status,
    amount_paid: props.payment.amount_paid,
    payment_date: props.payment.payment_date || '',
    payment_method: props.payment.payment_method || '',
    reference_number: props.payment.reference_number || '',
    notes: props.payment.notes || '',
});

const updateStatus = () => form.put(route('landlord.payments.update', props.payment.id));
</script>

<template>
    <Head :title="`Payment · ${payment.period_label || payment.id}`" />
    <LandlordLayout>
        <RnPageHeader
            :title="payment.period_label || `Payment #${payment.id}`"
            :subtitle="payment.tenant?.name"
        >
            <template #actions>
                <Link :href="route('landlord.payments.edit', payment.id)">
                    <RnButton variant="secondary">Edit payment</RnButton>
                </Link>
                <a v-if="payment.status === 'paid'" :href="route('landlord.payments.receipt', payment.id)">
                    <RnButton variant="secondary">Download receipt</RnButton>
                </a>
                <Link :href="route('landlord.payments.index')"><RnButton variant="ghost">Back</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <RnCard>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-rn-muted">Amount due</p>
                            <p class="text-3xl font-semibold text-rn-text">{{ money(payment.amount) }}</p>
                        </div>
                        <RnBadge :color="payment.status_color" size="lg">{{ payment.status_label }}</RnBadge>
                    </div>
                    <dl class="mt-6 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-rn-muted">Paid</dt><dd class="mt-1 font-medium">{{ money(payment.amount_paid) }}</dd></div>
                        <div><dt class="text-rn-muted">Due date</dt><dd class="mt-1 font-medium">{{ dateLabel(payment.due_date) }}</dd></div>
                        <div><dt class="text-rn-muted">Payment date</dt><dd class="mt-1 font-medium">{{ dateLabel(payment.payment_date) }}</dd></div>
                        <div><dt class="text-rn-muted">Method</dt><dd class="mt-1 font-medium">{{ payment.payment_method_label || '—' }}</dd></div>
                        <div><dt class="text-rn-muted">Reference</dt><dd class="mt-1 font-medium">{{ payment.reference_number || '—' }}</dd></div>
                        <div><dt class="text-rn-muted">Receipt</dt><dd class="mt-1 font-medium">{{ payment.receipt_number || '—' }}</dd></div>
                    </dl>
                    <p v-if="payment.notes" class="mt-4 rounded-xl bg-rn-bg p-4 text-sm text-rn-muted">{{ payment.notes }}</p>
                </RnCard>

                <RnCard v-if="payment.proof_url">
                    <template #title>Payment proof</template>
                    <img
                        v-if="payment.proof_is_image"
                        :src="payment.proof_url"
                        alt="Payment screenshot"
                        class="max-h-96 w-full rounded-xl border border-rn-border object-contain bg-rn-bg"
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

                <RnCard>
                    <template #title>Update status</template>
                    <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="updateStatus">
                        <RnSelect v-model="form.status" label="Status" :options="statusOptions" :error="form.errors.status" />
                        <RnInput v-model="form.amount_paid" type="number" label="Amount paid" :error="form.errors.amount_paid" />
                        <RnInput v-model="form.payment_date" type="date" label="Payment date" :error="form.errors.payment_date" />
                        <RnSelect v-model="form.payment_method" label="Method" :options="methodOptions" :error="form.errors.payment_method" />
                        <RnInput v-model="form.reference_number" label="Reference" :error="form.errors.reference_number" class="sm:col-span-2" />
                        <RnTextarea v-model="form.notes" label="Notes" :error="form.errors.notes" class="sm:col-span-2" />
                        <div class="sm:col-span-2 flex justify-end">
                            <RnButton type="submit" :loading="form.processing">Update</RnButton>
                        </div>
                    </form>
                </RnCard>
            </div>

            <div class="space-y-6">
                <RnCard>
                    <template #title>Tenant</template>
                    <p class="font-medium">{{ payment.tenant?.name }}</p>
                    <p class="text-sm text-rn-muted">{{ payment.tenant?.email }}</p>
                    <p class="text-sm text-rn-muted">{{ payment.tenant?.phone }}</p>
                </RnCard>
                <RnCard>
                    <template #title>Property</template>
                    <p class="font-medium">{{ payment.property?.name }}</p>
                    <p class="text-sm text-rn-muted">{{ payment.property?.address }}, {{ payment.property?.city }}</p>
                </RnCard>
            </div>
        </div>
    </LandlordLayout>
</template>

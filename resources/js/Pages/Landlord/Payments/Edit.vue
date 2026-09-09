<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnFileUploader from '@/Components/ui/RnFileUploader.vue';
import RnFormErrors from '@/Components/ui/RnFormErrors.vue';

const props = defineProps({
    payment: Object,
    statusOptions: Array,
    methodOptions: Array,
});

const form = useForm({
    amount: props.payment.amount,
    amount_paid: props.payment.amount_paid,
    due_date: props.payment.due_date,
    payment_date: props.payment.payment_date || '',
    payment_method: props.payment.payment_method || '',
    reference_number: props.payment.reference_number || '',
    status: props.payment.status,
    period_label: props.payment.period_label || '',
    notes: props.payment.notes || '',
    proof: null,
    remove_proof: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        proof: data.proof || undefined,
        remove_proof: data.remove_proof ? 1 : 0,
    })).post(route('landlord.payments.update', props.payment.id), {
        forceFormData: Boolean(form.proof) || form.remove_proof,
    });
};
</script>

<template>
    <Head :title="`Edit payment · ${payment.period_label || payment.id}`" />
    <LandlordLayout>
        <RnPageHeader title="Edit payment" :subtitle="payment.lease_label">
            <template #actions>
                <Link :href="route('landlord.payments.show', payment.id)">
                    <RnButton variant="secondary">Cancel</RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <RnFormErrors :form="form" />
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <p class="rn-label">Lease</p>
                        <p class="mt-1 rounded-xl border border-rn-border bg-rn-bg px-3 py-2.5 text-sm text-rn-text">
                            {{ payment.lease_label || '—' }}
                        </p>
                    </div>
                    <RnInput v-model="form.period_label" label="Period label" placeholder="August 2026" :error="form.errors.period_label" class="sm:col-span-2" />
                    <RnInput v-model="form.amount" type="number" label="Amount due (₹)" required :error="form.errors.amount" />
                    <RnInput v-model="form.amount_paid" type="number" label="Amount paid (₹)" :error="form.errors.amount_paid" />
                    <RnInput v-model="form.due_date" type="date" label="Due date" required :error="form.errors.due_date" />
                    <RnInput v-model="form.payment_date" type="date" label="Payment date" :error="form.errors.payment_date" />
                    <RnSelect v-model="form.status" label="Status" :options="statusOptions" :error="form.errors.status" />
                    <RnSelect v-model="form.payment_method" label="Method" :options="methodOptions" :error="form.errors.payment_method" />
                    <RnInput v-model="form.reference_number" label="Reference / UTR" :error="form.errors.reference_number" class="sm:col-span-2" />
                    <RnTextarea v-model="form.notes" label="Notes" :error="form.errors.notes" class="sm:col-span-2" />
                </div>
            </RnCard>

            <RnCard>
                <template #title>Payment proof</template>
                <template #subtitle>Screenshot of UPI, bank transfer, or receipt</template>
                <div v-if="payment.proof_url && !form.remove_proof" class="mb-4">
                    <img
                        v-if="payment.proof_is_image"
                        :src="payment.proof_url"
                        alt="Current payment proof"
                        class="max-h-56 rounded-xl border border-rn-border object-contain"
                    >
                    <a
                        v-else
                        :href="payment.proof_url"
                        class="text-sm font-medium text-rn-accent hover:underline"
                        target="_blank"
                    >
                        View current proof
                    </a>
                    <div class="mt-2">
                        <RnButton variant="ghost" type="button" @click="form.remove_proof = true">Remove current proof</RnButton>
                    </div>
                </div>
                <RnFileUploader
                    accepts="image/*,.pdf"
                    label="Upload screenshot"
                    hint="JPG, PNG, or PDF — max 8 MB"
                    @update:files="form.proof = $event[0] || null"
                />
                <p v-if="form.errors.proof" class="mt-2 text-sm text-rn-danger">{{ form.errors.proof }}</p>
            </RnCard>

            <div class="flex justify-end gap-2">
                <Link :href="route('landlord.payments.show', payment.id)">
                    <RnButton variant="secondary" type="button">Cancel</RnButton>
                </Link>
                <RnButton type="submit" :loading="form.processing">Save changes</RnButton>
            </div>
        </form>
    </LandlordLayout>
</template>

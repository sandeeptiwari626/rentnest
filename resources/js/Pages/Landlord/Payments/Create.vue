<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';

const props = defineProps({
    leases: Array,
    statusOptions: Array,
    methodOptions: Array,
});

const form = useForm({
    lease_id: '',
    amount: '',
    amount_paid: '',
    due_date: '',
    payment_date: '',
    payment_method: '',
    reference_number: '',
    status: 'paid',
    period_label: '',
    notes: '',
});

const leaseOptions = computed(() => props.leases.map((l) => ({ value: l.id, label: l.label })));

watch(() => form.lease_id, (id) => {
    const lease = props.leases.find((l) => Number(l.id) === Number(id));
    if (lease && !form.amount) form.amount = lease.monthly_rent;
});

const submit = () => form.post(route('landlord.payments.store'));
</script>

<template>
    <Head title="Record payment" />
    <LandlordLayout>
        <RnPageHeader title="Record payment" subtitle="Manually log a rent payment.">
            <template #actions>
                <Link :href="route('landlord.payments.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl" @submit.prevent="submit">
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnSelect v-model="form.lease_id" label="Lease" :options="leaseOptions" required :error="form.errors.lease_id" class="sm:col-span-2" />
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
                <div class="mt-6 flex justify-end"><RnButton type="submit" :loading="form.processing">Save payment</RnButton></div>
            </RnCard>
        </form>
    </LandlordLayout>
</template>

<script setup>
import { computed } from 'vue';
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
    lease: Object,
    properties: Array,
    units: Array,
    tenants: Array,
    statusOptions: Array,
});

const form = useForm({
    property_id: props.lease.property_id,
    unit_id: props.lease.unit_id,
    tenant_id: props.lease.tenant_id,
    start_date: props.lease.start_date,
    end_date: props.lease.end_date || '',
    monthly_rent: props.lease.monthly_rent,
    security_deposit: props.lease.security_deposit,
    rent_due_day: props.lease.rent_due_day,
    notice_period_days: props.lease.notice_period_days,
    status: props.lease.status,
    notes: props.lease.notes || '',
    lease_document: null,
});

const propertyOptions = computed(() => props.properties.map((p) => ({ value: p.id, label: p.name })));
const tenantOptions = computed(() => props.tenants.map((t) => ({ value: t.id, label: t.name })));
const unitOptions = computed(() =>
    props.units
        .filter((u) => !form.property_id || Number(u.property_id) === Number(form.property_id))
        .map((u) => ({ value: u.id, label: u.name })),
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        lease_document: data.lease_document || undefined,
    })).post(route('landlord.leases.update', props.lease.id), {
        forceFormData: Boolean(form.lease_document),
    });
};
</script>

<template>
    <Head title="Edit lease" />
    <LandlordLayout>
        <RnPageHeader title="Edit lease">
            <template #actions>
                <Link :href="route('landlord.leases.show', lease.id)"><RnButton variant="secondary">Back</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-3xl space-y-6" @submit.prevent="submit">
            <RnFormErrors :form="form" />
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnSelect v-model="form.property_id" label="Property" :options="propertyOptions" required :error="form.errors.property_id" />
                    <RnSelect v-model="form.unit_id" label="Unit" :options="unitOptions" required :error="form.errors.unit_id" />
                    <RnSelect v-model="form.tenant_id" label="Tenant" :options="tenantOptions" required :error="form.errors.tenant_id" class="sm:col-span-2" />
                    <RnInput v-model="form.start_date" type="date" label="Start date" required :error="form.errors.start_date" />
                    <RnInput v-model="form.end_date" type="date" label="End date" :error="form.errors.end_date" />
                    <RnInput v-model="form.monthly_rent" type="number" label="Monthly rent (₹)" required :error="form.errors.monthly_rent" />
                    <RnInput v-model="form.security_deposit" type="number" label="Security deposit (₹)" :error="form.errors.security_deposit" />
                    <RnInput v-model="form.rent_due_day" type="number" label="Rent due day" required :error="form.errors.rent_due_day" />
                    <RnInput v-model="form.notice_period_days" type="number" label="Notice period (days)" :error="form.errors.notice_period_days" />
                    <RnSelect v-model="form.status" label="Status" :options="statusOptions" required :error="form.errors.status" />
                    <RnTextarea v-model="form.notes" label="Notes" :error="form.errors.notes" class="sm:col-span-2" />
                </div>
            </RnCard>
            <RnCard>
                <template #title>Replace lease document</template>
                <RnFileUploader accepts=".pdf,image/*" @update:files="form.lease_document = $event[0] || null" />
            </RnCard>
            <div class="flex justify-end"><RnButton type="submit" :loading="form.processing">Save changes</RnButton></div>
        </form>
    </LandlordLayout>
</template>

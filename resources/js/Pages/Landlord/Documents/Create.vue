<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnFileUploader from '@/Components/ui/RnFileUploader.vue';

const props = defineProps({
    typeOptions: Array,
    properties: Array,
    tenants: Array,
    leases: Array,
});

const form = useForm({
    title: '',
    type: 'other',
    file: null,
    visible_to_tenant: true,
    documentable_type: '',
    property_id: '',
    tenant_id: '',
    lease_id: '',
});

const propertyOptions = computed(() => props.properties.map((p) => ({ value: p.id, label: p.name })));
const tenantOptions = computed(() => props.tenants.map((t) => ({ value: t.id, label: t.name })));
const leaseOptions = computed(() => props.leases.map((l) => ({ value: l.id, label: l.label })));
const attachOptions = [
    { value: '', label: 'None (org library)' },
    { value: 'property', label: 'Property' },
    { value: 'tenant', label: 'Tenant' },
    { value: 'lease', label: 'Lease' },
];

const submit = () => form.post(route('landlord.documents.store'), { forceFormData: true });
</script>

<template>
    <Head title="Upload document" />
    <LandlordLayout>
        <RnPageHeader title="Upload document" subtitle="Stored privately on the local disk.">
            <template #actions>
                <Link :href="route('landlord.documents.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <RnCard>
                <div class="grid gap-4">
                    <RnInput v-model="form.title" label="Title" required :error="form.errors.title" />
                    <RnSelect v-model="form.type" label="Type" :options="typeOptions" required :error="form.errors.type" />
                    <RnSelect v-model="form.documentable_type" label="Attach to" :options="attachOptions" />
                    <RnSelect v-if="form.documentable_type === 'property'" v-model="form.property_id" label="Property" :options="propertyOptions" :error="form.errors.property_id" />
                    <RnSelect v-if="form.documentable_type === 'tenant'" v-model="form.tenant_id" label="Tenant" :options="tenantOptions" :error="form.errors.tenant_id" />
                    <RnSelect v-if="form.documentable_type === 'lease'" v-model="form.lease_id" label="Lease" :options="leaseOptions" :error="form.errors.lease_id" />
                    <label class="flex items-center gap-2 text-sm text-rn-text">
                        <input v-model="form.visible_to_tenant" type="checkbox" class="rounded border-rn-border text-rn-accent focus:ring-rn-accent" />
                        Visible to tenant
                    </label>
                </div>
            </RnCard>
            <RnCard>
                <template #title>File</template>
                <RnFileUploader @update:files="form.file = $event[0] || null" />
                <p v-if="form.errors.file" class="mt-2 text-sm text-rn-danger">{{ form.errors.file }}</p>
            </RnCard>
            <div class="flex justify-end"><RnButton type="submit" :loading="form.processing">Upload</RnButton></div>
        </form>
    </LandlordLayout>
</template>

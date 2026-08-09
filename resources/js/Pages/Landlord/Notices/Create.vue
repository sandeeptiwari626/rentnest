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

const props = defineProps({ properties: Array, tenants: Array });

const form = useForm({
    title: '',
    message: '',
    property_id: '',
    tenant_id: '',
    publish_date: new Date().toISOString().slice(0, 10),
    expiry_date: '',
});

const propertyOptions = computed(() => [
    { value: '', label: 'All properties' },
    ...props.properties.map((p) => ({ value: p.id, label: p.name })),
]);
const tenantOptions = computed(() => [
    { value: '', label: 'All tenants' },
    ...props.tenants.map((t) => ({ value: t.id, label: t.name })),
]);

const submit = () => form.post(route('landlord.notices.store'));
</script>

<template>
    <Head title="New notice" />
    <LandlordLayout>
        <RnPageHeader title="New notice" subtitle="Reach everyone, one property, or a single tenant.">
            <template #actions>
                <Link :href="route('landlord.notices.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl" @submit.prevent="submit">
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.title" label="Title" required :error="form.errors.title" class="sm:col-span-2" />
                    <RnTextarea v-model="form.message" label="Message" required :error="form.errors.message" class="sm:col-span-2" :rows="6" />
                    <RnSelect v-model="form.property_id" label="Property (optional)" :options="propertyOptions" :error="form.errors.property_id" />
                    <RnSelect v-model="form.tenant_id" label="Tenant (optional)" :options="tenantOptions" :error="form.errors.tenant_id" />
                    <RnInput v-model="form.publish_date" type="date" label="Publish date" required :error="form.errors.publish_date" />
                    <RnInput v-model="form.expiry_date" type="date" label="Expiry date" :error="form.errors.expiry_date" />
                </div>
                <div class="mt-6 flex justify-end"><RnButton type="submit" :loading="form.processing">Publish notice</RnButton></div>
            </RnCard>
        </form>
    </LandlordLayout>
</template>

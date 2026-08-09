<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';

const props = defineProps({ tenant: Object });

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email || '',
    phone: props.tenant.phone || '',
    notes: props.tenant.notes || '',
    emergency_contact_name: props.tenant.emergency_contact_name || '',
    emergency_contact_phone: props.tenant.emergency_contact_phone || '',
});

const submit = () => form.put(route('landlord.tenants.update', props.tenant.id));
</script>

<template>
    <Head :title="`Edit ${tenant.name}`" />
    <LandlordLayout>
        <RnPageHeader :title="`Edit ${tenant.name}`">
            <template #actions>
                <Link :href="route('landlord.tenants.show', tenant.id)"><RnButton variant="secondary">Back</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl" @submit.prevent="submit">
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.name" label="Full name" required :error="form.errors.name" class="sm:col-span-2" />
                    <RnInput v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                    <RnInput v-model="form.phone" label="Phone" :error="form.errors.phone" />
                    <RnInput v-model="form.emergency_contact_name" label="Emergency contact" :error="form.errors.emergency_contact_name" />
                    <RnInput v-model="form.emergency_contact_phone" label="Emergency phone" :error="form.errors.emergency_contact_phone" />
                    <RnTextarea v-model="form.notes" label="Notes" :error="form.errors.notes" class="sm:col-span-2" />
                </div>
                <div class="mt-6 flex justify-end"><RnButton type="submit" :loading="form.processing">Save changes</RnButton></div>
            </RnCard>
        </form>
    </LandlordLayout>
</template>

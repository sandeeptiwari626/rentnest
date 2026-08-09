<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    notes: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    create_account: false,
    password: '',
    password_confirmation: '',
});

const submit = () => form.post(route('landlord.tenants.store'));
</script>

<template>
    <Head title="Add tenant" />
    <LandlordLayout>
        <RnPageHeader title="Add tenant" subtitle="Create a tenant profile and optionally invite them with a login.">
            <template #actions>
                <Link :href="route('landlord.tenants.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <RnCard>
                <template #title>Profile</template>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.name" label="Full name" required :error="form.errors.name" class="sm:col-span-2" />
                    <RnInput v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                    <RnInput v-model="form.phone" label="Phone" :error="form.errors.phone" />
                    <RnInput v-model="form.emergency_contact_name" label="Emergency contact" :error="form.errors.emergency_contact_name" />
                    <RnInput v-model="form.emergency_contact_phone" label="Emergency phone" :error="form.errors.emergency_contact_phone" />
                    <RnTextarea v-model="form.notes" label="Notes" :error="form.errors.notes" class="sm:col-span-2" />
                </div>
            </RnCard>

            <RnCard>
                <template #title>Invite / login account</template>
                <label class="flex items-center gap-2 text-sm text-rn-text">
                    <input v-model="form.create_account" type="checkbox" class="rounded border-rn-border text-rn-accent focus:ring-rn-accent" />
                    Create login account for this tenant
                </label>
                <div v-if="form.create_account" class="mt-4 grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.password" type="password" label="Password" required :error="form.errors.password" />
                    <RnInput v-model="form.password_confirmation" type="password" label="Confirm password" required />
                    <p class="sm:col-span-2 text-xs text-rn-muted">Email is required when creating an account. They’ll be attached as a tenant in your organization.</p>
                </div>
            </RnCard>

            <div class="flex justify-end"><RnButton type="submit" :loading="form.processing">Save tenant</RnButton></div>
        </form>
    </LandlordLayout>
</template>

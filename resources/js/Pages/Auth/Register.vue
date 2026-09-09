<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    organization_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create landlord account" />

        <div class="mb-6">
            <h1 class="font-brand text-2xl font-semibold tracking-tight text-rn-text">
                Create your landlord account
            </h1>
            <p class="mt-1 text-sm text-rn-muted">
                A private workspace for the properties you own.
            </p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <RnInput
                id="name"
                v-model="form.name"
                label="Full name"
                autocomplete="name"
                required
                autofocus
                :error="form.errors.name"
            />

            <RnInput
                id="organization_name"
                v-model="form.organization_name"
                label="Organization name"
                placeholder="Optional — defaults to your name"
                :error="form.errors.organization_name"
            />

            <RnInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                autocomplete="username"
                required
                :error="form.errors.email"
            />

            <RnInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                autocomplete="new-password"
                required
                :error="form.errors.password"
            />

            <RnInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Confirm password"
                autocomplete="new-password"
                required
                :error="form.errors.password_confirmation"
            />

            <RnButton
                type="submit"
                class="w-full"
                size="lg"
                :loading="form.processing"
            >
                Create account
            </RnButton>
        </form>

        <p class="mt-6 text-center text-sm text-rn-muted">
            Already have an account?
            <Link :href="route('login')" class="font-medium text-rn-accent hover:text-teal-800">
                Log in
            </Link>
        </p>
    </GuestLayout>
</template>

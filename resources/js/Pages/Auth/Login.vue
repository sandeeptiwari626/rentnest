<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnAlert from '@/Components/ui/RnAlert.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-6">
            <h1 class="font-brand text-2xl font-semibold tracking-tight text-rn-text">
                Welcome back
            </h1>
            <p class="mt-1 text-sm text-rn-muted">
                Sign in to your private workspace.
            </p>
        </div>

        <RnAlert
            v-if="status"
            type="success"
            class="mb-4"
            :message="status"
        />

        <form class="space-y-4" @submit.prevent="submit">
            <RnInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                autocomplete="username"
                required
                autofocus
                :error="form.errors.email"
            />

            <RnInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                autocomplete="current-password"
                required
                :error="form.errors.password"
            />

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-rn-muted">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-rn-accent hover:text-teal-800"
                >
                    Forgot password?
                </Link>
            </div>

            <RnButton
                type="submit"
                class="w-full"
                size="lg"
                :loading="form.processing"
            >
                Log in
            </RnButton>
        </form>

        <p class="mt-6 text-center text-sm text-rn-muted">
            Landlord?
            <Link
                v-if="canRegister"
                :href="route('register')"
                class="font-medium text-rn-accent hover:text-teal-800"
            >
                Create an account
            </Link>
            <span v-else>Ask your administrator for access.</span>
        </p>
        <p class="mt-2 text-center text-sm text-rn-muted">
            Tenant access is invite-only. Contact your landlord for an account.
        </p>
    </GuestLayout>
</template>

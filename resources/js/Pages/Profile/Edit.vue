<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const role = computed(() => page.props.auth?.role);
const Layout = computed(() => (role.value === 'tenant' ? TenantLayout : LandlordLayout));
</script>

<template>
    <Head title="Profile" />

    <component :is="Layout">
        <RnPageHeader
            title="Profile"
            subtitle="Manage your account details and password."
        />

        <div class="space-y-6">
            <RnCard>
                <template #title>Profile information</template>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </RnCard>

            <RnCard>
                <template #title>Update password</template>
                <UpdatePasswordForm class="max-w-xl" />
            </RnCard>

            <RnCard>
                <template #title>Delete account</template>
                <DeleteUserForm class="max-w-xl" />
            </RnCard>
        </div>
    </component>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';

defineProps({ organization: Object });
</script>

<template>
    <Head title="Settings" />
    <LandlordLayout>
        <RnPageHeader title="Settings" subtitle="Organization details for your RentNest workspace.">
            <template #actions>
                <Link :href="route('profile.edit')"><RnButton variant="secondary">Edit profile</RnButton></Link>
            </template>
        </RnPageHeader>

        <RnCard class="mx-auto max-w-2xl">
            <template #title>Organization</template>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-rn-muted">Name</dt>
                    <dd class="mt-1 text-lg font-semibold text-rn-text">{{ organization.name }}</dd>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-rn-muted">Email</dt>
                        <dd class="mt-1 font-medium text-rn-text">{{ organization.email || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-rn-muted">Phone</dt>
                        <dd class="mt-1 font-medium text-rn-text">{{ organization.phone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-rn-muted">Timezone</dt>
                        <dd class="mt-1 font-medium text-rn-text">{{ organization.timezone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-rn-muted">Currency</dt>
                        <dd class="mt-1 font-medium text-rn-text">{{ organization.currency || 'INR' }}</dd>
                    </div>
                </div>
            </dl>
        </RnCard>
    </LandlordLayout>
</template>

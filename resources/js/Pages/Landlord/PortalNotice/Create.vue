<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnFormErrors from '@/Components/ui/RnFormErrors.vue';

const props = defineProps({
    suggestedVersion: String,
    sections: Array,
    title: String,
});

const form = useForm({
    title: props.title || 'Tenant Portal Notice',
    version: props.suggestedVersion || '1.1',
    activate: true,
    sections: (props.sections || []).map((section) => ({
        heading: section.heading,
        body: section.body,
    })),
});

const submit = () => form.post(route('landlord.portal-notice.store'));
</script>

<template>
    <Head title="New portal notice version" />
    <LandlordLayout>
        <RnPageHeader
            title="New notice version"
            subtitle="Publishing a new active version will ask tenants to acknowledge it before using the portal again. Previous acknowledgements are kept."
        >
            <template #actions>
                <Link :href="route('landlord.portal-notice.index')">
                    <RnButton variant="secondary">Cancel</RnButton>
                </Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-3xl space-y-6" @submit.prevent="submit">
            <RnFormErrors :form="form" />
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.title" label="Title" required :error="form.errors.title" class="sm:col-span-2" />
                    <RnInput v-model="form.version" label="Version" placeholder="1.1" required :error="form.errors.version" />
                    <label class="flex items-center gap-2 self-end pb-2 text-sm text-rn-text">
                        <input v-model="form.activate" type="checkbox" class="rounded border-rn-border text-rn-accent focus:ring-rn-accent" />
                        Activate immediately
                    </label>
                </div>
            </RnCard>

            <RnCard v-for="(section, index) in form.sections" :key="index">
                <RnInput v-model="section.heading" label="Section heading" required :error="form.errors[`sections.${index}.heading`]" />
                <RnTextarea
                    v-model="section.body"
                    class="mt-4"
                    label="Section text"
                    :rows="6"
                    required
                    :error="form.errors[`sections.${index}.body`]"
                />
            </RnCard>

            <div class="flex justify-end gap-2">
                <Link :href="route('landlord.portal-notice.index')">
                    <RnButton variant="secondary" type="button">Cancel</RnButton>
                </Link>
                <RnButton type="submit" :loading="form.processing">Save version</RnButton>
            </div>
        </form>
    </LandlordLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnFileUploader from '@/Components/ui/RnFileUploader.vue';

defineProps({
    typeOptions: Array,
    statusOptions: Array,
});

const form = useForm({
    name: '',
    type: 'apartment',
    status: 'vacant',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    description: '',
    bedrooms: '',
    bathrooms: '',
    area: '',
    area_unit: 'sqft',
    rent_amount: '',
    photos: [],
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            photos: data.photos?.length ? data.photos : undefined,
            bedrooms: data.bedrooms === '' ? null : data.bedrooms,
            bathrooms: data.bathrooms === '' ? null : data.bathrooms,
            area: data.area === '' ? null : data.area,
            rent_amount: data.rent_amount === '' ? null : data.rent_amount,
        }))
        .post(route('landlord.properties.store'), {
            forceFormData: form.photos.length > 0,
        });
};
</script>

<template>
    <Head title="Add property" />
    <LandlordLayout>
        <RnPageHeader title="Add property" subtitle="Saving this form also creates Unit 1 with the details below.">
            <template #actions>
                <Link :href="route('landlord.properties.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-3xl space-y-6" @submit.prevent="submit">
            <div
                v-if="Object.keys(form.errors).length"
                class="rounded-2xl border border-rn-danger/30 bg-rn-danger/5 px-4 py-3 text-sm text-rn-danger"
            >
                Please fix the highlighted fields and try again.
            </div>

            <RnCard>
                <template #title>Basics</template>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.name" label="Property name" required :error="form.errors.name" class="sm:col-span-2" />
                    <RnSelect v-model="form.type" label="Type" :options="typeOptions" required :error="form.errors.type" />
                    <RnSelect v-model="form.status" label="Status" :options="statusOptions" :error="form.errors.status" />
                    <RnInput v-model="form.address" label="Address" required :error="form.errors.address" class="sm:col-span-2" />
                    <RnInput v-model="form.city" label="City" required :error="form.errors.city" />
                    <RnInput v-model="form.state" label="State" required :error="form.errors.state" />
                    <RnInput v-model="form.postal_code" label="Postal code" required :error="form.errors.postal_code" />
                    <RnTextarea v-model="form.description" label="Description" :error="form.errors.description" class="sm:col-span-2" />
                </div>
            </RnCard>

            <RnCard>
                <template #title>Default unit details</template>
                <template #subtitle>Applied to Unit 1 created with this property</template>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.bedrooms" type="number" label="Bedrooms" :error="form.errors.bedrooms" />
                    <RnInput v-model="form.bathrooms" type="number" label="Bathrooms" :error="form.errors.bathrooms" />
                    <RnInput v-model="form.area" type="number" label="Area" :error="form.errors.area" />
                    <RnInput v-model="form.area_unit" label="Area unit" :error="form.errors.area_unit" />
                    <RnInput v-model="form.rent_amount" type="number" label="Expected rent (₹)" :error="form.errors.rent_amount" class="sm:col-span-2" />
                </div>
            </RnCard>

            <RnCard>
                <template #title>Photos</template>
                <RnFileUploader
                    accepts="image/*"
                    multiple
                    label="Property photos"
                    hint="Up to 10 images, 5MB each"
                    @update:files="form.photos = $event"
                />
                <p v-if="form.errors.photos" class="mt-2 text-sm text-rn-danger">{{ form.errors.photos }}</p>
            </RnCard>

            <div class="flex justify-end gap-2">
                <Link :href="route('landlord.properties.index')"><RnButton variant="secondary" type="button">Cancel</RnButton></Link>
                <RnButton type="submit" :loading="form.processing">Create property</RnButton>
            </div>
        </form>
    </LandlordLayout>
</template>

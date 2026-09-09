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
import RnFormErrors from '@/Components/ui/RnFormErrors.vue';

const props = defineProps({
    property: Object,
    typeOptions: Array,
    statusOptions: Array,
});

const form = useForm({
    name: props.property.name,
    type: props.property.type,
    status: props.property.status,
    address: props.property.address,
    city: props.property.city,
    state: props.property.state,
    postal_code: props.property.postal_code,
    description: props.property.description || '',
    bedrooms: props.property.bedrooms ?? '',
    bathrooms: props.property.bathrooms ?? '',
    area: props.property.area ?? '',
    area_unit: props.property.area_unit || 'sqft',
    photos: [],
    remove_photos: [],
});

const removeExisting = (path) => {
    if (!form.remove_photos.includes(path)) {
        form.remove_photos.push(path);
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        photos: data.photos?.length ? data.photos : undefined,
    })).post(route('landlord.properties.update', props.property.id), {
        forceFormData: form.photos.length > 0 || form.remove_photos.length > 0,
    });
};
</script>

<template>
    <Head :title="`Edit ${property.name}`" />
    <LandlordLayout>
        <RnPageHeader :title="`Edit ${property.name}`" subtitle="Update details and photos.">
            <template #actions>
                <Link :href="route('landlord.properties.show', property.id)"><RnButton variant="secondary">Back</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-3xl space-y-6" @submit.prevent="submit">
            <RnFormErrors :form="form" />
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnInput v-model="form.name" label="Property name" required :error="form.errors.name" class="sm:col-span-2" />
                    <RnSelect v-model="form.type" label="Type" :options="typeOptions" required :error="form.errors.type" />
                    <RnSelect v-model="form.status" label="Status" :options="statusOptions" required :error="form.errors.status" />
                    <RnInput v-model="form.address" label="Address" required :error="form.errors.address" class="sm:col-span-2" />
                    <RnInput v-model="form.city" label="City" required :error="form.errors.city" />
                    <RnInput v-model="form.state" label="State" required :error="form.errors.state" />
                    <RnInput v-model="form.postal_code" label="Postal code" required :error="form.errors.postal_code" />
                    <RnInput v-model="form.bedrooms" type="number" label="Bedrooms" :error="form.errors.bedrooms" />
                    <RnInput v-model="form.bathrooms" type="number" label="Bathrooms" :error="form.errors.bathrooms" />
                    <RnInput v-model="form.area" type="number" label="Area" :error="form.errors.area" />
                    <RnInput v-model="form.area_unit" label="Area unit" :error="form.errors.area_unit" />
                    <RnTextarea v-model="form.description" label="Description" :error="form.errors.description" class="sm:col-span-2" />
                </div>
            </RnCard>

            <RnCard v-if="property.photos?.length">
                <template #title>Current photos</template>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div
                        v-for="(photo, index) in property.photos"
                        :key="index"
                        class="relative overflow-hidden rounded-xl border border-rn-border"
                        :class="{ 'opacity-40': form.remove_photos.includes(photo.path) }"
                    >
                        <img :src="photo.url" class="h-28 w-full object-cover" alt="" />
                        <button
                            type="button"
                            class="absolute bottom-2 right-2 rounded-lg bg-white/90 px-2 py-1 text-xs font-medium text-rn-danger"
                            @click="removeExisting(photo.path)"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </RnCard>

            <RnCard>
                <template #title>Add photos</template>
                <RnFileUploader accepts="image/*" multiple @update:files="form.photos = $event" />
            </RnCard>

            <div class="flex justify-end gap-2">
                <RnButton type="submit" :loading="form.processing">Save changes</RnButton>
            </div>
        </form>
    </LandlordLayout>
</template>

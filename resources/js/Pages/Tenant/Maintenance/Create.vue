<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnFileUploader from '@/Components/ui/RnFileUploader.vue';
import RnFormErrors from '@/Components/ui/RnFormErrors.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';

defineProps({
    categories: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    home: { type: Object, default: null },
});

const photos = ref([]);

const form = useForm({
    title: '',
    description: '',
    category: '',
    priority: 'medium',
    photos: [],
});

const submit = () => {
    form.photos = photos.value;
    form.post(route('tenant.maintenance.store'), {
        forceFormData: photos.value.length > 0,
    });
};
</script>

<template>
    <Head title="New maintenance request" />

    <TenantLayout>
        <div class="mx-auto max-w-2xl">
            <div class="mb-4">
                <Link
                    :href="route('tenant.maintenance.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-rn-muted hover:text-rn-text"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back
                </Link>
            </div>

            <RnPageHeader
                title="New request"
                :subtitle="
                    home
                        ? `${home.property_name}${home.unit_name ? ` · Unit ${home.unit_name}` : ''}`
                        : 'Describe the issue so your landlord can help'
                "
            />

            <RnCard>
                <form class="space-y-5" @submit.prevent="submit">
                    <RnFormErrors :form="form" />
                    <RnInput
                        id="title"
                        v-model="form.title"
                        label="Title"
                        placeholder="e.g. Kitchen sink leaking"
                        required
                        :error="form.errors.title"
                    />

                    <RnTextarea
                        id="description"
                        v-model="form.description"
                        label="Description"
                        placeholder="What happened? When did it start? Any access notes?"
                        :rows="5"
                        required
                        :error="form.errors.description"
                    />

                    <div class="grid gap-5 sm:grid-cols-2">
                        <RnSelect
                            id="category"
                            v-model="form.category"
                            label="Category"
                            :options="categories"
                            placeholder="Select category"
                            required
                            :error="form.errors.category"
                        />
                        <RnSelect
                            id="priority"
                            v-model="form.priority"
                            label="Priority"
                            :options="priorities"
                            placeholder="Select priority"
                            required
                            :error="form.errors.priority"
                        />
                    </div>

                    <div>
                        <p class="rn-label">Photos</p>
                        <RnFileUploader
                            accepts="image/*"
                            multiple
                            label="Add photos"
                            hint="Up to 5 images, 5 MB each"
                            @update:files="(files) => (photos = files)"
                        />
                        <p v-if="form.errors.photos" class="mt-1.5 text-sm text-rn-danger">
                            {{ form.errors.photos }}
                        </p>
                        <p v-if="form.errors['photos.0']" class="mt-1.5 text-sm text-rn-danger">
                            {{ form.errors['photos.0'] }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                        <Link :href="route('tenant.maintenance.index')">
                            <RnButton type="button" variant="secondary" class="w-full min-h-11 sm:w-auto">
                                Cancel
                            </RnButton>
                        </Link>
                        <RnButton
                            type="submit"
                            class="w-full min-h-11 sm:w-auto"
                            :loading="form.processing"
                        >
                            Submit request
                        </RnButton>
                    </div>
                </form>
            </RnCard>
        </div>
    </TenantLayout>
</template>

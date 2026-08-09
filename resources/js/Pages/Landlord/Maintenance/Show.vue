<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnTimeline from '@/Components/ui/RnTimeline.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';

const props = defineProps({
    request: Object,
    timeline: Array,
    statusOptions: Array,
    priorityOptions: Array,
});

const form = useForm({
    status: props.request.status,
    priority: props.request.priority,
    scheduled_at: props.request.scheduled_at || '',
    comment: '',
});

const commentForm = useForm({ body: '' });

const update = () => form.patch(route('landlord.maintenance.update', props.request.id), {
    onSuccess: () => form.reset('comment'),
});

const addComment = () => commentForm.post(route('landlord.maintenance.comments.store', props.request.id), {
    onSuccess: () => commentForm.reset(),
});
</script>

<template>
    <Head :title="request.title" />
    <LandlordLayout>
        <RnPageHeader :title="request.title" :subtitle="request.property?.name">
            <template #actions>
                <Link :href="route('landlord.maintenance.index')"><RnButton variant="secondary">Back</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 flex flex-wrap gap-2">
            <RnBadge :color="request.status_color">{{ request.status_label }}</RnBadge>
            <RnBadge :color="request.priority_color">{{ request.priority_label }}</RnBadge>
            <RnBadge color="neutral">{{ request.category_label }}</RnBadge>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <RnCard>
                    <template #title>Details</template>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-rn-text">{{ request.description }}</p>
                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-rn-muted">Tenant</dt><dd class="font-medium">{{ request.tenant?.name }}</dd></div>
                        <div><dt class="text-rn-muted">Unit</dt><dd class="font-medium">{{ request.unit || '—' }}</dd></div>
                        <div><dt class="text-rn-muted">Reported by</dt><dd class="font-medium">{{ request.reported_by || '—' }}</dd></div>
                        <div><dt class="text-rn-muted">Created</dt><dd class="font-medium">{{ request.created_at }}</dd></div>
                    </dl>
                </RnCard>

                <RnCard>
                    <template #title>Timeline</template>
                    <RnTimeline v-if="timeline?.length" :items="timeline" />
                    <RnEmptyState v-else title="No activity yet" description="Status changes and comments will appear here." />
                </RnCard>

                <RnCard>
                    <template #title>Add comment</template>
                    <form class="space-y-3" @submit.prevent="addComment">
                        <RnTextarea v-model="commentForm.body" label="Comment" required :error="commentForm.errors.body" />
                        <div class="flex justify-end"><RnButton type="submit" :loading="commentForm.processing" size="sm">Post comment</RnButton></div>
                    </form>
                </RnCard>
            </div>

            <RnCard>
                <template #title>Update request</template>
                <form class="space-y-4" @submit.prevent="update">
                    <RnSelect v-model="form.status" label="Status" :options="statusOptions" required :error="form.errors.status" />
                    <RnSelect v-model="form.priority" label="Priority" :options="priorityOptions" :error="form.errors.priority" />
                    <RnInput v-model="form.scheduled_at" type="datetime-local" label="Scheduled at" :error="form.errors.scheduled_at" />
                    <RnTextarea v-model="form.comment" label="Note (optional)" :error="form.errors.comment" />
                    <RnButton type="submit" :loading="form.processing" class="w-full">Save update</RnButton>
                </form>
            </RnCard>
        </div>
    </LandlordLayout>
</template>

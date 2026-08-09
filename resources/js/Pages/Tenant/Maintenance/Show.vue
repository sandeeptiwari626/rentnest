<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnTimeline from '@/Components/ui/RnTimeline.vue';
import { formatDateTime } from '@/utils/format';

const props = defineProps({
    request: { type: Object, required: true },
    timeline: { type: Array, default: () => [] },
});

const form = useForm({
    body: '',
});

const submitComment = () => {
    form.post(route('tenant.maintenance.comments.store', props.request.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
};
</script>

<template>
    <Head :title="request.title" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <div class="mb-4">
                <Link
                    :href="route('tenant.maintenance.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-rn-muted hover:text-rn-text"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to requests
                </Link>
            </div>

            <RnPageHeader :title="request.title">
                <template #subtitle>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <RnBadge :color="request.status_color">
                            {{ request.status_label }}
                        </RnBadge>
                        <RnBadge :color="request.priority_color">
                            {{ request.priority_label }}
                        </RnBadge>
                        <span class="text-sm text-rn-muted">
                            {{ request.category_label }}
                        </span>
                    </div>
                </template>
            </RnPageHeader>

            <div class="space-y-5">
                <RnCard>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-rn-text">
                        {{ request.description }}
                    </p>

                    <dl class="mt-5 grid gap-3 border-t border-rn-border pt-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-rn-muted">Property</dt>
                            <dd class="mt-1 font-medium text-rn-text">
                                {{ request.property_name || '—' }}
                                <span v-if="request.unit_name" class="text-rn-muted">
                                    · Unit {{ request.unit_name }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-rn-muted">Submitted</dt>
                            <dd class="mt-1 font-medium text-rn-text">
                                {{ formatDateTime(request.created_at) }}
                            </dd>
                        </div>
                    </dl>

                    <div v-if="request.photos?.length" class="mt-5 border-t border-rn-border pt-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-rn-muted">
                            Photos
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <a
                                v-for="photo in request.photos"
                                :key="photo.index"
                                :href="photo.url"
                                target="_blank"
                                class="overflow-hidden rounded-xl border border-rn-border bg-rn-bg"
                            >
                                <img
                                    :src="photo.url"
                                    :alt="photo.name"
                                    class="aspect-square w-full object-cover"
                                />
                            </a>
                        </div>
                    </div>
                </RnCard>

                <RnCard>
                    <template #title>Timeline</template>
                    <RnTimeline v-if="timeline.length" :items="timeline" />
                    <p v-else class="text-sm text-rn-muted">No updates yet.</p>
                </RnCard>

                <RnCard>
                    <template #title>Add a comment</template>
                    <form class="space-y-4" @submit.prevent="submitComment">
                        <RnTextarea
                            id="body"
                            v-model="form.body"
                            placeholder="Share an update or extra details…"
                            :rows="3"
                            required
                            :error="form.errors.body"
                        />
                        <RnButton
                            type="submit"
                            class="min-h-11 w-full sm:w-auto"
                            :loading="form.processing"
                            :disabled="!form.body.trim()"
                        >
                            Post comment
                        </RnButton>
                    </form>
                </RnCard>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';

const props = defineProps({ notice: Object });
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const destroy = () => {
    if (confirm('Delete this notice?')) router.delete(route('landlord.notices.destroy', props.notice.id));
};
</script>

<template>
    <Head :title="notice.title" />
    <LandlordLayout>
        <RnPageHeader :title="notice.title" :subtitle="`Published ${dateLabel(notice.publish_date)}`">
            <template #actions>
                <Link :href="route('landlord.notices.index')"><RnButton variant="secondary">Back</RnButton></Link>
                <RnButton variant="danger" @click="destroy">Delete</RnButton>
            </template>
        </RnPageHeader>

        <RnCard class="mx-auto max-w-3xl">
            <div class="mb-4 flex flex-wrap gap-2">
                <RnBadge v-if="notice.property" color="neutral">{{ notice.property }}</RnBadge>
                <RnBadge v-if="notice.tenant" color="info">{{ notice.tenant }}</RnBadge>
                <RnBadge color="neutral">{{ notice.reads_count }} reads</RnBadge>
            </div>
            <p class="whitespace-pre-wrap text-base leading-relaxed text-rn-text">{{ notice.message }}</p>
            <dl class="mt-8 grid gap-3 border-t border-rn-border pt-6 text-sm sm:grid-cols-3">
                <div><dt class="text-rn-muted">Publish</dt><dd class="font-medium">{{ dateLabel(notice.publish_date) }}</dd></div>
                <div><dt class="text-rn-muted">Expiry</dt><dd class="font-medium">{{ dateLabel(notice.expiry_date) }}</dd></div>
                <div><dt class="text-rn-muted">Created by</dt><dd class="font-medium">{{ notice.created_by || '—' }}</dd></div>
            </dl>
        </RnCard>
    </LandlordLayout>
</template>

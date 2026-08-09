<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import { formatDate } from '@/utils/format';

defineProps({
    notice: { type: Object, required: true },
});
</script>

<template>
    <Head :title="notice.title" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <div class="mb-4">
                <Link
                    :href="route('tenant.notices.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-rn-muted hover:text-rn-text"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to notices
                </Link>
            </div>

            <RnPageHeader :title="notice.title">
                <template #subtitle>
                    <p class="mt-1 text-sm text-rn-muted">
                        Published {{ formatDate(notice.publish_date) }}
                        <span v-if="notice.expiry_date">
                            · Expires {{ formatDate(notice.expiry_date) }}
                        </span>
                    </p>
                </template>
            </RnPageHeader>

            <RnCard>
                <div class="prose prose-sm max-w-none whitespace-pre-wrap text-rn-text">
                    {{ notice.message }}
                </div>
            </RnCard>
        </div>
    </TenantLayout>
</template>

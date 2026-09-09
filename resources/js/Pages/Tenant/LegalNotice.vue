<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import LegalNoticeLayout from '@/Layouts/LegalNoticeLayout.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnFormErrors from '@/Components/ui/RnFormErrors.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    notice: { type: Object, required: true },
});

const scrollEl = ref(null);
const reachedEnd = ref(false);

const form = useForm({
    notice_id: props.notice.id,
    accepted: false,
});

const canCheck = computed(() => reachedEnd.value);
const canContinue = computed(() => canCheck.value && form.accepted && !form.processing);

const checkScroll = () => {
    const el = scrollEl.value;
    if (!el) {
        return;
    }

    const atEnd = el.scrollHeight <= el.clientHeight + 8
        || el.scrollTop + el.clientHeight >= el.scrollHeight - 32;

    if (atEnd) {
        reachedEnd.value = true;
    }
};

onMounted(() => {
    nextTick(() => checkScroll());
});

const submit = () => {
    if (!canContinue.value) {
        return;
    }

    form.post(route('tenant.legal-notice.store'));
};
</script>

<template>
    <Head :title="notice.display_title" />

    <LegalNoticeLayout>
        <p class="rn-kicker">Required before continuing</p>
        <h1 class="mt-2 font-brand text-3xl font-semibold tracking-tight text-rn-text sm:text-4xl">
            Important: Tenant Portal Notice
        </h1>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-rn-muted sm:text-base">
            Please read the following information before continuing to your tenant portal.
            This notice explains how the portal is used. It does not change your lease or tenancy agreement.
        </p>
        <p class="mt-2 text-xs font-medium uppercase tracking-wide text-rn-muted">
            {{ notice.display_title }}
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a
                :href="route('tenant.legal-notice.download')"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-rn-border bg-rn-surface px-4 py-2 text-sm font-medium text-rn-text transition hover:bg-rn-bg"
            >
                <ArrowDownTrayIcon class="h-4 w-4" />
                Download complete notice
            </a>
        </div>

        <form class="mt-6 space-y-6" @submit.prevent="submit">
            <RnFormErrors :form="form" />

            <RnCard flush>
                <div
                    ref="scrollEl"
                    class="max-h-[min(28rem,60vh)] overflow-y-auto px-5 py-5 sm:px-6"
                    tabindex="0"
                    @scroll="checkScroll"
                >
                    <article
                        v-for="(section, index) in notice.sections"
                        :key="section.heading"
                        class="pb-6"
                        :class="{ 'border-b border-rn-border mb-6': index < notice.sections.length - 1 }"
                    >
                        <h2 class="font-semibold text-rn-text">{{ section.heading }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-rn-muted">{{ section.body }}</p>
                    </article>
                </div>
            </RnCard>

            <p v-if="!reachedEnd" class="text-sm text-rn-muted">
                Scroll through the notice above to enable the confirmation checkbox. The full text is available in this page and in the download.
            </p>

            <label class="flex items-start gap-3 rounded-2xl border border-rn-border bg-rn-surface px-4 py-4 text-sm leading-relaxed text-rn-text">
                <Checkbox
                    class="mt-0.5"
                    :checked="form.accepted"
                    :disabled="!canCheck"
                    @update:checked="form.accepted = $event"
                />
                <span :class="{ 'text-rn-muted': !canCheck }">
                    I confirm that I have read and understood the above Tenant Portal Notice and acknowledge the terms applicable to my use of this portal. I understand that electronic communications and records may be maintained and made available through the portal.
                </span>
            </label>
            <p v-if="form.errors.accepted" class="text-sm text-rn-danger">{{ form.errors.accepted }}</p>

            <RnButton
                type="submit"
                size="lg"
                class="w-full sm:w-auto"
                :disabled="!canContinue"
                :loading="form.processing"
            >
                Continue to Tenant Portal
            </RnButton>
        </form>
    </LegalNoticeLayout>
</template>

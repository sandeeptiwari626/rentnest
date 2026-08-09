<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';

const props = defineProps({ expense: Object });
const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

const destroy = () => {
    if (confirm('Delete this expense?')) router.delete(route('landlord.expenses.destroy', props.expense.id));
};
</script>

<template>
    <Head :title="`Expense · ${expense.category_label}`" />
    <LandlordLayout>
        <RnPageHeader :title="expense.category_label" :subtitle="dateLabel(expense.expense_date)">
            <template #actions>
                <Link :href="route('landlord.expenses.index')"><RnButton variant="secondary">Back</RnButton></Link>
                <RnButton variant="danger" @click="destroy">Delete</RnButton>
            </template>
        </RnPageHeader>

        <RnCard class="mx-auto max-w-2xl">
            <p class="text-3xl font-semibold text-rn-text">{{ money(expense.amount) }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <RnBadge color="neutral">{{ expense.category_label }}</RnBadge>
                <RnBadge v-if="expense.has_receipt" color="info">Has receipt</RnBadge>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-rn-muted">Property</dt><dd>{{ expense.property || '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-rn-muted">Vendor</dt><dd>{{ expense.vendor || '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-rn-muted">Recorded by</dt><dd>{{ expense.recorded_by || '—' }}</dd></div>
            </dl>
            <p v-if="expense.description" class="mt-6 rounded-xl bg-rn-bg p-4 text-sm text-rn-muted">{{ expense.description }}</p>
        </RnCard>
    </LandlordLayout>
</template>

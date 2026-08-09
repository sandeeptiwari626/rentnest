<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnStatCard from '@/Components/ui/RnStatCard.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPagination from '@/Components/ui/RnPagination.vue';
import { PlusIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    expenses: Object,
    summary: Object,
    filters: Object,
    categoryOptions: Array,
});

const category = ref(props.filters.category || '');
const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const dateLabel = (d) => (d ? new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

watch(category, () => {
    router.get(route('landlord.expenses.index'), { category: category.value || undefined }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Delete this expense?')) router.delete(route('landlord.expenses.destroy', id));
};
</script>

<template>
    <Head title="Expenses" />
    <LandlordLayout>
        <RnPageHeader title="Expenses" :subtitle="`Summary for ${summary.month_label}`">
            <template #actions>
                <Link :href="route('landlord.expenses.create')"><RnButton><PlusIcon class="h-4 w-4" />Add expense</RnButton></Link>
            </template>
        </RnPageHeader>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <RnStatCard label="This month" :value="money(summary.monthly_total)" />
            <RnCard class="sm:col-span-2">
                <template #title>By category</template>
                <div v-if="!summary.by_category?.length" class="text-sm text-rn-muted">No expenses this month.</div>
                <div v-else class="flex flex-wrap gap-3">
                    <div v-for="row in summary.by_category" :key="row.category" class="rounded-xl bg-rn-bg px-3 py-2 text-sm">
                        <span class="text-rn-muted">{{ row.label }}</span>
                        <span class="ml-2 font-semibold text-rn-text">{{ money(row.total) }}</span>
                    </div>
                </div>
            </RnCard>
        </div>

        <div class="mb-6 max-w-xs"><RnSelect v-model="category" :options="categoryOptions" placeholder="All categories" /></div>

        <RnCard v-if="!expenses.data?.length" flush>
            <RnEmptyState title="No expenses recorded" description="Track repairs, utilities, taxes, and more.">
                <template #icon><ReceiptPercentIcon class="h-6 w-6" /></template>
                <template #action><Link :href="route('landlord.expenses.create')"><RnButton>Add expense</RnButton></Link></template>
            </RnEmptyState>
        </RnCard>

        <div v-else class="space-y-3">
            <div v-for="expense in expenses.data" :key="expense.id" class="rn-card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link :href="route('landlord.expenses.show', expense.id)" class="font-semibold text-rn-text hover:text-rn-accent">
                        {{ expense.category_label }} · {{ money(expense.amount) }}
                    </Link>
                    <p class="mt-1 text-sm text-rn-muted">
                        {{ dateLabel(expense.expense_date) }}
                        <span v-if="expense.property"> · {{ expense.property }}</span>
                        <span v-if="expense.vendor"> · {{ expense.vendor }}</span>
                    </p>
                    <p v-if="expense.description" class="mt-1 text-sm text-rn-muted line-clamp-1">{{ expense.description }}</p>
                </div>
                <RnButton variant="danger" size="sm" @click="destroy(expense.id)">Delete</RnButton>
            </div>
        </div>
        <div class="mt-6"><RnPagination :links="expenses.links || []" /></div>
    </LandlordLayout>
</template>

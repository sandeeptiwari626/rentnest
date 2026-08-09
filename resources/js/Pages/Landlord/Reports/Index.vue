<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnStatCard from '@/Components/ui/RnStatCard.vue';

const props = defineProps({
    summary: Object,
    chart: Object,
    history: Array,
});

const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN')}`;
const chartReady = ref(false);
const ChartBar = ref(null);

const maxBar = computed(() => Math.max(1, ...(props.history || []).flatMap((h) => [h.collected, h.expenses])));

onMounted(async () => {
    try {
        const { Bar } = await import('vue-chartjs');
        const {
            Chart,
            BarElement,
            CategoryScale,
            LinearScale,
            Legend,
            Tooltip,
        } = await import('chart.js');

        Chart.register(BarElement, CategoryScale, LinearScale, Legend, Tooltip);
        ChartBar.value = Bar;
        chartReady.value = true;
    } catch {
        chartReady.value = false;
    }
});

const chartData = computed(() => ({
    labels: props.chart?.labels || [],
    datasets: [
        {
            label: 'Collected',
            backgroundColor: '#0f766e',
            data: props.chart?.collected || [],
            borderRadius: 6,
        },
        {
            label: 'Expenses',
            backgroundColor: '#f59e0b',
            data: props.chart?.expenses || [],
            borderRadius: 6,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' },
    },
    scales: {
        y: {
            ticks: {
                callback: (value) => `₹${Number(value).toLocaleString('en-IN')}`,
            },
        },
    },
};
</script>

<template>
    <Head title="Reports" />
    <LandlordLayout>
        <RnPageHeader title="Reports" subtitle="Rent collection, expenses, and net income at a glance." />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <RnStatCard label="Collected this month" :value="money(summary.rent_collected_month)" />
            <RnStatCard label="Collected this year" :value="money(summary.rent_collected_year)" />
            <RnStatCard label="Outstanding" :value="money(summary.outstanding)" />
            <RnStatCard label="Net income (month)" :value="money(summary.net_income_month)" />
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <RnStatCard label="Expenses (month)" :value="money(summary.expenses_month)" />
            <RnStatCard label="Expenses (year)" :value="money(summary.expenses_year)" />
            <RnStatCard label="Maintenance costs (YTD)" :value="money(summary.maintenance_costs)" />
            <RnStatCard label="Open maintenance" :value="summary.open_maintenance" />
        </div>

        <RnCard class="mt-8">
            <template #title>Payment history</template>
            <template #subtitle>Last 6 months · collected vs expenses</template>

            <div v-if="chartReady && ChartBar" class="h-72">
                <component :is="ChartBar" :data="chartData" :options="chartOptions" />
            </div>

            <div v-else class="space-y-3">
                <div v-for="row in history" :key="row.label">
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="font-medium text-rn-text">{{ row.label }}</span>
                        <span class="text-rn-muted">Net {{ money(row.net) }}</span>
                    </div>
                    <div class="flex h-3 overflow-hidden rounded-full bg-rn-bg">
                        <div
                            class="bg-rn-accent"
                            :style="{ width: `${(row.collected / maxBar) * 100}%` }"
                            :title="`Collected ${money(row.collected)}`"
                        />
                        <div
                            class="bg-rn-warning"
                            :style="{ width: `${(row.expenses / maxBar) * 100}%` }"
                            :title="`Expenses ${money(row.expenses)}`"
                        />
                    </div>
                </div>
                <p v-if="!history?.length" class="text-sm text-rn-muted">No history yet.</p>
            </div>
        </RnCard>
    </LandlordLayout>
</template>

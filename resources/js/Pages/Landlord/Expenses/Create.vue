<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/Layouts/LandlordLayout.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import RnInput from '@/Components/ui/RnInput.vue';
import RnSelect from '@/Components/ui/RnSelect.vue';
import RnTextarea from '@/Components/ui/RnTextarea.vue';
import RnFileUploader from '@/Components/ui/RnFileUploader.vue';

const props = defineProps({ categoryOptions: Array, properties: Array });

const form = useForm({
    property_id: '',
    category: 'maintenance',
    amount: '',
    expense_date: new Date().toISOString().slice(0, 10),
    vendor: '',
    description: '',
    receipt: null,
});

const propertyOptions = computed(() => [
    { value: '', label: 'No specific property' },
    ...props.properties.map((p) => ({ value: p.id, label: p.name })),
]);

const submit = () => form.post(route('landlord.expenses.store'), { forceFormData: true });
</script>

<template>
    <Head title="Add expense" />
    <LandlordLayout>
        <RnPageHeader title="Add expense">
            <template #actions>
                <Link :href="route('landlord.expenses.index')"><RnButton variant="secondary">Cancel</RnButton></Link>
            </template>
        </RnPageHeader>

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <RnCard>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RnSelect v-model="form.category" label="Category" :options="categoryOptions" required :error="form.errors.category" />
                    <RnInput v-model="form.amount" type="number" label="Amount (₹)" required :error="form.errors.amount" />
                    <RnInput v-model="form.expense_date" type="date" label="Date" required :error="form.errors.expense_date" />
                    <RnSelect v-model="form.property_id" label="Property" :options="propertyOptions" placeholder="" :error="form.errors.property_id" />
                    <RnInput v-model="form.vendor" label="Vendor" :error="form.errors.vendor" class="sm:col-span-2" />
                    <RnTextarea v-model="form.description" label="Description" :error="form.errors.description" class="sm:col-span-2" />
                </div>
            </RnCard>
            <RnCard>
                <template #title>Receipt (optional)</template>
                <RnFileUploader accepts=".pdf,image/*" @update:files="form.receipt = $event[0] || null" />
            </RnCard>
            <div class="flex justify-end"><RnButton type="submit" :loading="form.processing">Save expense</RnButton></div>
        </form>
    </LandlordLayout>
</template>

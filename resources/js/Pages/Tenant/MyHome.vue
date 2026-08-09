<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    BuildingOffice2Icon,
    EnvelopeIcon,
    HomeIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import RnBadge from '@/Components/ui/RnBadge.vue';
import RnCard from '@/Components/ui/RnCard.vue';
import RnEmptyState from '@/Components/ui/RnEmptyState.vue';
import RnPageHeader from '@/Components/ui/RnPageHeader.vue';
import { formatDate } from '@/utils/format';

defineProps({
    lease: { type: Object, default: null },
    property: { type: Object, default: null },
    unit: { type: Object, default: null },
    organization: { type: Object, default: null },
    tenant: { type: Object, default: null },
});
</script>

<template>
    <Head title="My Home" />

    <TenantLayout>
        <div class="mx-auto max-w-3xl">
            <RnPageHeader
                title="My Home"
                subtitle="Your property, unit, and lease details"
            />

            <template v-if="property && lease">
                <div class="space-y-5">
                    <RnCard>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-rn-muted">
                                    Property
                                </p>
                                <h2 class="mt-1 text-2xl font-semibold text-rn-text">
                                    {{ property.name }}
                                </h2>
                                <p v-if="property.type_label" class="mt-1 text-sm text-rn-muted">
                                    {{ property.type_label }}
                                </p>
                            </div>
                            <RnBadge :color="lease.status_color">
                                {{ lease.status_label }}
                            </RnBadge>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-rn-text">
                            {{ property.full_address }}
                        </p>

                        <dl class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div v-if="unit">
                                <dt class="text-xs text-rn-muted">Unit</dt>
                                <dd class="mt-1 text-sm font-semibold text-rn-text">
                                    {{ unit.name }}
                                </dd>
                            </div>
                            <div v-if="unit?.bedrooms != null || property.bedrooms != null">
                                <dt class="text-xs text-rn-muted">Bedrooms</dt>
                                <dd class="mt-1 text-sm font-semibold text-rn-text">
                                    {{ unit?.bedrooms ?? property.bedrooms }}
                                </dd>
                            </div>
                            <div v-if="unit?.bathrooms != null || property.bathrooms != null">
                                <dt class="text-xs text-rn-muted">Bathrooms</dt>
                                <dd class="mt-1 text-sm font-semibold text-rn-text">
                                    {{ unit?.bathrooms ?? property.bathrooms }}
                                </dd>
                            </div>
                            <div v-if="unit?.area || property.area">
                                <dt class="text-xs text-rn-muted">Area</dt>
                                <dd class="mt-1 text-sm font-semibold text-rn-text">
                                    {{ unit?.area || property.area }}
                                    {{ property.area_unit || '' }}
                                </dd>
                            </div>
                        </dl>

                        <p
                            v-if="property.description"
                            class="mt-5 border-t border-rn-border pt-4 text-sm text-rn-muted"
                        >
                            {{ property.description }}
                        </p>
                    </RnCard>

                    <RnCard>
                        <template #title>Lease</template>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-rn-muted">Monthly rent</dt>
                                <dd class="mt-1 text-lg font-semibold text-rn-text">
                                    {{ lease.monthly_rent_formatted }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-rn-muted">Security deposit</dt>
                                <dd class="mt-1 text-lg font-semibold text-rn-text">
                                    {{ lease.security_deposit_formatted }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-rn-muted">Start date</dt>
                                <dd class="mt-1 text-sm font-medium text-rn-text">
                                    {{ formatDate(lease.start_date) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-rn-muted">End date</dt>
                                <dd class="mt-1 text-sm font-medium text-rn-text">
                                    {{ formatDate(lease.end_date) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-rn-muted">Rent due day</dt>
                                <dd class="mt-1 text-sm font-medium text-rn-text">
                                    Day {{ lease.rent_due_day }} of each month
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-rn-muted">Notice period</dt>
                                <dd class="mt-1 text-sm font-medium text-rn-text">
                                    {{ lease.notice_period_days }} days
                                </dd>
                            </div>
                        </dl>
                    </RnCard>

                    <RnCard v-if="organization">
                        <template #title>Landlord contact</template>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <BuildingOffice2Icon class="h-5 w-5 text-rn-accent" />
                                <span class="text-sm font-medium text-rn-text">
                                    {{ organization.name }}
                                </span>
                            </div>
                            <a
                                v-if="organization.email"
                                :href="`mailto:${organization.email}`"
                                class="flex min-h-11 items-center gap-3 rounded-xl px-1 text-sm text-rn-text hover:bg-rn-bg"
                            >
                                <EnvelopeIcon class="h-5 w-5 text-rn-muted" />
                                {{ organization.email }}
                            </a>
                            <a
                                v-if="organization.phone"
                                :href="`tel:${organization.phone}`"
                                class="flex min-h-11 items-center gap-3 rounded-xl px-1 text-sm text-rn-text hover:bg-rn-bg"
                            >
                                <PhoneIcon class="h-5 w-5 text-rn-muted" />
                                {{ organization.phone }}
                            </a>
                        </div>
                    </RnCard>

                    <RnCard v-if="tenant?.emergency_contact_name || tenant?.emergency_contact_phone">
                        <template #title>Your emergency contact</template>
                        <p class="text-sm font-medium text-rn-text">
                            {{ tenant.emergency_contact_name || '—' }}
                        </p>
                        <p v-if="tenant.emergency_contact_phone" class="mt-1 text-sm text-rn-muted">
                            {{ tenant.emergency_contact_phone }}
                        </p>
                    </RnCard>
                </div>
            </template>

            <RnCard v-else>
                <RnEmptyState
                    title="No active home"
                    description="When your lease is set up, property and unit details will appear here."
                >
                    <template #icon>
                        <HomeIcon class="h-6 w-6" />
                    </template>
                    <template #action>
                        <Link
                            :href="route('tenant.home')"
                            class="text-sm font-semibold text-rn-accent"
                        >
                            Back to home
                        </Link>
                    </template>
                </RnEmptyState>
            </RnCard>
        </div>
    </TenantLayout>
</template>

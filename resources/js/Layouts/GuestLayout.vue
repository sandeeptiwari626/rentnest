<script setup>
import { Link } from '@inertiajs/vue3';
import RnBrand from '@/Components/ui/RnBrand.vue';
import FlashToast from '@/Components/ui/FlashToast.vue';
</script>

<template>
    <div class="rn-guest-pattern flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">
        <FlashToast />

        <div class="mb-8 text-center">
            <Link href="/" class="inline-block">
                <RnBrand size="lg" />
            </Link>
            <p class="mt-3 text-sm text-rn-muted sm:text-base">
                Everything about your rental, in one place.
            </p>
        </div>

        <div
            class="w-full max-w-md overflow-hidden rounded-2xl border border-rn-border bg-rn-surface px-6 py-7 shadow-rn sm:px-8"
        >
            <slot />
        </div>

        <p class="mt-8 text-center text-xs text-rn-muted">
            Secure access for landlords and tenants.
        </p>
    </div>
</template>

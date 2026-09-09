<script setup>
import { Link } from '@inertiajs/vue3';
import RnBrand from '@/Components/ui/RnBrand.vue';
import FlashToast from '@/Components/ui/FlashToast.vue';
</script>

<template>
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <FlashToast />

        <aside class="rn-ink-panel relative hidden flex-col justify-between overflow-hidden px-12 py-12 text-rn-surface lg:flex">
            <Link href="/" class="relative z-10 inline-block w-fit">
                <RnBrand size="md" variant="light" />
            </Link>

            <div class="relative z-10 max-w-md">
                <p class="rn-kicker">Private rental operations</p>
                <h1 class="mt-5 font-brand text-5xl font-semibold leading-[1.12] tracking-tight">
                    Quiet control over every property you own.
                </h1>
                <p class="mt-6 text-base leading-relaxed text-white/65">
                    Landlords run the portfolio. Tenants are invited in — never the other way around.
                </p>
            </div>

            <p class="relative z-10 text-sm text-white/40">
                Secure access for landlords and residents.
            </p>
        </aside>

        <div class="rn-guest-pattern flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-8">
            <div class="mb-8 text-center lg:hidden">
                <Link href="/" class="inline-block">
                    <RnBrand size="lg" />
                </Link>
            </div>

            <div class="w-full max-w-md overflow-hidden rounded-[1.6rem] border border-rn-border bg-rn-surface/90 px-6 py-8 shadow-rn backdrop-blur sm:px-9">
                <slot />
            </div>
        </div>
    </div>
</template>

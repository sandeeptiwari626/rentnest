<script setup>
import { Link } from '@inertiajs/vue3';
import RnBrand from '@/Components/ui/RnBrand.vue';
import FlashToast from '@/Components/ui/FlashToast.vue';
</script>

<template>
    <div class="min-h-screen bg-rn-bg">
        <FlashToast />
        <header class="border-b border-rn-border bg-rn-surface/90">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6">
                <RnBrand size="sm" />
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-medium text-rn-muted hover:text-rn-text"
                >
                    Log out
                </Link>
            </div>
        </header>
        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-10">
            <slot />
        </main>
    </div>
</template>

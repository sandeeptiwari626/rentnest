<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import RnBrand from '@/Components/ui/RnBrand.vue';
import RnButton from '@/Components/ui/RnButton.vue';
import {
    BanknotesIcon,
    BuildingOffice2Icon,
    DocumentTextIcon,
    MegaphoneIcon,
    SparklesIcon,
    UserGroupIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';

const mobileOpen = ref(false);

const features = [
    {
        title: 'Properties & units',
        body: 'A living inventory of every building, flat, and occupancy status — without the spreadsheets.',
        icon: BuildingOffice2Icon,
    },
    {
        title: 'Tenants you invite',
        body: 'Add residents from your dashboard and open a login only when you are ready.',
        icon: UserGroupIcon,
    },
    {
        title: 'Rent, collected calmly',
        body: 'See due, paid, and overdue at a glance. Share a receipt in one click.',
        icon: BanknotesIcon,
    },
    {
        title: 'Maintenance with a trail',
        body: 'Tenants raise tickets. You schedule, comment, and close with a full history.',
        icon: WrenchScrewdriverIcon,
    },
    {
        title: 'A private document vault',
        body: 'Agreements, IDs, and notices live with the property — not in a downloads folder.',
        icon: DocumentTextIcon,
    },
    {
        title: 'Notices that land',
        body: 'Publish to the whole house or a single tenant. They see it the next time they log in.',
        icon: MegaphoneIcon,
    },
];

const steps = [
    {
        n: '01',
        title: 'Open your workspace',
        body: 'Create a landlord account. Your organization is ready the moment you sign in.',
    },
    {
        n: '02',
        title: 'Invite the household',
        body: 'Add a property, then add the tenant. Optionally create their login from the same screen.',
    },
    {
        n: '03',
        title: 'Run the month',
        body: 'Rent, repairs, papers, and notices stay in one quiet, ordered place.',
    },
];

const year = computed(() => new Date().getFullYear());
</script>

<template>
    <Head title="Rental management, refined" />

    <div class="min-h-screen bg-rn-bg text-rn-text">
        <header class="sticky top-0 z-30 border-b border-white/10 bg-rn-ink/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                <Link href="/" class="shrink-0">
                    <RnBrand size="md" variant="light" />
                </Link>

                <nav class="hidden items-center gap-8 text-sm font-medium text-white/60 md:flex">
                    <a href="#features" class="transition hover:text-white">Product</a>
                    <a href="#how-it-works" class="transition hover:text-white">How it works</a>
                    <a href="#for-landlords" class="transition hover:text-white">For landlords</a>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    <Link :href="route('login')">
                        <RnButton variant="ghost" class="!text-white/80 hover:!bg-white/10 hover:!text-white">
                            Log in
                        </RnButton>
                    </Link>
                    <Link :href="route('register')">
                        <RnButton variant="gold">Get started</RnButton>
                    </Link>
                </div>

                <button
                    type="button"
                    class="rounded-2xl border border-white/15 px-3 py-2 text-sm font-medium text-white md:hidden"
                    @click="mobileOpen = !mobileOpen"
                >
                    Menu
                </button>
            </div>

            <div v-if="mobileOpen" class="border-t border-white/10 px-4 py-4 md:hidden">
                <div class="flex flex-col gap-3 text-sm font-medium text-white/70">
                    <a href="#features" @click="mobileOpen = false">Product</a>
                    <a href="#how-it-works" @click="mobileOpen = false">How it works</a>
                    <Link :href="route('login')">Log in</Link>
                    <Link :href="route('register')">
                        <RnButton variant="gold" class="w-full">Get started</RnButton>
                    </Link>
                </div>
            </div>
        </header>

        <section class="rn-ink-panel relative overflow-hidden text-rn-surface">
            <div class="relative mx-auto grid max-w-6xl gap-14 px-4 py-20 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-28">
                <div>
                    <p class="rn-kicker inline-flex items-center gap-2">
                        <SparklesIcon class="h-4 w-4" />
                        Built for owners, not marketplaces
                    </p>
                    <h1 class="mt-6 font-brand text-4xl font-semibold tracking-tight sm:text-6xl sm:leading-[1.05]">
                        Your rentals,<br class="hidden sm:block" /> kept with care.
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-white/65">
                        RentNest is a private CRM for landlords. Sign up, add your tenants from the dashboard,
                        and keep rent, repairs, and records in one composed workspace.
                    </p>
                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        <Link :href="route('register')">
                            <RnButton size="lg" variant="gold">Create landlord account</RnButton>
                        </Link>
                        <Link :href="route('login')">
                            <RnButton size="lg" variant="ghost" class="!text-white hover:!bg-white/10">
                                Log in
                            </RnButton>
                        </Link>
                    </div>
                    <p class="mt-5 text-sm text-white/45">
                        Tenants never self-register. You invite them after you add their profile.
                    </p>
                </div>

                <div class="relative">
                    <div class="absolute -inset-6 rounded-[2rem] bg-rn-gold/10 blur-2xl" />
                    <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-[#14211e] p-5 shadow-rn-lg sm:p-6">
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-rn-gold">Dashboard</p>
                                <p class="mt-1 font-brand text-xl text-white">Good evening, Sandeep</p>
                            </div>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white/70">Live preview</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div class="rounded-2xl bg-white/5 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-white/40">Properties</p>
                                <p class="mt-1 font-brand text-2xl text-white">12</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-white/40">Collected</p>
                                <p class="mt-1 font-brand text-2xl text-white">₹4.2L</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-white/40">Open</p>
                                <p class="mt-1 font-brand text-2xl text-rn-gold">3</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2.5">
                            <div class="flex items-center justify-between rounded-2xl bg-white/[0.04] px-4 py-3">
                                <div>
                                    <p class="text-sm text-white">1BHK · Derabassi</p>
                                    <p class="text-xs text-white/40">Priya Sharma · due 5 Sep</p>
                                </div>
                                <span class="text-sm font-medium text-emerald-300">Paid</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-white/[0.04] px-4 py-3">
                                <div>
                                    <p class="text-sm text-white">Studio · Chandigarh</p>
                                    <p class="text-xs text-white/40">Plumbing · high</p>
                                </div>
                                <span class="text-sm font-medium text-rn-gold">In progress</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-dashed border-white/15 px-4 py-3">
                                <p class="text-sm text-white/55">Add tenant</p>
                                <span class="text-xs text-rn-gold">From the dashboard →</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-b border-rn-border bg-rn-surface/80">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-3 sm:px-6">
                <div>
                    <p class="font-brand text-3xl text-rn-text">One workspace</p>
                    <p class="mt-1 text-sm text-rn-muted">Properties, people, and paper in a single ledger.</p>
                </div>
                <div>
                    <p class="font-brand text-3xl text-rn-text">Invite-only tenants</p>
                    <p class="mt-1 text-sm text-rn-muted">You decide who enters. They never find you on a listing wall.</p>
                </div>
                <div>
                    <p class="font-brand text-3xl text-rn-text">Built for India</p>
                    <p class="mt-1 text-sm text-rn-muted">INR, UPI-friendly receipts, and Asia/Kolkata by default.</p>
                </div>
            </div>
        </section>

        <section id="features" class="py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="max-w-2xl">
                    <p class="rn-kicker">The product</p>
                    <h2 class="mt-3 font-brand text-4xl font-semibold tracking-tight">Everything a serious landlord actually needs.</h2>
                    <p class="mt-4 text-rn-muted">No marketplace noise. Just the operating system for the homes you already own.</p>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-[1.4rem] border border-rn-border bg-rn-surface p-6 shadow-rn transition duration-300 hover:-translate-y-0.5 hover:border-rn-gold/40"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rn-ink text-rn-gold transition group-hover:bg-rn-accent">
                            <component :is="feature.icon" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-5 font-semibold text-rn-text">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-rn-muted">{{ feature.body }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="border-y border-rn-border bg-rn-surface/70 py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="max-w-2xl">
                    <p class="rn-kicker">How it works</p>
                    <h2 class="mt-3 font-brand text-4xl font-semibold tracking-tight">Three calm steps.</h2>
                </div>
                <div class="mt-12 grid gap-6 lg:grid-cols-3">
                    <article v-for="step in steps" :key="step.n" class="relative overflow-hidden rounded-[1.5rem] border border-rn-border bg-rn-bg/50 p-7">
                        <p class="font-brand text-5xl text-rn-gold/80">{{ step.n }}</p>
                        <h3 class="mt-6 text-xl font-semibold">{{ step.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-rn-muted">{{ step.body }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="for-landlords" class="rn-ink-panel px-4 py-20 text-rn-surface sm:px-6 sm:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <p class="rn-kicker">For landlords</p>
                <h2 class="mt-4 font-brand text-4xl font-semibold tracking-tight sm:text-5xl">
                    Ready when you are to add your first tenant.
                </h2>
                <p class="mx-auto mt-5 max-w-xl text-base leading-relaxed text-white/60">
                    Open your account, walk into the dashboard, and invite the people who live in your properties.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-3">
                    <Link :href="route('register')">
                        <RnButton size="lg" variant="gold">Sign up as landlord</RnButton>
                    </Link>
                    <Link :href="route('login')">
                        <RnButton size="lg" variant="ghost" class="!text-white hover:!bg-white/10">
                            I already have an account
                        </RnButton>
                    </Link>
                </div>
            </div>
        </section>

        <footer class="border-t border-rn-border bg-rn-surface">
            <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-4 px-4 py-10 text-sm text-rn-muted sm:flex-row sm:items-center sm:px-6">
                <RnBrand size="sm" />
                <p>© {{ year }} RentNest. A private workspace for property owners.</p>
            </div>
        </footer>
    </div>
</template>

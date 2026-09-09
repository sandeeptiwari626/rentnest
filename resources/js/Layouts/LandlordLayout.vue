<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    HomeIcon,
    MegaphoneIcon,
    ReceiptPercentIcon,
    UserCircleIcon,
    UserGroupIcon,
    WrenchScrewdriverIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import RnBrand from '@/Components/ui/RnBrand.vue';
import RnAvatar from '@/Components/ui/RnAvatar.vue';
import RnDropdown from '@/Components/ui/RnDropdown.vue';
import FlashToast from '@/Components/ui/FlashToast.vue';

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications?.items ?? []);
const unreadCount = computed(() => page.props.notifications?.unread_count ?? 0);

const mainNav = [
    { name: 'Dashboard', href: 'landlord.dashboard', icon: HomeIcon },
    { name: 'Properties', href: 'landlord.properties.index', icon: BuildingOffice2Icon },
    { name: 'Tenants', href: 'landlord.tenants.index', icon: UserGroupIcon },
    { name: 'Leases', href: 'landlord.leases.index', icon: ClipboardDocumentListIcon },
    { name: 'Payments', href: 'landlord.payments.index', icon: CurrencyDollarIcon },
    { name: 'Maintenance', href: 'landlord.maintenance.index', icon: WrenchScrewdriverIcon },
    { name: 'Documents', href: 'landlord.documents.index', icon: DocumentTextIcon },
    { name: 'Expenses', href: 'landlord.expenses.index', icon: ReceiptPercentIcon },
    { name: 'Notices', href: 'landlord.notices.index', icon: MegaphoneIcon },
    { name: 'Reports', href: 'landlord.reports.index', icon: ChartBarIcon },
];

const bottomNav = [
    { name: 'Settings', href: 'landlord.settings.index', icon: Cog6ToothIcon },
    { name: 'Profile', href: 'profile.edit', icon: UserCircleIcon },
];

const isActive = (name) => {
    try {
        return route().current(name) || route().current(`${name.replace(/\.index$/, '')}.*`);
    } catch {
        return false;
    }
};

const safeRoute = (name) => {
    try {
        return route(name);
    } catch {
        return '#';
    }
};

const formatTime = (iso) => {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    } catch {
        return '';
    }
};

const openNotification = (item) => {
    if (item?.id) {
        router.post(route('notifications.read', item.id), {}, { preserveScroll: true });
    }
    const url = item?.data?.url;
    if (url) {
        router.visit(url);
    }
};

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true });
};

const logout = () => {
    router.post(route('logout'));
};

watch(
    () => page.url,
    () => {
        sidebarOpen.value = false;
    },
);
</script>

<template>
    <div class="min-h-screen bg-rn-bg">
        <FlashToast />

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-rn-ink/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            class="rn-ink-panel fixed inset-y-0 left-0 z-50 flex w-64 flex-col text-rn-surface transition-transform duration-200 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-[4.25rem] items-center justify-between px-5">
                <Link :href="safeRoute('landlord.dashboard')">
                    <RnBrand size="md" variant="light" />
                </Link>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-white/50 hover:bg-white/10 hover:text-white lg:hidden"
                    @click="sidebarOpen = false"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <div v-if="page.props.organization?.name" class="px-5 pb-3">
                <p class="truncate text-[11px] font-semibold uppercase tracking-[0.18em] text-rn-gold/80">
                    {{ page.props.organization.name }}
                </p>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2">
                <Link
                    v-for="item in mainNav"
                    :key="item.name"
                    :href="safeRoute(item.href)"
                    class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition"
                    :class="
                        isActive(item.href)
                            ? 'bg-white/10 text-white shadow-sm ring-1 ring-rn-gold/30'
                            : 'text-white/55 hover:bg-white/5 hover:text-white'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" :class="isActive(item.href) ? 'text-rn-gold' : ''" />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="space-y-0.5 border-t border-white/10 px-3 py-3">
                <Link
                    v-for="item in bottomNav"
                    :key="item.name"
                    :href="safeRoute(item.href)"
                    class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-white/55 transition hover:bg-white/5 hover:text-white"
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    {{ item.name }}
                </Link>
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-white/55 transition hover:bg-white/5 hover:text-white"
                    @click="logout"
                >
                    <ArrowRightOnRectangleIcon class="h-5 w-5 shrink-0" />
                    Logout
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="lg:pl-64">
            <header
                class="sticky top-0 z-30 flex h-[4.25rem] items-center justify-between gap-3 border-b border-rn-border/80 bg-rn-surface/80 px-4 backdrop-blur-xl sm:px-6"
            >
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-rn-border p-2 text-rn-muted hover:bg-rn-bg lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <Bars3Icon class="h-5 w-5" />
                    </button>
                    <div class="hidden sm:block">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <RnDropdown align="right" width="w-80">
                        <template #trigger>
                            <button
                                type="button"
                        class="relative rounded-2xl border border-rn-border p-2 text-rn-muted transition hover:bg-white hover:text-rn-text"
                            >
                                <BellIcon class="h-5 w-5" />
                                <span
                                    v-if="unreadCount"
                                    class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rn-gold"
                                />
                            </button>
                        </template>

                        <div class="flex items-center justify-between px-3 py-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-rn-muted">
                                Notifications
                            </div>
                            <button
                                v-if="unreadCount"
                                type="button"
                                class="text-xs font-medium text-rn-accent hover:underline"
                                @click="markAllRead"
                            >
                                Mark all read
                            </button>
                        </div>
                        <div v-if="!notifications.length" class="px-4 py-6 text-center text-sm text-rn-muted">
                            You're all caught up.
                        </div>
                        <button
                            v-for="item in notifications"
                            :key="item.id"
                            type="button"
                            class="block w-full border-t border-rn-border px-4 py-3 text-left text-sm hover:bg-rn-bg"
                            @click="openNotification(item)"
                        >
                            <p class="font-medium text-rn-text">
                                {{ item.title || item.message }}
                            </p>
                            <p v-if="item.message && item.title" class="mt-0.5 text-xs text-rn-muted">
                                {{ item.message }}
                            </p>
                            <p v-if="item.created_at" class="mt-1 text-xs text-rn-muted">
                                {{ formatTime(item.created_at) }}
                            </p>
                        </button>
                    </RnDropdown>

                    <RnDropdown align="right" width="w-56">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-2xl border border-rn-border bg-white/60 py-1.5 pl-1.5 pr-3 transition hover:bg-white"
                            >
                                <RnAvatar :name="user?.name || 'User'" size="sm" />
                                <span class="hidden max-w-[8rem] truncate text-sm font-medium text-rn-text sm:inline">
                                    {{ user?.name }}
                                </span>
                            </button>
                        </template>
                        <Link
                            :href="safeRoute('profile.edit')"
                            class="block px-4 py-2 text-sm text-rn-text hover:bg-rn-bg"
                        >
                            Profile
                        </Link>
                        <button
                            type="button"
                            class="block w-full px-4 py-2 text-left text-sm text-rn-text hover:bg-rn-bg"
                            @click="logout"
                        >
                            Log out
                        </button>
                    </RnDropdown>
                </div>
            </header>

            <main class="px-4 py-7 sm:px-6 lg:px-8">
                <div class="sm:hidden" v-if="$slots.header">
                    <div class="mb-4">
                        <slot name="header" />
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

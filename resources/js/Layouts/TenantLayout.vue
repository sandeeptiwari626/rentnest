<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BellIcon,
    DocumentTextIcon,
    HomeIcon,
    MegaphoneIcon,
    BanknotesIcon,
    UserCircleIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import RnBrand from '@/Components/ui/RnBrand.vue';
import RnAvatar from '@/Components/ui/RnAvatar.vue';
import RnDropdown from '@/Components/ui/RnDropdown.vue';
import FlashToast from '@/Components/ui/FlashToast.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications?.items ?? []);
const unreadCount = computed(() => page.props.notifications?.unread_count ?? 0);

const sidebarNav = [
    { name: 'Home', href: 'tenant.home', icon: HomeIcon, short: 'Home' },
    { name: 'My Home', href: 'tenant.my-home', icon: HomeIcon, short: 'My Home' },
    { name: 'Rent & Payments', href: 'tenant.payments.index', icon: BanknotesIcon, short: 'Payments' },
    { name: 'Maintenance', href: 'tenant.maintenance.index', icon: WrenchScrewdriverIcon, short: 'Maintenance' },
    { name: 'Documents', href: 'tenant.documents.index', icon: DocumentTextIcon, short: 'Docs' },
    { name: 'Notices', href: 'tenant.notices.index', icon: MegaphoneIcon, short: 'Notices' },
    { name: 'Profile', href: 'profile.edit', icon: UserCircleIcon, short: 'Profile' },
];

const bottomNav = [
    { name: 'Home', href: 'tenant.home', icon: HomeIcon },
    { name: 'Payments', href: 'tenant.payments.index', icon: BanknotesIcon },
    { name: 'Maintenance', href: 'tenant.maintenance.index', icon: WrenchScrewdriverIcon },
    { name: 'Documents', href: 'tenant.documents.index', icon: DocumentTextIcon },
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
</script>

<template>
    <div class="min-h-screen bg-rn-bg pb-20 md:pb-0">
        <FlashToast />

        <!-- Desktop slim sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-56 flex-col border-r border-rn-border bg-rn-surface md:flex"
        >
            <div class="flex h-16 items-center px-5">
                <Link :href="safeRoute('tenant.home')">
                    <RnBrand size="md" />
                </Link>
            </div>

            <nav class="flex-1 space-y-0.5 px-3 py-2">
                <Link
                    v-for="item in sidebarNav"
                    :key="item.name"
                    :href="safeRoute(item.href)"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition"
                    :class="
                        isActive(item.href)
                            ? 'bg-rn-accent-soft text-rn-accent'
                            : 'text-rn-muted hover:bg-rn-bg hover:text-rn-text'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    <span class="truncate">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="border-t border-rn-border px-4 py-4">
                <div class="flex items-center gap-3">
                    <RnAvatar :name="user?.name || 'User'" size="sm" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-rn-text">
                            {{ user?.name }}
                        </p>
                        <p class="truncate text-xs text-rn-muted">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main column -->
        <div class="md:pl-56">
            <header
                class="sticky top-0 z-30 flex h-14 items-center justify-between gap-3 border-b border-rn-border bg-rn-surface/90 px-4 backdrop-blur sm:h-16 sm:px-6"
            >
                <div class="flex items-center gap-3 md:hidden">
                    <RnBrand size="sm" />
                </div>
                <div class="hidden md:block">
                    <slot name="header" />
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <RnDropdown align="right" width="w-80">
                        <template #trigger>
                            <button
                                type="button"
                                class="relative rounded-xl border border-rn-border p-2 text-rn-muted transition hover:bg-rn-bg hover:text-rn-text"
                            >
                                <BellIcon class="h-5 w-5" />
                                <span
                                    v-if="unreadCount"
                                    class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rn-accent"
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
                            No new notifications.
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

                    <Link
                        :href="safeRoute('profile.edit')"
                        class="hidden rounded-full sm:inline-flex"
                    >
                        <RnAvatar :name="user?.name || 'User'" size="sm" />
                    </Link>
                </div>
            </header>

            <main class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                <div class="mb-4 md:hidden" v-if="$slots.header">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>

        <!-- Mobile bottom nav -->
        <nav
            class="fixed inset-x-0 bottom-0 z-40 border-t border-rn-border bg-rn-surface/95 backdrop-blur md:hidden"
        >
            <div class="grid grid-cols-5 gap-1 px-1 py-1.5">
                <Link
                    v-for="item in bottomNav"
                    :key="item.name"
                    :href="safeRoute(item.href)"
                    class="flex flex-col items-center gap-0.5 rounded-xl px-1 py-2 text-[10px] font-medium transition"
                    :class="
                        isActive(item.href)
                            ? 'text-rn-accent'
                            : 'text-rn-muted'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    {{ item.name }}
                </Link>
            </div>
        </nav>
    </div>
</template>

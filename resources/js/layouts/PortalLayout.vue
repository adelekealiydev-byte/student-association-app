<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { Auth } from '@/types';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    Home,
    UserCheck,
    CreditCard,
    QrCode,
    Users,
    Mail,
    DollarSign,
    Shield,
    Menu,
    X,
    LogOut,
    Settings as SettingsIcon,
    ChevronRight,
} from '@lucide/vue';

const props = defineProps<{
    title?: string;
    section?: 'home' | 'membership' | 'members' | 'finance' | 'roles';
    tabs?: Array<{ label: string; href: string; active: boolean }>;
}>();

const page = usePage<{ auth: Auth; name: string }>();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles ?? []);

const isAdmin = computed(() => roles.value.includes('Admin'));
const isSecretary = computed(() => roles.value.includes('Secretary'));
const isTreasurer = computed(() => roles.value.includes('Treasurer'));
const canManageMembers = computed(() => isAdmin.value || isSecretary.value);
const canManageFinance = computed(() => isAdmin.value || isTreasurer.value);

const currentPath = computed(() => page.url);

const mobileMenuOpen = ref(false);

const navItems = computed(() => {
    const items = [
        {
            name: 'Home',
            href: '/dashboard',
            icon: Home,
            active: currentPath.value === '/dashboard' || props.section === 'home',
        },
        {
            name: 'Membership',
            href: '/me/membership',
            icon: UserCheck,
            active: currentPath.value.startsWith('/me/') || props.section === 'membership',
        },
    ];

    if (canManageMembers.value) {
        items.push({
            name: 'Members',
            href: '/members',
            icon: Users,
            active: currentPath.value.startsWith('/members') || currentPath.value.startsWith('/invitations') || props.section === 'members',
        });
    }

    if (canManageFinance.value) {
        items.push({
            name: 'Finance',
            href: '/finance',
            icon: DollarSign,
            active: currentPath.value.startsWith('/finance') || props.section === 'finance',
        });
    }

    if (isAdmin.value) {
        items.push({
            name: 'Roles',
            href: '/roles',
            icon: Shield,
            active: currentPath.value.startsWith('/roles') || props.section === 'roles',
        });
    }

    return items;
});

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 flex flex-col antialiased">
        <!-- Top Bar -->
        <header class="sticky top-0 z-40 w-full border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-4">
                    <Link href="/dashboard" class="flex items-center gap-2.5 font-semibold text-lg tracking-tight hover:opacity-90">
                        <div class="h-8 w-8 rounded-lg bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            SA
                        </div>
                        <span class="hidden sm:inline font-bold">Student Association</span>
                        <span class="sm:hidden font-bold">SAPortal</span>
                    </Link>

                    <div v-if="user?.membership_status" class="hidden md:flex items-center gap-1.5 ml-2">
                        <StatusBadge :status="user.membership_status" size="sm" />
                        <span v-for="role in roles" :key="role" class="rounded bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                            {{ role }}
                        </span>
                    </div>
                </div>

                <!-- Top Bar User Actions -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold leading-tight">{{ user?.username || user?.email }}</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ user?.student_number || user?.email }}</span>
                    </div>

                    <Link
                        href="/me/settings"
                        class="p-2 rounded-lg text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition"
                        title="Settings"
                        aria-label="Account Settings"
                    >
                        <SettingsIcon class="h-5 w-5" />
                    </Link>

                    <button
                        @click="handleLogout"
                        class="p-2 rounded-lg text-zinc-600 hover:text-rose-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/30 transition"
                        title="Sign out"
                        aria-label="Sign out"
                    >
                        <LogOut class="h-5 w-5" />
                    </button>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="sm:hidden p-2 rounded-lg text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800"
                        aria-label="Toggle navigation menu"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Body Frame -->
        <div class="mx-auto flex w-full max-w-7xl flex-1 px-4 sm:px-6 lg:px-8 pb-20 sm:pb-8 pt-4 sm:pt-6 gap-8">
            <!-- Desktop Sidebar Navigation -->
            <aside class="hidden sm:block w-56 shrink-0" aria-label="Desktop Navigation">
                <nav class="sticky top-24 space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.active
                                ? 'bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/60 dark:text-indigo-300'
                                : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100',
                            'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition font-medium',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            :class="[
                                item.active ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300',
                                'h-5 w-5 shrink-0',
                            ]"
                            aria-hidden="true"
                        />
                        <span>{{ item.name }}</span>
                    </Link>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 min-w-0" id="main-content">
                <!-- Section Header & Title -->
                <div v-if="title" class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                            {{ title }}
                        </h1>
                        <slot name="subtitle" />
                    </div>
                    <div class="flex items-center gap-3">
                        <slot name="actions" />
                    </div>
                </div>

                <!-- Section Tabs (Section 5.2) -->
                <div v-if="tabs && tabs.length > 1" class="mb-6 border-b border-zinc-200 dark:border-zinc-800">
                    <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Section tabs">
                        <Link
                            v-for="tab in tabs"
                            :key="tab.label"
                            :href="tab.href"
                            :class="[
                                tab.active
                                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 font-semibold'
                                    : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-200 font-medium',
                                'whitespace-nowrap border-b-2 py-3 text-sm transition',
                            ]"
                        >
                            {{ tab.label }}
                        </Link>
                    </nav>
                </div>

                <!-- Page Content Slot -->
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Bar (Section 5.3: Home, Dues, Card, More) -->
        <nav class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-zinc-900/95 border-t border-zinc-200 dark:border-zinc-800 backdrop-blur" aria-label="Mobile Bottom Navigation">
            <div class="grid grid-cols-4 h-16">
                <Link
                    href="/dashboard"
                    class="flex flex-col items-center justify-center gap-1 text-xs"
                    :class="currentPath === '/dashboard' ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'"
                >
                    <Home class="h-5 w-5" />
                    <span>Home</span>
                </Link>

                <Link
                    href="/me/dues"
                    class="flex flex-col items-center justify-center gap-1 text-xs"
                    :class="currentPath === '/me/dues' ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'"
                >
                    <CreditCard class="h-5 w-5" />
                    <span>Dues</span>
                </Link>

                <!-- ID Card: 1 tap away from anywhere for event check-in -->
                <Link
                    href="/me/card"
                    class="flex flex-col items-center justify-center gap-1 text-xs font-semibold"
                    :class="currentPath === '/me/card' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-700 dark:text-zinc-200'"
                >
                    <div class="rounded-full bg-indigo-600 p-1.5 text-white shadow-sm dark:bg-indigo-500">
                        <QrCode class="h-5 w-5" />
                    </div>
                    <span>ID Card</span>
                </Link>

                <button
                    @click="mobileMenuOpen = true"
                    class="flex flex-col items-center justify-center gap-1 text-xs text-zinc-500 hover:text-zinc-900 dark:text-zinc-400"
                >
                    <Menu class="h-5 w-5" />
                    <span>More</span>
                </button>
            </div>
        </nav>

        <!-- Mobile More Sheet Modal -->
        <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 flex flex-col justify-end bg-black/60 backdrop-blur-sm sm:hidden" role="dialog" aria-modal="true">
            <div class="bg-white dark:bg-zinc-900 rounded-t-2xl p-6 border-t border-zinc-200 dark:border-zinc-800 space-y-4 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <div class="font-bold text-base">{{ user?.username || user?.email }}</div>
                        <div class="text-xs text-zinc-500">{{ user?.student_number }}</div>
                    </div>
                    <button @click="mobileMenuOpen = false" class="p-2 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.name"
                        :href="item.href"
                        @click="mobileMenuOpen = false"
                        class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <div class="flex items-center gap-3">
                            <component :is="item.icon" class="h-5 w-5 text-zinc-500" />
                            <span>{{ item.name }}</span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-zinc-400" />
                    </Link>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
                    <Link
                        href="/me/settings"
                        @click="mobileMenuOpen = false"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <SettingsIcon class="h-5 w-5 text-zinc-500" />
                        <span>Settings</span>
                    </Link>

                    <button
                        @click="handleLogout"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                    >
                        <LogOut class="h-5 w-5" />
                        <span>Sign Out</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>


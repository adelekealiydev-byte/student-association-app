<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Search, UserPlus, Filter, CheckCircle2, AlertCircle, ChevronLeft, ChevronRight } from '@lucide/vue';

interface MemberItem {
    member_id: number;
    email: string;
    student_number: string;
    username: string | null;
    membership_status: string;
    is_verified: boolean;
    roles: string[];
    created_at: string;
}

interface PaginatedData {
    data: MemberItem[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    members: PaginatedData;
    filters: {
        search?: string;
        status?: string;
    };
    statusOptions: string[];
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const handleFilter = () => {
    router.get(
        '/members',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get('/members');
};

const tabs = [
    { label: 'All Members', href: '/members', active: true },
    { label: 'Invitations', href: '/invitations', active: false },
];
</script>

<template>
    <Head title="Member Directory" />

    <PortalLayout title="Members" section="members" :tabs="tabs">
        <div class="space-y-6">
            <!-- Header and Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Member Directory</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        View, search, and manage all association members and their membership statuses.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/members/invite"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition"
                    >
                        <UserPlus class="h-4 w-4" />
                        Invite Member
                    </Link>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by student number, email, or username..."
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 pl-10 pr-4 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @keydown.enter="handleFilter"
                        />
                    </div>
                    <div class="w-full sm:w-48">
                        <select
                            v-model="status"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @change="handleFilter"
                        >
                            <option value="">All Statuses</option>
                            <option v-for="opt in statusOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                        @click="handleFilter"
                    >
                        Filter
                    </button>
                    <button
                        v-if="filters.search || filters.status"
                        type="button"
                        class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 underline"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Members Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Student Number</th>
                                <th scope="col" class="px-3 py-3.5">Member</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Verified</th>
                                <th scope="col" class="px-3 py-3.5">Roles</th>
                                <th scope="col" class="px-3 py-3.5">Registered</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="members.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    No members found matching your search criteria.
                                </td>
                            </tr>
                            <tr
                                v-for="item in members.data"
                                :key="item.member_id"
                                class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition"
                            >
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 font-mono font-medium text-zinc-900 dark:text-zinc-100 sm:pl-6">
                                    {{ item.student_number }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ item.username || '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ item.email }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <StatusBadge :status="item.membership_status" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <span
                                        v-if="item.is_verified"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Verified
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400"
                                    >
                                        <AlertCircle class="h-3.5 w-3.5" />
                                        Unverified
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="role in item.roles"
                                            :key="role"
                                            class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20 dark:bg-purple-950/40 dark:text-purple-300 dark:ring-purple-500/30"
                                        >
                                            {{ role }}
                                        </span>
                                        <span v-if="item.roles.length === 0" class="text-xs text-zinc-400">
                                            Member
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ item.created_at }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium sm:pr-6">
                                    <Link
                                        :href="`/members/${item.member_id}`"
                                        class="text-emerald-600 hover:text-emerald-500 dark:text-emerald-400"
                                    >
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 px-4 py-3 sm:px-6">
                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                        Showing <span class="font-medium">{{ members.from || 0 }}</span> to
                        <span class="font-medium">{{ members.to || 0 }}</span> of
                        <span class="font-medium">{{ members.total }}</span> members
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="members.prev_page_url"
                            :href="members.prev_page_url"
                            class="inline-flex items-center gap-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition"
                        >
                            <ChevronLeft class="h-3.5 w-3.5" />
                            Previous
                        </Link>
                        <Link
                            v-if="members.next_page_url"
                            :href="members.next_page_url"
                            class="inline-flex items-center gap-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition"
                        >
                            Next
                            <ChevronRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>


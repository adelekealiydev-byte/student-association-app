<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Search, Download, ChevronLeft, ChevronRight, Hash, Mail, AlertTriangle } from '@lucide/vue';

interface UnpaidMember {
    member_id: number;
    student_number: string;
    email: string;
    username: string;
    membership_status: string;
}

interface PaginatedData {
    data: UnpaidMember[];
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
    selectedTerm: string;
    filters: {
        search?: string;
        academic_term?: string;
    };
    duesAmount: string;
}>();

const search = ref(props.filters.search || '');
const term = ref(props.selectedTerm);

const handleFilter = () => {
    router.get(
        '/finance/unpaid',
        {
            search: search.value || undefined,
            academic_term: term.value,
        },
        { preserveState: true, replace: true }
    );
};

const clearFilters = () => {
    search.value = '';
    router.get('/finance/unpaid', { academic_term: term.value });
};

const exportUrl = `/finance/unpaid/export?academic_term=${encodeURIComponent(props.selectedTerm)}`;

const tabs = [
    { label: 'Overview', href: '/finance', active: false },
    { label: 'Unpaid Dues', href: '/finance/unpaid', active: true },
    { label: 'All Payments', href: '/finance/payments', active: false },
];
</script>

<template>
    <Head title="Unpaid Dues" />

    <PortalLayout title="Unpaid Dues" section="finance" :tabs="tabs">
        <div class="space-y-6">
            <!-- Header and Export -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Unpaid Dues</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Active, verified members with unpaid association dues for {{ selectedTerm }}.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-500 transition"
                    >
                        <Download class="h-4 w-4" />
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Notice card -->
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 text-sm text-amber-900 dark:text-amber-200 flex items-start gap-3">
                <AlertTriangle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-medium">Annual Dues Requirement (${{ duesAmount }})</p>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        Digital ID card eligibility requires completed annual dues payment. Members below will show as ineligible until dues are cleared.
                    </p>
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
                        <input
                            v-model="term"
                            type="text"
                            placeholder="Academic Term"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 font-medium focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @keydown.enter="handleFilter"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                        @click="handleFilter"
                    >
                        Filter
                    </button>
                    <button
                        v-if="filters.search"
                        type="button"
                        class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 underline"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Unpaid Members Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Student Number</th>
                                <th scope="col" class="px-3 py-3.5">Member Name</th>
                                <th scope="col" class="px-3 py-3.5">Email</th>
                                <th scope="col" class="px-3 py-3.5">Dues Amount</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="members.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-zinc-500">
                                    All active verified members have completed their dues for {{ selectedTerm }}!
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
                                <td class="whitespace-nowrap px-3 py-4 font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ item.username }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ item.email }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 font-semibold text-rose-600 dark:text-rose-400">
                                    ${{ duesAmount }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <StatusBadge :status="item.membership_status" />
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium sm:pr-6">
                                    <Link
                                        :href="`/members/${item.member_id}`"
                                        class="text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 text-xs"
                                    >
                                        View Member
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
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


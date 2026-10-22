<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Search, ChevronLeft, ChevronRight, Hash, Receipt } from '@lucide/vue';

interface MemberSummary {
    member_id: number;
    student_number: string;
    email: string;
    username: string | null;
}

interface PaymentItem {
    payment_id: number;
    payment_type: string;
    status: string;
    amount: string;
    academic_term: string;
    provider_reference: string;
    created_at: string;
    member: MemberSummary | null;
}

interface PaginatedData {
    data: PaymentItem[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    payments: PaginatedData;
    filters: {
        search?: string;
        status?: string;
        payment_type?: string;
        academic_term?: string;
    };
    statusOptions: string[];
    typeOptions: string[];
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const paymentType = ref(props.filters.payment_type || '');
const academicTerm = ref(props.filters.academic_term || '');

const handleFilter = () => {
    router.get(
        '/finance/payments',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_type: paymentType.value || undefined,
            academic_term: academicTerm.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    paymentType.value = '';
    academicTerm.value = '';
    router.get('/finance/payments');
};

const tabs = [
    { label: 'Overview', href: '/finance', active: false },
    { label: 'Unpaid Dues', href: '/finance/unpaid', active: false },
    { label: 'All Payments', href: '/finance/payments', active: true },
];
</script>

<template>
    <Head title="All Payments" />

    <PortalLayout title="Payments" section="finance" :tabs="tabs">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Transaction History</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Audit log of all member transactions, gateway references, and status events.
                </p>
            </div>

            <!-- Filters Bar -->
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="relative lg:col-span-2">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Reference, student number, or email..."
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 pl-10 pr-4 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @keydown.enter="handleFilter"
                        />
                    </div>
                    <div>
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
                    <div>
                        <select
                            v-model="paymentType"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @change="handleFilter"
                        >
                            <option value="">All Categories</option>
                            <option v-for="opt in typeOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                            @click="handleFilter"
                        >
                            Filter
                        </button>
                        <button
                            v-if="filters.search || filters.status || filters.payment_type || filters.academic_term"
                            type="button"
                            class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 underline shrink-0"
                            @click="clearFilters"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Reference</th>
                                <th scope="col" class="px-3 py-3.5">Member</th>
                                <th scope="col" class="px-3 py-3.5">Category</th>
                                <th scope="col" class="px-3 py-3.5">Term</th>
                                <th scope="col" class="px-3 py-3.5">Amount</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-right sm:pr-6">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="payments.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-zinc-500">
                                    No payment records match your filters.
                                </td>
                            </tr>
                            <tr
                                v-for="item in payments.data"
                                :key="item.payment_id"
                                class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition"
                            >
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 font-mono text-xs font-medium sm:pl-6 text-zinc-900 dark:text-zinc-100">
                                    {{ item.provider_reference }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <div v-if="item.member">
                                        <Link
                                            :href="`/members/${item.member.member_id}`"
                                            class="font-mono text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400"
                                        >
                                            {{ item.member.student_number }}
                                        </Link>
                                        <div class="text-[11px] text-zinc-500">
                                            {{ item.member.username || item.member.email }}
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-zinc-400">N/A</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ item.payment_type }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ item.academic_term || '—' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                    ${{ item.amount }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <StatusBadge :status="item.status" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-xs text-zinc-500 sm:pr-6">
                                    {{ item.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 px-4 py-3 sm:px-6">
                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                        Showing <span class="font-medium">{{ payments.from || 0 }}</span> to
                        <span class="font-medium">{{ payments.to || 0 }}</span> of
                        <span class="font-medium">{{ payments.total }}</span> transactions
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="payments.prev_page_url"
                            :href="payments.prev_page_url"
                            class="inline-flex items-center gap-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition"
                        >
                            <ChevronLeft class="h-3.5 w-3.5" />
                            Previous
                        </Link>
                        <Link
                            v-if="payments.next_page_url"
                            :href="payments.next_page_url"
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


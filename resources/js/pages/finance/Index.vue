<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { DollarSign, CreditCard, Users, ArrowUpRight, ChevronRight, FileSpreadsheet } from '@lucide/vue';

interface TypeBreakdown {
    type: string;
    count: number;
    total: string;
    raw_total: number;
}

interface SummaryData {
    total_revenue: string;
    total_completed: number;
    dues_paid_count: number;
    unpaid_dues_count: number;
    type_breakdown: TypeBreakdown[];
}

interface RecentPayment {
    payment_id: number;
    student_number: string | null;
    email: string | null;
    payment_type: string;
    status: string;
    amount: string;
    academic_term: string;
    provider_reference: string;
    created_at: string;
}

const props = defineProps<{
    selectedTerm: string;
    availableTerms: string[];
    summary: SummaryData;
    recentPayments: RecentPayment[];
}>();

const term = ref(props.selectedTerm);

const handleTermChange = () => {
    router.get('/finance', { academic_term: term.value }, { preserveState: true });
};

const tabs = [
    { label: 'Overview', href: '/finance', active: true },
    { label: 'Unpaid Dues', href: '/finance/unpaid', active: false },
    { label: 'All Payments', href: '/finance/payments', active: false },
];
</script>

<template>
    <Head title="Finance Overview" />

    <PortalLayout title="Finance" section="finance" :tabs="tabs">
        <div class="space-y-6">
            <!-- Header and Term Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Financial Overview</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Income, dues collections, and payment history across academic terms.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-xs font-medium text-zinc-500">Academic Term:</label>
                    <select
                        v-model="term"
                        class="rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 font-medium focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        @change="handleTermChange"
                    >
                        <option v-for="t in availableTerms" :key="t" :value="t">
                            {{ t }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Total Revenue -->
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between text-xs font-medium text-zinc-500">
                        <span>Term Revenue</span>
                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                            <DollarSign class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                        ${{ summary.total_revenue }}
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">
                        {{ summary.total_completed }} completed transactions
                    </p>
                </div>

                <!-- Dues Paid -->
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between text-xs font-medium text-zinc-500">
                        <span>Dues Paid</span>
                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                        {{ summary.dues_paid_count }} members
                    </div>
                    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                        Good standing for {{ selectedTerm }}
                    </p>
                </div>

                <!-- Unpaid Dues -->
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between text-xs font-medium text-zinc-500">
                        <span>Unpaid Dues</span>
                        <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                            <CreditCard class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                        {{ summary.unpaid_dues_count }} members
                    </div>
                    <div class="mt-1">
                        <Link
                            :href="`/finance/unpaid?academic_term=${selectedTerm}`"
                            class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
                        >
                            View unpaid list
                            <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Revenue Breakdown by Payment Type -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    Income Breakdown by Category ({{ selectedTerm }})
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="cat in summary.type_breakdown"
                        :key="cat.type"
                        class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-950/40 p-4"
                    >
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wide">
                            {{ cat.type }}
                        </div>
                        <div class="mt-1 text-xl font-bold text-zinc-900 dark:text-zinc-50">
                            ${{ cat.total }}
                        </div>
                        <div class="text-xs text-zinc-400 mt-0.5">
                            {{ cat.count }} transactions
                        </div>
                    </div>
                    <div
                        v-if="summary.type_breakdown.length === 0"
                        class="col-span-full py-8 text-center text-sm text-zinc-500"
                    >
                        No payments recorded for academic term {{ selectedTerm }}.
                    </div>
                </div>
            </div>

            <!-- Recent Payments Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Recent Transactions</h2>
                        <p class="text-xs text-zinc-500">Latest completed payments across all terms.</p>
                    </div>
                    <Link
                        href="/finance/payments"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400"
                    >
                        View all payments
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Member</th>
                                <th scope="col" class="px-3 py-3.5">Type</th>
                                <th scope="col" class="px-3 py-3.5">Term</th>
                                <th scope="col" class="px-3 py-3.5">Amount</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Reference</th>
                                <th scope="col" class="px-3 py-3.5 text-right sm:pr-6">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="recentPayments.length === 0">
                                <td colspan="7" class="py-10 text-center text-zinc-500 text-sm">
                                    No recent transactions found.
                                </td>
                            </tr>
                            <tr v-for="pay in recentPayments" :key="pay.payment_id">
                                <td class="whitespace-nowrap py-3.5 pl-4 pr-3 sm:pl-6">
                                    <div class="font-mono font-medium text-xs text-zinc-900 dark:text-zinc-100">
                                        {{ pay.student_number || 'N/A' }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500">
                                        {{ pay.email }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ pay.payment_type }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ pay.academic_term || '—' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                    ${{ pay.amount }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5">
                                    <StatusBadge :status="pay.status" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 font-mono text-xs text-zinc-500">
                                    {{ pay.provider_reference }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-right text-xs text-zinc-500 sm:pr-6">
                                    {{ pay.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>


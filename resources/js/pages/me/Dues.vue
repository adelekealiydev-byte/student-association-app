<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    CreditCard,
    CheckCircle2,
    Calendar,
    DollarSign,
    Loader2,
    ShieldCheck,
    Receipt,
} from '@lucide/vue';

const props = defineProps<{
    hasCompletedDues: boolean;
    currentTerm: string;
    duesAmount: string;
    payments: Array<{
        payment_id: number;
        payment_type: string;
        status: string;
        amount: string;
        academic_term: string;
        provider_reference: string | null;
        created_at: string;
    }>;
}>();

const terms = ['2026-2027', '2025-2026'];
const selectedTerm = ref(props.currentTerm);

const form = useForm({
    academic_term: props.currentTerm,
});

const handlePayDues = () => {
    form.academic_term = selectedTerm.value;
    form.post('/payments/dues');
};

const tabs = [
    { label: 'Profile', href: '/me/membership', active: false },
    { label: 'Dues & Payments', href: '/me/dues', active: true },
    { label: 'Digital ID Card', href: '/me/card', active: false },
];
</script>

<template>
    <PortalLayout title="Dues & Payments" section="membership" :tabs="tabs">
        <template #subtitle>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Manage your annual membership dues and review past payment records.
            </p>
        </template>

        <Head title="Dues & Payments" />

        <div class="space-y-8 max-w-4xl">
            <!-- Current Term Dues Card (Section 7.4) -->
            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                                Annual Association Dues
                            </h2>
                            <StatusBadge :status="hasCompletedDues ? 'Completed' : 'Pending'" size="sm" />
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ hasCompletedDues
                                ? `Your dues for the ${currentTerm} academic year are fully paid.`
                                : `Paying activates your digital student ID card for ${currentTerm}.`
                            }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="text-xs text-zinc-400 font-semibold uppercase">Fee Amount</span>
                        <div class="text-3xl font-black text-zinc-900 dark:text-zinc-100">${{ duesAmount }}</div>
                    </div>
                </div>

                <!-- Payment Action or Paid Confirmation -->
                <div class="pt-6">
                    <div v-if="hasCompletedDues" class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50">
                        <CheckCircle2 class="h-6 w-6 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        <div>
                            <p class="font-bold text-sm">Membership In Good Standing</p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400">
                                Your ID card is currently unlocked and eligible for all events.
                            </p>
                        </div>
                    </div>

                    <form v-else @submit.prevent="handlePayDues" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="term-select" class="block text-xs font-bold uppercase text-zinc-600 dark:text-zinc-400 mb-1.5">
                                    Academic Term
                                </label>
                                <select
                                    id="term-select"
                                    v-model="selectedTerm"
                                    class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:ring-indigo-600/20"
                                >
                                    <option v-for="t in terms" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-2.5 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
                                >
                                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                                    <CreditCard v-else class="h-4 w-4" />
                                    <span>Pay Dues Now (${{ duesAmount }})</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            <ShieldCheck class="h-4 w-4 text-emerald-600" />
                            <span>Payment confirms instantly and grants immediate ID card eligibility.</span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment History Table (Section 8: Data table with status badge) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <Receipt class="h-5 w-5 text-zinc-500" />
                        <span>Payment History</span>
                    </h3>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
                    <div v-if="payments.length === 0" class="p-8 text-center text-sm text-zinc-500">
                        No payment records found.
                    </div>

                    <table v-else class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs uppercase font-semibold text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3">Type</th>
                                <th scope="col" class="px-3 py-3.5">Term</th>
                                <th scope="col" class="px-3 py-3.5">Amount</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Reference</th>
                                <th scope="col" class="py-3.5 pl-3 pr-6 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                            <tr v-for="p in payments" :key="p.payment_id" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                                <td class="py-4 pl-6 pr-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ p.payment_type }}
                                </td>
                                <td class="px-3 py-4 text-zinc-600 dark:text-zinc-400">
                                    {{ p.academic_term }}
                                </td>
                                <td class="px-3 py-4 font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                                    ${{ p.amount }}
                                </td>
                                <td class="px-3 py-4">
                                    <StatusBadge :status="p.status" size="sm" />
                                </td>
                                <td class="px-3 py-4 font-mono text-xs text-zinc-500 truncate max-w-[120px]">
                                    {{ p.provider_reference || '&mdash;' }}
                                </td>
                                <td class="py-4 pl-3 pr-6 text-right text-xs text-zinc-500">
                                    {{ p.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>


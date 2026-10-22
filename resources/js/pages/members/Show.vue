<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Auth } from '@/types';
import {
    ArrowLeft,
    User,
    Mail,
    Hash,
    Calendar,
    Shield,
    CheckCircle2,
    AlertCircle,
    Send,
    Ban,
    UserCheck,
    CreditCard,
    Receipt,
} from '@lucide/vue';

interface MemberDetail {
    member_id: number;
    email: string;
    student_number: string;
    username: string | null;
    membership_status: string;
    is_verified: boolean;
    roles: string[];
    created_at: string;
}

interface InvitationItem {
    invitation_id: number;
    status: string;
    expires_at: string;
    submitted_at: string | null;
    created_at: string;
}

interface PaymentItem {
    payment_id: number;
    payment_type: string;
    status: string;
    amount: string;
    academic_term: string;
    provider_reference: string;
    created_at: string;
}

interface CardItem {
    id_card_id: number;
    card_number: string;
    is_eligible: boolean;
}

const props = defineProps<{
    member: MemberDetail;
    invitations: InvitationItem[];
    payments: PaymentItem[];
    latestCard: CardItem | null;
}>();

const page = usePage<{ auth: Auth }>();
const isAdmin = page.props.auth?.user?.roles?.includes('Admin');

const activeTab = ref<'history' | 'invitations' | 'card'>('history');

const handleSuspend = () => {
    if (confirm(`Are you sure you want to suspend member ${props.member.student_number}?`)) {
        router.post(`/members/${props.member.member_id}/suspend`);
    }
};

const handleActivate = () => {
    if (confirm(`Are you sure you want to activate member ${props.member.student_number}?`)) {
        router.post(`/members/${props.member.member_id}/activate`);
    }
};

const handleResendInvite = () => {
    router.post(`/members/${props.member.member_id}/invitations`);
};
</script>

<template>
    <Head :title="`Member - ${member.student_number}`" />

    <PortalLayout title="Member Details" section="members">
        <div class="space-y-6">
            <!-- Back button -->
            <div>
                <Link
                    href="/members"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to Member Directory
                </Link>
            </div>

            <!-- Profile Summary Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 font-bold text-2xl font-mono">
                            {{ member.student_number.slice(0, 2) }}
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-50">
                                    {{ member.username || 'Unnamed Member' }}
                                </h1>
                                <StatusBadge :status="member.membership_status" />
                                <span
                                    v-if="member.is_verified"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Verified
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    Pending Verification
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="flex items-center gap-1 font-mono">
                                    <Hash class="h-3.5 w-3.5" />
                                    {{ member.student_number }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Mail class="h-3.5 w-3.5" />
                                    {{ member.email }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Calendar class="h-3.5 w-3.5" />
                                    Joined {{ member.created_at }}
                                </span>
                            </div>

                            <!-- Roles -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-xs text-zinc-400">Roles:</span>
                                <span
                                    v-for="role in member.roles"
                                    :key="role"
                                    class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20 dark:bg-purple-950/40 dark:text-purple-300"
                                >
                                    {{ role }}
                                </span>
                                <span v-if="member.roles.length === 0" class="text-xs text-zinc-400">
                                    Standard Member
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button
                            v-if="!member.is_verified"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-blue-500 transition"
                            @click="handleResendInvite"
                        >
                            <Send class="h-3.5 w-3.5" />
                            Re-send Invitation
                        </button>

                        <template v-if="isAdmin">
                            <button
                                v-if="member.membership_status === 'Active'"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/40 px-3.5 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 transition"
                                @click="handleSuspend"
                            >
                                <Ban class="h-3.5 w-3.5" />
                                Suspend Member
                            </button>
                            <button
                                v-else-if="member.membership_status === 'Suspended'"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-emerald-500 transition"
                                @click="handleActivate"
                            >
                                <UserCheck class="h-3.5 w-3.5" />
                                Activate Member
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium transition"
                    :class="activeTab === 'history' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                    @click="activeTab = 'history'"
                >
                    Payment History ({{ payments.length }})
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium transition"
                    :class="activeTab === 'invitations' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                    @click="activeTab = 'invitations'"
                >
                    Invitations ({{ invitations.length }})
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium transition"
                    :class="activeTab === 'card' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                    @click="activeTab = 'card'"
                >
                    ID Card Info
                </button>
            </div>

            <!-- TAB 1: Payment History -->
            <div v-if="activeTab === 'history'" class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Payments & Dues</h3>
                    <p class="text-xs text-zinc-500">History of all transactions recorded for this member.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Type</th>
                                <th scope="col" class="px-3 py-3.5">Term</th>
                                <th scope="col" class="px-3 py-3.5">Amount</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Reference</th>
                                <th scope="col" class="px-3 py-3.5 text-right sm:pr-6">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="payments.length === 0">
                                <td colspan="6" class="py-10 text-center text-zinc-500 text-sm">
                                    No payments recorded for this member.
                                </td>
                            </tr>
                            <tr v-for="pay in payments" :key="pay.payment_id">
                                <td class="whitespace-nowrap py-3.5 pl-4 pr-3 font-medium sm:pl-6 text-zinc-900 dark:text-zinc-100">
                                    {{ pay.payment_type }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ pay.academic_term || '—' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                    ${{ pay.amount }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="pay.status === 'Completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'"
                                    >
                                        {{ pay.status }}
                                    </span>
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

            <!-- TAB 2: Invitations History -->
            <div v-if="activeTab === 'invitations'" class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Registration Invitations</h3>
                    <p class="text-xs text-zinc-500">Audit log of invitation tokens issued for this member.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">ID</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Issued At</th>
                                <th scope="col" class="px-3 py-3.5">Expires At</th>
                                <th scope="col" class="px-3 py-3.5 text-right sm:pr-6">Submitted At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="invitations.length === 0">
                                <td colspan="5" class="py-10 text-center text-zinc-500 text-sm">
                                    No invitation records found.
                                </td>
                            </tr>
                            <tr v-for="inv in invitations" :key="inv.invitation_id">
                                <td class="whitespace-nowrap py-3.5 pl-4 pr-3 font-mono text-xs sm:pl-6">
                                    #{{ inv.invitation_id }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5">
                                    <StatusBadge :status="inv.status" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ inv.created_at }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ inv.expires_at }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-right text-xs text-zinc-500 sm:pr-6">
                                    {{ inv.submitted_at || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: ID Card Details -->
            <div v-if="activeTab === 'card'" class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div v-if="latestCard" class="max-w-md space-y-4">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        Digital ID Card Status
                    </h3>
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 p-4 space-y-2 bg-zinc-50/50 dark:bg-zinc-950/40">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Card Number:</span>
                            <span class="font-mono font-medium">{{ latestCard.card_number }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Eligibility:</span>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="latestCard.is_eligible ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'"
                            >
                                {{ latestCard.is_eligible ? 'Eligible' : 'Ineligible' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-zinc-500 text-sm">
                    No digital ID card has been generated for this member yet.
                </div>
            </div>
        </div>
    </PortalLayout>
</template>


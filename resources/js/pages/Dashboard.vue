<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    UserCheck,
    CreditCard,
    QrCode,
    Shield,
    Users,
    Mail,
    DollarSign,
    ArrowRight,
    CheckCircle2,
    Lock,
    ExternalLink,
} from '@lucide/vue';

const props = defineProps<{
    member: {
        member_id: number;
        email: string;
        student_number: string;
        username: string | null;
        membership_status: string;
        is_verified: boolean;
        roles: string[];
    };
    currentTerm: string;
    duesPaid: boolean;
    duesAmount: string;
    card: {
        id_card_id: number;
        card_number: string;
        qr_code_data: string;
        is_eligible: boolean;
    } | null;
    widgets: string[];
    officerStats?: {
        total_members: number;
        verified_members: number;
        open_invitations: number;
        term_income: number;
    } | null;
}>();

const handlePayDues = () => {
    router.post('/payments/dues', {
        academic_term: props.currentTerm,
    });
};
</script>

<template>
    <PortalLayout title="Dashboard" section="home">
        <template #subtitle>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Welcome back, {{ member.username || member.email }}.
            </p>
        </template>

        <Head title="Dashboard" />

        <div class="space-y-8">
            <!-- Officer Summary Widgets (Section 7.1: Shown first for officers) -->
            <section v-if="officerStats" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        Officer Overview
                    </h2>
                    <span class="text-xs text-zinc-500">Academic Term: {{ currentTerm }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Members -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-1">
                        <div class="flex items-center justify-between text-zinc-500">
                            <span class="text-xs font-semibold uppercase">Total Members</span>
                            <Users class="h-4 w-4" />
                        </div>
                        <div class="text-2xl font-bold tracking-tight">{{ officerStats.total_members }}</div>
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                            {{ officerStats.verified_members }} verified
                        </div>
                    </div>

                    <!-- Open Invitations -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-1">
                        <div class="flex items-center justify-between text-zinc-500">
                            <span class="text-xs font-semibold uppercase">Open Invitations</span>
                            <Mail class="h-4 w-4" />
                        </div>
                        <div class="text-2xl font-bold tracking-tight">{{ officerStats.open_invitations }}</div>
                        <Link href="/invitations" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            View invitations &rarr;
                        </Link>
                    </div>

                    <!-- Term Income (Treasurer Widget) -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-1">
                        <div class="flex items-center justify-between text-zinc-500">
                            <span class="text-xs font-semibold uppercase">Term Dues Income</span>
                            <DollarSign class="h-4 w-4" />
                        </div>
                        <div class="text-2xl font-bold tracking-tight">
                            ${{ Number(officerStats.term_income).toFixed(2) }}
                        </div>
                        <Link href="/finance" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            Financial summary &rarr;
                        </Link>
                    </div>

                    <!-- Officer Quick Action -->
                    <div class="rounded-2xl border border-dashed border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/40 dark:bg-indigo-950/20 p-5 flex flex-col justify-between">
                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                            Officer Actions
                        </div>
                        <div class="pt-2 flex flex-col gap-1.5">
                            <Link
                                v-if="member.roles.includes('Admin') || member.roles.includes('Secretary')"
                                href="/members/invite"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
                            >
                                + Invite Member
                            </Link>
                            <Link
                                v-if="member.roles.includes('Treasurer') || member.roles.includes('Admin')"
                                href="/finance/unpaid"
                                class="inline-flex items-center justify-center rounded-lg border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition"
                            >
                                Review Unpaid Dues
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Member Personal Cards Section (Section 7.1) -->
            <section class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-500">
                    Your Membership
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 1. Membership Status Card -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Status</span>
                                <StatusBadge :status="member.membership_status" />
                            </div>
                            <div class="space-y-1">
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ member.username ? `@${member.username}` : member.email }}
                                </div>
                                <div class="text-xs text-zinc-500">Student ID: {{ member.student_number }}</div>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs" :class="member.is_verified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                <CheckCircle2 v-if="member.is_verified" class="h-4 w-4" />
                                <Lock v-else class="h-4 w-4" />
                                <span>{{ member.is_verified ? 'Identity verified' : 'Pending verification' }}</span>
                            </div>
                        </div>

                        <Link
                            href="/me/membership"
                            class="inline-flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline pt-2 border-t border-zinc-100 dark:border-zinc-800"
                        >
                            <span>View profile details</span>
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <!-- 2. Dues Card -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Term Dues</span>
                                <StatusBadge :status="duesPaid ? 'Completed' : 'Pending'" size="sm" />
                            </div>

                            <div class="space-y-1">
                                <div class="text-2xl font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                                    ${{ duesAmount }}
                                </div>
                                <div class="text-xs text-zinc-500">Term: {{ currentTerm }}</div>
                            </div>

                            <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ duesPaid ? 'Dues paid for this term. Your ID card is active.' : `Paying activates your ID card for ${currentTerm}.` }}
                            </p>
                        </div>

                        <div>
                            <button
                                v-if="!duesPaid"
                                @click="handlePayDues"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-2.5 px-3 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition"
                            >
                                <CreditCard class="h-4 w-4" />
                                <span>Pay Dues (${{ duesAmount }})</span>
                            </button>
                            <Link
                                v-else
                                href="/me/dues"
                                class="inline-flex items-center justify-between w-full text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline pt-2 border-t border-zinc-100 dark:border-zinc-800"
                            >
                                <span>Payment history</span>
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- 3. Digital ID Card Preview -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Digital ID Card</span>
                                <StatusBadge :status="card?.is_eligible ? 'Eligible' : 'Locked'" size="sm" />
                            </div>

                            <div v-if="card" class="space-y-1">
                                <div class="font-mono text-sm font-bold text-zinc-900 dark:text-zinc-100 tracking-wider">
                                    {{ card.card_number }}
                                </div>
                                <p class="text-xs text-zinc-500">
                                    {{ card.is_eligible ? 'Ready for door check-in' : 'Dues payment required to activate' }}
                                </p>
                            </div>
                            <div v-else class="space-y-1">
                                <div class="text-sm font-semibold text-zinc-500">No card issued yet</div>
                                <p class="text-xs text-zinc-400">Pay term dues to generate your digital card.</p>
                            </div>
                        </div>

                        <Link
                            href="/me/card"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 py-2.5 px-3 text-xs font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition"
                        >
                            <QrCode class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                            <span>{{ card?.is_eligible ? 'Show QR Code' : 'View ID Card' }}</span>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </PortalLayout>
</template>

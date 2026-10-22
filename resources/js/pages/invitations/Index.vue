<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Search, UserPlus, Send, XCircle, RotateCcw, ChevronLeft, ChevronRight, Hash, Mail } from '@lucide/vue';

interface MemberSummary {
    member_id: number;
    email: string;
    student_number: string;
    username: string | null;
    is_verified: boolean;
}

interface InvitationItem {
    invitation_id: number;
    status: string;
    expires_at: string;
    is_expired: boolean;
    submitted_at: string | null;
    created_at: string;
    member: MemberSummary | null;
}

interface PaginatedData {
    data: InvitationItem[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    invitations: PaginatedData;
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
        '/invitations',
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
    router.get('/invitations');
};

const handleRevoke = (id: number) => {
    if (confirm('Are you sure you want to revoke this invitation token?')) {
        router.post(`/invitations/${id}/revoke`);
    }
};

const handleResend = (id: number) => {
    router.post(`/invitations/${id}/resend`);
};

const tabs = [
    { label: 'All Members', href: '/members', active: false },
    { label: 'Invitations', href: '/invitations', active: true },
];
</script>

<template>
    <Head title="Invitations" />

    <PortalLayout title="Invitations" section="members" :tabs="tabs">
        <div class="space-y-6">
            <!-- Header and Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Member Invitations</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Monitor, revoke, and resend secure registration tokens issued to prospective members.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/members/invite"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-500 transition"
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
                            placeholder="Search by student number or email..."
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

            <!-- Invitations Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Invitee</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="px-3 py-3.5">Issued At</th>
                                <th scope="col" class="px-3 py-3.5">Expires At</th>
                                <th scope="col" class="px-3 py-3.5">Submitted At</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="invitations.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    No invitations found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="item in invitations.data"
                                :key="item.invitation_id"
                                class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition"
                            >
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                                    <div v-if="item.member" class="space-y-0.5">
                                        <div class="font-mono font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                            <Hash class="h-3.5 w-3.5 text-zinc-400" />
                                            {{ item.member.student_number }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                                            <Mail class="h-3.5 w-3.5 text-zinc-400" />
                                            {{ item.member.email }}
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-zinc-400 italic">No member linked</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <StatusBadge :status="item.status" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ item.created_at }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs">
                                    <span :class="item.is_expired ? 'text-rose-600 dark:text-rose-400 font-medium' : 'text-zinc-600 dark:text-zinc-400'">
                                        {{ item.expires_at }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ item.submitted_at || '—' }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium sm:pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="(item.status === 'Pending' || item.status === 'Sent') && !item.is_expired"
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-medium transition"
                                            @click="handleRevoke(item.invitation_id)"
                                        >
                                            <XCircle class="h-3.5 w-3.5" />
                                            Revoke
                                        </button>

                                        <button
                                            v-if="(item.status === 'Expired' || item.status === 'Revoked') && item.member && !item.member.is_verified"
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition"
                                            @click="handleResend(item.invitation_id)"
                                        >
                                            <RotateCcw class="h-3.5 w-3.5" />
                                            Resend
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 px-4 py-3 sm:px-6">
                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                        Showing <span class="font-medium">{{ invitations.from || 0 }}</span> to
                        <span class="font-medium">{{ invitations.to || 0 }}</span> of
                        <span class="font-medium">{{ invitations.total }}</span> invitations
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="invitations.prev_page_url"
                            :href="invitations.prev_page_url"
                            class="inline-flex items-center gap-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition"
                        >
                            <ChevronLeft class="h-3.5 w-3.5" />
                            Previous
                        </Link>
                        <Link
                            v-if="invitations.next_page_url"
                            :href="invitations.next_page_url"
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


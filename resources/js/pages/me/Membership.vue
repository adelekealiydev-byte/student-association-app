<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    UserCheck,
    Mail,
    CheckCircle2,
    Calendar,
    Save,
    Loader2,
    Shield,
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
        joined_at: string;
    };
    invitation: {
        status: string;
        submitted_at: string | null;
    } | null;
}>();

const form = useForm({
    username: props.member.username || '',
});

const submit = () => {
    form.patch('/me/membership', {
        preserveScroll: true,
    });
};

const tabs = [
    { label: 'Profile', href: '/me/membership', active: true },
    { label: 'Dues & Payments', href: '/me/dues', active: false },
    { label: 'Digital ID Card', href: '/me/card', active: false },
];
</script>

<template>
    <PortalLayout title="Membership Profile" section="membership" :tabs="tabs">
        <template #subtitle>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                View your official membership record and update your account details.
            </p>
        </template>

        <Head title="Membership Profile" />

        <div class="space-y-8 max-w-4xl">
            <!-- Top Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Status -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm space-y-2">
                    <span class="text-xs font-semibold uppercase text-zinc-400">Account Status</span>
                    <div class="pt-1">
                        <StatusBadge :status="member.membership_status" />
                    </div>
                    <p class="text-xs text-zinc-500 pt-1">
                        Active members hold voting rights and event privileges.
                    </p>
                </div>

                <!-- Verification Gate -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm space-y-2">
                    <span class="text-xs font-semibold uppercase text-zinc-400">Verification Gate</span>
                    <div class="flex items-center gap-2 pt-1 font-bold text-sm" :class="member.is_verified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600'">
                        <CheckCircle2 class="h-5 w-5" />
                        <span>{{ member.is_verified ? 'Verified via Form' : 'Pending Verification' }}</span>
                    </div>
                    <p class="text-xs text-zinc-500 pt-1">
                        Form submitted on {{ invitation?.submitted_at || member.joined_at }}.
                    </p>
                </div>

                <!-- Roles -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm space-y-2">
                    <span class="text-xs font-semibold uppercase text-zinc-400">Assigned Roles</span>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span
                            v-for="r in member.roles"
                            :key="r"
                            class="rounded-md bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-600/20"
                        >
                            {{ r }}
                        </span>
                        <span v-if="member.roles.length === 0" class="text-xs text-zinc-400">
                            General Member
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Details and Edit Form -->
            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm space-y-6">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        Personal Information
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Your university credentials verified during registration.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Student Number (Read-only as per section 9) -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500">
                                Student ID Number
                            </label>
                            <input
                                :value="member.student_number"
                                readonly
                                disabled
                                class="block w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-800/50 px-3.5 py-2.5 text-sm font-mono text-zinc-500 cursor-not-allowed"
                            />
                            <p class="text-xs text-zinc-400">Assigned by the university; cannot be modified.</p>
                        </div>

                        <!-- University Email (Read-only verified identifier) -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500">
                                University Email Address
                            </label>
                            <input
                                :value="member.email"
                                readonly
                                disabled
                                class="block w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-800/50 px-3.5 py-2.5 text-sm text-zinc-500 cursor-not-allowed"
                            />
                            <p class="text-xs text-zinc-400">All notifications and ID card notices are sent here.</p>
                        </div>

                        <!-- Username (Editable) -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label for="username" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Association Username
                            </label>
                            <input
                                id="username"
                                v-model="form.username"
                                type="text"
                                required
                                class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:ring-indigo-600/20"
                            />
                            <p v-if="form.errors.username" class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                                {{ form.errors.username }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing || form.username === member.username"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 py-2.5 px-5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
                        >
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <Save v-else class="h-4 w-4" />
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>


<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import { Mail, Hash, UserPlus, ArrowLeft, Info, CheckCircle2 } from '@lucide/vue';

const form = useForm({
    student_number: '',
    email: '',
});

const submit = () => {
    form.post('/members/invite', {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Invite Member" />

    <PortalLayout title="Invite Member" section="members">
        <div class="mx-auto max-w-2xl space-y-6">
            <!-- Back navigation -->
            <div>
                <Link
                    href="/members"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Back to Member Directory
                </Link>
            </div>

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Invite New Member</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                    Send a verified registration invitation to a prospective association member.
                </p>
            </div>

            <!-- Guidance Info Card -->
            <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-900/40 dark:bg-blue-950/20 text-sm text-blue-900 dark:text-blue-200 flex items-start gap-3">
                <Info class="h-5 w-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-medium">Invitation Workflow</p>
                    <p class="text-xs text-blue-800 dark:text-blue-300">
                        Entering the member's student number and email will create a preliminary member record with status <code class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 py-0.5 rounded">PendingInvite</code>.
                        The student receives a secure invitation link valid for 7 days to complete registration. Once submitted, their account automatically switches to <code class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 py-0.5 rounded">Active</code> and verified.
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="student_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Student Number
                        </label>
                        <div class="relative mt-1">
                            <Hash class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                            <input
                                id="student_number"
                                v-model="form.student_number"
                                type="text"
                                required
                                placeholder="e.g. S12345678"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                        <p v-if="form.errors.student_number" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.student_number }}
                        </p>
                        <p v-else class="mt-1 text-xs text-zinc-500">
                            Must be unique across all active and pending members.
                        </p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Email Address
                        </label>
                        <div class="relative mt-1">
                            <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="student@university.edu"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.email }}
                        </p>
                        <p v-else class="mt-1 text-xs text-zinc-500">
                            The student's institution email where the registration link will be sent.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <Link
                            href="/members"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:opacity-50 transition"
                        >
                            <UserPlus class="h-4 w-4" />
                            <span>{{ form.processing ? 'Sending...' : 'Send Invitation' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>


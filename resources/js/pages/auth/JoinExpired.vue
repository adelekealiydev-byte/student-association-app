<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthPortalLayout from '@/layouts/AuthPortalLayout.vue';
import { Clock, HelpCircle, ArrowLeft } from '@lucide/vue';

defineProps<{
    reason?: 'expired' | 'used';
}>();
</script>

<template>
    <AuthPortalLayout title="Invitation Problem" description="Unable to complete verification with this link">
        <Head title="Invitation Expired" />

        <div class="space-y-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                <Clock class="h-8 w-8" />
            </div>

            <div class="space-y-2">
                <p v-if="reason === 'used'" class="text-sm text-zinc-600 dark:text-zinc-300">
                    This invitation has already been submitted. If you already set your password, you can sign in below.
                </p>
                <p v-else class="text-sm text-zinc-600 dark:text-zinc-300">
                    This invitation link has expired. For your security, invitations are valid for 7 days from when they are issued.
                </p>
            </div>

            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800/60 p-4 text-xs text-left text-zinc-600 dark:text-zinc-400 space-y-2 border border-zinc-200 dark:border-zinc-700/60">
                <div class="flex items-center gap-1.5 font-semibold text-zinc-800 dark:text-zinc-200">
                    <HelpCircle class="h-4 w-4 text-indigo-500" />
                    <span>How to get a new invitation</span>
                </div>
                <p>
                    Please contact an Association Secretary or Administrator to have your invitation link re-sent to your university email.
                </p>
                <p>
                    Office Support:
                    <a href="mailto:support@studentassociation.org" class="text-indigo-600 dark:text-indigo-400 font-medium underline">
                        support@studentassociation.org
                    </a>
                </p>
            </div>

            <div class="space-y-3 pt-2">
                <Link
                    href="/login"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition"
                >
                    Sign in to existing account
                </Link>

                <Link
                    href="/"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 py-2.5 px-4 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Return to home</span>
                </Link>
            </div>
        </div>
    </AuthPortalLayout>
</template>


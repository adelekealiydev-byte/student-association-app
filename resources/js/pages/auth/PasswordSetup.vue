<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthPortalLayout from '@/layouts/AuthPortalLayout.vue';
import { Lock, CheckCircle2, AlertCircle, Loader2, ArrowRight } from '@lucide/vue';

const props = defineProps<{
    member: {
        member_id: number;
        username: string | null;
        email: string;
    };
    alreadySet?: boolean;
}>();

const form = useForm({
    password: '',
    password_confirmation: '',
});

// Password Strength Meter (Section 7.2)
const strengthScore = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let score = 0;
    if (p.length >= 12) score += 1;
    if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score += 1;
    if (/[0-9]/.test(p)) score += 1;
    if (/[^A-Za-z0-9]/.test(p)) score += 1;
    return score;
});

const strengthLabel = computed(() => {
    switch (strengthScore.value) {
        case 1:
            return { text: 'Weak', color: 'bg-rose-500' };
        case 2:
            return { text: 'Fair', color: 'bg-amber-500' };
        case 3:
            return { text: 'Good', color: 'bg-blue-500' };
        case 4:
            return { text: 'Strong', color: 'bg-emerald-500' };
        default:
            return { text: 'Too short (min 12 characters)', color: 'bg-zinc-300 dark:bg-zinc-700' };
    }
});

const submit = () => {
    form.post(`/password/setup/${props.member.member_id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthPortalLayout
        :title="alreadySet ? 'Password Already Set' : 'Set Your Password'"
        :description="alreadySet ? 'Your account is ready for sign in' : `Choose a secure password for @${member.username || member.email}`"
    >
        <Head title="Set Password" />

        <!-- Already set state (Section 7.2) -->
        <div v-if="alreadySet" class="space-y-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <div class="space-y-2">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    You have already set your password for this account. You can proceed directly to sign in.
                </p>
            </div>

            <Link
                href="/login"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition"
            >
                <span>Sign in now</span>
                <ArrowRight class="h-4 w-4" />
            </Link>
        </div>

        <!-- Password Setup Form -->
        <form v-else @submit.prevent="submit" class="space-y-5" novalidate>
            <!-- Error Banner -->
            <div
                v-if="form.hasErrors"
                class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 flex items-start gap-3"
            >
                <AlertCircle class="h-5 w-5 shrink-0 text-rose-600 mt-0.5" />
                <div>
                    <p class="font-medium">Please review the password requirements:</p>
                    <ul class="list-disc list-inside mt-1 text-xs space-y-0.5">
                        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5 text-left">
                <label for="password" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    New Password
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-400">
                        <Lock class="h-4 w-4" aria-hidden="true" />
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        required
                        autofocus
                        class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 pl-10 pr-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 transition disabled:opacity-50"
                        :disabled="form.processing"
                    />
                </div>

                <!-- Password Strength Meter (Section 7.2: min 12 characters, strength indicator) -->
                <div class="pt-2 space-y-1">
                    <div class="flex items-center justify-between text-xs text-zinc-500">
                        <span>Strength:</span>
                        <span class="font-semibold">{{ strengthLabel.text }}</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 h-1.5">
                        <div
                            v-for="step in 4"
                            :key="step"
                            class="rounded-full transition"
                            :class="step <= strengthScore ? strengthLabel.color : 'bg-zinc-200 dark:bg-zinc-750'"
                        />
                    </div>
                    <p class="text-xs text-zinc-500">Minimum 12 characters required</p>
                </div>
            </div>

            <!-- Password Confirmation Field -->
            <div class="space-y-1.5 text-left">
                <label for="password_confirmation" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    Confirm Password
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-400">
                        <Lock class="h-4 w-4" aria-hidden="true" />
                    </div>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 pl-10 pr-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 transition disabled:opacity-50"
                        :disabled="form.processing"
                    />
                </div>
            </div>

            <!-- Submit button -->
            <button
                type="submit"
                :disabled="form.processing || form.password.length < 12 || form.password !== form.password_confirmation"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <span>{{ form.processing ? 'Saving password...' : 'Save password & activate' }}</span>
            </button>
        </form>
    </AuthPortalLayout>
</template>


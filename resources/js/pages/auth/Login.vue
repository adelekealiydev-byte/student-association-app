<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthPortalLayout from '@/layouts/AuthPortalLayout.vue';
import { AlertCircle, Lock, User, Loader2 } from '@lucide/vue';

const form = useForm({
    login: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.password = '';
        },
    });
};
</script>

<template>
    <AuthPortalLayout title="Sign in" description="Access your Student Association membership portal">
        <Head title="Sign in" />

        <!-- Top Error Banner for blocked states (Section 7.3) -->
        <div
            v-if="form.errors.login"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 flex items-start gap-3"
            role="alert"
        >
            <AlertCircle class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" />
            <div class="space-y-1">
                <p class="font-medium">{{ form.errors.login }}</p>
                <p v-if="form.errors.login.includes('not active')" class="text-xs">
                    Need help finding your invitation?
                    <Link href="/help" class="underline font-semibold hover:text-rose-900 dark:hover:text-rose-200">
                        Visit help & support
                    </Link>
                </p>
                <p v-if="form.errors.login.includes('suspended')" class="text-xs">
                    Please contact the association office at
                    <a href="mailto:office@studentassociation.org" class="underline font-semibold">
                        office@studentassociation.org
                    </a>
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5" novalidate>
            <!-- Email / Username field -->
            <div class="space-y-1.5 text-left">
                <label for="login" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    Email address or Username
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-400">
                        <User class="h-4 w-4" aria-hidden="true" />
                    </div>
                    <input
                        id="login"
                        v-model="form.login"
                        type="text"
                        autocomplete="username"
                        required
                        autofocus
                        :aria-invalid="!!form.errors.login"
                        :aria-describedby="form.errors.login ? 'login-error' : undefined"
                        class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 pl-10 pr-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 transition disabled:opacity-50"
                        :disabled="form.processing"
                    />
                </div>
            </div>

            <!-- Password field -->
            <div class="space-y-1.5 text-left">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                        Password
                    </label>
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-400">
                        <Lock class="h-4 w-4" aria-hidden="true" />
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        :aria-invalid="!!form.errors.password"
                        :aria-describedby="form.errors.password ? 'password-error' : undefined"
                        class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 pl-10 pr-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 transition disabled:opacity-50"
                        :disabled="form.processing"
                    />
                </div>
                <p v-if="form.errors.password" id="password-error" class="text-xs text-rose-600 dark:text-rose-400 font-medium pt-1">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Submit Button (Section 9: Disabled on submit, shows progress) -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <span>{{ form.processing ? 'Signing in...' : 'Sign in' }}</span>
            </button>
        </form>

        <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800 pt-6 text-center text-xs text-zinc-500">
            <span>Received an invitation? </span>
            <Link href="/help" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                Read how to activate your account
            </Link>
        </div>
    </AuthPortalLayout>
</template>

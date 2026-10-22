<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import { Lock, Save, Loader2, CheckCircle2, AlertCircle } from '@lucide/vue';

defineProps<{
    member: {
        email: string;
        username: string | null;
    };
}>();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    form.put('/me/settings/password', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <PortalLayout title="Account Settings">
        <template #subtitle>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Manage your credentials and security preferences.
            </p>
        </template>

        <Head title="Account Settings" />

        <div class="max-w-2xl space-y-8">
            <!-- Flash Message Banner -->
            <div
                v-if="$page.props.flash?.status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center gap-3"
            >
                <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <p class="font-medium">{{ $page.props.flash.status }}</p>
            </div>

            <!-- Change Password Card (Section 6: Settings) -->
            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm space-y-6">
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <Lock class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <span>Update Password</span>
                    </h2>
                    <p class="text-xs text-zinc-500 mt-1">
                        Ensure your account uses a strong password of at least 12 characters.
                    </p>
                </div>

                <form @submit.prevent="submitPassword" class="space-y-5" novalidate>
                    <!-- Current Password -->
                    <div class="space-y-1.5">
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            Current Password
                        </label>
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:ring-indigo-600/20"
                        />
                        <p v-if="form.errors.current_password" class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            New Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:ring-indigo-600/20"
                        />
                        <p class="text-xs text-zinc-400">Minimum 12 characters</p>
                        <p v-if="form.errors.password" class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            Confirm New Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-600 focus:ring-indigo-600/20"
                        />
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing || form.password.length < 12 || form.password !== form.password_confirmation"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 py-2.5 px-5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-50"
                        >
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <Save v-else class="h-4 w-4" />
                            <span>Save Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>


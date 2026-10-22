<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">
        <!-- Public Header (Section 5.1) -->
        <header class="border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95 sticky top-0 z-30">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <Link href="/" class="flex items-center gap-2.5 font-bold text-lg tracking-tight">
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        SA
                    </div>
                    <span>Student Association</span>
                </Link>

                <nav class="flex items-center gap-6" aria-label="Public Navigation">
                    <a href="/#how-it-works" class="hidden sm:inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
                        How it works
                    </a>
                    <Link href="/help" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
                        Help & Support
                    </Link>

                    <Link
                        v-if="!isAuthenticated"
                        href="/login"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                    >
                        Sign in
                    </Link>
                    <Link
                        v-else
                        href="/dashboard"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 transition"
                    >
                        Go to Dashboard
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Main Content (Max 1,080px as per Section 8) -->
        <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
            <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; {{ new Date().getFullYear() }} Student Association Portal. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <Link href="/help" class="hover:underline">Contact Office</Link>
                    <Link href="/login" class="hover:underline">Sign In</Link>
                </div>
            </div>
        </footer>
    </div>
</template>


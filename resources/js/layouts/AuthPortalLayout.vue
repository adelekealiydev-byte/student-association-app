<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex flex-col justify-between bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased px-4 py-8">
        <!-- Top header brand -->
        <header class="w-full max-w-sm mx-auto text-center pt-4">
            <Link href="/" class="inline-flex items-center gap-2 font-bold text-lg hover:opacity-90">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    SA
                </div>
                <span class="text-xl">Student Association</span>
            </Link>
        </header>

        <!-- Centred Card Container (Max 420px as per Section 8) -->
        <main class="w-full max-w-[420px] mx-auto my-auto bg-white dark:bg-zinc-900 rounded-2xl p-6 sm:p-8 shadow-sm border border-zinc-200 dark:border-zinc-800">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ title }}
                </h1>
                <p v-if="description" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ description }}
                </p>
            </div>

            <slot />
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-sm mx-auto text-center text-xs text-zinc-500 dark:text-zinc-500 pb-4">
            <Link href="/help" class="hover:underline">Need assistance or have an issue? Contact the office</Link>
        </footer>
    </div>
</template>


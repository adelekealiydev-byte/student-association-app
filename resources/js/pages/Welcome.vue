<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Mail, CheckCircle2, CreditCard, QrCode, ArrowRight, ShieldCheck } from '@lucide/vue';

const steps = [
    {
        number: '01',
        title: 'Receive Invitation',
        description: 'An association officer invites you by student number and university email. A single-use secure token is generated.',
        icon: Mail,
    },
    {
        number: '02',
        title: 'Complete Google Form',
        description: 'Open the link with your token to confirm membership information and select your association username.',
        icon: CheckCircle2,
    },
    {
        number: '03',
        title: 'Set Password & Pay Dues',
        description: 'Receive your signed activation email to create your password, then complete term dues securely.',
        icon: CreditCard,
    },
    {
        number: '04',
        title: 'Digital ID Card',
        description: 'Your ID card activates instantly upon dues payment. Show your cryptographically signed QR code at doors and events.',
        icon: QrCode,
    },
];
</script>

<template>
    <PublicLayout>
        <Head title="Welcome - Student Association Portal" />

        <!-- Hero Section -->
        <div class="text-center py-8 sm:py-16 space-y-6">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 dark:bg-indigo-950/60 px-3.5 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-600/20">
                <ShieldCheck class="h-4 w-4" />
                <span>Official Association Membership Portal</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 max-w-3xl mx-auto leading-tight">
                Your Student Membership, Dues, and ID Card in One Place
            </h1>

            <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                Join the Student Association, manage your dues, access officer services, and present your verified digital ID card with QR scanning at all association events.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <Link
                    href="/login"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                >
                    <span>Sign in to your account</span>
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <a
                    href="#how-it-works"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-6 py-3.5 text-base font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition"
                >
                    Learn how it works
                </a>
            </div>
        </div>

        <!-- How It Works Section (Section 5.1 / 6) -->
        <section id="how-it-works" class="py-12 border-t border-zinc-200 dark:border-zinc-800 scroll-mt-20">
            <div class="text-center max-w-xl mx-auto mb-12">
                <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Membership Process</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">How to join in four simple steps</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <div
                    v-for="step in steps"
                    :key="step.number"
                    class="relative rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 flex flex-col justify-between shadow-sm hover:border-indigo-300 dark:hover:border-indigo-800 transition"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ step.number }}</span>
                            <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                <component :is="step.icon" class="h-6 w-6" />
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-50">{{ step.title }}</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Digital ID Highlight -->
        <section class="mt-12 rounded-3xl bg-gradient-to-br from-indigo-900 to-zinc-900 text-white p-8 sm:p-12 shadow-xl flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-4 max-w-xl">
                <span class="rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-300 border border-indigo-500/30">
                    Instant Event Access
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Cryptographic Digital Student ID</h2>
                <p class="text-zinc-300 text-sm sm:text-base leading-relaxed">
                    Once your membership dues are completed, your digital ID card unlocks automatically with a secure, tamper-proof QR code. Officers scan the code at events for quick check-in.
                </p>
            </div>

            <div class="w-full sm:w-auto shrink-0">
                <Link
                    href="/login"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-white text-zinc-950 px-6 py-3.5 text-sm font-bold shadow-lg hover:bg-zinc-100 transition"
                >
                    View Your ID Card
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>

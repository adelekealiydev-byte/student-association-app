<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Mail, HelpCircle, FileText, CreditCard, Shield } from '@lucide/vue';

const faqs = [
    {
        q: 'How do I join the Student Association?',
        a: 'Membership is by university invitation. An officer will send an invitation link containing a secure token to your official student email address. You will verify your student number and choose a username via our Google Form.',
    },
    {
        q: 'Why does my ID card show "Locked" or "Ineligible"?',
        a: 'Under association rules, an ID card is only eligible when your membership is verified and your annual dues for the current academic term are completed. Paying your dues immediately activates your digital ID card.',
    },
    {
        q: 'My invitation link expired or says already used. What should I do?',
        a: 'Invitations remain valid for 7 days. If your link has expired, request a new invitation from an association Secretary or Admin. Re-issuing the invitation automatically revokes the previous link.',
    },
    {
        q: 'How does event check-in work?',
        a: 'Open the portal on your phone and tap "ID Card" at the bottom of the screen. Present the high-contrast QR code to event officers. The QR code contains a cryptographic signature to verify your active membership instantly without paper lists.',
    },
];
</script>

<template>
    <PublicLayout>
        <Head title="Help & Support - Student Association" />

        <div class="max-w-3xl mx-auto space-y-10">
            <!-- Header -->
            <div class="text-center space-y-3">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                    Help & Contact
                </h1>
                <p class="text-base text-zinc-600 dark:text-zinc-400">
                    Frequently asked questions about membership verification, dues, and digital ID cards.
                </p>
            </div>

            <!-- FAQs -->
            <div class="space-y-4">
                <div
                    v-for="faq in faqs"
                    :key="faq.q"
                    class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm"
                >
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-start gap-2.5">
                        <HelpCircle class="h-5 w-5 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" />
                        <span>{{ faq.q }}</span>
                    </h2>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 pl-7 leading-relaxed">
                        {{ faq.a }}
                    </p>
                </div>
            </div>

            <!-- Contact Box -->
            <div class="rounded-2xl border border-indigo-100 dark:border-indigo-950/60 bg-indigo-50/60 dark:bg-indigo-950/20 p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="space-y-1 text-center sm:text-left">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Still have questions?</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Our student officer team is available Monday through Friday during semester hours.
                    </p>
                </div>

                <a
                    href="mailto:support@studentassociation.org"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition shrink-0"
                >
                    <Mail class="h-4 w-4" />
                    <span>Contact Association Office</span>
                </a>
            </div>
        </div>
    </PublicLayout>
</template>


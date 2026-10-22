<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import QRCode from 'qrcode';
import {
    QrCode as QrIcon,
    Maximize2,
    Minimize2,
    Lock,
    AlertCircle,
    CheckCircle2,
    CreditCard,
    Shield,
} from '@lucide/vue';

const props = defineProps<{
    card: {
        id_card_id: number;
        card_number: string;
        qr_code_data: string;
        is_eligible: boolean;
        created_at: string;
    } | null;
    member: {
        student_number: string;
        username: string | null;
        email: string;
        membership_status: string;
        is_verified: boolean;
    };
    academicTerm: string;
    lockReason: 'unverified' | 'suspended' | 'unpaid_dues' | 'pending_activation' | null;
}>();

const qrDataUrl = ref<string>('');
const isFullscreen = ref(false);

const generateQr = async () => {
    if (props.card?.qr_code_data) {
        try {
            qrDataUrl.value = await QRCode.toDataURL(props.card.qr_code_data, {
                width: 280,
                margin: 1,
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
                errorCorrectionLevel: 'H',
            });
        } catch (err) {
            console.error('Failed to generate QR code', err);
        }
    }
};

onMounted(generateQr);
watch(() => props.card?.qr_code_data, generateQr);

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

const tabs = [
    { label: 'Profile', href: '/me/membership', active: false },
    { label: 'Dues & Payments', href: '/me/dues', active: false },
    { label: 'Digital ID Card', href: '/me/card', active: true },
];
</script>

<template>
    <PortalLayout title="Digital ID Card" section="membership" :tabs="tabs">
        <template #subtitle>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Official Student Association digital identification and door pass.
            </p>
        </template>

        <Head title="Digital ID Card" />

        <div class="max-w-xl mx-auto space-y-6">
            <!-- 1. LOCKED CARD STATE (Section 7.5 rule 3) -->
            <div
                v-if="!card || !card.is_eligible"
                class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-zinc-100/80 dark:bg-zinc-900/80 p-6 sm:p-8 text-center space-y-6 filter grayscale contrast-75"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-200 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                    <Lock class="h-8 w-8" />
                </div>

                <div class="space-y-2">
                    <div class="inline-flex">
                        <StatusBadge status="Locked" size="sm" />
                    </div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        Digital ID Card Inactive
                    </h2>
                    <p v-if="lockReason === 'unpaid_dues'" class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mx-auto">
                        Your ID card is locked because term dues for {{ academicTerm }} have not been completed.
                    </p>
                    <p v-else-if="lockReason === 'unverified'" class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mx-auto">
                        Your ID card is locked pending membership verification.
                    </p>
                    <p v-else-if="lockReason === 'suspended'" class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mx-auto">
                        Your membership account is currently suspended. Please contact the office.
                    </p>
                    <p v-else class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mx-auto">
                        Complete your dues payment to issue and unlock your card.
                    </p>
                </div>

                <!-- Fix Action Button (Section 7.5 rule 3) -->
                <div class="pt-2">
                    <Link
                        v-if="lockReason === 'unpaid_dues'"
                        href="/me/dues"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition"
                    >
                        <CreditCard class="h-4 w-4" />
                        <span>Pay Dues to Activate Card</span>
                    </Link>
                    <Link
                        v-else
                        href="/help"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-6 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 transition"
                    >
                        <span>Contact Association Office</span>
                    </Link>
                </div>
            </div>

            <!-- 2. ELIGIBLE / ACTIVE CARD (Section 7.5 rule 2) -->
            <div
                v-else
                class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-gradient-to-b from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950 p-6 sm:p-8 shadow-md space-y-6"
            >
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow">
                            SA
                        </div>
                        <div>
                            <div class="font-black text-sm uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                                Student Association
                            </div>
                            <div class="text-xs text-zinc-500">Official Membership Card</div>
                        </div>
                    </div>

                    <StatusBadge status="Eligible" />
                </div>

                <!-- QR Code Box (Section 12: min 160px square, full contrast) -->
                <div class="flex flex-col items-center justify-center py-2 space-y-3">
                    <div class="bg-white p-3 rounded-2xl shadow-inner border border-zinc-200 inline-block">
                        <img
                            v-if="qrDataUrl"
                            :src="qrDataUrl"
                            :alt="`Digital Student ID Card QR Code for card number ${card.card_number}`"
                            class="w-48 h-48 sm:w-56 sm:h-56 object-contain"
                        />
                        <div v-else class="w-48 h-48 flex items-center justify-center text-xs text-zinc-400">
                            Loading QR code...
                        </div>
                    </div>

                    <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center">
                        Present this QR code for scanning at association event entrances.
                    </p>
                </div>

                <!-- Member Metadata -->
                <div class="grid grid-cols-2 gap-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 p-4 border border-zinc-100 dark:border-zinc-800/80 text-xs">
                    <div>
                        <span class="text-zinc-400 font-semibold uppercase tracking-wider">Member Name</span>
                        <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate">
                            {{ member.username ? `@${member.username}` : member.email }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-400 font-semibold uppercase tracking-wider">Student Number</span>
                        <div class="font-bold text-sm font-mono text-zinc-900 dark:text-zinc-100">
                            {{ member.student_number }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-400 font-semibold uppercase tracking-wider">Card Number</span>
                        <div class="font-bold text-sm font-mono text-indigo-600 dark:text-indigo-400">
                            {{ card.card_number }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-400 font-semibold uppercase tracking-wider">Academic Term</span>
                        <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                            {{ academicTerm }}
                        </div>
                    </div>
                </div>

                <!-- Fullscreen Scanner Action (Section 7.5) -->
                <button
                    @click="toggleFullscreen"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-3 text-sm font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition shadow-sm"
                >
                    <Maximize2 class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                    <span>Open Full Screen for Scanning</span>
                </button>
            </div>
        </div>

        <!-- Fullscreen Scanner Modal (Section 12) -->
        <div
            v-if="isFullscreen && card?.is_eligible"
            class="fixed inset-0 z-50 bg-black flex flex-col items-center justify-center p-6 text-white"
            role="dialog"
            aria-modal="true"
        >
            <button
                @click="toggleFullscreen"
                class="absolute top-6 right-6 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white transition"
                aria-label="Close full screen"
            >
                <Minimize2 class="h-6 w-6" />
            </button>

            <div class="text-center space-y-6 max-w-xs">
                <div class="text-xs uppercase font-bold tracking-widest text-indigo-400">
                    Student Association Door Pass
                </div>

                <div class="bg-white p-4 rounded-3xl inline-block shadow-2xl">
                    <img
                        :src="qrDataUrl"
                        :alt="`Full screen QR code for card ${card.card_number}`"
                        class="w-64 h-64 sm:w-72 sm:h-72 object-contain"
                    />
                </div>

                <div class="space-y-1">
                    <div class="text-xl font-bold font-mono tracking-wider">{{ card.card_number }}</div>
                    <div class="text-sm text-zinc-400">{{ member.student_number }} &bull; {{ academicTerm }}</div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>


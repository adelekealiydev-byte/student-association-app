<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        status: string;
        size?: 'sm' | 'md';
    }>(),
    {
        size: 'md',
    }
);

type BadgeVariant = 'success' | 'warning' | 'danger' | 'neutral';

const variant = computed<BadgeVariant>(() => {
    const s = props.status.toLowerCase();
    if (['active', 'completed', 'eligible'].includes(s)) return 'success';
    if (['pending', 'sent', 'pending invite'].includes(s)) return 'warning';
    if (['suspended', 'failed', 'revoked'].includes(s)) return 'danger';
    return 'neutral';
});

const variantClasses = computed(() => {
    switch (variant.value) {
        case 'success':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400 dark:ring-emerald-500/30';
        case 'warning':
            return 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400 dark:ring-amber-500/30';
        case 'danger':
            return 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-400 dark:ring-rose-500/30';
        default:
            return 'bg-zinc-50 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full font-medium ring-1 ring-inset"
        :class="[
            size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs sm:text-sm',
            variantClasses,
        ]"
    >
        <span
            class="h-1.5 w-1.5 rounded-full"
            :class="{
                'bg-emerald-500': variant === 'success',
                'bg-amber-500': variant === 'warning',
                'bg-rose-500': variant === 'danger',
                'bg-zinc-400': variant === 'neutral',
            }"
            aria-hidden="true"
        />
        <span>{{ status }}</span>
    </span>
</template>


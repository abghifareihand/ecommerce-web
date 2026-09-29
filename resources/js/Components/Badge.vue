<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'success', // success, secondary, warning, danger, info
    },
    size: {
        type: String,
        default: 'md', // sm, md
    },
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'success':
            return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
        case 'secondary':
        case 'neutral':
            return 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/20';
        case 'warning':
            return 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20';
        case 'danger':
            return 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20';
        case 'info':
            return 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20';
        default:
            return 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/20';
    }
});

const sizeClasses = computed(() => {
    return props.size === 'sm'
        ? 'px-2 py-0.5 text-xs'
        : 'px-2.5 py-1 text-xs';
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-x-1.5 font-medium rounded-full transition-colors',
            variantClasses,
            sizeClasses
        ]"
    >
        <slot />
    </span>
</template>

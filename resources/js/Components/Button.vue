<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary', // primary, secondary, danger, outline, ghost
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-emerald-600 text-white shadow-xs hover:bg-emerald-700 active:bg-emerald-800 focus-visible:outline-emerald-600';
        case 'secondary':
            return 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 active:bg-slate-100 focus-visible:outline-slate-600 shadow-xs';
        case 'danger':
            return 'bg-rose-600 text-white shadow-xs hover:bg-rose-700 active:bg-rose-800 focus-visible:outline-rose-600';
        case 'outline':
            return 'border border-emerald-600 text-emerald-700 hover:bg-emerald-50 active:bg-emerald-100 focus-visible:outline-emerald-600';
        case 'ghost':
            return 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-200';
        default:
            return 'bg-emerald-600 text-white hover:bg-emerald-700';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs font-medium rounded-lg gap-1.5';
        case 'lg':
            return 'px-5 py-3 text-base font-semibold rounded-xl gap-2.5';
        default:
            return 'px-4 py-2 text-sm font-semibold rounded-lg gap-2';
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-sans transition-all duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 select-none cursor-pointer',
            variantClasses,
            sizeClasses,
            (disabled || loading) ? 'opacity-60 cursor-not-allowed pointer-events-none' : ''
        ]"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-0.5 h-4 w-4 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <slot />
    </button>
</template>

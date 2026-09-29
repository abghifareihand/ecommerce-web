<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'success', // success, danger, warning, info
    },
    title: {
        type: String,
        default: '',
    },
    dismissible: {
        type: Boolean,
        default: true,
    },
});

const dismissed = ref(false);

const variantConfig = computed(() => {
    switch (props.variant) {
        case 'success':
            return {
                container: 'bg-emerald-50 border-emerald-200 text-emerald-800',
                iconColor: 'text-emerald-500',
                buttonHover: 'hover:bg-emerald-100 text-emerald-600',
            };
        case 'danger':
            return {
                container: 'bg-rose-50 border-rose-200 text-rose-800',
                iconColor: 'text-rose-500',
                buttonHover: 'hover:bg-rose-100 text-rose-600',
            };
        case 'warning':
            return {
                container: 'bg-amber-50 border-amber-200 text-amber-800',
                iconColor: 'text-amber-500',
                buttonHover: 'hover:bg-amber-100 text-amber-600',
            };
        case 'info':
            return {
                container: 'bg-sky-50 border-sky-200 text-sky-800',
                iconColor: 'text-sky-500',
                buttonHover: 'hover:bg-sky-100 text-sky-600',
            };
        default:
            return {
                container: 'bg-emerald-50 border-emerald-200 text-emerald-800',
                iconColor: 'text-emerald-500',
                buttonHover: 'hover:bg-emerald-100 text-emerald-600',
            };
    }
});
</script>

<template>
    <div
        v-if="!dismissed"
        :class="[
            'flex items-start gap-3 p-4 rounded-xl border transition-all duration-200',
            variantConfig.container
        ]"
        role="alert"
    >
        <!-- Icon -->
        <div class="shrink-0 mt-0.5" :class="variantConfig.iconColor">
            <svg v-if="variant === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="variant === 'danger'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="variant === 'warning'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 text-sm leading-relaxed">
            <h4 v-if="title" class="font-semibold mb-0.5">{{ title }}</h4>
            <div>
                <slot />
            </div>
        </div>

        <!-- Dismiss button -->
        <button
            v-if="dismissible"
            type="button"
            @click="dismissed = true"
            :class="[
                'shrink-0 p-1 -mr-1 -mt-1 rounded-lg transition-colors cursor-pointer',
                variantConfig.buttonHover
            ]"
            aria-label="Dismiss alert"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

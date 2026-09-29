<script setup>
defineProps({
    title: {
        type: String,
        default: 'No items found',
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="text-center py-12 px-4 rounded-2xl bg-white border border-dashed border-slate-300">
        <!-- Icon -->
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-4">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>

        <h3 class="text-base font-bold text-slate-800 tracking-tight">
            {{ title }}
        </h3>
        
        <p v-if="description" class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
            {{ description }}
        </p>

        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>

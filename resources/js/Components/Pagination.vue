<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    align: {
        type: String,
        default: 'end', // 'end' | 'center' | 'start'
    },
});

const isPrevious = (label, index) => {
    return index === 0 || label.includes('Previous') || label.includes('laquo') || label.includes('«');
};

const isNext = (label, index) => {
    return index === props.links.length - 1 || label.includes('Next') || label.includes('raquo') || label.includes('»');
};
</script>

<template>
    <div
        v-if="links && links.length > 3"
        :class="[
            'flex flex-wrap items-center gap-1.5 py-2',
            align === 'center' ? 'justify-center' : (align === 'start' ? 'justify-start' : 'justify-end')
        ]"
    >
        <template v-for="(link, index) in links" :key="index">
            <!-- Disabled link (e.g. current page is 1, previous is disabled) -->
            <span
                v-if="!link.url"
                class="h-9 min-w-9 px-2.5 flex items-center justify-center text-sm rounded-lg text-slate-300 select-none bg-slate-50 border border-slate-200 cursor-not-allowed"
                :aria-label="isPrevious(link.label, index) ? 'Halaman sebelumnya' : (isNext(link.label, index) ? 'Halaman berikutnya' : link.label)"
            >
                <svg v-if="isPrevious(link.label, index)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <svg v-else-if="isNext(link.label, index)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span v-else v-html="link.label" />
            </span>

            <!-- Active / Clickable link -->
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                :class="[
                    'h-9 min-w-9 px-2.5 flex items-center justify-center text-sm font-semibold rounded-lg transition-colors border select-none',
                    link.active
                        ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
                        : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-emerald-700 hover:border-slate-300'
                ]"
                :aria-label="isPrevious(link.label, index) ? 'Halaman sebelumnya' : (isNext(link.label, index) ? 'Halaman berikutnya' : link.label)"
            >
                <svg v-if="isPrevious(link.label, index)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <svg v-else-if="isNext(link.label, index)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span v-else v-html="link.label" />
            </Link>
        </template>
    </div>
</template>

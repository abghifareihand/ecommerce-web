<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-xl hover:border-emerald-200/80 transition-all duration-300">
        <!-- Image Container -->
        <div class="relative aspect-4/3 w-full overflow-hidden bg-slate-100">
            <img
                :src="product.image_url"
                :alt="product.name"
                class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-500 ease-out"
                loading="lazy"
            />
            
            <div class="absolute inset-0 bg-linear-to-t from-slate-900/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
        </div>

        <!-- Content -->
        <div class="flex flex-1 flex-col p-5">
            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-700 transition-colors line-clamp-1">
                <Link :href="`/products/${product.slug}`">
                    <span class="absolute inset-0 z-10" />
                    {{ product.name }}
                </Link>
            </h3>

            <p class="mt-2 text-sm text-slate-500 line-clamp-2 leading-relaxed flex-1">
                {{ product.description || 'No description provided.' }}
            </p>

            <div class="mt-4 pt-3 flex items-center justify-between border-t border-slate-100">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Price</span>
                    <p class="text-lg font-bold text-slate-900 tracking-tight">
                        {{ formatPrice(product.price) }}
                    </p>
                </div>

                <span class="inline-flex items-center text-xs font-semibold text-emerald-600 group-hover:translate-x-0.5 transition-transform">
                    View Details &rarr;
                </span>
            </div>
        </div>
    </div>
</template>

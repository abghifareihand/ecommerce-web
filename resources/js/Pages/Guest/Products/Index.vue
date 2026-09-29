<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '../../../Layouts/ShopLayout.vue';
import Pagination from '../../../Components/Pagination.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import CustomDropdown from '../../../Components/CustomDropdown.vue';
import { useCart } from '../../../Stores/cart';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', sort: 'latest' }),
    },
    totalCount: {
        type: Number,
        default: 0,
    },
    banners: {
        type: Array,
        default: () => [],
    },
});

const cart = useCart();
const searchInput = ref(props.filters.search || '');
const addedNotification = ref(null);
const currentBannerIndex = ref(0);
let bannerTimer = null;
const touchStartX = ref(0);
const touchEndX = ref(0);

const nextBanner = () => {
    if (props.banners && props.banners.length > 0) {
        currentBannerIndex.value = (currentBannerIndex.value + 1) % props.banners.length;
    }
};

const prevBanner = () => {
    if (props.banners && props.banners.length > 0) {
        currentBannerIndex.value = (currentBannerIndex.value - 1 + props.banners.length) % props.banners.length;
    }
};

const goToBanner = (index) => {
    currentBannerIndex.value = index;
    startTimer();
};

const startTimer = () => {
    if (props.banners && props.banners.length > 1) {
        clearInterval(bannerTimer);
        bannerTimer = setInterval(nextBanner, 4500);
    }
};

const pauseTimer = () => {
    if (bannerTimer) {
        clearInterval(bannerTimer);
        bannerTimer = null;
    }
};

const handleTouchStart = (e) => {
    touchStartX.value = e.changedTouches[0].screenX;
    pauseTimer();
};

const handleTouchEnd = (e) => {
    touchEndX.value = e.changedTouches[0].screenX;
    const diff = touchStartX.value - touchEndX.value;
    if (diff > 50) {
        nextBanner();
    } else if (diff < -50) {
        prevBanner();
    }
    startTimer();
};

onMounted(() => {
    startTimer();
});

onBeforeUnmount(() => {
    pauseTimer();
});

const sortOptions = [
    { value: 'latest', label: 'Terbaru' },
    { value: 'oldest', label: 'Terlama' },
    { value: 'price_high', label: 'Harga Tertinggi' },
    { value: 'price_low', label: 'Harga Terendah' },
];

const handleSearch = () => {
    router.get(
        '/products',
        {
            search: searchInput.value,
            sort: props.filters.sort,
        },
        { preserveState: true, replace: true }
    );
};

const handleSortChange = (newSort) => {
    router.get(
        '/products',
        {
            search: searchInput.value,
            sort: newSort,
        },
        { preserveState: true, replace: true }
    );
};

const clearSearch = () => {
    searchInput.value = '';
    router.get('/products');
};

const addToCart = (product) => {
    cart.addItem(product, 1);
    addedNotification.value = product.name;
    setTimeout(() => {
        if (addedNotification.value === product.name) {
            addedNotification.value = null;
        }
    }, 2500);
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};
</script>

<template>
    <Head :title="`Katalog Belanja Online - ${$page.props.store?.name || 'EcoStore'}`" />

    <ShopLayout>
        <!-- Added Item Toast -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="addedNotification"
                class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-emerald-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-emerald-700/60"
            >
                <div class="h-8 w-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-emerald-300 font-medium">Masuk ke Keranjang!</p>
                    <p class="text-sm font-bold truncate max-w-xs">{{ addedNotification }}</p>
                </div>
                <Link
                    href="/cart"
                    class="ml-3 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-xs font-bold rounded-lg transition-colors"
                >
                    Buka Cart &rarr;
                </Link>
            </div>
        </transition>

        <!-- Store Sub-Header / Breadcrumb & Filters -->
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                        <Link href="/" class="hover:text-emerald-700">Beranda</Link>
                        <span>/</span>
                        <span class="text-slate-900 font-semibold">Semua Produk Belanja</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Etalase Produk ({{ totalCount }} Produk)
                    </h1>
                </div>

                <!-- Sorting and active search tag -->
                <div class="flex items-center gap-3">
                    <div v-if="filters.search" class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold border border-emerald-200">
                        <span>Pencarian: "{{ filters.search }}"</span>
                        <button type="button" @click="clearSearch" class="text-emerald-600 hover:text-emerald-900 font-bold ml-1">✕</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500 shrink-0">Urutkan:</label>
                        <CustomDropdown
                            :full-width="false"
                            :model-value="filters.sort || 'latest'"
                            :options="sortOptions"
                            @change="handleSortChange"
                            wrapper-class="w-48"
                            button-class="text-xs font-bold text-slate-800 border-emerald-600 rounded-lg py-1.5"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Promo Banner (Only visible when NOT searching) -->
            <div
                v-if="!filters.search && banners && banners.length > 0"
                class="mb-8"
            >
                <div
                    class="relative w-full rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-21/9 sm:aspect-24/7 max-h-[340px] bg-slate-900 group"
                    @mouseenter="pauseTimer"
                    @mouseleave="startTimer"
                    @touchstart="handleTouchStart"
                    @touchend="handleTouchEnd"
                >
                    <!-- Horizontal Slide Track (Smooth sliding transition) -->
                    <div
                        class="flex w-full h-full transition-transform duration-700 ease-in-out"
                        :style="{ transform: `translateX(-${currentBannerIndex * 100}%)` }"
                    >
                        <div
                            v-for="banner in banners"
                            :key="banner.id"
                            class="relative w-full h-full shrink-0 overflow-hidden"
                        >
                            <img
                                :src="banner.image_url"
                                :alt="banner.title || 'Promo Banner'"
                                class="w-full h-full object-cover object-center select-none"
                            />
                            <div class="absolute inset-0 bg-linear-to-r from-black/80 via-black/35 to-transparent flex flex-col justify-center px-6 sm:px-12 text-white">
                                <span
                                    v-if="banner.subtitle"
                                    class="text-xs sm:text-sm font-bold text-emerald-400 uppercase tracking-wider mb-2"
                                >
                                    {{ banner.subtitle }}
                                </span>
                                <h2
                                    v-if="banner.title"
                                    class="text-xl sm:text-3xl font-extrabold max-w-xl leading-tight"
                                >
                                    {{ banner.title }}
                                </h2>
                                <a
                                    v-if="banner.link_url"
                                    :href="banner.link_url"
                                    class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition-all w-fit"
                                >
                                    <span>Lihat Penawaran</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Carousel Controls (if more than 1 banner) -->
                    <template v-if="banners.length > 1">
                        <button
                            type="button"
                            @click="prevBanner(); startTimer();"
                            class="absolute left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/75 text-white flex items-center justify-center transition-all cursor-pointer backdrop-blur-xs opacity-70 group-hover:opacity-100 hover:scale-105"
                            aria-label="Previous Banner"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="nextBanner(); startTimer();"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/75 text-white flex items-center justify-center transition-all cursor-pointer backdrop-blur-xs opacity-70 group-hover:opacity-100 hover:scale-105"
                            aria-label="Next Banner"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Dots Indicator -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 bg-black/30 backdrop-blur-xs px-2.5 py-1 rounded-full">
                            <button
                                v-for="(_, idx) in banners"
                                :key="idx"
                                type="button"
                                @click="goToBanner(idx)"
                                class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                :class="currentBannerIndex === idx ? 'w-6 bg-emerald-400' : 'w-2 bg-white/60 hover:bg-white'"
                                :aria-label="`Slide ${idx + 1}`"
                            />
                        </div>
                    </template>
                </div>
            </div>

            <!-- Product Grid -->
            <div v-if="products.data && products.data.length > 0">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div
                        v-for="product in products.data"
                        :key="product.id"
                        class="group flex flex-col bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-xl hover:border-emerald-300 transition-all duration-300 overflow-hidden"
                    >
                        <!-- Product Image Link -->
                        <Link :href="`/products/${product.slug}`" class="relative aspect-4/3 w-full overflow-hidden bg-slate-100 block">
                            <img
                                :src="product.image_url"
                                :alt="product.name"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                loading="lazy"
                            />
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/95 text-emerald-800 shadow-xs border border-slate-200">
                                    Ready Stock
                                </span>
                            </div>
                        </Link>

                        <!-- Product Content -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Rating indicator -->
                                <div class="flex items-center gap-1.5 text-xs text-amber-500 mb-1.5">
                                    <span>★ 4.9</span>
                                    <span class="text-slate-400">• Terjual 50+</span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-1">
                                    <Link :href="`/products/${product.slug}`">
                                        {{ product.name }}
                                    </Link>
                                </h3>

                                <p class="mt-1 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ product.description || 'Karya kriya lokal berkualitas.' }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <div class="flex items-baseline justify-between mb-3">
                                    <span class="text-xs text-slate-400 font-medium">Harga</span>
                                    <span class="text-lg font-extrabold text-emerald-700">
                                        {{ formatRupiah(product.price) }}
                                    </span>
                                </div>

                                <!-- Add to Cart Button -->
                                <button
                                    type="button"
                                    @click="addToCart(product)"
                                    class="w-full flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 transition-all shadow-xs cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    <Pagination :links="products.links" align="center" />
                </div>
            </div>

            <!-- Empty Search State -->
            <EmptyState
                v-else
                title="Produk tidak ditemukan"
                description="Tidak ada produk yang cocok dengan kata kunci pencarian Anda. Coba kata kunci lain atau reset filter."
            >
                <template #action>
                    <button
                        type="button"
                        @click="clearSearch"
                        class="px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-xl shadow-xs hover:bg-emerald-700"
                    >
                        Tampilkan Semua Produk
                    </button>
                </template>
            </EmptyState>
        </div>
    </ShopLayout>
</template>

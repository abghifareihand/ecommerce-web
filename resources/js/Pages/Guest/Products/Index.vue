<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
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
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '', category: '', sort: 'latest' }),
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
    touchStartX.value = e.touches[0].clientX;
    pauseTimer();
};

const handleTouchEnd = (e) => {
    touchEndX.value = e.changedTouches[0].clientX;
    const diff = touchStartX.value - touchEndX.value;
    if (diff > 40) {
        nextBanner();
    } else if (diff < -40) {
        prevBanner();
    }
    startTimer();
};

// Category Horizontal Scroll & Auto-Scroll
const categoryScrollContainer = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);
let isCategoryMouseDown = false;
let categoryStartX = 0;
let categoryScrollLeftStart = 0;

const checkCategoryScroll = () => {
    if (!categoryScrollContainer.value) return;
    const { scrollLeft, scrollWidth, clientWidth } = categoryScrollContainer.value;
    canScrollLeft.value = scrollLeft > 10;
    canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 10;
};

const scrollCategories = (amount) => {
    if (!categoryScrollContainer.value) return;
    categoryScrollContainer.value.scrollBy({
        left: amount,
        behavior: 'smooth',
    });
    setTimeout(checkCategoryScroll, 350);
};

const scrollToActiveCategory = () => {
    nextTick(() => {
        if (!categoryScrollContainer.value) return;
        const activeEl = categoryScrollContainer.value.querySelector('[data-active-category="true"]');
        if (activeEl) {
            activeEl.scrollIntoView({
                behavior: 'smooth',
                inline: 'center',
                block: 'nearest',
            });
        }
        checkCategoryScroll();
    });
};

const onCategoryMouseDown = (e) => {
    if (!categoryScrollContainer.value) return;
    isCategoryMouseDown = true;
    categoryStartX = e.pageX - categoryScrollContainer.value.offsetLeft;
    categoryScrollLeftStart = categoryScrollContainer.value.scrollLeft;
};

const onCategoryMouseMove = (e) => {
    if (!isCategoryMouseDown || !categoryScrollContainer.value) return;
    e.preventDefault();
    const x = e.pageX - categoryScrollContainer.value.offsetLeft;
    const walk = (x - categoryStartX) * 1.5;
    categoryScrollContainer.value.scrollLeft = categoryScrollLeftStart - walk;
    checkCategoryScroll();
};

const onCategoryMouseUp = () => {
    isCategoryMouseDown = false;
};

onMounted(() => {
    startTimer();
    scrollToActiveCategory();
    window.addEventListener('resize', checkCategoryScroll);
    document.addEventListener('mouseup', onCategoryMouseUp);
});

onBeforeUnmount(() => {
    pauseTimer();
    window.removeEventListener('resize', checkCategoryScroll);
    document.removeEventListener('mouseup', onCategoryMouseUp);
});

watch(
    () => props.filters.category,
    () => {
        scrollToActiveCategory();
    }
);

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
            category: props.filters.category || '',
            sort: props.filters.sort,
        },
        { preserveState: true, replace: true }
    );
};

const handleCategoryChange = (slug, event) => {
    if (event?.currentTarget) {
        event.currentTarget.scrollIntoView({
            behavior: 'smooth',
            inline: 'center',
            block: 'nearest',
        });
    }
    router.get(
        '/products',
        {
            search: searchInput.value,
            category: slug || '',
            sort: props.filters.sort,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const handleSortChange = (newSort) => {
    router.get(
        '/products',
        {
            search: searchInput.value,
            category: props.filters.category || '',
            sort: newSort,
        },
        { preserveState: true, replace: true }
    );
};

const clearSearch = () => {
    searchInput.value = '';
    router.get('/products', {
        category: props.filters.category || '',
        sort: props.filters.sort,
    });
};

const clearAllFilters = () => {
    searchInput.value = '';
    router.get('/products');
};

const addToCart = (product) => {
    if (product.stock <= 0) return;
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
            enter-from-class="translate-y-4 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="addedNotification"
                class="fixed bottom-4 sm:bottom-6 left-4 right-4 z-50 flex items-center justify-between gap-3 bg-emerald-950/95 backdrop-blur-md text-white px-4 py-3 sm:px-5 sm:py-3.5 rounded-2xl shadow-2xl border border-emerald-600/40 max-w-md mx-auto"
            >
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="h-8 w-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] sm:text-xs text-emerald-300 font-medium">Masuk ke Keranjang!</p>
                        <p class="text-xs sm:text-sm font-bold truncate">{{ addedNotification }}</p>
                    </div>
                </div>
                <Link
                    href="/cart"
                    class="shrink-0 whitespace-nowrap px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-xs font-bold rounded-xl transition-colors shadow-xs"
                >
                    Buka Cart &rarr;
                </Link>
            </div>
        </transition>

        <!-- Store Sub-Header / Breadcrumb & Filters -->
        <div class="bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                        <Link href="/" class="hover:text-emerald-700">Beranda</Link>
                        <span>/</span>
                        <span class="text-slate-900 font-semibold">Semua Produk Belanja</span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Etalase Produk ({{ totalCount }} Produk)
                    </h1>
                </div>

                <!-- Sorting and active search tag -->
                <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                    <div v-if="filters.search" class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold border border-emerald-200 truncate">
                        <span class="truncate">"{{ filters.search }}"</span>
                        <button type="button" @click="clearSearch" class="text-emerald-600 hover:text-emerald-900 font-bold ml-1 cursor-pointer">✕</button>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        <label class="text-xs font-semibold text-slate-500 shrink-0">Urutkan:</label>
                        <CustomDropdown
                            :full-width="false"
                            :model-value="filters.sort || 'latest'"
                            :options="sortOptions"
                            @change="handleSortChange"
                            wrapper-class="w-40 sm:w-48"
                            button-class="text-xs font-bold text-slate-800 py-1.5"
                            :show-dot="false"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Promo Banner (Carousel) -->
            <div
                v-if="!filters.search && banners && banners.length > 0"
                class="mb-6 sm:mb-8"
            >
                <div
                    class="relative overflow-hidden rounded-2xl md:rounded-3xl shadow-md border border-slate-200/80 bg-slate-900 h-[190px] sm:h-[280px] md:h-[350px] lg:h-[390px] group"
                    @mouseenter="pauseTimer"
                    @mouseleave="startTimer"
                    @touchstart.passive="handleTouchStart"
                    @touchend.passive="handleTouchEnd"
                >
                    <!-- Slides Track with explicit width calculation (Prevents half-slide cut) -->
                    <div
                        class="flex h-full transition-transform duration-500 ease-in-out"
                        :style="{
                            width: `${(banners.length || 1) * 100}%`,
                            transform: `translateX(-${(currentBannerIndex * 100) / (banners.length || 1)}%)`
                        }"
                    >
                        <div
                            v-for="banner in banners"
                            :key="banner.id"
                            class="relative h-full shrink-0 flex items-center"
                            :style="{ width: `${100 / (banners.length || 1)}%` }"
                        >
                            <!-- Banner Image with dark overlay -->
                            <img
                                :src="banner.image_url"
                                :alt="banner.title || 'EcoStore Promo'"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="eager"
                            />
                            <!-- Mobile-friendly dimming overlay -->
                            <div class="absolute inset-0 bg-slate-950/70 sm:bg-gradient-to-r sm:from-slate-950/90 sm:via-slate-950/60 sm:to-transparent" />

                            <!-- Banner Text Content -->
                            <div class="relative z-10 p-4 sm:p-8 md:p-12 max-w-xl text-white flex flex-col justify-center h-full">
                                <h2 class="text-sm sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white leading-snug line-clamp-2">
                                    {{ banner.title || 'Penawaran Eksklusif Produk Ramah Lingkungan' }}
                                </h2>
                                <p v-if="banner.subtitle" class="mt-1 sm:mt-2 text-[11px] sm:text-sm text-slate-200 line-clamp-2 leading-relaxed max-w-md">
                                    {{ banner.subtitle }}
                                </p>
                                <div v-if="banner.link_url" class="mt-2 sm:mt-5">
                                    <a
                                        :href="banner.link_url"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl font-bold text-[11px] sm:text-sm bg-white text-slate-950 hover:bg-emerald-500 hover:text-white transition-all shadow-md active:scale-95"
                                    >
                                        Jelajahi Sekarang &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Left / Right Controls (hidden on mobile, visible on tablet/desktop) -->
                    <template v-if="banners.length > 1">
                        <button
                            type="button"
                            @click="prevBanner(); startTimer();"
                            class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/80 text-white items-center justify-center transition-all cursor-pointer backdrop-blur-xs opacity-75 group-hover:opacity-100 hover:scale-105 z-10"
                            aria-label="Previous Banner"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="nextBanner(); startTimer();"
                            class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/80 text-white items-center justify-center transition-all cursor-pointer backdrop-blur-xs opacity-75 group-hover:opacity-100 hover:scale-105 z-10"
                            aria-label="Next Banner"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Dots Indicator -->
                        <div class="absolute bottom-2.5 sm:bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-full z-10">
                            <button
                                v-for="(_, idx) in banners"
                                :key="idx"
                                type="button"
                                @click="goToBanner(idx)"
                                class="h-1.5 sm:h-2 rounded-full transition-all duration-300 cursor-pointer"
                                :class="currentBannerIndex === idx ? 'w-5 sm:w-6 bg-emerald-400' : 'w-1.5 sm:w-2 bg-white/60 hover:bg-white'"
                                :aria-label="`Slide ${idx + 1}`"
                            />
                        </div>
                    </template>
                </div>
            </div>

            <!-- Category Pills Bar with Smooth Auto-Scroll & Arrow Controls -->
            <div v-if="categories && categories.length > 0" class="relative mb-6 sm:mb-8 group">
                <!-- Left Scroll Arrow Button (Desktop) -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-x-0"
                    leave-to-class="opacity-0 -translate-x-2"
                >
                    <div
                        v-if="canScrollLeft"
                        class="hidden md:flex absolute left-0 top-0 bottom-2 z-10 items-center pl-0.5 pr-8 bg-gradient-to-r from-white via-white/90 to-transparent pointer-events-none"
                    >
                        <button
                            type="button"
                            @click="scrollCategories(-280)"
                            class="pointer-events-auto w-8 h-8 rounded-full bg-white shadow-md border border-slate-200 text-slate-700 hover:text-emerald-700 hover:border-emerald-300 hover:scale-105 transition-all flex items-center justify-center cursor-pointer"
                            aria-label="Scroll Kategori ke Kiri"
                            title="Geser kategori ke kiri"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                </transition>

                <!-- Scrollable Category Track -->
                <div
                    ref="categoryScrollContainer"
                    @scroll.passive="checkCategoryScroll"
                    @mousedown="onCategoryMouseDown"
                    @mousemove="onCategoryMouseMove"
                    class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none scroll-smooth select-none cursor-grab active:cursor-grabbing"
                >
                    <button
                        type="button"
                        @click="handleCategoryChange('', $event)"
                        :data-active-category="!filters.category ? 'true' : 'false'"
                        class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer border shrink-0"
                        :class="!filters.category
                            ? 'bg-emerald-700 text-white border-emerald-700 shadow-sm'
                            : 'bg-white text-slate-700 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50'"
                    >
                        Semua Kategori
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="handleCategoryChange(cat.slug, $event)"
                        :data-active-category="filters.category === cat.slug ? 'true' : 'false'"
                        class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer border flex items-center gap-1.5 shrink-0"
                        :class="filters.category === cat.slug
                            ? 'bg-emerald-700 text-white border-emerald-700 shadow-sm'
                            : 'bg-white text-slate-700 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50'"
                    >
                        <span>{{ cat.name }}</span>
                        <span
                            class="text-[10px] font-bold px-1.5 py-0.2 rounded-md"
                            :class="filters.category === cat.slug ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ cat.products_count ?? 0 }}
                        </span>
                    </button>
                </div>

                <!-- Right Scroll Arrow Button (Desktop) -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-x-0"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <div
                        v-if="canScrollRight"
                        class="hidden md:flex absolute right-0 top-0 bottom-2 z-10 items-center pr-0.5 pl-8 bg-gradient-to-l from-white via-white/90 to-transparent pointer-events-none"
                    >
                        <button
                            type="button"
                            @click="scrollCategories(280)"
                            class="pointer-events-auto w-8 h-8 rounded-full bg-white shadow-md border border-slate-200 text-slate-700 hover:text-emerald-700 hover:border-emerald-300 hover:scale-105 transition-all flex items-center justify-center cursor-pointer"
                            aria-label="Scroll Kategori ke Kanan"
                            title="Geser kategori ke kanan"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </transition>
            </div>

            <!-- Product Grid -->
            <div v-if="products.data && products.data.length > 0">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
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
                            
                            <!-- Category Badge (Top Left, Single clean badge) -->
                            <div v-if="product.category" class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-950/80 text-white backdrop-blur-xs shadow-xs">
                                    {{ product.category.name }}
                                </span>
                            </div>

                            <!-- Out of Stock Overlay -->
                            <div v-if="product.stock <= 0" class="absolute inset-0 bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow-md">
                                    Stok Habis
                                </span>
                            </div>
                        </Link>

                        <!-- Product Content -->
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Rating & Stock Indicator -->
                                <div class="flex items-center justify-between gap-2 text-xs mb-2">
                                    <div class="flex items-center gap-1 text-amber-500 font-semibold">
                                        <span>★ 4.9</span>
                                        <span class="text-slate-400 font-normal">• Terjual 50+</span>
                                    </div>
                                    <span v-if="product.stock > 5" class="text-xs text-slate-500">
                                        Stok: <strong class="text-slate-800 font-semibold">{{ product.stock }}</strong>
                                    </span>
                                    <span v-else-if="product.stock > 0" class="text-xs font-bold text-amber-600">
                                        Sisa {{ product.stock }} unit
                                    </span>
                                    <span v-else class="text-xs font-bold text-rose-500">
                                        Habis
                                    </span>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-1">
                                    <Link :href="`/products/${product.slug}`">
                                        {{ product.name }}
                                    </Link>
                                </h3>

                                <p class="mt-1 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ product.description || 'Karya kriya lokal berkualitas.' }}
                                </p>
                            </div>

                            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] text-slate-400 font-medium block">Harga</span>
                                    <span class="text-sm sm:text-lg font-extrabold text-emerald-700">
                                        {{ formatRupiah(product.price) }}
                                    </span>
                                </div>

                                <!-- Add to Cart Button -->
                                <button
                                    v-if="product.stock > 0"
                                    type="button"
                                    @click="addToCart(product)"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl font-bold text-xs bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 transition-all shadow-xs cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Keranjang
                                </button>
                                <span
                                    v-else
                                    class="shrink-0 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl font-bold text-xs bg-slate-100 text-slate-400 border border-slate-200"
                                >
                                    Stok Habis
                                </span>
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
            <div v-else class="py-12 bg-white rounded-3xl border border-slate-200">
                <EmptyState
                    title="Tidak Ada Produk Ditemukan"
                    :description="filters.search || filters.category ? 'Coba ganti kata kunci pencarian atau pilih kategori lain.' : 'Belum ada produk aktif yang tersedia saat ini.'"
                    action-text="Reset Semua Filter"
                    @action="clearAllFilters"
                />
            </div>
        </div>
    </ShopLayout>
</template>

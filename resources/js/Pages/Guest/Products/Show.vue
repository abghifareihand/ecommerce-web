<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '../../../Layouts/ShopLayout.vue';
import Badge from '../../../Components/Badge.vue';
import QuantityStepper from '../../../Components/QuantityStepper.vue';
import { useCart } from '../../../Stores/cart';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    relatedProducts: {
        type: Array,
        default: () => [],
    },
});

const cart = useCart();
const quantity = ref(1);
const showAddedToast = ref(false);

const incrementQty = () => {
    quantity.value++;
};

const decrementQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};

const totalPrice = computed(() => {
    return Number(props.product.price) * quantity.value;
});

const handleAddToCart = () => {
    cart.addItem(props.product, quantity.value);
    showAddedToast.value = true;
    setTimeout(() => {
        showAddedToast.value = false;
    }, 3000);
};

const handleBuyNow = () => {
    cart.addItem(props.product, quantity.value);
    router.visit('/checkout');
};
</script>

<template>
    <Head :title="`${product.name} - ${$page.props.store?.name || 'EcoStore'}`" />

    <ShopLayout>
        <!-- Added Notification Toast -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-4 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="showAddedToast"
                class="fixed bottom-4 sm:bottom-6 left-4 right-4 z-50 flex items-center justify-between gap-3 bg-emerald-950/95 backdrop-blur-md text-white px-4 py-3 sm:px-5 sm:py-3.5 rounded-2xl shadow-2xl border border-emerald-600/40 max-w-md mx-auto"
            >
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="h-8 w-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] sm:text-xs text-emerald-300 font-medium">Berhasil Masuk Keranjang!</p>
                        <p class="text-xs sm:text-sm font-bold truncate">{{ quantity }}x {{ product.name }}</p>
                    </div>
                </div>
                <Link
                    href="/cart"
                    class="shrink-0 whitespace-nowrap px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-xs font-bold rounded-xl transition-colors shadow-xs"
                >
                    Lihat Cart &rarr;
                </Link>
            </div>
        </transition>

        <!-- Breadcrumbs -->
        <div class="bg-white border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <Link href="/" class="hover:text-emerald-600 transition-colors">Home</Link>
                    <span>/</span>
                    <Link href="/products" class="hover:text-emerald-600 transition-colors">Katalog Produk</Link>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold truncate">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <!-- Product Detail Main Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-start">
                <!-- Product Image Gallery -->
                <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs">
                    <div class="relative aspect-4/3 w-full rounded-2xl overflow-hidden bg-slate-100">
                        <img
                            :src="product.image_url"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm">
                                100% Produk Original
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Product Purchase Panel -->
                <div class="flex flex-col bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <Badge variant="success">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Stok Tersedia
                        </Badge>
                        <span class="text-xs text-slate-400">• Terjual 50+ pcs</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        {{ product.name }}
                    </h1>

                    <!-- Price -->
                    <div class="mt-4 p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 flex items-baseline justify-between">
                        <div>
                            <span class="text-xs text-emerald-800 font-semibold block">Harga Satuan</span>
                            <span class="text-3xl font-extrabold text-emerald-700">
                                {{ formatRupiah(product.price) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block">Total Pembelian</span>
                            <span class="text-xl font-bold text-slate-800">
                                {{ formatRupiah(totalPrice) }}
                            </span>
                        </div>
                    </div>

                    <!-- Quantity Picker -->
                    <div class="mt-6 flex items-center justify-between border-y border-slate-100 py-4">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Jumlah Pesanan</span>
                            <span class="text-xs text-slate-400">Atur kuantiti produk yang ingin dipesan</span>
                        </div>

                        <QuantityStepper
                            v-model="quantity"
                            :min="1"
                            size="md"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="handleAddToCart"
                            class="w-full flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-bold text-sm bg-white text-emerald-700 border-2 border-emerald-600 hover:bg-emerald-50 active:scale-98 transition-all shadow-xs cursor-pointer"
                        >
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            + Masukkan Keranjang
                        </button>

                        <button
                            type="button"
                            @click="handleBuyNow"
                            class="w-full flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-bold text-sm bg-emerald-600 text-white hover:bg-emerald-700 active:scale-98 transition-all shadow-md shadow-emerald-600/25 cursor-pointer"
                        >
                            Beli Sekarang &rarr;
                        </button>
                    </div>

                    <!-- Description Accordion / Detail Content -->
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-2">Deskripsi Produk</h3>
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ product.description || 'Tidak ada deskripsi detail untuk produk ini.' }}
                        </p>
                    </div>

                    <!-- Value Highlights -->
                    <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span> Pengrajin Lokal Asli
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span> Packing Aman &amp; Rapi
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span> Konfirmasi via WhatsApp
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span> Invoice Resmi Tersedia
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-16 pt-12 border-t border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-6">
                    Produk Pilihan Lainnya
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="related in relatedProducts"
                        :key="related.id"
                        class="bg-white rounded-2xl border border-slate-200/80 p-4 hover:shadow-lg transition-all"
                    >
                        <Link :href="`/products/${related.slug}`" class="block aspect-4/3 rounded-xl overflow-hidden bg-slate-100 mb-3">
                            <img :src="related.image_url" :alt="related.name" class="w-full h-full object-cover" />
                        </Link>
                        <h4 class="font-bold text-slate-900 text-sm line-clamp-1">
                            <Link :href="`/products/${related.slug}`" class="hover:text-emerald-600">
                                {{ related.name }}
                            </Link>
                        </h4>
                        <p class="text-xs text-emerald-700 font-extrabold mt-1">
                            {{ formatRupiah(related.price) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

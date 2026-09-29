<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Alert from '../Components/Alert.vue';
import { useCart } from '../Stores/cart';

const page = usePage();
const cart = useCart();
const searchQuery = ref('');

const flashMessage = computed(() => {
    return page.props.flash?.success || page.props.flash?.error || null;
});

const flashVariant = computed(() => {
    if (page.props.flash?.error) return 'danger';
    return 'success';
});

const clearFlash = () => {
    if (page.props.flash) {
        page.props.flash.success = null;
        page.props.flash.error = null;
    }
};

const executeSearch = () => {
    router.get('/products', { search: searchQuery.value }, { preserveState: true, replace: true });
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-slate-800 font-sans">
        <!-- Main E-Commerce Navbar -->
        <header class="sticky top-0 z-40 bg-white border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20 gap-3 sm:gap-8">
                    <!-- Store Brand -->
                    <Link href="/products" class="flex items-center gap-2.5 sm:gap-3 shrink-0 group min-w-0">
                        <div class="relative flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-white border border-slate-200/80 shadow-xs group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                            <img
                                :src="page.props.store?.logo_url || '/assets/img/logo.png'"
                                :alt="page.props.store?.name || 'Logo'"
                                class="w-full h-full object-cover object-center"
                                @error="$event.target.src = '/assets/img/logo.png'"
                            />
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2 truncate">
                            <span class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors truncate">
                                {{ page.props.store?.name || 'EcoStore' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 shrink-0">
                                SHOP
                            </span>
                        </div>
                    </Link>

                    <!-- Real-Time Search Bar (Shopping Centerpiece) -->
                    <form @submit.prevent="executeSearch" class="hidden md:flex flex-1 max-w-lg relative">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Cari nama barang atau produk di etalase..."
                            class="w-full pl-10 pr-24 py-2.5 rounded-xl border border-slate-300 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 transition-all shadow-inner"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button
                            type="submit"
                            class="absolute inset-y-1 right-1 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer"
                        >
                            Cari
                        </button>
                    </form>

                    <!-- Right Ecommerce Actions -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Shopping Cart Button (Icon + Badge Count only) -->
                        <Link
                            href="/cart"
                            class="relative flex items-center justify-center h-9 w-9 sm:h-11 sm:w-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 transition-all transform hover:scale-105 shrink-0"
                            title="Keranjang Belanja"
                            aria-label="Keranjang Belanja"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>

                            <!-- Badge Count (hanya jika ada isi keranjang) -->
                            <span
                                v-if="cart.totalCount.value > 0"
                                class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[11px] font-extrabold h-5 min-w-5 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs"
                            >
                                {{ cart.totalCount.value }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Mobile Search Bar -->
                <div class="md:hidden pb-4">
                    <form @submit.prevent="executeSearch" class="relative w-full">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Cari barang di etalase toko..."
                            class="w-full pl-10 pr-20 py-2 rounded-xl border border-slate-300 text-xs bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button
                            type="submit"
                            class="absolute inset-y-1 right-1 px-3 bg-emerald-600 text-white font-bold text-xs rounded-lg"
                        >
                            Cari
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Flash Message Notification with Auto-dismiss & Zero Leftover Space -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div v-if="flashMessage" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                <Alert
                    :key="flashMessage"
                    :variant="flashVariant"
                    :duration="4000"
                    @dismiss="clearFlash"
                >
                    {{ flashMessage }}
                </Alert>
            </div>
        </Transition>

        <!-- Shop App Content -->
        <main class="flex-1 w-full">
            <slot />
        </main>

        <!-- E-Commerce Footer -->
        <footer class="bg-white border-t border-slate-200 mt-16 py-10 text-slate-500 text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <p class="font-bold text-slate-800 text-sm">{{ page.props.store?.name || 'EcoStore' }} Shopping Application</p>
                    <p class="mt-1">Pemesanan online langsung terhubung ke WhatsApp Admin {{ page.props.store?.phone || '+62 898-5454-555' }}.</p>
                </div>

                <div class="flex items-center gap-6">
                    <Link href="/orders/track" class="hover:text-emerald-700 font-semibold">Lacak Pesanan</Link>
                    <Link href="/" class="hover:text-emerald-700 font-semibold">&larr; Kembali ke Beranda</Link>
                    <Link href="/admin/login" class="hover:text-slate-700">Akses Admin</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

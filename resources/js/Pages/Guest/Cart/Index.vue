<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '../../../Layouts/ShopLayout.vue';
import QuantityStepper from '../../../Components/QuantityStepper.vue';
import { useCart } from '../../../Stores/cart';

const cart = useCart();

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};
</script>

<template>
    <Head :title="`Keranjang Belanja - ${$page.props.store?.name || 'EcoStore'}`" />

    <ShopLayout>
        <!-- Page Header -->
        <div class="bg-white border-b border-slate-200/80 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Keranjang Belanja
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kelola produk pesanan Anda sebelum melanjutkan ke pengisian alamat dan checkout.
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- If Cart has items -->
            <div v-if="cart.items.value && cart.items.value.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Cart Items List (2 cols) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="text-base font-bold text-slate-900">
                                Daftar Produk ({{ cart.totalCount.value }} barang)
                            </h2>
                            <button
                                type="button"
                                @click="cart.clearCart"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline cursor-pointer"
                            >
                                Kosongkan Keranjang
                            </button>
                        </div>

                        <!-- Item Row -->
                        <div class="divide-y divide-slate-100">
                            <div
                                v-for="item in cart.items.value"
                                :key="item.id"
                                class="p-4 sm:p-6 flex items-center justify-between gap-4"
                            >
                                <!-- Product Info (Left) -->
                                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0 flex-1">
                                    <div class="h-16 w-16 sm:h-18 sm:w-18 shrink-0 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200">
                                        <img
                                            :src="item.image_url"
                                            :alt="item.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="min-w-0 pr-2">
                                        <Link :href="`/products/${item.slug}`" class="font-bold text-slate-900 text-sm sm:text-base hover:text-emerald-700 transition-colors line-clamp-1">
                                            {{ item.name }}
                                        </Link>
                                        <p class="text-xs text-emerald-700 font-semibold mt-0.5">
                                            {{ formatRupiah(item.price) }} / pcs
                                        </p>
                                    </div>
                                </div>

                                <!-- Right Column: Price at Top, [Trash] [Stepper] at Bottom Right -->
                                <div class="flex flex-col items-end justify-between gap-3 shrink-0">
                                    <!-- Harga / Subtotal (di atas increment decrement) -->
                                    <div class="text-right">
                                        <span class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                            {{ formatRupiah(item.price * item.quantity) }}
                                        </span>
                                    </div>

                                    <!-- Bottom Row: Trash Icon di samping kiri, Increment Decrement di pojok kanan -->
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        <!-- Delete Button (Icon Trash di samping kiri stepper) -->
                                        <button
                                            type="button"
                                            @click="cart.removeItem(item.id)"
                                            class="h-8 w-8 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer"
                                            title="Hapus dari keranjang"
                                            aria-label="Hapus dari keranjang"
                                        >
                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>

                                        <!-- Increment Decrement Stepper (Pojok kanan bawah) -->
                                        <QuantityStepper
                                            :model-value="item.quantity"
                                            :min="1"
                                            size="sm"
                                            @change="(qty) => cart.updateQuantity(item.id, qty)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Return to shop link -->
                    <div class="flex items-center justify-between text-xs pt-2">
                        <Link href="/products" class="font-bold text-emerald-700 hover:underline">
                            &larr; Lanjut Tambah Produk Lainnya
                        </Link>
                    </div>
                </div>

                <!-- Order Summary (1 col) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-4">
                        Ringkasan Belanja
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Total Jumlah Barang</span>
                            <span class="font-semibold text-slate-800">{{ cart.totalCount.value }} pcs</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Estimasi Subtotal</span>
                            <span class="font-semibold text-slate-800">{{ formatRupiah(cart.totalAmount.value) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span>Ongkos Kirim</span>
                            <span class="italic">Dihitung otomatis via WhatsApp</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-baseline justify-between">
                        <span class="text-sm font-bold text-slate-900">Total Pembayaran</span>
                        <span class="text-2xl font-extrabold text-emerald-700">
                            {{ formatRupiah(cart.totalAmount.value) }}
                        </span>
                    </div>

                    <div>
                        <Link
                            href="/checkout"
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-xl font-bold text-sm bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/25 transition-all cursor-pointer text-center"
                        >
                            Lanjut ke Pengisian Alamat &rarr;
                        </Link>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-500 space-y-1">
                        <p class="font-bold text-slate-700">Pemesanan Langsung Tanpa Login:</p>
                        <p>Setelah mengisi alamat, pesanan akan diteruskan langsung ke WhatsApp admin toko.</p>
                    </div>
                </div>
            </div>

            <!-- If Cart is Empty -->
            <div v-else class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 max-w-2xl mx-auto p-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Keranjang Belanja Anda Masih Kosong</h2>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Yuk cari dan pilih produk UMKM berkualitas favorit Anda sekarang.
                </p>
                <div class="mt-6">
                    <Link
                        href="/products"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-xs inline-block transition-colors"
                    >
                        Mulai Belanja Produk &rarr;
                    </Link>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

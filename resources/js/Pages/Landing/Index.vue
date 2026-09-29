<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '../../Layouts/LandingLayout.vue';
</script>

<template>
    <Head :title="`${$page.props.store?.name || 'EcoStore'} - ${$page.props.store?.tagline || 'Produk Kriya & Gaya Hidup Ramah Lingkungan'}`" />

    <LandingLayout>
        <!-- Hero Section -->
        <section id="beranda" class="relative overflow-hidden bg-linear-to-b from-emerald-50/80 via-white to-slate-50 py-20 sm:py-28 border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Pill Tag -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-6 shadow-xs">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        Official UMKM Storefront
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                        Koleksi Kriya Lokal &amp; Kebutuhan Harian
                        <span class="text-emerald-600">Ramah Lingkungan</span>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl mx-auto">
                        Dibuat dengan dedikasi pengrajin lokal untuk menghadirkan kualitas terbaik, alami, dan awet langsung ke genggaman Anda.
                    </p>

                    <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full">
                        <Link
                            href="/products"
                            class="w-full sm:w-auto text-center px-6 py-3.5 sm:px-8 sm:py-4 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-600/30 hover:shadow-xl hover:shadow-emerald-600/40 transition-all text-sm sm:text-base transform hover:-translate-y-0.5"
                        >
                            Masuk ke Katalog Belanja &rarr;
                        </Link>
                        <a
                            href="#story"
                            class="w-full sm:w-auto text-center px-6 py-3.5 sm:px-8 sm:py-4 rounded-xl font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 transition-all text-sm sm:text-base shadow-xs"
                        >
                            Cerita Kami
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Brand Values / Keunggulan UMKM -->
        <section id="keunggulan" class="py-16 bg-white border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Mengapa Memilih Kami</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Standar Kualitas {{ $page.props.store?.name || 'EcoStore' }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 mb-5">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">100% Bahan Alami</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Kami menolak bahan kimia berbahaya dan hanya memilih material organik serta daur ulang yang ramah bumi.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 mb-5">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Tangan Pertama Pengrajin</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Harga jujur dan transparan tanpa perantara tengkulak, langsung mendukung kesejahteraan pengrajin lokal.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 mb-5">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Pemesanan Langsung WhatsApp</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Pesan barang dengan cepat dan terhubung langsung dengan admin toko via WhatsApp tanpa perlu repot registrasi akun.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Story UMKM Section -->
        <section id="story" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tentang Kami</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1 mb-6 leading-tight">
                            Dari Studio Kecil Menuju Kenyamanan Rumah Anda
                        </h2>
                        <div class="text-slate-600 text-base leading-relaxed space-y-4">
                            <p>
                                {{ $page.props.store?.description || 'EcoStore bermula dari sebuah bengkel kriya keluarga yang peduli dengan keberlanjutan lingkungan. Kami percaya bahwa produk rumah tangga dan dekorasi tidak harus mengorbankan kelestarian alam.' }}
                            </p>
                            <p>
                                Setiap produk yang Anda pesan melewati kontrol kualitas teliti, diproduksi dalam jumlah terbatas untuk menjaga keaslian detail, dan dikemas rapi dengan material aman hingga sampai di depan pintu rumah Anda.
                            </p>
                        </div>
                        <div class="mt-8">
                            <Link
                                href="/products"
                                class="inline-flex items-center gap-2 font-bold text-emerald-700 hover:text-emerald-800 text-base group"
                            >
                                Jelajahi Koleksi Produk Lengkap
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </Link>
                        </div>
                    </div>

                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200">
                        <img
                            src="https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1000&auto=format&fit=crop&q=80"
                            alt="Bengkel Kriya UMKM"
                            class="w-full h-96 object-cover"
                        />
                        <div class="absolute inset-0 bg-linear-to-t from-slate-900/40 via-transparent to-transparent"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cara Belanja / 3 Langkah Mudah -->
        <section id="alur" class="py-20 bg-white border-y border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-xl mx-auto mb-16">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Alur Belanja</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Hanya 3 Langkah Mudah</h2>
                    <p class="mt-2 text-sm text-slate-500">Pemesanan santai, tanpa perlu bikin akun dan tanpa ribet.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center p-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mb-4 shadow-md shadow-emerald-600/30">
                            1
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Pilih Barang ke Keranjang</h4>
                        <p class="text-sm text-slate-500">
                            Buka katalog, tentukan jumlah barang yang diinginkan, dan masukkan ke keranjang belanja Anda.
                        </p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mb-4 shadow-md shadow-emerald-600/30">
                            2
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Isi Data Pengiriman</h4>
                        <p class="text-sm text-slate-500">
                            Masukkan nama, nomor WhatsApp aktif, dan alamat tujuan pengiriman pesanan Anda.
                        </p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mb-4 shadow-md shadow-emerald-600/30">
                            3
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Kirim Pesanan ke WhatsApp</h4>
                        <p class="text-sm text-slate-500">
                            Sistem akan membuat invoice otomatis dan membuka chat WhatsApp ke admin kami untuk konfirmasi pengiriman.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Big CTA Banner to Products -->
        <section class="bg-linear-to-r from-emerald-800 via-emerald-900 to-slate-900 py-20 text-white text-center">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    Mulai Belanja &amp; Dukung Produk Lokal Sekarang
                </h3>
                <p class="mt-4 text-emerald-200 text-base sm:text-lg max-w-2xl mx-auto">
                    Koleksi barang berkualitas tinggi dengan stok siap kirim. Cek katalog sekarang sebelum kehabisan!
                </p>
                <div class="mt-8">
                    <Link
                        href="/products"
                        class="px-9 py-4 rounded-xl font-bold bg-white text-emerald-900 hover:bg-emerald-50 transition-all shadow-xl text-base inline-block transform hover:scale-105"
                    >
                        Buka Katalog Belanja &rarr;
                    </Link>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

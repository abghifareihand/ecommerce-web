<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-slate-800 font-sans">
        <!-- Landing Header -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
                    <!-- Brand / Logo -->
                    <Link href="/" class="flex items-center gap-2.5 sm:gap-3 group min-w-0">
                        <div class="relative flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-white border border-slate-200/80 shadow-xs group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                            <img
                                :src="$page.props.store?.logo_url || '/assets/img/logo.png'"
                                :alt="$page.props.store?.name || 'Logo'"
                                class="w-full h-full object-cover object-center"
                                @error="$event.target.src = '/assets/img/logo.png'"
                            />
                        </div>
                        <span class="text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors truncate">
                            {{ $page.props.store?.name || 'EcoStore' }}
                        </span>
                    </Link>

                    <!-- Landing Nav Links (Home, Story, Values, Process) -->
                    <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                        <a href="#beranda" class="hover:text-emerald-600 transition-colors">Beranda</a>
                        <a href="#keunggulan" class="hover:text-emerald-600 transition-colors">Keunggulan</a>
                        <a href="#story" class="hover:text-emerald-600 transition-colors">Cerita Kami</a>
                        <a href="#alur" class="hover:text-emerald-600 transition-colors">Cara Pesan</a>
                    </nav>

                    <!-- Main Action Button to Shop App -->
                    <div class="flex items-center shrink-0">
                        <Link
                            href="/products"
                            class="inline-flex items-center gap-1.5 sm:gap-2 px-3.5 py-2 sm:px-6 sm:py-2.5 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs sm:shadow-md shadow-emerald-600/25 hover:shadow-lg transition-all shrink-0 whitespace-nowrap"
                        >
                            <span>Belanja Sekarang</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Landing Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Landing Footer -->
        <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-white font-bold text-sm">{{ $page.props.store?.name || 'EcoStore' }}</span> — {{ $page.props.store?.tagline || 'Produk Kriya Berkualitas Ramah Lingkungan' }}
                    <p v-if="$page.props.store?.address" class="text-slate-500 text-[11px] mt-1">{{ $page.props.store.address }}</p>
                </div>
                <div class="flex items-center gap-6">
                    <Link href="/orders/track" class="hover:text-emerald-400 font-semibold transition-colors">Lacak Pesanan</Link>
                    <Link href="/products" class="hover:text-emerald-400 font-semibold transition-colors">Masuk ke Toko</Link>
                    <Link href="/admin/login" class="hover:text-slate-300 transition-colors">Login Admin</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

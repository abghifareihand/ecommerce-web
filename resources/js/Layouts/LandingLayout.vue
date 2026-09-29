<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-slate-800 font-sans">
        <!-- Landing Header -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Brand / Logo -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="relative flex h-11 w-11 items-center justify-center rounded-lg bg-white border border-slate-200/80 shadow-xs group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                            <img
                                v-if="$page.props.store?.logo_url"
                                :src="$page.props.store.logo_url"
                                :alt="$page.props.store?.name || 'Logo'"
                                class="w-full h-full object-cover object-center"
                            />
                            <div v-else class="w-full h-full bg-linear-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <span class="text-2xl font-extrabold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors">
                                {{ $page.props.store?.name || 'EcoStore' }}
                            </span>
                            <span class="block text-[11px] font-semibold text-slate-400 tracking-wider uppercase -mt-1">{{ $page.props.store?.tagline || 'Kriya & Produk UMKM' }}</span>
                        </div>
                    </Link>

                    <!-- Landing Nav Links (Home, Story, Values, Process) -->
                    <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                        <a href="#beranda" class="hover:text-emerald-600 transition-colors">Beranda</a>
                        <a href="#keunggulan" class="hover:text-emerald-600 transition-colors">Keunggulan</a>
                        <a href="#story" class="hover:text-emerald-600 transition-colors">Cerita Kami</a>
                        <a href="#alur" class="hover:text-emerald-600 transition-colors">Cara Pesan</a>
                    </nav>

                    <!-- Main Action Button to Shop App -->
                    <div class="flex items-center gap-4">
                        <Link
                            href="/products"
                            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-600/25 hover:shadow-lg transition-all transform hover:-translate-y-0.5"
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
                    <Link href="/products" class="hover:text-emerald-400 font-semibold transition-colors">Masuk ke Toko</Link>
                    <Link href="/admin/login" class="hover:text-slate-300 transition-colors">Login Admin</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

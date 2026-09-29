<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Alert from '../Components/Alert.vue';

defineProps({
    title: {
        type: String,
        default: 'Admin Panel',
    },
});

const page = usePage();
const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
};

const closeUserMenu = () => {
    userMenuOpen.value = false;
};

const handleClickOutside = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        closeUserMenu();
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && userMenuOpen.value) {
        closeUserMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeyDown);
});

const logout = () => {
    router.post('/admin/logout');
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.charAt(0).toUpperCase();
};
</script>

<template>
    <div class="h-screen overflow-hidden bg-slate-100 flex font-sans text-slate-800">
        <!-- Mobile Sidebar Backdrop -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs md:hidden"
        />

        <!-- Sidebar (Desktop & Mobile Drawer) -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 text-slate-700 flex flex-col transition-transform duration-300 md:static md:translate-x-0 md:h-full shrink-0 select-none shadow-xs md:shadow-none',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Brand -->
            <div class="h-18 shrink-0 flex items-center px-6 border-b border-slate-200/80">
                <Link href="/admin/dashboard" class="flex items-center gap-3 group">
                    <div class="relative flex h-9 w-9 items-center justify-center rounded-lg bg-white border border-slate-200/80 shadow-xs overflow-hidden shrink-0">
                        <img
                            v-if="page.props.store?.logo_url"
                            :src="page.props.store.logo_url"
                            :alt="page.props.store?.name || 'Logo'"
                            class="w-full h-full object-cover object-center"
                        />
                        <div v-else class="w-full h-full bg-emerald-600 flex items-center justify-center text-white font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 text-base tracking-tight">{{ page.props.store?.name || 'EcoStore' }}</div>
                        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest">Admin Portal</div>
                    </div>
                </Link>
            </div>

            <!-- Navigation Links (Independent Scroll) -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto no-scrollbar">
                <Link
                    href="/admin/dashboard"
                    @click="sidebarOpen = false"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all',
                        $page.component === 'Admin/Dashboard'
                            ? 'bg-emerald-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                    ]"
                >
                    <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </Link>

                <Link
                    href="/admin/products"
                    @click="sidebarOpen = false"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all',
                        $page.component.startsWith('Admin/Products')
                            ? 'bg-emerald-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                    ]"
                >
                    <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Produk Katalog
                </Link>

                <Link
                    href="/admin/banners"
                    @click="sidebarOpen = false"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all',
                        $page.component.startsWith('Admin/Banners')
                            ? 'bg-emerald-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                    ]"
                >
                    <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Banner Promo
                </Link>

                <Link
                    href="/admin/orders"
                    @click="sidebarOpen = false"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all',
                        $page.component.startsWith('Admin/Orders')
                            ? 'bg-emerald-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                    ]"
                >
                    <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Pesanan Masuk
                </Link>
            </nav>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            <!-- Topbar Header -->
            <header class="h-18 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shrink-0 z-20">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="p-2 text-slate-600 hover:text-slate-900 rounded-lg md:hidden"
                        aria-label="Open sidebar"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900">{{ title }}</h1>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-4">
                    <!-- User Menu Dropdown Trigger -->
                    <div class="relative" ref="userMenuRef">
                        <button
                            type="button"
                            @click="toggleUserMenu"
                            class="flex items-center gap-3 p-1 sm:px-2.5 sm:py-1.5 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer select-none focus:outline-hidden"
                            :aria-expanded="userMenuOpen"
                        >
                            <div class="w-9 h-9 rounded-lg overflow-hidden bg-emerald-600 text-white flex items-center justify-center font-bold text-base shadow-xs shrink-0 border border-slate-200/60">
                                <img
                                    v-if="page.props.auth?.user?.avatar_url"
                                    :src="page.props.auth?.user?.avatar_url"
                                    :alt="page.props.auth?.user?.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>
                                    {{ getInitials(page.props.auth?.user?.name) }}
                                </span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-sm font-bold text-slate-800 leading-tight">
                                    {{ page.props.auth?.user?.name || 'Admin EcoStore' }}
                                </div>
                                <div class="text-xs text-slate-500 font-medium leading-tight mt-0.5">
                                    Admin
                                </div>
                            </div>
                            <svg
                                class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                :class="{ 'rotate-180': userMenuOpen }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 p-4 z-50 origin-top-right focus:outline-hidden"
                            >
                                <!-- User Info Header -->
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-lg overflow-hidden bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-xs shrink-0 border border-slate-200/60">
                                        <img
                                            v-if="page.props.auth?.user?.avatar_url"
                                            :src="page.props.auth?.user?.avatar_url"
                                            :alt="page.props.auth?.user?.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else>
                                            {{ getInitials(page.props.auth?.user?.name) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight truncate">
                                            {{ page.props.auth?.user?.name || 'Admin EcoStore' }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                                            {{ page.props.auth?.user?.email || 'admin@ecostore.com' }}
                                        </p>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70 uppercase">
                                                Admin
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-slate-100 my-3"></div>

                                <!-- Menu Links: Profil & Toko -->
                                <div class="space-y-1">
                                    <Link
                                        href="/admin/profile"
                                        @click="closeUserMenu"
                                        :class="[
                                            'w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors',
                                            $page.component.startsWith('Admin/Profile')
                                                ? 'bg-emerald-50 text-emerald-700 font-bold'
                                                : 'text-slate-700 hover:bg-slate-100 hover:text-emerald-700'
                                        ]"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            :class="$page.component.startsWith('Admin/Profile') ? 'text-emerald-600' : 'text-slate-400'"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Profil Saya</span>
                                        <span v-if="$page.component.startsWith('Admin/Profile')" class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    </Link>
                                    <Link
                                        href="/admin/store"
                                        @click="closeUserMenu"
                                        :class="[
                                            'w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors',
                                            $page.component.startsWith('Admin/Store')
                                                ? 'bg-emerald-50 text-emerald-700 font-bold'
                                                : 'text-slate-700 hover:bg-slate-100 hover:text-emerald-700'
                                        ]"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            :class="$page.component.startsWith('Admin/Store') ? 'text-emerald-600' : 'text-slate-400'"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>Pengaturan Toko</span>
                                        <span v-if="$page.component.startsWith('Admin/Store')" class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    </Link>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-slate-100 my-2"></div>

                                <!-- Logout action -->
                                <button
                                    type="button"
                                    @click="logout"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer text-left"
                                >
                                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Keluar
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <!-- Content Area with Flash Banner -->
            <main class="flex-1 overflow-y-auto no-scrollbar p-4 sm:p-8">
                <div class="w-full">
                    <div v-if="page.props.flash?.success || page.props.flash?.error" class="mb-6">
                        <Alert v-if="page.props.flash.success" variant="success">
                            {{ page.props.flash.success }}
                        </Alert>
                        <Alert v-if="page.props.flash.error" variant="danger">
                            {{ page.props.flash.error }}
                        </Alert>
                    </div>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        default: null,
    },
    searchedQuery: {
        type: String,
        default: '',
    },
});

const page = usePage();
const queryInput = ref(props.searchedQuery || '');
const copiedResi = ref(false);
const copiedBank = ref(false);

const handleSearch = () => {
    if (!queryInput.value.trim()) return;
    router.get('/orders/track', { q: queryInput.value.trim() }, { preserveState: false });
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
};

const formatDate = (isoDate) => {
    if (!isoDate) return '-';
    return new Date(isoDate).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const copyToClipboard = (text, type = 'resi') => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        if (type === 'resi') {
            copiedResi.value = true;
            setTimeout(() => { copiedResi.value = false; }, 2000);
        } else {
            copiedBank.value = true;
            setTimeout(() => { copiedBank.value = false; }, 2000);
        }
    });
};

// Stepper logic
// Stages:
// 1: pending (Pending Cek Ongkir)
// 2: payment_pending (Menunggu Pembayaran)
// 3: processing / confirmed (Diproses & Dikemas)
// 4: shipped (Sedang Dikirim)
// 5: completed (Selesai)
const getActiveStep = (status) => {
    switch (status) {
        case 'pending': return 1;
        case 'payment_pending': return 2;
        case 'processing':
        case 'confirmed': return 3;
        case 'shipped': return 4;
        case 'completed': return 5;
        default: return 0;
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending':
            return {
                bg: 'bg-amber-50 text-amber-800 border-amber-200',
                dot: 'bg-amber-500',
                label: 'Pending (Cek Ongkir)',
                desc: 'Pesanan telah diterima. Admin sedang mengecek biaya ongkos kirim ke alamat Anda.'
            };
        case 'payment_pending':
            return {
                bg: 'bg-orange-50 text-orange-800 border-orange-200',
                dot: 'bg-orange-500',
                label: 'Menunggu Pembayaran',
                desc: 'Total tagihan telah diterbitkan. Silakan transfer sesuai nominal ke rekening toko.'
            };
        case 'processing':
        case 'confirmed':
            return {
                bg: 'bg-sky-50 text-sky-800 border-sky-200',
                dot: 'bg-sky-500',
                label: 'Diproses & Dikemas',
                desc: 'Pembayaran telah terverifikasi. Pesanan sedang disiapkan dan dikemas oleh toko.'
            };
        case 'shipped':
            return {
                bg: 'bg-purple-50 text-purple-800 border-purple-200',
                dot: 'bg-purple-500',
                label: 'Sedang Dikirim',
                desc: 'Paket telah diserahkan ke jasa ekspedisi/kurir pengiriman.'
            };
        case 'completed':
            return {
                bg: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                dot: 'bg-emerald-500',
                label: 'Pesanan Selesai',
                desc: 'Pesanan telah selesai dan barang telah diterima dengan baik.'
            };
        case 'cancelled':
            return {
                bg: 'bg-rose-50 text-rose-800 border-rose-200',
                dot: 'bg-rose-500',
                label: 'Dibatalkan',
                desc: 'Pesanan ini telah dibatalkan.'
            };
        default:
            return {
                bg: 'bg-slate-50 text-slate-800 border-slate-200',
                dot: 'bg-slate-500',
                label: 'Pesanan Masuk',
                desc: 'Pesanan Anda sedang dalam pemrosesan.'
            };
    }
};

// WhatsApp assistance link
const getWaContactUrl = () => {
    const adminPhone = page.props.store?.clean_phone || '628985454555';
    let text = `Halo Admin ${page.props.store?.name || 'EcoStore'}, saya ingin bertanya status pesanan saya`;
    if (props.order) {
        text += ` dengan nomor invoice #${props.order.order_number}.`;
    }
    return `https://api.whatsapp.com/send?phone=${adminPhone}&text=${encodeURIComponent(text)}`;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-800">
        <Head :title="order ? `Lacak Pesanan #${order.order_number} - ${$page.props.store?.name || 'EcoStore'}` : `Lacak Pesanan - ${$page.props.store?.name || 'EcoStore'}`" />

        <!-- Minimal Dedicated Header for Tracking Portal -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Store Brand with LACAK Badge -->
                <Link href="/products" class="flex items-center gap-2.5 sm:gap-3 group min-w-0">
                    <div class="relative flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-lg bg-white border border-slate-200/80 shadow-xs group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                        <img
                            v-if="$page.props.store?.logo_url"
                            :src="$page.props.store.logo_url"
                            :alt="$page.props.store?.name || 'Logo'"
                            class="w-full h-full object-cover object-center"
                        />
                        <div v-else class="w-full h-full bg-emerald-600 flex items-center justify-center text-white font-bold">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 sm:gap-2 truncate">
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors truncate">
                            {{ $page.props.store?.name || 'EcoStore' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 shrink-0">
                            LACAK
                        </span>
                    </div>
                </Link>
            </div>
        </header>

        <!-- Main Tracking Content Container -->
        <main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 w-full">
            <!-- Header Section -->
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold mb-3 border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Layanan Mandiri Pelanggan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Lacak Status Pesanan
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Pantau tahapan pesanan Anda mulai dari konfirmasi pembayaran, pengemasan, hingga nomor resi pengiriman kurir secara langsung tanpa perlu login.
                </p>

                <!-- Search Form -->
                <form @submit.prevent="handleSearch" class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="queryInput"
                            placeholder="Masukkan No. Invoice (misal: INV-20260929-0001) atau No. WA..."
                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 shadow-xs transition-all"
                            required
                        />
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <button
                        type="submit"
                        class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-600/25 transition-all shrink-0 cursor-pointer"
                    >
                        Cari Pesanan
                    </button>
                </form>
            </div>

            <!-- RESULT: Order Found -->
            <div v-if="order" class="space-y-6">
                <!-- Order Overview Card -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase font-extrabold tracking-wider text-slate-400">Nomor Invoice</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-mono font-extrabold text-slate-900 mt-0.5">
                                #{{ order.order_number }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">
                                Dipesan pada {{ formatDate(order.created_at) }} WIB
                            </p>
                        </div>

                        <div class="flex flex-col sm:items-end">
                            <div :class="['inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold border', getStatusBadge(order.status).bg]">
                                <span :class="['w-2 h-2 rounded-full animate-pulse', getStatusBadge(order.status).dot]"></span>
                                <span>{{ getStatusBadge(order.status).label }}</span>
                            </div>
                            <span class="text-xs text-slate-400 mt-1 max-w-xs text-left sm:text-right">
                                {{ getStatusBadge(order.status).desc }}
                            </span>
                        </div>
                    </div>

                    <!-- Visual Progress Stepper (Only for normal workflow) -->
                    <div v-if="order.status !== 'cancelled'" class="p-6 sm:p-8 bg-white border-b border-slate-100">
                        <div class="relative">
                            <!-- Desktop Stepper Line -->
                            <div class="hidden sm:block absolute top-5 left-1/10 right-1/10 h-1 bg-slate-200 z-0">
                                <div
                                    class="h-full bg-emerald-500 transition-all duration-500"
                                    :style="{
                                        width: getActiveStep(order.status) === 1 ? '0%' :
                                               getActiveStep(order.status) === 2 ? '25%' :
                                               getActiveStep(order.status) === 3 ? '50%' :
                                               getActiveStep(order.status) === 4 ? '75%' : '100%'
                                    }"
                                ></div>
                            </div>

                            <!-- Stepper Points (5 Steps) -->
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-6 sm:gap-2 relative z-10">
                                <!-- Step 1: Pesanan Masuk -->
                                <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border-2 transition-all',
                                            getActiveStep(order.status) >= 1
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                                : 'bg-white border-slate-300 text-slate-400'
                                        ]"
                                    >
                                        <svg v-if="getActiveStep(order.status) > 1" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Pesanan Masuk</div>
                                        <div class="text-[11px] text-slate-500">Admin cek ongkir</div>
                                    </div>
                                </div>

                                <!-- Step 2: Menunggu Pembayaran -->
                                <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border-2 transition-all',
                                            getActiveStep(order.status) >= 2
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                                : 'bg-white border-slate-300 text-slate-400'
                                        ]"
                                    >
                                        <svg v-if="getActiveStep(order.status) > 2" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Menunggu Bayar</div>
                                        <div class="text-[11px] text-slate-500">Transfer ke rekening</div>
                                    </div>
                                </div>

                                <!-- Step 3: Diproses & Dikemas -->
                                <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border-2 transition-all',
                                            getActiveStep(order.status) >= 3
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                                : 'bg-white border-slate-300 text-slate-400'
                                        ]"
                                    >
                                        <svg v-if="getActiveStep(order.status) > 3" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span v-else>3</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Diproses &amp; Dikemas</div>
                                        <div class="text-[11px] text-slate-500">Toko menyiapkan barang</div>
                                    </div>
                                </div>

                                <!-- Step 4: Sedang Dikirim -->
                                <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border-2 transition-all',
                                            getActiveStep(order.status) >= 4
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                                : 'bg-white border-slate-300 text-slate-400'
                                        ]"
                                    >
                                        <svg v-if="getActiveStep(order.status) > 4" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span v-else>4</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Sedang Dikirim</div>
                                        <div class="text-[11px] text-slate-500">Paket di kurir ekspedisi</div>
                                    </div>
                                </div>

                                <!-- Step 5: Selesai -->
                                <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border-2 transition-all',
                                            getActiveStep(order.status) === 5
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                                : 'bg-white border-slate-300 text-slate-400'
                                        ]"
                                    >
                                        <svg v-if="getActiveStep(order.status) === 5" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span v-else>5</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Pesanan Selesai</div>
                                        <div class="text-[11px] text-slate-500">Barang telah diterima</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Notice (if order is cancelled) -->
                    <div v-else class="p-6 bg-rose-50/70 border-b border-rose-100 flex items-start gap-4 text-rose-800">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center shrink-0 text-rose-600 font-bold">
                            &times;
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-rose-900">Pesanan Dibatalkan</h3>
                            <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                                Pesanan ini telah dibatalkan oleh pihak toko atau pelanggan. Jika Anda memiliki pertanyaan atau kendala mengenai transaksi ini, silakan hubungi WhatsApp Admin toko kami.
                            </p>
                        </div>
                    </div>

                    <!-- Highlight: Courier & Tracking Info (If available or shipped) -->
                    <div v-if="order.tracking_number || order.courier" class="p-5 sm:p-6 bg-linear-to-r from-purple-50/70 to-indigo-50/70 border-b border-purple-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-purple-600/30">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-purple-700">Informasi Pengiriman Ekspedisi</div>
                                    <div class="text-sm text-slate-800 font-semibold mt-0.5">
                                        Kurir: <span class="text-slate-900 font-bold">{{ order.courier || 'Kurir Toko' }}</span>
                                    </div>
                                    <div v-if="order.tracking_number" class="flex items-center gap-2 mt-1.5 flex-wrap">
                                        <span class="text-xs text-slate-500">No. Resi:</span>
                                        <code class="px-2.5 py-1 bg-white border border-purple-200 text-purple-900 font-mono font-bold rounded-lg text-sm">
                                            {{ order.tracking_number }}
                                        </code>
                                        <button
                                            type="button"
                                            @click="copyToClipboard(order.tracking_number, 'resi')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-100 hover:bg-purple-200 text-purple-800 transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ copiedResi ? 'Tersalin!' : 'Salin Resi' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight: Status Pending (Admin sedang cek ongkir) -->
                    <div v-if="order.status === 'pending'" class="p-5 sm:p-6 bg-amber-50/70 border-b border-amber-100">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs font-bold uppercase tracking-wider text-amber-800">Sedang Menghitung Ongkos Kirim</div>
                                <p class="text-xs text-amber-900 mt-1 leading-relaxed">
                                    Pesanan Anda telah kami terima. Admin toko sedang memeriksa alamat pengiriman Anda untuk menentukan ongkos kirim kurir terbaik.
                                    Rincian total tagihan akhir dan nomor rekening transfer akan segera diinfokan oleh admin melalui WhatsApp.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight: Status Menunggu Pembayaran (payment_pending) -->
                    <div v-else-if="order.status === 'payment_pending'" class="p-5 sm:p-6 bg-orange-50/70 border-b border-orange-100">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs font-bold uppercase tracking-wider text-orange-800">Menunggu Pembayaran Transfer Bank</div>
                                <p class="text-xs text-orange-900 mt-1 leading-relaxed">
                                    Admin telah menentukan biaya ongkir. Silakan lakukan transfer sejumlah <strong>{{ formatRupiah(order.grand_total) }}</strong> ke rekening resmi toko di bawah ini, kemudian kirimkan foto bukti transfer ke WhatsApp admin.
                                </p>

                                <div v-if="page.props.store?.bank_account" class="mt-3 p-3 bg-white rounded-xl border border-orange-200">
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rekening Tujuan:</div>
                                    <div class="text-xs font-semibold text-slate-800 whitespace-pre-line mt-1">{{ page.props.store.bank_account }}</div>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(page.props.store.bank_account, 'bank')"
                                        class="mt-2 text-xs font-bold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1 cursor-pointer"
                                    >
                                        <span>{{ copiedBank ? 'Informasi Rekening Tersalin!' : 'Salin Rincian Rekening' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-Columns Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left: Customer and Delivery Address (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Tujuan Pengiriman</span>
                            </h3>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Nama Penerima</span>
                                    <span class="font-bold text-slate-900 text-sm">{{ order.customer_name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Nomor WhatsApp</span>
                                    <span class="font-mono font-semibold text-slate-800">{{ order.customer_phone }}</span>
                                </div>
                                <div v-if="order.customer_email">
                                    <span class="text-slate-400 block text-[11px]">Email</span>
                                    <span class="text-slate-800">{{ order.customer_email }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Alamat Lengkap</span>
                                    <p class="text-slate-700 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-200/60 mt-1">
                                        {{ order.customer_address }}
                                    </p>
                                </div>
                                <div v-if="order.notes">
                                    <span class="text-slate-400 block text-[11px]">Catatan Pembeli</span>
                                    <p class="text-slate-600 italic bg-amber-50/60 p-2.5 rounded-lg border border-amber-200/60 mt-1">
                                        "{{ order.notes }}"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Need Help Box -->
                        <div class="bg-emerald-50/60 rounded-2xl border border-emerald-100 p-5 text-xs">
                            <h4 class="font-bold text-emerald-950 mb-1">Butuh Bantuan Pesanan?</h4>
                            <p class="text-emerald-800 leading-relaxed mb-3">
                                Ada kendala alamat, perubahan rincian, atau konfirmasi transfer? Hubungi langsung admin kami via WhatsApp.
                            </p>
                            <a
                                :href="getWaContactUrl()"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition-colors"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Hubungi WhatsApp Admin</span>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Ordered Items & Billing Summary (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6">
                            <h3 class="text-sm font-bold text-slate-900 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 mb-4 pb-3 border-b border-slate-100">
                                 <span class="flex items-center gap-2">
                                     <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                     </svg>
                                     <span>Produk Dipesan ({{ order.items?.length || 0 }} Item)</span>
                                 </span>
                                 <a
                                     :href="`/orders/${order.order_number}/invoice`"
                                     target="_blank"
                                     class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap shrink-0"
                                 >
                                     <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                     </svg>
                                     <span>Unduh Invoice (PDF)</span>
                                 </a>
                            </h3>

                            <!-- Items List -->
                            <div class="divide-y divide-slate-100 mb-6">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="py-3 flex items-center justify-between gap-3 text-xs"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                                            <img
                                                v-if="item.product?.primary_image_url"
                                                :src="item.product.primary_image_url"
                                                :alt="item.product_name"
                                                class="w-full h-full object-cover"
                                            />
                                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate sm:whitespace-normal">{{ item.product_name }}</div>
                                            <div class="text-[11px] text-slate-500">
                                                {{ item.quantity }} &times; {{ formatRupiah(item.price) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-extrabold text-slate-900 text-right shrink-0 whitespace-nowrap">
                                        {{ formatRupiah(item.subtotal) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Breakdown Card -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs space-y-2.5">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>Subtotal Belanja</span>
                                    <span class="font-semibold">{{ formatRupiah(order.total_amount) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>Ongkos Kirim {{ order.courier ? `(${order.courier})` : '' }}</span>
                                    <span class="font-semibold">
                                        <template v-if="order.shipping_cost > 0">
                                            {{ formatRupiah(order.shipping_cost) }}
                                        </template>
                                        <template v-else-if="order.status === 'pending'">
                                            <span class="text-amber-600 italic">Menunggu info admin</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-emerald-700 font-bold">Rp 0 (Gratis)</span>
                                        </template>
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between text-sm font-extrabold text-slate-900">
                                    <span>Total Tagihan</span>
                                    <span class="text-emerald-700 text-base">
                                        {{ formatRupiah(order.grand_total || (Number(order.total_amount) + Number(order.shipping_cost || 0))) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESULT: Query Searched But Not Found -->
            <div
                v-else-if="searchedQuery"
                class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-8 sm:p-12 text-center max-w-xl mx-auto"
            >
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-100">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Pesanan Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Kami tidak dapat menemukan pesanan dengan kata kunci "<span class="font-semibold text-slate-700">{{ searchedQuery }}</span>".
                    Pastikan nomor invoice sesuai dengan yang tertera di pesan WhatsApp Anda (contoh: <strong>INV-20260929-0001</strong>), atau coba cari dengan nomor WhatsApp yang Anda gunakan saat memesan.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button
                        type="button"
                        @click="queryInput = ''; router.get('/orders/track')"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto cursor-pointer"
                    >
                        Reset Pencarian
                    </button>
                    <a
                        :href="getWaContactUrl()"
                        target="_blank"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-xs transition-colors w-full sm:w-auto"
                    >
                        Tanya Admin via WhatsApp
                    </a>
                </div>
            </div>

            <!-- INITIAL: No Search Yet Guidance -->
            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6"
            >
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">1. Buka Chat WhatsApp</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Lihat nomor invoice pesanan Anda di pesan WhatsApp checkout yang dikirimkan ke Admin.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">2. Masukkan & Pantau</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Ketik nomor invoice atau no. WA di kotak pencarian di atas untuk melihat proses persiapan dan pengiriman barang.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">3. Dapatkan No. Resi</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Saat paket telah dikirim, nama kurir dan nomor resi pengiriman akan muncul secara otomatis di halaman ini.
                    </p>
                </div>
            </div>
        </main>

        <!-- Dedicated Minimal Tracking Footer -->
        <footer class="bg-white border-t border-slate-200/90 py-6 mt-16 text-xs text-slate-500">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div>
                    <span class="font-bold text-slate-800">{{ $page.props.store?.name || 'EcoStore' }}</span> &copy; {{ new Date().getFullYear() }} — Layanan Pelacakan Pesanan
                </div>
                <div class="flex items-center gap-4 font-semibold">
                    <Link href="/products" class="hover:text-emerald-700 transition-colors">Belanja di Toko</Link>
                    <span class="text-slate-300">&bull;</span>
                    <Link href="/" class="hover:text-emerald-700 transition-colors">Beranda</Link>
                    <span class="text-slate-300">&bull;</span>
                    <a :href="getWaContactUrl()" target="_blank" class="hover:text-emerald-700 transition-colors">Bantuan Admin</a>
                </div>
            </div>
        </footer>
    </div>
</template>

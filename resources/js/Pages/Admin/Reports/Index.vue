<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import CustomDropdown from '../../../Components/CustomDropdown.vue';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
    },
});

const filterForm = ref({
    preset: props.filters.preset || 'this_month',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    status: props.filters.status || 'all',
});

// Keep filter form synchronized if props change
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        filterForm.value.preset = newFilters.preset || 'this_month';
        filterForm.value.start_date = newFilters.start_date || '';
        filterForm.value.end_date = newFilters.end_date || '';
        filterForm.value.status = newFilters.status || 'all';
    }
}, { deep: true, immediate: true });

const presets = [
    { key: 'today', label: 'Hari Ini' },
    { key: 'last_7_days', label: '7 Hari Terakhir' },
    { key: 'this_month', label: 'Bulan Ini' },
    { key: 'last_month', label: 'Bulan Lalu' },
    { key: 'this_year', label: 'Tahun Ini' },
];

const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'completed', label: 'Selesai' },
    { value: 'shipped', label: 'Sedang Dikirim' },
    { value: 'processing', label: 'Diproses & Dikemas' },
    { value: 'payment_pending', label: 'Menunggu Pembayaran' },
    { value: 'pending', label: 'Pending (Cek Ongkir)' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const calculatePresetDates = (presetKey) => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const toYmd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    switch (presetKey) {
        case 'today': {
            const todayStr = toYmd(now);
            return { start: todayStr, end: todayStr };
        }
        case 'last_7_days': {
            const start = new Date(now);
            start.setDate(now.getDate() - 6);
            return { start: toYmd(start), end: toYmd(now) };
        }
        case 'this_month': {
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            return { start: toYmd(start), end: toYmd(end) };
        }
        case 'last_month': {
            const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const end = new Date(now.getFullYear(), now.getMonth(), 0);
            return { start: toYmd(start), end: toYmd(end) };
        }
        case 'this_year': {
            const start = new Date(now.getFullYear(), 0, 1);
            const end = new Date(now.getFullYear(), 11, 31);
            return { start: toYmd(start), end: toYmd(end) };
        }
        default:
            return { start: filterForm.value.start_date, end: filterForm.value.end_date };
    }
};

const selectPreset = (presetKey) => {
    filterForm.value.preset = presetKey;
    const dates = calculatePresetDates(presetKey);
    filterForm.value.start_date = dates.start;
    filterForm.value.end_date = dates.end;
    applyFilters();
};

const activateCustom = () => {
    filterForm.value.preset = 'custom';
};

const applyFilters = () => {
    router.get('/admin/reports', {
        preset: filterForm.value.preset,
        start_date: filterForm.value.start_date,
        end_date: filterForm.value.end_date,
        status: filterForm.value.status,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    selectPreset('this_month');
};

const exportUrl = computed(() => {
    const params = new URLSearchParams({
        preset: filterForm.value.preset,
        start_date: filterForm.value.start_date || props.filters.start_date,
        end_date: filterForm.value.end_date || props.filters.end_date,
        status: filterForm.value.status,
    });
    return `/admin/reports/export?${params.toString()}`;
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const getStatusBadgeVariant = (status) => {
    switch (status) {
        case 'completed':
            return 'success';
        case 'shipped':
            return 'info';
        case 'processing':
        case 'confirmed':
            return 'info';
        case 'payment_pending':
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(d);
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(d);
};
</script>

<template>
    <Head title="Laporan Penjualan - Admin" />

    <AdminLayout title="Laporan Penjualan">
        <div class="w-full space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Rekap &amp; Laporan Penjualan</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Pantau performa omset, ringkasan transaksi pelanggan, dan unduh data ke file Excel (.xlsx) dengan format rapi.
                    </p>
                </div>

                <!-- Export Action Button -->
                <div class="flex items-center gap-2.5">
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors cursor-pointer select-none"
                    >
                        <!-- Excel Document Icon -->
                        <svg class="w-4 h-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Unduh Excel (.xlsx)</span>
                    </a>
                </div>
            </div>

            <!-- Preset Filters Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-for="p in presets"
                        :key="p.key"
                        type="button"
                        @click="selectPreset(p.key)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer select-none',
                            filterForm.preset === p.key
                                ? 'bg-emerald-600 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
                        ]"
                    >
                        {{ p.label }}
                    </button>
                    <button
                        type="button"
                        @click="activateCustom"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer select-none',
                            filterForm.preset === 'custom'
                                ? 'bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-600/30'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
                        ]"
                    >
                        Kustom Rentang Tanggal
                    </button>
                </div>

                <!-- Custom Date & Status Filter Inputs (Consistent h-10 across all controls) -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <!-- Start Date -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">
                                Dari Tanggal:
                            </label>
                            <span v-if="filterForm.preset !== 'custom'" class="text-[10px] text-emerald-600 font-medium bg-emerald-50 px-1.5 py-0.2 rounded-md">
                                Otomatis
                            </span>
                        </div>
                        <input
                            type="date"
                            v-model="filterForm.start_date"
                            :disabled="filterForm.preset !== 'custom'"
                            class="w-full h-10 bg-white rounded-xl px-3.5 text-xs font-medium border border-slate-300 disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 outline-hidden transition-all shadow-xs box-border"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">
                                Sampai Tanggal:
                            </label>
                            <span v-if="filterForm.preset !== 'custom'" class="text-[10px] text-emerald-600 font-medium bg-emerald-50 px-1.5 py-0.2 rounded-md">
                                Otomatis
                            </span>
                        </div>
                        <input
                            type="date"
                            v-model="filterForm.end_date"
                            :disabled="filterForm.preset !== 'custom'"
                            class="w-full h-10 bg-white rounded-xl px-3.5 text-xs font-medium border border-slate-300 disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 outline-hidden transition-all shadow-xs box-border"
                        />
                    </div>

                    <!-- Status Dropdown -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Status Transaksi:
                        </label>
                        <CustomDropdown
                            v-model="filterForm.status"
                            :options="statusOptions"
                            :full-width="true"
                            :show-dot="true"
                            button-class="w-full h-10 text-xs font-medium rounded-xl py-0"
                        />
                    </div>

                    <!-- Action Buttons (Matched to Exact h-10 height) -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="applyFilters"
                            class="flex-1 h-10 inline-flex items-center justify-center px-4 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors cursor-pointer select-none"
                        >
                            Terapkan
                        </button>
                        <button
                            type="button"
                            @click="resetFilters"
                            class="h-10 inline-flex items-center justify-center px-3.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 shadow-xs transition-colors cursor-pointer select-none"
                            title="Reset Filter ke Bulan Ini"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Metric Cards (4 Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Omset -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Omset</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ formatRupiah(metrics.grand_total_revenue) }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                            <span>Barang: {{ formatRupiah(metrics.total_revenue) }}</span>
                            <span>Ongkir: {{ formatRupiah(metrics.total_shipping) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Pesanan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pesanan</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ metrics.total_orders }} <span class="text-xs font-semibold text-slate-400">Transaksi</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                            <span class="text-emerald-600 font-semibold">{{ metrics.completed_orders }} Selesai</span>
                            <span>•</span>
                            <span class="text-amber-600 font-medium">{{ metrics.active_orders }} Berjalan</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Produk Terjual -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Produk Terjual</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ metrics.total_items_sold.toLocaleString('id-ID') }} <span class="text-xs font-semibold text-slate-400">Pcs / Item</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Akumulasi kuantiti dari seluruh pesanan valid.
                        </p>
                    </div>
                </div>

                <!-- Card 4: Rata-rata Transaksi (AOV) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata Order</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ formatRupiah(metrics.average_order_value) }}
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Nilai rata-rata per transaksi pelanggan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Orders Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Rincian Transaksi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Menampilkan {{ orders.data.length }} dari {{ orders.total }} total data transaksi pada rentang terpilih.
                        </p>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 select-none">
                                <th class="py-3 px-4">Invoice / Waktu</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Rincian Barang</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Subtotal &amp; Ongkir</th>
                                <th class="py-3 px-4 text-right">Total Bayar</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                class="hover:bg-slate-50/60 transition-colors"
                            >
                                <!-- Invoice & Date -->
                                <td class="py-3.5 px-4 align-top">
                                    <Link
                                        :href="`/admin/orders/${order.id}`"
                                        class="font-mono font-bold text-emerald-700 hover:text-emerald-900 hover:underline block"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                        <span>{{ formatDate(order.created_at) }}</span>
                                        <span>•</span>
                                        <span>{{ formatTime(order.created_at) }}</span>
                                    </div>
                                </td>

                                <!-- Customer Info -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900">{{ order.customer_name }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                        <span>{{ order.customer_phone }}</span>
                                        <a
                                            :href="`https://wa.me/${order.customer_phone.replace(/^0/, '62').replace(/\D/g, '')}`"
                                            target="_blank"
                                            class="text-emerald-600 hover:text-emerald-800 font-bold"
                                            title="Chat WhatsApp"
                                        >
                                            WA
                                        </a>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5" :title="order.customer_address">
                                        {{ order.customer_address }}
                                    </div>
                                </td>

                                <!-- Items List -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="space-y-1 max-w-xs">
                                        <div
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="text-[11px] text-slate-700 flex items-center justify-between gap-2"
                                        >
                                            <span class="truncate">{{ item.product_name }}</span>
                                            <span class="font-semibold text-slate-500 shrink-0">&times;{{ item.quantity }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 align-top">
                                    <Badge :variant="getStatusBadgeVariant(order.status)" size="sm">
                                        {{ order.status_label }}
                                    </Badge>
                                </td>

                                <!-- Subtotal & Shipping -->
                                <td class="py-3.5 px-4 align-top text-right">
                                    <div class="font-medium text-slate-700">
                                        {{ formatRupiah(order.total_amount) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        + Ongkir: {{ formatRupiah(order.shipping_cost) }}
                                    </div>
                                </td>

                                <!-- Grand Total -->
                                <td class="py-3.5 px-4 align-top text-right">
                                    <div class="font-bold text-slate-900">
                                        {{ formatRupiah(order.grand_total) }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 align-top text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link
                                            :href="`/admin/orders/${order.id}`"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors"
                                            title="Lihat Detail Pesanan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <a
                                            :href="`/admin/orders/${order.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition-colors"
                                            title="Unduh PDF Invoice"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="orders.data.length === 0">
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="w-12 h-12 stroke-[1.5] mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div class="text-sm font-bold text-slate-700">Tidak Ada Transaksi</div>
                                        <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                            Tidak ditemukan data pesanan pada rentang tanggal atau status yang dipilih. Silakan ubah filter di atas.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="orders.links && orders.links.length > 3"
                    class="p-4 border-t border-slate-100 flex items-center justify-between gap-4"
                >
                    <div class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold">{{ orders.from || 0 }}</span> sampai <span class="font-bold">{{ orders.to || 0 }}</span> dari <span class="font-bold">{{ orders.total }}</span> total transaksi
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in orders.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors select-none',
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : link.url
                                        ? 'bg-slate-50 text-slate-700 hover:bg-slate-100'
                                        : 'text-slate-300 pointer-events-none cursor-default'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

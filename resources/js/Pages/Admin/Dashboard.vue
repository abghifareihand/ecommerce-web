<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Badge from '../../Components/Badge.vue';
import Button from '../../Components/Button.vue';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recent_orders: {
        type: Array,
        default: () => [],
    },
    recent_products: {
        type: Array,
        default: () => [],
    },
});

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};

const formatDate = (isoDate) => {
    if (!isoDate) return '-';
    return new Date(isoDate).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'confirmed':
            return { variant: 'info', label: 'Diproses' };
        case 'completed':
            return { variant: 'success', label: 'Selesai' };
        case 'cancelled':
            return { variant: 'danger', label: 'Batal' };
        default:
            return { variant: 'warning', label: 'Pending' };
    }
};
</script>

<template>
    <Head title="Admin Dashboard - EcoStore" />

    <AdminLayout title="Ringkasan Toko">
        <!-- Top Stats Row (Orders & Revenue) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
            <!-- Total Revenue -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Estimasi Omset</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">
                    {{ formatRupiah(stats.total_revenue || 0) }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Total pesanan non-batal</p>
            </div>

            <!-- Total Orders -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pesanan</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">
                            {{ stats.total_orders || 0 }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Semua pesanan masuk</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Pesanan Pending</p>
                        <p class="mt-2 text-2xl font-extrabold text-amber-700 tracking-tight">
                            {{ stats.pending_orders || 0 }}
                        </p>
                        <p class="mt-1 text-xs text-amber-600 font-medium">Perlu dikonfirmasi WA</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Produk Aktif</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">
                            {{ stats.active_products || 0 }} / {{ stats.total_products || 0 }}
                        </p>
                        <p class="mt-1 text-xs text-emerald-600 font-medium">Tampil di katalog</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Recent Orders Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Pesanan Masuk</h3>
                        <p class="text-xs text-slate-400">Daftar checkout pelanggan</p>
                    </div>
                    <Link href="/admin/orders">
                        <Button variant="secondary" size="sm">
                            Lihat Semua
                        </Button>
                    </Link>
                </div>

                <div v-if="recent_orders && recent_orders.length > 0" class="divide-y divide-slate-100">
                    <div
                        v-for="order in recent_orders"
                        :key="order.id"
                        class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <Link :href="`/admin/orders/${order.id}`" class="font-mono font-bold text-slate-900 hover:text-emerald-700">
                                    #{{ order.order_number }}
                                </Link>
                                <Badge :variant="getStatusBadge(order.status).variant">
                                    {{ getStatusBadge(order.status).label }}
                                </Badge>
                            </div>
                            <p class="text-slate-600 font-semibold mt-1">{{ order.customer_name }} • {{ order.customer_phone }}</p>
                            <p class="text-slate-400 text-[11px]">{{ formatDate(order.created_at) }}</p>
                        </div>

                        <div class="text-right">
                            <p class="font-extrabold text-slate-900 text-sm">{{ formatRupiah(order.total_amount) }}</p>
                            <Link :href="`/admin/orders/${order.id}`" class="text-emerald-700 font-bold hover:underline">
                                Detail &rarr;
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="p-8 text-center text-xs text-slate-400">
                    Belum ada pesanan yang masuk.
                </div>
            </div>

            <!-- Recent Products Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Katalog Produk</h3>
                        <p class="text-xs text-slate-400">Produk yang terdaftar</p>
                    </div>
                    <Link href="/admin/products">
                        <Button variant="secondary" size="sm">
                            Lihat Semua
                        </Button>
                    </Link>
                </div>

                <div class="divide-y divide-slate-100">
                    <div
                        v-for="product in recent_products"
                        :key="product.id"
                        class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                                <img :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 line-clamp-1">{{ product.name }}</p>
                                <p class="text-emerald-700 font-semibold">{{ formatRupiah(product.price) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge :variant="product.is_active ? 'success' : 'secondary'">
                                {{ product.is_active ? 'Aktif' : 'Non-aktif' }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

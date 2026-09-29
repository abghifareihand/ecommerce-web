<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import Pagination from '../../../Components/Pagination.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Modal from '../../../Components/Modal.vue';
import Button from '../../../Components/Button.vue';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '' }),
    },
    statusCounts: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(
        '/admin/orders',
        { search: search.value, status: props.filters.status },
        { preserveState: true, replace: true }
    );
};

const filterStatus = (status) => {
    router.get(
        '/admin/orders',
        { search: search.value, status: status },
        { preserveState: true, replace: true }
    );
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};

const formatDate = (isoDate) => {
    if (!isoDate) return '-';
    return new Date(isoDate).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
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
            return { variant: 'danger', label: 'Dibatalkan' };
        default:
            return { variant: 'warning', label: 'Menunggu' };
    }
};

// Delete confirmation modal state for cancelled orders
const confirmDeleteModal = ref(false);
const orderToDelete = ref(null);
const deleting = ref(false);

const openDeleteModal = (order) => {
    orderToDelete.value = order;
    confirmDeleteModal.value = true;
};

const closeDeleteModal = () => {
    confirmDeleteModal.value = false;
    orderToDelete.value = null;
};

const confirmDelete = () => {
    if (!orderToDelete.value) return;

    deleting.value = true;
    router.delete(`/admin/orders/${orderToDelete.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Rekap Pesanan Pelanggan - Admin" />

    <AdminLayout title="Rekap Pesanan Masuk">
        <!-- Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <button
                type="button"
                @click="filterStatus('')"
                :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    !filters.status
                        ? 'bg-emerald-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                ]"
            >
                Semua ({{ statusCounts.all || 0 }})
            </button>
            <button
                type="button"
                @click="filterStatus('pending')"
                :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    filters.status === 'pending'
                        ? 'bg-amber-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                ]"
            >
                Menunggu ({{ statusCounts.pending || 0 }})
            </button>
            <button
                type="button"
                @click="filterStatus('confirmed')"
                :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    filters.status === 'confirmed'
                        ? 'bg-sky-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                ]"
            >
                Diproses ({{ statusCounts.confirmed || 0 }})
            </button>
            <button
                type="button"
                @click="filterStatus('completed')"
                :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    filters.status === 'completed'
                        ? 'bg-emerald-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                ]"
            >
                Selesai ({{ statusCounts.completed || 0 }})
            </button>
            <button
                type="button"
                @click="filterStatus('cancelled')"
                :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                    filters.status === 'cancelled'
                        ? 'bg-rose-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                ]"
            >
                Dibatalkan ({{ statusCounts.cancelled || 0 }})
            </button>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 flex items-center justify-between">
            <form @submit.prevent="handleSearch" class="relative w-full sm:w-96">
                <input
                    type="text"
                    v-model="search"
                    placeholder="Cari no. invoice, nama, atau no. WA..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>
        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div v-if="orders.data && orders.data.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Invoice</th>
                                <th class="py-3.5 px-6">Waktu</th>
                                <th class="py-3.5 px-6">Pelanggan</th>
                                <th class="py-3.5 px-6">Total Belanja</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                class="hover:bg-slate-50/60 transition-colors"
                            >
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <Link :href="`/admin/orders/${order.id}`" class="text-emerald-700 hover:underline">
                                        #{{ order.order_number }}
                                    </Link>
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-500">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900">{{ order.customer_name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ order.customer_phone }}</div>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-900">
                                    {{ formatRupiah(order.total_amount) }}
                                </td>
                                <td class="py-4 px-6">
                                    <Badge :variant="getStatusBadge(order.status).variant">
                                        {{ getStatusBadge(order.status).label }}
                                    </Badge>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Tombol hapus pesanan: Hanya tampil jika status pesanan 'cancelled' (Dibatalkan) -->
                                        <button
                                            v-if="order.status === 'cancelled'"
                                            type="button"
                                            @click="openDeleteModal(order)"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Pesanan Dibatalkan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>

                                        <Link
                                            :href="`/admin/orders/${order.id}`"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors"
                                        >
                                            Detail &rarr;
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    <Pagination :links="orders.links" />
                </div>
            </div>

            <div v-else class="p-8">
                <EmptyState
                    title="Belum ada pesanan"
                    description="Belum ada pesanan yang masuk dari pelanggan untuk kriteria filter ini."
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmDeleteModal" @close="closeDeleteModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus Invoice Pesanan?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus data invoice pesanan
                    <span class="font-bold text-slate-900">#{{ orderToDelete?.order_number }}</span>
                    atas nama <span class="font-bold text-slate-900">{{ orderToDelete?.customer_name }}</span>?
                    Seluruh rincian pesanan yang dibatalkan ini akan dihapus permanen dari sistem.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="closeDeleteModal" :disabled="deleting">
                        Batal
                    </Button>
                    <Button variant="danger" @click="confirmDelete" :loading="deleting" :disabled="deleting">
                        Hapus Pesanan
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

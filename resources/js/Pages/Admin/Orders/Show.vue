<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import Button from '../../../Components/Button.vue';
import CustomDropdown from '../../../Components/CustomDropdown.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    customerWaUrl: {
        type: String,
        required: true,
    },
});

const statusForm = useForm({
    status: props.order.status,
});

const statusOptions = [
    { value: 'pending', label: 'Menunggu' },
    { value: 'confirmed', label: 'Diproses' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const updateStatus = () => {
    statusForm.put(`/admin/orders/${props.order.id}/status`);
};

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
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

const confirmDeleteModal = ref(false);
const deleting = ref(false);

const confirmDelete = () => {
    deleting.value = true;
    router.delete(`/admin/orders/${props.order.id}`, {
        onSuccess: () => {
            confirmDeleteModal.value = false;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Detail Pesanan #${order.order_number} - Admin`" />

    <AdminLayout :title="`Pesanan #${order.order_number}`">
        <div class="w-full space-y-6">
            <!-- Header bar with back and actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Link
                        href="/admin/orders"
                        class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors inline-flex items-center gap-1"
                    >
                        &larr; Kembali ke Rekap Pesanan
                    </Link>
                    <div class="flex items-center gap-3 mt-1">
                        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                            #{{ order.order_number }}
                        </h2>
                        <Badge :variant="getStatusBadge(order.status).variant">
                            {{ getStatusBadge(order.status).label }}
                        </Badge>
                    </div>
                </div>

                <!-- Action Buttons: PDF & WhatsApp -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- WhatsApp Send Invoice Link -->
                    <a
                        :href="customerWaUrl"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.35.491 1.199.534 1.286.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.086-.178.18-.077.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                        </svg>
                        Kirim Invoice ke WA Pelanggan
                    </a>

                    <!-- Download PDF -->
                    <a
                        :href="`/admin/orders/${order.id}/pdf`"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition-colors"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh PDF
                    </a>

                    <a
                        :href="`/admin/orders/${order.id}/pdf/stream`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition-colors"
                    >
                        Lihat PDF
                    </a>

                    <!-- Hapus Pesanan (Hanya untuk pesanan Dibatalkan) -->
                    <button
                        v-if="order.status === 'cancelled'"
                        type="button"
                        @click="confirmDeleteModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Pesanan
                    </button>
                </div>
            </div>

            <!-- Two Columns: Customer Info & Status Updater -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Customer Details (2 Cols) -->
                <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                        Informasi Pemesan
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Nama Pemesan</span>
                            <span class="font-bold text-slate-900">{{ order.customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Nomor WhatsApp</span>
                            <span class="font-bold text-slate-900 font-mono">{{ order.customer_phone }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Alamat Email</span>
                            <span class="text-slate-700">{{ order.customer_email || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Waktu Pemesanan</span>
                            <span class="text-slate-700">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-xs text-slate-400 font-medium block">Alamat Pengiriman</span>
                            <span class="text-slate-800 leading-relaxed font-medium">{{ order.customer_address }}</span>
                        </div>
                        <div v-if="order.notes" class="sm:col-span-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs">
                            <strong>Catatan Tambahan:</strong> {{ order.notes }}
                        </div>
                    </div>
                </div>

                <!-- Status Updater Card (1 Col) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                        Perbarui Status
                    </h3>

                    <form @submit.prevent="updateStatus" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Status Pesanan
                            </label>
                            <CustomDropdown
                                v-model="statusForm.status"
                                :options="statusOptions"
                                :full-width="true"
                                button-class="w-full text-sm font-semibold rounded-xl py-2.5"
                            />
                        </div>

                        <Button
                            type="submit"
                            variant="primary"
                            size="md"
                            class="w-full justify-center py-2.5 text-sm font-bold rounded-xl shadow-xs"
                            :loading="statusForm.processing"
                            :disabled="statusForm.processing"
                        >
                            Simpan Perubahan
                        </Button>
                    </form>

                    <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">
                        Tips: Setelah mengubah status, klik tombol <strong>"Kirim Invoice ke WA"</strong> di atas untuk memberi update langsung ke pembeli.
                    </div>
                </div>
            </div>

            <!-- Ordered Items Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Rincian Barang yang Dipesan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-6">Produk</th>
                                <th class="py-3 px-6 text-right">Harga Satuan</th>
                                <th class="py-3 px-6 text-center">Qty</th>
                                <th class="py-3 px-6 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    {{ item.product_name }}
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-slate-600">
                                    {{ formatRupiah(item.price) }}
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-slate-800">
                                    {{ item.quantity }}
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900">
                                    {{ formatRupiah(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-emerald-50/40 border-t-2 border-slate-200 text-base font-extrabold">
                                <td colspan="3" class="py-4 px-6 text-slate-800 text-right">
                                    Total Tagihan:
                                </td>
                                <td class="py-4 px-6 text-right text-emerald-800 text-xl font-extrabold">
                                    {{ formatRupiah(order.total_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmDeleteModal" @close="confirmDeleteModal = false" maxWidth="md">
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
                    <span class="font-bold text-slate-900">#{{ order.order_number }}</span>?
                    Seluruh rincian pesanan yang dibatalkan ini akan dihapus permanen dari sistem.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="confirmDeleteModal = false" :disabled="deleting">
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

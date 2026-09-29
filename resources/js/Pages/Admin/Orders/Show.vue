<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import Button from '../../../Components/Button.vue';
import Input from '../../../Components/Input.vue';
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
    waBillingUrl: {
        type: String,
        default: '',
    },
    waShippingUrl: {
        type: String,
        default: '',
    },
    trackingUrl: {
        type: String,
        default: '',
    },
    store: {
        type: Object,
        default: () => ({}),
    },
});

const statusForm = useForm({
    status: props.order.status === 'confirmed' ? 'processing' : props.order.status,
    shipping_cost: Number(props.order.shipping_cost) || 0,
    courier: props.order.courier || '',
    tracking_number: props.order.tracking_number || '',
});

const showManualStatus = ref(false);

const statusOptions = [
    { value: 'pending', label: '1. Pending (Cek Ongkir)' },
    { value: 'payment_pending', label: '2. Menunggu Pembayaran' },
    { value: 'processing', label: '3. Diproses & Dikemas' },
    { value: 'shipped', label: '4. Sedang Dikirim' },
    { value: 'completed', label: '5. Selesai' },
    { value: 'cancelled', label: '6. Dibatalkan' },
];

const courierPresets = ['J&T Express', 'JNE', 'SiCepat', 'Anteraja', 'Pos Indonesia', 'GoSend / Grab', 'Kurir Toko'];

const selectCourierPreset = (preset) => {
    statusForm.courier = preset;
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

const calculatedGrandTotal = computed(() => {
    return (Number(props.order.total_amount) || 0) + (Number(statusForm.shipping_cost) || 0);
});

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending':
            return { variant: 'warning', label: 'Pending (Cek Ongkir)' };
        case 'payment_pending':
            return { variant: 'warning', label: 'Menunggu Pembayaran' };
        case 'processing':
        case 'confirmed':
            return { variant: 'info', label: 'Diproses & Dikemas' };
        case 'shipped':
            return { variant: 'purple', label: 'Sedang Dikirim' };
        case 'completed':
            return { variant: 'success', label: 'Selesai' };
        case 'cancelled':
            return { variant: 'danger', label: 'Dibatalkan' };
        default:
            return { variant: 'warning', label: 'Pending' };
    }
};

// Clean formatted phone for WA link
const customerCleanPhone = computed(() => {
    const raw = String(props.order.customer_phone || '').replace(/[^0-9]/g, '');
    return raw.startsWith('0') ? '62' + raw.slice(1) : raw;
});

// Dynamic WhatsApp message with live input values
const liveWaBillingUrl = computed(() => {
    const storeName = props.store?.name || 'EcoStore';
    const subtotalFmt = Number(props.order.total_amount).toLocaleString('id-ID');
    const shippingFmt = Number(statusForm.shipping_cost || 0).toLocaleString('id-ID');
    const grandTotalFmt = calculatedGrandTotal.value.toLocaleString('id-ID');
    const courierName = statusForm.courier || 'Kurir Rekanan';
    const trackingUrl = props.trackingUrl || `${window.location.origin}/orders/track/${props.order.order_number}`;

    const bankText = props.store?.bank_account ? `💳 *Silakan Transfer ke Rekening Toko:*\n${props.store.bank_account}\n\n` : '';

    const text = `Halo Kak ${props.order.customer_name}, berikut rincian tagihan pesanan Anda di ${storeName} (*#${props.order.order_number}*):\n\n` +
        `📦 *Rincian Biaya:*\n` +
        `- Subtotal Barang : Rp ${subtotalFmt}\n` +
        `- Ongkos Kirim (${courierName}) : Rp ${shippingFmt}\n` +
        `- *Total Pembayaran:* *Rp ${grandTotalFmt}*\n\n` +
        bankText +
        `🔍 *Lacak Status Pesanan:* ${trackingUrl}\n\n` +
        `Mohon kirimkan bukti transfer ke WhatsApp ini jika sudah melakukan pembayaran ya Kak. Terima kasih!`;

    return `https://api.whatsapp.com/send?phone=${customerCleanPhone.value}&text=${encodeURIComponent(text)}`;
});

const liveWaShippingUrl = computed(() => {
    const storeName = props.store?.name || 'EcoStore';
    const courierName = statusForm.courier || props.order.courier || 'Kurir Ekspedisi';
    const trackingNum = statusForm.tracking_number || props.order.tracking_number || '-';
    const trackingUrl = props.trackingUrl || `${window.location.origin}/orders/track/${props.order.order_number}`;

    const text = `Halo Kak ${props.order.customer_name}, kabar gembira! Paket pesanan Anda (*#${props.order.order_number}*) dari ${storeName} sudah diserahkan ke kurir pengiriman!\n\n` +
        `🚚 *Ekspedisi:* ${courierName}\n` +
        `🔖 *No. Resi:* *${trackingNum}*\n\n` +
        `🔍 *Lacak Status Pesanan Anda:* ${trackingUrl}\n\n` +
        `Terima kasih atas pesanannya! Ditunggu barangnya sampai dengan selamat ya Kak.`;

    return `https://api.whatsapp.com/send?phone=${customerCleanPhone.value}&text=${encodeURIComponent(text)}`;
});

// Step Action Handlers
const submitSaveOnly = () => {
    statusForm.put(`/admin/orders/${props.order.id}/status`, {
        preserveScroll: true,
    });
};

// 1. Simpan Ongkir & Kirim Tagihan ke WA (mengubah status ke payment_pending)
const saveAndSendBilling = () => {
    statusForm.status = 'payment_pending';
    statusForm.put(`/admin/orders/${props.order.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            window.open(liveWaBillingUrl.value, '_blank');
        },
    });
};

// 2. Tandai Uang Masuk & Mulai Kemas (mengubah status ke processing)
const markAsProcessing = () => {
    statusForm.status = 'processing';
    statusForm.put(`/admin/orders/${props.order.id}/status`, {
        preserveScroll: true,
    });
};

// 3. Simpan Resi & Tandai Sedang Dikirim (mengubah status ke shipped)
const saveAndSendShipping = () => {
    if (!statusForm.tracking_number) {
        alert('Silakan isi Nomor Resi Pengiriman terlebih dahulu.');
        return;
    }
    statusForm.status = 'shipped';
    statusForm.put(`/admin/orders/${props.order.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            window.open(liveWaShippingUrl.value, '_blank');
        },
    });
};

// 4. Tandai Selesai (mengubah status ke completed)
const markAsCompleted = () => {
    statusForm.status = 'completed';
    statusForm.put(`/admin/orders/${props.order.id}/status`, {
        preserveScroll: true,
    });
};

// Delete confirmation modal state
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
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <Link
                        href="/admin/orders"
                        class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors inline-flex items-center gap-1 mb-2"
                    >
                        &larr; Kembali ke Rekap Pesanan
                    </Link>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-mono font-extrabold text-slate-900 tracking-tight">
                            #{{ order.order_number }}
                        </h1>
                        <Badge :variant="getStatusBadge(order.status).variant">
                            {{ getStatusBadge(order.status).label }}
                        </Badge>
                    </div>
                </div>

                <!-- Action Buttons: Secondary & PDF -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Link Lacak Pesanan Publik -->
                    <a
                        :href="`/orders/track/${order.order_number}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition-colors"
                        title="Buka halaman pelacakan publik (tanpa login)"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Halaman Lacak</span>
                    </a>

                    <!-- Download PDF -->
                    <a
                        :href="`/admin/orders/${order.id}/pdf`"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition-colors"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh PDF</span>
                    </a>

                    <!-- Hapus Pesanan (Hanya untuk pesanan Dibatalkan) -->
                    <button
                        v-if="order.status === 'cancelled'"
                        type="button"
                        @click="confirmDeleteModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>

            <!-- Two Columns: Customer Info & Alur Kelola Pesanan -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Customer Details (7 Cols on lg) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                                <span>Informasi Pemesan &amp; Alamat Tujuan</span>
                                <a
                                    :href="`https://api.whatsapp.com/send?phone=${customerCleanPhone}`"
                                    target="_blank"
                                    class="text-xs font-bold text-emerald-700 hover:underline inline-flex items-center gap-1 normal-case tracking-normal"
                                >
                                    <span>Chat WA Pembeli &rarr;</span>
                                </a>
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
                                    <span class="text-xs text-slate-400 font-medium block">Alamat Lengkap Pengiriman</span>
                                    <div class="text-slate-800 leading-relaxed font-semibold bg-slate-50 p-3 rounded-xl border border-slate-200/80 mt-1">
                                        {{ order.customer_address }}
                                    </div>
                                </div>
                                <div v-if="order.notes" class="sm:col-span-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs">
                                    <strong>Catatan Tambahan Pembeli:</strong> {{ order.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Rekening Pembayaran Toko Info Banner -->
                        <div v-if="store?.bank_account" class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                            <span class="font-bold text-slate-800 block mb-1">Rekening Pembayaran Toko (Dikirimkan ke Pelanggan):</span>
                            <pre class="font-sans text-slate-600 whitespace-pre-wrap leading-relaxed">{{ store.bank_account }}</pre>
                        </div>
                    </div>

                    <!-- Ordered Items Preview in Left Column -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                            Rincian Barang yang Dipesan ({{ order.items?.length || 0 }} Item)
                        </h3>
                        <div class="divide-y divide-slate-100 text-xs">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="py-2.5 flex items-center justify-between gap-3"
                            >
                                <div>
                                    <div class="font-bold text-slate-900">{{ item.product_name }}</div>
                                    <div class="text-slate-400">{{ item.quantity }} &times; {{ formatRupiah(item.price) }}</div>
                                </div>
                                <div class="font-bold text-slate-900">{{ formatRupiah(item.subtotal) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ALUR LOGIS & PENGIRIMAN (5 Cols on lg) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                                Alur Proses Pesanan
                            </h3>
                            <button
                                type="button"
                                @click="showManualStatus = !showManualStatus"
                                class="text-[11px] font-bold text-slate-500 hover:text-slate-800 underline cursor-pointer"
                            >
                                {{ showManualStatus ? 'Sembunyikan Pilihan Manual' : 'Pilihan Status Manual' }}
                            </button>
                        </div>

                        <!-- Manual Status Dropdown (Collapsible jika ingin override paksa) -->
                        <div v-if="showManualStatus" class="p-3 bg-slate-50 rounded-xl border border-slate-200 mb-5">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Ganti Status Secara Manual:
                            </label>
                            <CustomDropdown
                                v-model="statusForm.status"
                                :options="statusOptions"
                                :full-width="true"
                                :show-dot="true"
                            >
                                <template #icon>
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                            </CustomDropdown>
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                class="w-full mt-2 justify-center font-bold"
                                :loading="statusForm.processing"
                                @click="submitSaveOnly"
                            >
                                Terapkan Status Baru
                            </Button>
                        </div>

                        <!-- KONDISIONAL TAHAP 1: Pending (Cek Ongkir) -->
                        <div v-if="order.status === 'pending'" class="space-y-4">
                            <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                                <div class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                    <span>Langkah 1: Cek Ongkir &amp; Terbitkan Tagihan</span>
                                </div>
                                <p class="text-amber-800 mt-1 leading-relaxed">
                                    Pelanggan baru saja checkout dan meminta estimasi ongkir ke alamat tujuannya. Cek biaya ke kurir, masukkan ongkir di bawah, lalu kirim rincian tagihan beserta nomor rekening ke WhatsApp pelanggan.
                                </p>
                            </div>

                            <!-- Input Ongkos Kirim -->
                            <div>
                                <Input
                                    id="shipping_cost"
                                    label="Ongkos Kirim (Rp)"
                                    is-currency
                                    v-model="statusForm.shipping_cost"
                                    :error="statusForm.errors.shipping_cost"
                                    placeholder="cth: 15.000"
                                    hint="Masukkan nominal ongkos kirim hasil cek kurir."
                                />
                            </div>

                            <!-- Input Ekspedisi / Kurir -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Rencana Jasa Kurir / Ekspedisi
                                </label>
                                <Input
                                    id="courier"
                                    v-model="statusForm.courier"
                                    :error="statusForm.errors.courier"
                                    placeholder="cth: J&T Express / JNE"
                                />
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <button
                                        v-for="preset in courierPresets"
                                        :key="preset"
                                        type="button"
                                        @click="selectCourierPreset(preset)"
                                        class="text-[10px] px-2 py-0.5 rounded-md font-semibold transition-colors cursor-pointer"
                                        :class="statusForm.courier === preset ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                                    >
                                        {{ preset }}
                                    </button>
                                </div>
                            </div>

                            <!-- Catatan Resi (Belum diminta di tahap ini) -->
                            <div class="p-3 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-[11px] text-slate-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Nomor Resi akan diisi nanti setelah pelanggan membayar dan paket siap diantar ke kurir.</span>
                            </div>

                            <!-- Tombol Utama: Simpan & Kirim Tagihan ke WA -->
                            <div class="pt-2 space-y-2">
                                <button
                                    type="button"
                                    @click="saveAndSendBilling"
                                    :disabled="statusForm.processing"
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.35.491 1.199.534 1.286.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.086-.178.18-.077.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                    </svg>
                                    <span>Simpan Ongkir &amp; Kirim Tagihan ke WA</span>
                                </button>
                                <button
                                    type="button"
                                    @click="submitSaveOnly"
                                    :disabled="statusForm.processing"
                                    class="w-full py-2 px-3 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                                >
                                    Simpan Ongkir Saja
                                </button>
                            </div>
                        </div>

                        <!-- KONDISIONAL TAHAP 2: Menunggu Pembayaran (payment_pending) -->
                        <div v-else-if="order.status === 'payment_pending'" class="space-y-4">
                            <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                                <div class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Langkah 2: Menunggu Pembayaran Pelanggan</span>
                                </div>
                                <p class="text-amber-800 mt-1 leading-relaxed">
                                    Tagihan total sebesar <strong>{{ formatRupiah(calculatedGrandTotal) }}</strong> telah diterbitkan. Silakan periksa mutasi bank Anda saat pembeli mengirimkan bukti transfer.
                                </p>
                            </div>

                            <!-- Tombol Utama: Uang Masuk -> Mulai Kemas -->
                            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 space-y-2">
                                <span class="text-xs font-bold text-emerald-950 block">Uang transfer sudah masuk?</span>
                                <button
                                    type="button"
                                    @click="markAsProcessing"
                                    :disabled="statusForm.processing"
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>✅ Verifikasi Pembayaran &amp; Mulai Kemas</span>
                                </button>
                            </div>

                            <!-- Opsi kirim ulang tagihan / sesuaikan ongkir -->
                            <div class="pt-2 border-t border-slate-100">
                                <a
                                    :href="liveWaBillingUrl"
                                    target="_blank"
                                    class="w-full py-2 px-3 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors flex items-center justify-center gap-1.5"
                                >
                                    <span>Kirim Ulang Rincian Tagihan ke WA</span>
                                </a>
                            </div>
                        </div>

                        <!-- KONDISIONAL TAHAP 3: Diproses & Dikemas (processing / confirmed) -->
                        <div v-else-if="order.status === 'processing' || order.status === 'confirmed'" class="space-y-4">
                            <div class="p-3.5 bg-sky-50 rounded-xl border border-sky-200 text-xs">
                                <div class="font-bold text-sky-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                                    <span>Langkah 3: Pengemasan &amp; Input No. Resi</span>
                                </div>
                                <p class="text-sky-800 mt-1 leading-relaxed">
                                    Pembayaran telah terverifikasi. Kemas pesanan dan serahkan paket ke kurir. Setelah mendapat bukti resi, input nomor resi di bawah ini.
                                </p>
                            </div>

                            <!-- Kurir Selector -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Kurir yang Digunakan
                                </label>
                                <Input
                                    id="courier"
                                    v-model="statusForm.courier"
                                    :error="statusForm.errors.courier"
                                    placeholder="cth: J&T Express / JNE"
                                />
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <button
                                        v-for="preset in courierPresets"
                                        :key="preset"
                                        type="button"
                                        @click="selectCourierPreset(preset)"
                                        class="text-[10px] px-2 py-0.5 rounded-md font-semibold transition-colors cursor-pointer"
                                        :class="statusForm.courier === preset ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                                    >
                                        {{ preset }}
                                    </button>
                                </div>
                            </div>

                            <!-- INPUT NOMOR RESI (MUNCUL AKTIF DI TAHAP INI) -->
                            <div class="p-4 bg-purple-50/80 rounded-xl border border-purple-200 space-y-2">
                                <label class="block text-xs font-bold text-purple-950">
                                    Nomor Resi Pengiriman:
                                </label>
                                <Input
                                    id="tracking_number"
                                    v-model="statusForm.tracking_number"
                                    :error="statusForm.errors.tracking_number"
                                    placeholder="cth: JT1234567890 / JNE0987654"
                                    class="font-mono font-bold"
                                />
                                <span class="text-[11px] text-purple-800 block">
                                    Nomor resi dari kurir akan otomatis tampil di halaman lacak pesanan pembeli.
                                </span>
                            </div>

                            <!-- Tombol Utama: Simpan Resi & Tandai Sedang Dikirim -->
                            <div class="pt-2 space-y-2">
                                <button
                                    type="button"
                                    @click="saveAndSendShipping"
                                    :disabled="statusForm.processing"
                                    class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    <span>Simpan Resi &amp; Kirim Info ke WA Pembeli</span>
                                </button>
                                <button
                                    type="button"
                                    @click="submitSaveOnly"
                                    :disabled="statusForm.processing"
                                    class="w-full py-2 px-3 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                                >
                                    Simpan Perubahan Saja
                                </button>
                            </div>
                        </div>

                        <!-- KONDISIONAL TAHAP 4: Sedang Dikirim (shipped) -->
                        <div v-else-if="order.status === 'shipped'" class="space-y-4">
                            <div class="p-3.5 bg-purple-50 rounded-xl border border-purple-200 text-xs">
                                <div class="font-bold text-purple-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    <span>Langkah 4: Paket Sedang Diantar Kurir</span>
                                </div>
                                <div class="mt-2 text-slate-800">
                                    Kurir: <strong>{{ order.courier || '-' }}</strong><br>
                                    No. Resi: <strong class="font-mono text-purple-950">{{ order.tracking_number || '-' }}</strong>
                                </div>
                            </div>

                            <!-- Tombol Kirim Resi ke WA & Selesai -->
                            <div class="space-y-2">
                                <a
                                    :href="liveWaShippingUrl"
                                    target="_blank"
                                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors"
                                >
                                    <span>Kirim Ulang Info Resi ke WA</span>
                                </a>

                                <button
                                    type="button"
                                    @click="markAsCompleted"
                                    :disabled="statusForm.processing"
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                                >
                                    <span>✅ Tandai Pesanan Selesai (Diterima)</span>
                                </button>
                            </div>
                        </div>

                        <!-- KONDISIONAL TAHAP 5: Selesai (completed) -->
                        <div v-else-if="order.status === 'completed'" class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 font-bold">
                                ✓
                            </div>
                            <h4 class="font-bold text-emerald-950 text-sm">Pesanan Selesai</h4>
                            <p class="text-xs text-emerald-800 mt-1">
                                Transaksi ini telah tuntas dan paket telah diterima oleh pelanggan.
                            </p>
                        </div>

                        <!-- KONDISIONAL TAHAP 6: Dibatalkan (cancelled) -->
                        <div v-else class="p-4 bg-rose-50 rounded-xl border border-rose-200 text-center">
                            <h4 class="font-bold text-rose-950 text-sm">Pesanan Dibatalkan</h4>
                            <p class="text-xs text-rose-800 mt-1">
                                Pesanan ini telah dibatalkan. Anda dapat menghapus data pesanan ini menggunakan tombol Hapus di pojok kanan atas.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Biaya Tagihan -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Total Tagihan</h4>
                        <div class="text-xs space-y-2 text-slate-600">
                            <div class="flex justify-between">
                                <span>Subtotal Barang:</span>
                                <span class="font-bold text-slate-900">{{ formatRupiah(order.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Ongkos Kirim {{ statusForm.courier ? `(${statusForm.courier})` : '' }}:</span>
                                <span class="font-bold text-slate-900">{{ formatRupiah(statusForm.shipping_cost) }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                                <span>Total Tagihan:</span>
                                <span class="text-emerald-700 text-base">{{ formatRupiah(calculatedGrandTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmDeleteModal" @close="confirmDeleteModal = false" maxWidth="md">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 mb-4 mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-bold text-slate-900">Hapus Pesanan Ini?</h3>
                    <p class="text-xs text-slate-500 mt-2">
                        Pesanan #{{ order.order_number }} yang berstatus <strong>Dibatalkan</strong> akan dihapus permanen dari sistem.
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="confirmDeleteModal = false"
                        :disabled="deleting"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="danger"
                        size="sm"
                        :loading="deleting"
                        @click="confirmDelete"
                    >
                        Hapus Pesanan
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

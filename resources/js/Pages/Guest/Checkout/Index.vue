<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '../../../Layouts/ShopLayout.vue';
import Input from '../../../Components/Input.vue';
import Textarea from '../../../Components/Textarea.vue';
import Button from '../../../Components/Button.vue';
import { useCart } from '../../../Stores/cart';

const cart = useCart();

const form = ref({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    customer_address: '',
    notes: '',
});

const errors = ref({});
const isSubmitting = ref(false);
const orderPlaced = ref(null);

onMounted(() => {
    if (cart.items.value.length === 0 && !orderPlaced.value) {
        router.visit('/products');
    }
});

const formatRupiah = (value) => {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
};

const handleCheckout = async () => {
    errors.value = {};

    if (!form.value.customer_name) {
        errors.value.customer_name = 'Nama lengkap wajib diisi.';
    }
    if (!form.value.customer_phone) {
        errors.value.customer_phone = 'Nomor WhatsApp wajib diisi.';
    }
    if (!form.value.customer_address) {
        errors.value.customer_address = 'Alamat pengiriman wajib diisi.';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    isSubmitting.value = true;

    try {
        const payload = {
            customer_name: form.value.customer_name,
            customer_phone: form.value.customer_phone,
            customer_email: form.value.customer_email || null,
            customer_address: form.value.customer_address,
            notes: form.value.notes || null,
            items: cart.items.value.map((item) => ({
                product_id: item.id,
                quantity: item.quantity,
            })),
        };

        const response = await fetch('/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            orderPlaced.value = data;
            cart.clearCart();
            // Redirect to WhatsApp
            window.location.href = data.whatsapp_url;
        } else {
            if (data.errors) {
                errors.value = data.errors;
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses pesanan.');
            }
        }
    } catch (e) {
        console.error('Checkout error:', e);
        alert('Gagal mengirim pesanan. Silakan coba kembali.');
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head :title="`Pengisian Alamat &amp; Checkout - ${$page.props.store?.name || 'EcoStore'}`" />

    <ShopLayout>
        <!-- Page Header -->
        <div class="bg-white border-b border-slate-200/80 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-2">
                    <Link href="/" class="hover:text-emerald-600">Home</Link>
                    <span>/</span>
                    <Link href="/cart" class="hover:text-emerald-600">Keranjang</Link>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Checkout</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Pengisian Alamat &amp; Pemesanan
                </h1>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Order Success Screen -->
            <div v-if="orderPlaced" class="max-w-2xl mx-auto bg-white rounded-3xl p-8 border border-emerald-200 shadow-xl text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900">Pesanan Berhasil Dicatat!</h2>
                <p class="text-sm font-mono text-emerald-800 font-bold mt-1">
                    No. Invoice: #{{ orderPlaced.order.order_number }}
                </p>
                <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                    Data pesanan Anda telah tersimpan di sistem kami. Chat WhatsApp otomatis diarahkan ke admin toko (+62 898-5454-555).
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        :href="orderPlaced.whatsapp_url"
                        class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition-colors"
                    >
                        Buka WhatsApp Sekarang &rarr;
                    </a>
                    <Link
                        href="/products"
                        class="w-full sm:w-auto px-6 py-3.5 bg-white border border-slate-300 text-slate-700 font-semibold text-sm rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Kembali Belanja
                    </Link>
                </div>
            </div>

            <!-- Checkout Form & Summary Grid -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                <!-- Customer Details Form (2 Cols) -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
                    <div class="mb-6 pb-4 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900">Data Pemesan &amp; Alamat Pengiriman</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Isi data diri Anda agar pesanan dan pengiriman dapat diproses dengan tepat.
                        </p>
                    </div>

                    <form @submit.prevent="handleCheckout" class="space-y-5">
                        <!-- Nama Lengkap -->
                        <Input
                            id="customer_name"
                            label="Nama Lengkap"
                            v-model="form.customer_name"
                            :error="errors.customer_name"
                            placeholder="Contoh: Budi Santoso"
                            required
                        />

                        <!-- Nomor WhatsApp -->
                        <Input
                            id="customer_phone"
                            label="Nomor WhatsApp / HP Aktif"
                            type="tel"
                            v-model="form.customer_phone"
                            :error="errors.customer_phone"
                            placeholder="Contoh: 08123456789"
                            hint="Nomor ini akan dihubungi untuk konfirmasi resi & pengiriman."
                            required
                        />

                        <!-- Email (Opsional) -->
                        <Input
                            id="customer_email"
                            label="Alamat Email (Opsional)"
                            type="email"
                            v-model="form.customer_email"
                            :error="errors.customer_email"
                            placeholder="nama@email.com"
                        />

                        <!-- Alamat Lengkap -->
                        <Textarea
                            id="customer_address"
                            label="Alamat Lengkap Pengiriman"
                            v-model="form.customer_address"
                            :error="errors.customer_address"
                            placeholder="Tuliskan nama jalan, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos..."
                            :rows="4"
                            required
                        />

                        <!-- Catatan Pesanan -->
                        <Textarea
                            id="notes"
                            label="Catatan Khusus untuk Toko (Opsional)"
                            v-model="form.notes"
                            :error="errors.notes"
                            placeholder="Contoh: Titip ke satpam jika sedang tidak di rumah..."
                            :rows="2"
                        />

                        <div class="pt-6 border-t border-slate-100">
                            <Button
                                type="submit"
                                variant="primary"
                                size="lg"
                                :loading="isSubmitting"
                                :disabled="isSubmitting"
                                class="w-full text-base"
                            >
                                <svg class="w-5 h-5 -ml-1 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.35.491 1.199.534 1.286.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.302c-.087.086-.178.18-.077.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                </svg>
                                Kirim Pesanan via WhatsApp (+62 898-5454-555)
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Order Review Column (1 Col) -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                        Rincian Pesanan ({{ cart.totalCount.value }})
                    </h3>

                    <!-- Mini item list -->
                    <div class="max-h-72 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                        <div
                            v-for="item in cart.items.value"
                            :key="item.id"
                            class="pt-3 first:pt-0 flex items-center justify-between text-xs"
                        >
                            <div class="min-w-0 pr-3">
                                <p class="font-bold text-slate-800 truncate">{{ item.name }}</p>
                                <p class="text-slate-400 mt-0.5">{{ item.quantity }}x @ {{ formatRupiah(item.price) }}</p>
                            </div>
                            <span class="font-extrabold text-slate-900 shrink-0">
                                {{ formatRupiah(item.price * item.quantity) }}
                            </span>
                        </div>
                    </div>

                    <!-- Grand Total -->
                    <div class="pt-4 border-t-2 border-slate-100 flex items-baseline justify-between">
                        <span class="text-sm font-bold text-slate-900">Total Pembayaran</span>
                        <span class="text-xl font-extrabold text-emerald-700">
                            {{ formatRupiah(cart.totalAmount.value) }}
                        </span>
                    </div>

                    <div class="p-3 bg-emerald-50/80 rounded-xl text-xs text-emerald-800 border border-emerald-200/60 leading-relaxed">
                        ✓ Setelah klik order, Anda langsung terhubung ke chat admin di WhatsApp untuk konfirmasi nomor rekening dan resi pengiriman.
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

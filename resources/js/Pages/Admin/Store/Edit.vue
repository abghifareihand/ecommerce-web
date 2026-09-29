<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Input from '../../../Components/Input.vue';
import Textarea from '../../../Components/Textarea.vue';
import Button from '../../../Components/Button.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.store.name || '',
    tagline: props.store.tagline || '',
    phone: props.store.phone || '',
    email: props.store.email || '',
    address: props.store.address || '',
    description: props.store.description || '',
    logo: null,
});

const logoPreview = ref(props.store.logo_url || null);
const logoFileInput = ref(null);
const showDeleteLogoModal = ref(false);
const removingLogo = ref(false);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            logoPreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    logoFileInput.value?.click();
};

const openDeleteLogoModal = () => {
    showDeleteLogoModal.value = true;
};

const confirmRemoveLogo = () => {
    removingLogo.value = true;
    router.delete('/admin/store/logo', {
        preserveScroll: true,
        onSuccess: (page) => {
            logoPreview.value = page.props.store?.logo_url || null;
            form.logo = null;
            if (logoFileInput.value) logoFileInput.value.value = '';
            showDeleteLogoModal.value = false;
        },
        onFinish: () => {
            removingLogo.value = false;
        },
    });
};

const submit = () => {
    form.post('/admin/store', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Toko - Admin" />

    <AdminLayout title="Pengaturan Toko">
        <div class="w-full space-y-6">
            <!-- Header explanation -->
            <div>
                <h2 class="text-xl font-bold text-slate-900">Pengaturan Informasi Toko</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola identitas toko, logo brand, nomor WhatsApp pemesanan, dan alamat fisik. Data ini terintegrasi langsung ke Landing Page, alur Checkout WhatsApp, dan Invoice PDF.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Card 1: Identitas & Kontak Toko -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                    <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Identitas &amp; Kontak Toko</h3>
                            <p class="text-xs text-slate-400">Nama toko, logo resmi, dan nomor kontak untuk interaksi dengan pelanggan.</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <!-- Logo Upload Section -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Logo Toko
                            </label>
                            <div class="mt-2 flex items-center gap-x-6">
                                <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-white border border-slate-200/90 shadow-sm flex items-center justify-center">
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Preview Logo Toko"
                                        class="h-full w-full object-cover object-center"
                                    />
                                    <svg
                                        v-else
                                        class="h-10 w-10 text-emerald-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>

                                <div>
                                    <input
                                        ref="logoFileInput"
                                        type="file"
                                        accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                                        @change="handleLogoChange"
                                        class="hidden"
                                    />
                                    <div class="flex flex-wrap items-center gap-2">
                                        <Button type="button" variant="secondary" size="sm" @click="triggerFileInput">
                                            <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Pilih Logo
                                        </Button>

                                        <Button
                                            v-if="store.logo || form.logo"
                                            type="button"
                                            variant="danger"
                                            size="sm"
                                            @click="openDeleteLogoModal"
                                            :disabled="removingLogo"
                                        >
                                            Hapus Logo
                                        </Button>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">Mendukung format PNG, JPG, WebP, atau SVG hingga 2MB.</p>
                                    <p v-if="form.errors.logo" class="mt-1 text-xs text-rose-600 font-medium">
                                        {{ form.errors.logo }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Name & Tagline Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <Input
                                id="store-name"
                                label="Nama Toko"
                                v-model="form.name"
                                :error="form.errors.name"
                                placeholder="cth: EcoStore"
                                required
                            />

                            <Input
                                id="store-tagline"
                                label="Slogan / Tagline"
                                v-model="form.tagline"
                                :error="form.errors.tagline"
                                placeholder="cth: Produk Kriya Berkualitas Ramah Lingkungan"
                            />
                        </div>

                        <!-- Phone & Email Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <Input
                                id="store-phone"
                                label="Nomor WhatsApp Pemesanan"
                                v-model="form.phone"
                                :error="form.errors.phone"
                                placeholder="cth: 08985454555 atau 628985454555"
                                hint="Nomor ini akan menjadi tujuan chat WhatsApp saat pelanggan melakukan checkout belanjaan."
                                required
                            />

                            <Input
                                id="store-email"
                                label="Email Toko"
                                type="email"
                                v-model="form.email"
                                :error="form.errors.email"
                                placeholder="cth: kontak@ecostore.com"
                            />
                        </div>
                    </div>
                </div>

                <!-- Card 2: Alamat & Cerita Toko -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                    <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Alamat &amp; Cerita Toko</h3>
                            <p class="text-xs text-slate-400">Informasi lokasi fisik dan deskripsi profil usaha Anda.</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <Textarea
                                id="store-address"
                                label="Alamat Lengkap Toko"
                                v-model="form.address"
                                :error="form.errors.address"
                                placeholder="Tuliskan alamat toko, kota, kode pos..."
                                :rows="3"
                            />
                            <p class="mt-1 text-xs text-slate-500">Alamat ini tercetak pada bagian kop invoice resmi dan kontak footer.</p>
                        </div>

                        <div>
                            <Textarea
                                id="store-description"
                                label="Deskripsi / Cerita Toko"
                                v-model="form.description"
                                :error="form.errors.description"
                                placeholder="Jelaskan sejarah singkat, keunggulan, atau nilai yang diusung toko Anda..."
                                :rows="4"
                            />
                            <p class="mt-1 text-xs text-slate-500">Ditampilkan pada bagian 'Tentang Toko' di Landing Page pengunjung.</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Pratinjau Tampilan Toko -->
                <div class="bg-gradient-to-br from-emerald-50/70 to-teal-50/50 rounded-2xl border border-emerald-100 p-6 sm:p-7">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                            Pratinjau Tampilan Toko Bagi Pelanggan
                        </span>
                        <span class="text-[11px] font-bold text-emerald-600 bg-white/80 px-2.5 py-1 rounded-full border border-emerald-200">
                            Live Preview
                        </span>
                    </div>

                    <div class="bg-white rounded-xl p-5 border border-emerald-200/70 shadow-xs space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="relative w-12 h-12 rounded-lg overflow-hidden border border-slate-200/80 bg-white shadow-xs shrink-0 flex items-center justify-center">
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Preview Logo"
                                        class="w-full h-full object-cover object-center"
                                    />
                                    <div v-else class="w-full h-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xl">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-extrabold text-slate-900">{{ form.name || 'Nama Toko' }}</h4>
                                    <p class="text-xs text-emerald-700 font-medium mt-0.5">{{ form.tagline || 'Slogan toko belum diisi' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                    WA: {{ form.phone || '08xxxxxxxx' }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-3 text-xs text-slate-600 space-y-1">
                            <p v-if="form.address"><strong>Alamat:</strong> {{ form.address }}</p>
                            <p v-if="form.email"><strong>Email:</strong> {{ form.email }}</p>
                            <p v-if="form.description" class="text-slate-500 italic mt-2">"{{ form.description }}"</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        Simpan Pengaturan Toko
                    </Button>
                </div>
            </form>
        </div>

        <!-- Custom Delete Logo Confirmation Modal -->
        <Modal :show="showDeleteLogoModal" @close="showDeleteLogoModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus Logo Toko?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tindakan ini akan mengembalikan ke logo default.</p>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus logo toko ini? Website, landing page, dan invoice cetak akan kembali menggunakan ikon standar toko sampai Anda mengunggah logo baru.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="showDeleteLogoModal = false" :disabled="removingLogo">
                        Batal
                    </Button>
                    <Button variant="danger" @click="confirmRemoveLogo" :loading="removingLogo" :disabled="removingLogo">
                        Hapus Logo
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

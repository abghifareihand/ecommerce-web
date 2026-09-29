<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import Button from '../../../Components/Button.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    banners: {
        type: Array,
        required: true,
    },
});

const bannerToDelete = ref(null);
const confirmDeleteModal = ref(false);
const deletingBanner = ref(false);

const openDeleteModal = (banner) => {
    bannerToDelete.value = banner;
    confirmDeleteModal.value = true;
};

const closeDeleteModal = () => {
    bannerToDelete.value = null;
    confirmDeleteModal.value = false;
};

const confirmDeleteBanner = () => {
    if (!bannerToDelete.value) return;
    deletingBanner.value = true;
    router.delete(`/admin/banners/${bannerToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deletingBanner.value = false;
        },
    });
};

const toggleStatus = (banner) => {
    router.patch(`/admin/banners/${banner.id}/toggle`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kelola Banner Promo - Admin" />

    <AdminLayout title="Kelola Banner Promo">
        <div class="space-y-6">
            <!-- Header bar with Create button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        Banner Promo &amp; Informasi
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Banner ini ditampilkan di bagian atas halaman belanja katalog pengunjung (hanya tampil jika tidak ada pencarian aktif).
                    </p>
                </div>

                <Link href="/admin/banners/create">
                    <Button
                        type="button"
                        variant="primary"
                        class="gap-2 shadow-xs shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Banner</span>
                    </Button>
                </Link>
            </div>

            <!-- Banners Grid / List -->
            <div v-if="banners && banners.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="banner in banners"
                    :key="banner.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col transition-all hover:shadow-md"
                >
                    <!-- Banner Image Container -->
                    <div class="relative aspect-21/9 w-full bg-slate-900 overflow-hidden group">
                        <img
                            :src="banner.image_url"
                            :alt="banner.title || 'Banner'"
                            class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                        />
                        <div class="absolute inset-0 bg-linear-to-t from-black/75 via-black/20 to-transparent flex flex-col justify-end p-5">
                            <span
                                v-if="banner.subtitle"
                                class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider mb-1"
                            >
                                {{ banner.subtitle }}
                            </span>
                            <h3 class="text-lg font-bold text-white leading-tight">
                                {{ banner.title || 'Banner Tanpa Judul' }}
                            </h3>
                        </div>

                        <!-- Status Badge Overlay -->
                        <div class="absolute top-3 right-3">
                            <Badge :variant="banner.is_active ? 'success' : 'neutral'">
                                {{ banner.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Banner Info & Actions -->
                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between gap-4">
                        <div class="text-xs text-slate-500 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-700">Urutan Prioritas:</span>
                                <span class="font-bold text-slate-900">{{ banner.order }}</span>
                            </div>
                            <div v-if="banner.link_url" class="flex items-center justify-between">
                                <span class="font-medium text-slate-700">Link Tujuan:</span>
                                <span class="text-emerald-700 truncate max-w-[200px]">{{ banner.link_url }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 gap-2">
                            <button
                                type="button"
                                @click="toggleStatus(banner)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors cursor-pointer"
                                :class="[
                                    banner.is_active
                                        ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                                        : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                                ]"
                            >
                                {{ banner.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/banners/${banner.id}/edit`"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    @click="openDeleteModal(banner)"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="bg-white rounded-2xl border border-slate-200 p-12 text-center"
            >
                <div class="h-16 w-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Banner Promo</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">
                    Tambahkan banner untuk mempromosikan produk unggulan UMKM kepada pengunjung di halaman depan katalog belanja.
                </p>
                <Link href="/admin/banners/create">
                    <Button variant="primary">
                        Tambah Banner Sekarang
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Custom Delete Banner Confirmation Modal -->
        <Modal :show="confirmDeleteModal" @close="closeDeleteModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus Banner Promo?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus banner promo
                    <span class="font-bold text-slate-900">"{{ bannerToDelete?.title || 'Promo' }}"</span>?
                    Banner ini tidak akan ditampilkan lagi pada katalog belanja pengunjung.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="closeDeleteModal" :disabled="deletingBanner">
                        Batal
                    </Button>
                    <Button variant="danger" @click="confirmDeleteBanner" :loading="deletingBanner" :disabled="deletingBanner">
                        Hapus Banner
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

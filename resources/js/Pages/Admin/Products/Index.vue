<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Badge from '../../../Components/Badge.vue';
import Button from '../../../Components/Button.vue';
import Modal from '../../../Components/Modal.vue';
import Pagination from '../../../Components/Pagination.vue';
import EmptyState from '../../../Components/EmptyState.vue';

defineProps({
    products: {
        type: Object,
        required: true,
    },
});

// Delete confirmation state
const confirmDeleteModal = ref(false);
const productToDelete = ref(null);
const deleting = ref(false);

const openDeleteModal = (product) => {
    productToDelete.value = product;
    confirmDeleteModal.value = true;
};

const closeDeleteModal = () => {
    confirmDeleteModal.value = false;
    productToDelete.value = null;
};

const confirmDelete = () => {
    if (!productToDelete.value) return;

    deleting.value = true;
    router.delete(`/admin/products/${productToDelete.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (isoDate) => {
    if (!isoDate) return '-';
    return new Date(isoDate).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Kelola Katalog Produk - Admin" />

    <AdminLayout title="Kelola Produk Toko">
        <div class="space-y-6">
            <!-- Header Actions Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        Katalog Produk &amp; Inventaris
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Kelola daftar produk, update harga, visibilitas etalase, dan stok barang dagangan toko.
                    </p>
                </div>

                <Link href="/admin/products/create">
                    <Button
                        type="button"
                        variant="primary"
                        class="gap-2 shadow-xs shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Produk Baru</span>
                    </Button>
                </Link>
            </div>

            <!-- Products Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div v-if="products.data && products.data.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-6">PRODUK</th>
                                    <th class="py-3.5 px-6">HARGA</th>
                                    <th class="py-3.5 px-6">STATUS</th>
                                    <th class="py-3.5 px-6">TANGGAL DIBUAT</th>
                                    <th class="py-3.5 px-6 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="product in products.data"
                                    :key="product.id"
                                    class="hover:bg-slate-50/60 transition-colors"
                                >
                                    <!-- Product & Image -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                                <img
                                                    :src="product.image_url"
                                                    :alt="product.name"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div class="min-w-0 max-w-xs">
                                                <p class="font-bold text-slate-900 truncate">{{ product.name }}</p>
                                                <p class="text-xs text-slate-400 font-mono truncate">
                                                    /products/{{ product.slug }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="py-4 px-6 font-semibold text-slate-900">
                                        {{ formatRupiah(product.price) }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-6">
                                        <Badge :variant="product.is_active ? 'success' : 'secondary'">
                                            <span
                                                :class="[
                                                    'w-1.5 h-1.5 rounded-full',
                                                    product.is_active ? 'bg-emerald-500' : 'bg-slate-400'
                                                ]"
                                            />
                                            {{ product.is_active ? 'Aktif' : 'Non-aktif' }}
                                        </Badge>
                                    </td>

                                    <!-- Created At -->
                                    <td class="py-4 px-6 text-slate-500 text-xs">
                                        {{ formatDate(product.created_at) }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="`/admin/products/${product.id}/edit`"
                                                class="p-2 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                                                title="Edit Produk"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </Link>

                                            <button
                                                type="button"
                                                @click="openDeleteModal(product)"
                                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Hapus Produk"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination footer -->
                    <div class="p-4 border-t border-slate-100">
                        <Pagination :links="products.links" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8">
                    <EmptyState
                        title="Belum ada produk terdaftar"
                        description="Anda belum menambahkan produk ke dalam katalog toko. Klik tombol di bawah untuk menambahkan produk pertama Anda."
                    >
                        <template #action>
                            <Link href="/admin/products/create">
                                <Button variant="primary">
                                    Tambah Produk Pertama
                                </Button>
                            </Link>
                        </template>
                    </EmptyState>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmDeleteModal" @close="closeDeleteModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus Produk?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus
                    <span class="font-bold text-slate-900">"{{ productToDelete?.name }}"</span>?
                    Foto dan data produk ini akan dihapus secara permanen dari sistem.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="closeDeleteModal" :disabled="deleting">
                        Batal
                    </Button>
                    <Button variant="danger" @click="confirmDelete" :loading="deleting" :disabled="deleting">
                        Hapus Produk
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

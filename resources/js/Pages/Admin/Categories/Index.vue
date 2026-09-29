<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Button from '../../../Components/Button.vue';
import Modal from '../../../Components/Modal.vue';
import EmptyState from '../../../Components/EmptyState.vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

// Local state for instant drag & drop reordering
const localCategories = ref([...props.categories]);

watch(
    () => props.categories,
    (newCategories) => {
        localCategories.value = [...newCategories];
    },
    { deep: true }
);

// Drag & Drop tracking
const draggedIndex = ref(null);
const dragOverIndex = ref(null);
const isSavingOrder = ref(false);

const onDragStart = (index, event) => {
    draggedIndex.value = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(index));
    }
};

const onDragOver = (index, event) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
    dragOverIndex.value = index;
};

const onDragLeave = (index) => {
    if (dragOverIndex.value === index) {
        dragOverIndex.value = null;
    }
};

const onDrop = (targetIndex, event) => {
    event.preventDefault();
    if (draggedIndex.value === null || draggedIndex.value === targetIndex) {
        draggedIndex.value = null;
        dragOverIndex.value = null;
        return;
    }

    const items = [...localCategories.value];
    const [movedItem] = items.splice(draggedIndex.value, 1);
    items.splice(targetIndex, 0, movedItem);

    // Re-index orders 1, 2, 3...
    items.forEach((item, idx) => {
        item.order = idx + 1;
    });

    localCategories.value = items;
    draggedIndex.value = null;
    dragOverIndex.value = null;

    // Persist new order to server
    isSavingOrder.value = true;
    router.post(
        '/admin/categories/reorder',
        {
            orders: items.map((c) => ({ id: c.id, order: c.order })),
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isSavingOrder.value = false;
            },
        }
    );
};

const onDragEnd = () => {
    draggedIndex.value = null;
    dragOverIndex.value = null;
};

// Delete Confirmation Modal
const isDeleteModalOpen = ref(false);
const categoryToDelete = ref(null);
const deleting = ref(false);

const openDeleteModal = (category) => {
    categoryToDelete.value = category;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    categoryToDelete.value = null;
};

const confirmDelete = () => {
    if (!categoryToDelete.value) return;

    deleting.value = true;
    router.delete(`/admin/categories/${categoryToDelete.value.id}`, {
        onSuccess: () => closeDeleteModal(),
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Kelola Kategori Produk - Admin" />

    <AdminLayout title="Kelola Kategori Produk">
        <div class="space-y-6">
            <!-- Header Actions Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        Daftar Kategori Produk
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Geser (drag &amp; drop) baris tabel untuk menukar urutan kategori secara otomatis di etalase toko.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        v-if="isSavingOrder"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200/60 animate-pulse"
                    >
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan urutan...
                    </span>

                    <Link href="/admin/categories/create">
                        <Button
                            type="button"
                            variant="primary"
                            class="gap-2 shadow-xs shrink-0 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Kategori</span>
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Categories Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div v-if="localCategories && localCategories.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-6 w-32">URUTAN</th>
                                    <th class="py-3.5 px-6">NAMA KATEGORI</th>
                                    <th class="py-3.5 px-6">DESKRIPSI</th>
                                    <th class="py-3.5 px-6">PRODUK TERKAIT</th>
                                    <th class="py-3.5 px-6 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="(cat, index) in localCategories"
                                    :key="cat.id"
                                    draggable="true"
                                    @dragstart="onDragStart(index, $event)"
                                    @dragover="onDragOver(index, $event)"
                                    @dragleave="onDragLeave(index)"
                                    @drop="onDrop(index, $event)"
                                    @dragend="onDragEnd"
                                    :class="[
                                        'transition-all duration-150 select-none cursor-move',
                                        draggedIndex === index ? 'opacity-40 bg-slate-100' : 'hover:bg-slate-50/80',
                                        dragOverIndex === index && draggedIndex !== index
                                            ? 'border-t-2 border-emerald-500 bg-emerald-50/40'
                                            : ''
                                    ]"
                                >
                                    <!-- Order sequence with Drag Handle -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg cursor-grab active:cursor-grabbing p-1.5 transition-colors" title="Tahan dan geser untuk menukar urutan">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M3 12h18M9 6l3-3 3 3M9 18l3 3 3-3M6 9l-3 3 3 3M18 9l3 3-3 3" />
                                                </svg>
                                            </div>
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 border border-slate-200 font-mono text-xs font-bold text-slate-700">
                                                {{ index + 1 }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Name & Slug -->
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">{{ cat.name }}</p>
                                        <p class="text-xs text-slate-400 font-mono">
                                            slug: {{ cat.slug }}
                                        </p>
                                    </td>

                                    <!-- Description -->
                                    <td class="py-4 px-6 text-xs text-slate-600 max-w-sm truncate">
                                        {{ cat.description || '-' }}
                                    </td>

                                    <!-- Associated Products Count -->
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ cat.products_count ?? 0 }} Produk
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2" @click.stop>
                                            <Link
                                                :href="`/admin/categories/${cat.id}/edit`"
                                                class="p-2 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit Kategori"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </Link>

                                            <button
                                                type="button"
                                                @click="openDeleteModal(cat)"
                                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Hapus Kategori"
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
                </div>

                <!-- Empty State -->
                <div v-else class="p-8">
                    <EmptyState
                        title="Belum ada kategori terdaftar"
                        description="Kategori membantu pembeli mencari barang di etalase toko. Klik tombol di bawah untuk membuat kategori pertama Anda."
                    >
                        <template #action>
                            <Link href="/admin/categories/create">
                                <Button variant="primary">
                                    Tambah Kategori Sekarang
                                </Button>
                            </Link>
                        </template>
                    </EmptyState>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="isDeleteModalOpen"
            title="Konfirmasi Hapus Kategori"
            @close="closeDeleteModal"
        >
            <div class="space-y-3">
                <p class="text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus kategori
                    <strong class="text-slate-900 font-semibold">
                        "{{ categoryToDelete?.name }}"
                    </strong>?
                </p>
                <div class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200/80 leading-relaxed">
                    <strong>Catatan:</strong> Produk yang menggunakan kategori ini <strong>tidak akan terhapus</strong>. Produk tersebut hanya akan diubah menjadi <em>"Tanpa Kategori"</em> secara otomatis.
                </div>
            </div>

            <template #footer>
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="deleting"
                    @click="closeDeleteModal"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    variant="danger"
                    :loading="deleting"
                    @click="confirmDelete"
                >
                    Ya, Hapus Kategori
                </Button>
            </template>
        </Modal>
    </AdminLayout>
</template>

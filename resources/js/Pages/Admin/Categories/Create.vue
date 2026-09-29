<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import CategoryForm from '../../../Components/CategoryForm.vue';
import Button from '../../../Components/Button.vue';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post('/admin/categories');
};
</script>

<template>
    <Head title="Tambah Kategori Produk - Admin" />

    <AdminLayout title="Tambah Kategori">
        <div class="w-full">
            <!-- Header with Back Link -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <Link
                        href="/admin/categories"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors"
                    >
                        &larr; Kembali ke Daftar Kategori
                    </Link>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Buat Kategori Baru</h2>
                    <p class="text-sm text-slate-500">Tambahkan kelompok kategori baru untuk memudahkan pengelompokan produk.</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <CategoryForm
                    :form="form"
                    :is-edit="false"
                    @submit="submit"
                >
                    <template #actions>
                        <Link href="/admin/categories">
                            <Button variant="secondary" type="button">
                                Batal
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                        >
                            Simpan Kategori
                        </Button>
                    </template>
                </CategoryForm>
            </div>
        </div>
    </AdminLayout>
</template>

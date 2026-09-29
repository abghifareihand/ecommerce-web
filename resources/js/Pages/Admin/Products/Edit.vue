<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import ProductForm from '../../../Components/ProductForm.vue';
import Button from '../../../Components/Button.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price,
    image: null,
    is_active: Boolean(props.product.is_active),
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/admin/products/${props.product.id}`);
};
</script>

<template>
    <Head :title="`Edit Produk: ${product.name} - Admin`" />

    <AdminLayout :title="`Edit Produk`">
        <div class="w-full">
            <!-- Header with Back Link -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <Link
                        href="/admin/products"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors"
                    >
                        &larr; Kembali ke Katalog Produk
                    </Link>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Perbarui Informasi Produk</h2>
                </div>

                <div class="text-xs font-mono text-slate-400">
                    ID: #{{ product.id }}
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <ProductForm
                    :form="form"
                    :is-edit="true"
                    :existing-image-url="product.image_url"
                    @submit="submit"
                >
                    <template #actions>
                        <Link href="/admin/products">
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
                            Simpan Perubahan
                        </Button>
                    </template>
                </ProductForm>
            </div>
        </div>
    </AdminLayout>
</template>

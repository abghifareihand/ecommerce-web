<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import BannerForm from '../../../Components/BannerForm.vue';
import Button from '../../../Components/Button.vue';

const props = defineProps({
    nextOrder: {
        type: Number,
        default: 1,
    },
});

const form = useForm({
    title: '',
    subtitle: '',
    link_url: '',
    image: null,
    order: props.nextOrder,
    is_active: true,
});

const submit = () => {
    form.post('/admin/banners');
};
</script>

<template>
    <Head title="Tambah Banner Promo - Admin" />

    <AdminLayout title="Tambah Banner Baru">
        <div class="w-full">
            <!-- Header with Back Link -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <Link
                        href="/admin/banners"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors"
                    >
                        &larr; Kembali ke Daftar Banner
                    </Link>
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Upload Banner Promo Baru</h2>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <BannerForm
                    :form="form"
                    :is-edit="false"
                    @submit="submit"
                >
                    <template #actions>
                        <Link href="/admin/banners">
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
                            Upload Banner
                        </Button>
                    </template>
                </BannerForm>
            </div>
        </div>
    </AdminLayout>
</template>

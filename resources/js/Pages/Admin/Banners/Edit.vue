<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import BannerForm from '../../../Components/BannerForm.vue';
import Button from '../../../Components/Button.vue';

const props = defineProps({
    banner: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    title: props.banner.title || '',
    subtitle: props.banner.subtitle || '',
    link_url: props.banner.link_url || '',
    image: null,
    order: props.banner.order || 0,
    is_active: Boolean(props.banner.is_active),
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/admin/banners/${props.banner.id}`);
};
</script>

<template>
    <Head :title="`Edit Banner: ${banner.title || 'Promo'} - Admin`" />

    <AdminLayout :title="`Edit Banner Promo`">
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
                    <h2 class="text-xl font-bold text-slate-900 mt-2">Perbarui Banner Promo</h2>
                </div>

                <div class="text-xs font-mono text-slate-400">
                    ID: #{{ banner.id }}
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <BannerForm
                    :form="form"
                    :is-edit="true"
                    :existing-image-url="banner.image_url"
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
                            Simpan Perubahan
                        </Button>
                    </template>
                </BannerForm>
            </div>
        </div>
    </AdminLayout>
</template>

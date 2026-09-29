<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Input from '../../../Components/Input.vue';
import Button from '../../../Components/Button.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Profile & Avatar form
const profileForm = useForm({
    _method: 'POST',
    name: props.user.name,
    email: props.user.email,
    avatar: null,
});

const avatarPreview = ref(props.user.avatar_url || null);
const avatarFileInput = ref(null);
const showDeleteAvatarModal = ref(false);
const removingAvatar = ref(false);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        profileForm.avatar = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            avatarPreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    avatarFileInput.value?.click();
};

const openDeleteAvatarModal = () => {
    showDeleteAvatarModal.value = true;
};

const confirmRemoveAvatar = () => {
    removingAvatar.value = true;
    router.delete('/admin/profile/avatar', {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
            profileForm.avatar = null;
            if (avatarFileInput.value) avatarFileInput.value.value = '';
            showDeleteAvatarModal.value = false;
        },
        onFinish: () => {
            removingAvatar.value = false;
        },
    });
};

const submitProfile = () => {
    profileForm.post('/admin/profile', {
        preserveScroll: true,
        forceFormData: true,
    });
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put('/admin/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.charAt(0).toUpperCase();
};
</script>

<template>
    <Head title="Profil Saya - Admin" />

    <AdminLayout title="Profil Saya">
        <div class="w-full space-y-6">
            <!-- Header explanation -->
            <div>
                <h2 class="text-xl font-bold text-slate-900">Pengaturan Akun Admin</h2>
                <p class="text-xs text-slate-500 mt-1">Kelola identitas personal, foto profil avatar, dan keamanan akun Anda.</p>
            </div>

            <!-- Card 1: Profil & Avatar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Pribadi &amp; Foto Profil</h3>
                        <p class="text-xs text-slate-400">Perbarui nama, email, dan foto avatar yang tampil di dashboard.</p>
                    </div>
                </div>

                <form @submit.prevent="submitProfile" class="mt-6 space-y-6">
                    <!-- Avatar section -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Foto Profil (Avatar)
                        </label>
                        <div class="mt-2 flex items-center gap-x-6">
                            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-xs border border-slate-200">
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Preview Foto Profil"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else>
                                    {{ getInitials(profileForm.name) }}
                                </span>
                            </div>

                            <div>
                                <input
                                    ref="avatarFileInput"
                                    type="file"
                                    accept="image/png, image/jpeg, image/jpg, image/webp"
                                    @change="handleAvatarChange"
                                    class="hidden"
                                />
                                <div class="flex flex-wrap items-center gap-2">
                                    <Button type="button" variant="secondary" size="sm" @click="triggerFileInput">
                                        <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Pilih Foto
                                    </Button>

                                    <Button
                                        v-if="avatarPreview"
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        @click="openDeleteAvatarModal"
                                        :disabled="removingAvatar"
                                    >
                                        Hapus Foto
                                    </Button>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Mendukung format JPG, PNG, atau WebP hingga 2MB.</p>
                                <p v-if="profileForm.errors.avatar" class="mt-1 text-xs text-rose-600 font-medium">
                                    {{ profileForm.errors.avatar }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Name & Email Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Input
                            id="profile-name"
                            label="Nama Lengkap"
                            v-model="profileForm.name"
                            :error="profileForm.errors.name"
                            placeholder="cth: Admin EcoStore"
                            required
                        />

                        <Input
                            id="profile-email"
                            label="Alamat Email"
                            type="email"
                            v-model="profileForm.email"
                            :error="profileForm.errors.email"
                            placeholder="cth: admin@example.com"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="profileForm.processing"
                            :disabled="profileForm.processing"
                        >
                            Simpan Perubahan
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Ubah Kata Sandi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Perbarui Kata Sandi</h3>
                        <p class="text-xs text-slate-400">Pastikan akun Anda terlindungi dengan kombinasi kata sandi yang kuat.</p>
                    </div>
                </div>

                <form @submit.prevent="submitPassword" class="mt-6 space-y-5">
                    <div>
                        <Input
                            id="current-password"
                            label="Kata Sandi Saat Ini"
                            type="password"
                            v-model="passwordForm.current_password"
                            :error="passwordForm.errors.current_password"
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Input
                            id="new-password"
                            label="Kata Sandi Baru"
                            type="password"
                            v-model="passwordForm.password"
                            :error="passwordForm.errors.password"
                            placeholder="Minimal 8 karakter"
                            required
                        />

                        <Input
                            id="password-confirmation"
                            label="Ulangi Kata Sandi Baru"
                            type="password"
                            v-model="passwordForm.password_confirmation"
                            :error="passwordForm.errors.password_confirmation"
                            placeholder="Konfirmasi kata sandi"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="passwordForm.processing"
                            :disabled="passwordForm.processing"
                        >
                            Perbarui Kata Sandi
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Custom Delete Avatar Confirmation Modal -->
        <Modal :show="showDeleteAvatarModal" @close="showDeleteAvatarModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus Foto Profil?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tindakan ini akan mengembalikan ke avatar inisial.</p>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus foto profil ini? Avatar Anda di dashboard admin akan kembali menggunakan inisial nama Anda.
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button variant="secondary" @click="showDeleteAvatarModal = false" :disabled="removingAvatar">
                        Batal
                    </Button>
                    <Button variant="danger" @click="confirmRemoveAvatar" :loading="removingAvatar" :disabled="removingAvatar">
                        Hapus Foto
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

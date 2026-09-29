<script setup>
import { ref } from 'vue';
import Input from './Input.vue';
import Button from './Button.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
    existingImageUrl: {
        type: String,
        default: null,
    },
});

defineEmits(['submit']);

const fileInput = ref(null);
const imagePreview = ref(props.existingImageUrl);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        props.form.image = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const removeSelectedImage = () => {
    props.form.image = null;
    imagePreview.value = props.existingImageUrl || null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
        <!-- Banner Image Upload Field -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">
                Gambar Banner Promo <span v-if="!isEdit" class="text-rose-500">*</span>
            </label>

            <!-- Image Preview Box -->
            <div
                v-if="imagePreview"
                class="relative mb-4 rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 aspect-21/9 max-h-[300px] group shadow-xs"
            >
                <img
                    :src="imagePreview"
                    alt="Preview Banner"
                    class="w-full h-full object-cover object-center"
                />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 bg-white/90 hover:bg-white text-slate-800 text-xs font-bold rounded-xl shadow-md cursor-pointer transition-colors"
                        @click="triggerFileInput"
                    >
                        Ganti Gambar
                    </button>
                    <button
                        v-if="form.image"
                        type="button"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md cursor-pointer transition-colors"
                        @click="removeSelectedImage"
                    >
                        Batal Pilih
                    </button>
                </div>
            </div>

            <!-- Upload Dropzone / Trigger -->
            <div
                v-else
                @click="triggerFileInput"
                class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-8 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-emerald-50/20"
            >
                <div class="mx-auto w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Klik untuk upload gambar banner</p>
                <p class="text-xs text-slate-400 mt-1">Rekomendasi rasio lanskap lebar (1200 &times; 450 px), format JPG, PNG, atau WebP.</p>
            </div>

            <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/png,image/jpg,image/webp"
                class="hidden"
                @change="handleFileChange"
            />

            <p v-if="form.errors.image" class="mt-2 text-xs text-rose-600 font-medium">
                {{ form.errors.image }}
            </p>
        </div>

        <!-- Banner Title -->
        <Input
            id="banner-title"
            label="Judul Utama Banner (Opsional)"
            v-model="form.title"
            :error="form.errors.title"
            placeholder="cth: Promo Spesial Ramadhan Kriya Nusantara"
            hint="Teks judul besar yang tampil di atas banner."
        />

        <!-- Banner Subtitle -->
        <Input
            id="banner-subtitle"
            label="Subjudul / Tag Promo (Opsional)"
            v-model="form.subtitle"
            :error="form.errors.subtitle"
            placeholder="cth: Dapatkan diskon spesial untuk koleksi kriya bambu pilihan"
            hint="Teks keterangan singkat / badge hijau di atas judul."
        />

        <!-- Link URL & Display Order -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <Input
                id="banner-link"
                label="Link Tujuan (Opsional)"
                v-model="form.link_url"
                :error="form.errors.link_url"
                placeholder="/products"
                hint="URL halaman saat tombol atau banner diklik."
            />

            <Input
                id="banner-order"
                label="Urutan Tampil"
                type="number"
                v-model.number="form.order"
                :error="form.errors.order"
                placeholder="1"
                min="0"
                hint="Angka urutan prioritas banner tampil."
            />
        </div>

        <!-- Is Active Checkbox -->
        <div class="flex items-center gap-3 pt-2">
            <input
                id="banner-active"
                type="checkbox"
                v-model="form.is_active"
                class="h-4.5 w-4.5 rounded border-slate-300 accent-emerald-600 text-emerald-600 focus:ring-emerald-600 cursor-pointer"
            />
            <label for="banner-active" class="text-sm font-semibold text-slate-700 cursor-pointer select-none">
                Tampilkan banner ini di etalase toko (Status Aktif)
            </label>
        </div>

        <!-- Actions Slot -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200/80">
            <slot name="actions" />
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue';
import Input from './Input.vue';
import Textarea from './Textarea.vue';
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
        <!-- Product Name -->
        <Input
            id="product-name"
            label="Nama Produk"
            v-model="form.name"
            :error="form.errors.name"
            placeholder="cth: Cangkir Keramik Buatan Tangan"
            required
        />

        <!-- Price -->
        <Input
            id="product-price"
            label="Harga Produk (Rp)"
            type="number"
            step="1"
            min="0"
            v-model="form.price"
            :error="form.errors.price"
            placeholder="cth: 75000"
            required
        />

        <!-- Description -->
        <Textarea
            id="product-description"
            label="Deskripsi Produk"
            v-model="form.description"
            :error="form.errors.description"
            placeholder="Tuliskan deskripsi lengkap mengenai spesifikasi, bahan, dan keunggulan produk..."
            :rows="5"
        />

        <!-- Image Upload -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Foto Produk
            </label>

            <div class="mt-2 flex items-center gap-x-6">
                <!-- Preview box -->
                <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 flex items-center justify-center">
                    <img
                        v-if="imagePreview"
                        :src="imagePreview"
                        alt="Product preview"
                        class="h-full w-full object-cover"
                    />
                    <svg
                        v-else
                        class="h-10 w-10 text-slate-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <div>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="hidden"
                        @change="handleFileChange"
                    />

                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="triggerFileInput"
                        >
                            {{ isEdit ? 'Ganti Foto' : 'Pilih Foto' }}
                        </Button>

                        <button
                            v-if="form.image"
                            type="button"
                            @click="removeSelectedImage"
                            class="text-xs text-rose-600 hover:text-rose-700 font-medium underline ml-2"
                        >
                            Hapus Pilihan
                        </button>
                    </div>

                    <p class="mt-2 text-xs text-slate-500">
                        Format JPG, PNG, JPEG, atau WebP hingga 2MB.
                    </p>
                    <p v-if="form.errors.image" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.image }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Status Toggle -->
        <div class="pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <label for="is_active" class="text-sm font-semibold text-slate-800 cursor-pointer">
                        Status Visibilitas Etalase
                    </label>
                    <p class="text-xs text-slate-500">
                        Saat aktif, produk ini akan tampil dan dapat dibeli oleh pelanggan di etalase toko.
                    </p>
                </div>
                
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        id="is_active"
                        v-model="form.is_active"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
            <slot name="actions">
                <Button
                    type="submit"
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    {{ isEdit ? 'Update Product' : 'Create Product' }}
                </Button>
            </slot>
        </div>
    </form>
</template>

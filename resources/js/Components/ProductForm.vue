<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import Input from './Input.vue';
import Textarea from './Textarea.vue';
import Button from './Button.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
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

// Custom Category Dropdown
const categoryDropdownOpen = ref(false);
const categoryDropdownRef = ref(null);

const selectedCategory = computed(() => {
    if (!props.form.category_id) return null;
    return props.categories.find((c) => String(c.id) === String(props.form.category_id)) || null;
});

const selectCategory = (id) => {
    props.form.category_id = id;
    categoryDropdownOpen.value = false;
};

const handleCategoryClickOutside = (e) => {
    if (categoryDropdownRef.value && !categoryDropdownRef.value.contains(e.target)) {
        categoryDropdownOpen.value = false;
    }
};

const handleCategoryKeyDown = (e) => {
    if (e.key === 'Escape' && categoryDropdownOpen.value) {
        categoryDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleCategoryClickOutside);
    document.addEventListener('keydown', handleCategoryKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleCategoryClickOutside);
    document.removeEventListener('keydown', handleCategoryKeyDown);
});
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

        <!-- Category & Stock Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Category Custom Dropdown -->
            <div class="relative w-full" ref="categoryDropdownRef">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Kategori Produk</span>
                    <span class="text-xs font-normal text-slate-400">Opsional</span>
                </label>

                <!-- Trigger Button (Matches Input.vue style) -->
                <button
                    type="button"
                    @click="categoryDropdownOpen = !categoryDropdownOpen"
                    :class="[
                        'w-full flex items-center justify-between rounded-lg px-3.5 py-2 text-sm text-left transition-all cursor-pointer bg-white ring-1 ring-inset shadow-xs',
                        categoryDropdownOpen
                            ? 'ring-2 ring-emerald-600 border-transparent bg-white'
                            : form.errors.category_id
                                ? 'ring-rose-400 focus:ring-rose-600 bg-rose-50/20'
                                : 'ring-slate-300 hover:ring-slate-400 focus:ring-2 focus:ring-emerald-600',
                    ]"
                    :aria-expanded="categoryDropdownOpen"
                >
                    <div class="flex items-center gap-2.5 truncate">
                        <!-- Category Tag Icon -->
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>

                        <!-- Selected text / placeholder -->
                        <span v-if="selectedCategory" class="font-medium text-slate-900 truncate">
                            {{ selectedCategory.name }}
                        </span>
                        <span v-else class="text-slate-400 font-normal truncate">
                            Pilih kategori (atau tanpa kategori)
                        </span>
                    </div>

                    <!-- Rotating Chevron Icon -->
                    <svg
                        class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-2"
                        :class="{ 'rotate-180 text-emerald-600': categoryDropdownOpen }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu Panel -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform opacity-0 scale-95 -translate-y-1"
                    enter-to-class="transform opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform opacity-100 scale-100 translate-y-0"
                    leave-to-class="transform opacity-0 scale-95 -translate-y-1"
                >
                    <div
                        v-if="categoryDropdownOpen"
                        class="absolute z-30 mt-1.5 w-full rounded-xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 py-1.5 max-h-72 overflow-y-auto focus:outline-none"
                    >
                        <!-- Option: Without Category -->
                        <button
                            type="button"
                            @click="selectCategory(null)"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2 text-sm transition-colors text-left cursor-pointer group',
                                form.category_id === null
                                    ? 'bg-emerald-50 text-emerald-800 font-semibold'
                                    : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'
                            ]"
                        >
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="w-2 h-2 rounded-full border border-slate-300 bg-slate-100 shrink-0"></span>
                                <span>Tanpa Kategori</span>
                                <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                            </div>
                            <svg v-if="form.category_id === null" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                        <div v-if="categories && categories.length > 0" class="border-t border-slate-100 my-1"></div>

                        <!-- Categories List -->
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            type="button"
                            @click="selectCategory(cat.id)"
                            :class="[
                                'w-full flex items-center justify-between px-3.5 py-2 text-sm transition-colors text-left cursor-pointer group',
                                String(form.category_id) === String(cat.id)
                                    ? 'bg-emerald-50 text-emerald-800 font-semibold'
                                    : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'
                            ]"
                        >
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">{{ cat.name }}</span>
                            </div>
                            <svg v-if="String(form.category_id) === String(cat.id)" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </Transition>

                <p v-if="form.errors.category_id" class="mt-1.5 text-xs text-rose-600 font-medium">
                    {{ form.errors.category_id }}
                </p>
            </div>

            <!-- Stock -->
            <Input
                id="product-stock"
                label="Jumlah Stok Barang"
                type="number"
                step="1"
                min="0"
                v-model="form.stock"
                :error="form.errors.stock"
                placeholder="cth: 25"
                required
            />
        </div>

        <!-- Price -->
        <Input
            id="product-price"
            label="Harga Produk (Rp)"
            is-currency
            v-model="form.price"
            :error="form.errors.price"
            placeholder="cth: 75.000"
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
                            class="text-xs text-rose-600 hover:text-rose-700 font-medium underline ml-2 cursor-pointer"
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

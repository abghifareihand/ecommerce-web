<script setup>
import Input from './Input.vue';
import Textarea from './Textarea.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
        <!-- Category Name -->
        <div>
            <Input
                id="category-name"
                label="Nama Kategori"
                v-model="form.name"
                :error="form.errors.name"
                placeholder="cth: Peralatan Rumah Tangga"
                required
            />
        </div>

        <!-- Description -->
        <Textarea
            id="category-description"
            label="Deskripsi Kategori (Opsional)"
            v-model="form.description"
            :error="form.errors.description"
            placeholder="Tuliskan keterangan singkat mengenai produk dalam kelompok kategori ini..."
            rows="3"
        />

        <div class="text-xs text-slate-500 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
            <span class="font-semibold text-slate-700">Catatan:</span> URL / Slug kategori akan dibuat otomatis berdasarkan nama kategori. Kategori ini dapat dipilih secara opsional saat membuat atau mengubah data produk.
        </div>

        <!-- Action Buttons Slot -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <slot name="actions" />
        </div>
    </form>
</template>

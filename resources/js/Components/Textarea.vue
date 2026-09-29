<script setup>
defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `textarea-${Math.random().toString(36).substring(2, 9)}`,
    },
    placeholder: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    rows: {
        type: Number,
        default: 4,
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-semibold text-slate-700 mb-1.5"
        >
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>

        <div class="relative">
            <textarea
                :id="id"
                :rows="rows"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="$emit('update:modelValue', $event.target.value)"
                :class="[
                    'block w-full rounded-lg px-3.5 py-2 text-sm text-slate-900 transition-colors placeholder:text-slate-400 bg-white ring-1 ring-inset focus:ring-2 focus:ring-inset focus:outline-none resize-y',
                    error
                        ? 'ring-rose-400 focus:ring-rose-600 bg-rose-50/20'
                        : 'ring-slate-300 focus:ring-emerald-600',
                    disabled ? 'bg-slate-100 text-slate-500 cursor-not-allowed ring-slate-200' : ''
                ]"
            ></textarea>
        </div>

        <p v-if="error" class="mt-1.5 text-xs font-medium text-rose-600">
            {{ error }}
        </p>
    </div>
</template>

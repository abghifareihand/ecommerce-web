<script setup>
const props = defineProps({
    modelValue: {
        type: Number,
        default: 1,
    },
    min: {
        type: Number,
        default: 1,
    },
    max: {
        type: Number,
        default: 999,
    },
    size: {
        type: String,
        default: 'sm', // 'sm' or 'md'
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const decrement = () => {
    if (props.modelValue > props.min) {
        const newVal = props.modelValue - 1;
        emit('update:modelValue', newVal);
        emit('change', newVal);
    }
};

const increment = () => {
    if (props.modelValue < props.max) {
        const newVal = props.modelValue + 1;
        emit('update:modelValue', newVal);
        emit('change', newVal);
    }
};
</script>

<template>
    <div
        class="inline-flex items-center bg-slate-50 border border-slate-200/90 rounded-xl p-1 shadow-2xs select-none"
    >
        <!-- Decrement Button -->
        <button
            type="button"
            @click="decrement"
            :disabled="modelValue <= min"
            :class="[
                'flex items-center justify-center rounded-lg text-slate-600 transition-all cursor-pointer',
                size === 'md' ? 'h-9 w-9' : 'h-7 w-7',
                modelValue <= min
                    ? 'opacity-30 cursor-not-allowed text-slate-400'
                    : 'hover:bg-white hover:text-emerald-700 hover:shadow-xs active:scale-95'
            ]"
            aria-label="Kurangi jumlah"
        >
            <svg
                :class="size === 'md' ? 'w-4 h-4' : 'w-3.5 h-3.5'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
            </svg>
        </button>

        <!-- Current Quantity Number -->
        <span
            :class="[
                'text-center font-extrabold text-slate-900 tracking-tight',
                size === 'md' ? 'w-12 text-sm' : 'w-8 text-xs'
            ]"
        >
            {{ modelValue }}
        </span>

        <!-- Increment Button -->
        <button
            type="button"
            @click="increment"
            :disabled="modelValue >= max"
            :class="[
                'flex items-center justify-center rounded-lg text-slate-600 transition-all cursor-pointer',
                size === 'md' ? 'h-9 w-9' : 'h-7 w-7',
                modelValue >= max
                    ? 'opacity-30 cursor-not-allowed text-slate-400'
                    : 'hover:bg-white hover:text-emerald-700 hover:shadow-xs active:scale-95'
            ]"
            aria-label="Tambah jumlah"
        >
            <svg
                :class="size === 'md' ? 'w-4 h-4' : 'w-3.5 h-3.5'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>
</template>

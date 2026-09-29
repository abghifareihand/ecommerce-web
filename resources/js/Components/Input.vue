<script setup>
import { ref, computed, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substring(2, 9)}`,
    },
    type: {
        type: String,
        default: 'text',
    },
    isCurrency: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    step: {
        type: String,
        default: null,
    },
    min: {
        type: [String, Number],
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const showPassword = ref(false);

const isCurrencyMode = computed(() => {
    return props.isCurrency || props.type === 'currency';
});

const computedType = computed(() => {
    if (isCurrencyMode.value) {
        return 'text';
    }
    if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password';
    }
    return props.type;
});

const displayValue = computed(() => {
    if (isCurrencyMode.value) {
        if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
            return '';
        }
        const clean = String(props.modelValue).replace(/[^0-9]/g, '');
        if (!clean) return '';
        return Number(clean).toLocaleString('id-ID');
    }
    return props.modelValue;
});

const handleInput = (e) => {
    if (!isCurrencyMode.value) {
        emit('update:modelValue', e.target.value);
        return;
    }

    const input = e.target;
    const originalValue = input.value;
    const originalCursor = input.selectionStart || 0;

    // Count how many digits were before the cursor
    const digitsBeforeCursor = originalValue.slice(0, originalCursor).replace(/[^0-9]/g, '').length;

    // Extract digits only
    const rawDigits = originalValue.replace(/[^0-9]/g, '');

    if (!rawDigits) {
        input.value = '';
        emit('update:modelValue', '');
        return;
    }

    // Format with dots according to Indonesian locale
    const formatted = Number(rawDigits).toLocaleString('id-ID');
    input.value = formatted;

    emit('update:modelValue', Number(rawDigits));

    // Restore cursor position based on digit count
    nextTick(() => {
        let newCursor = 0;
        let countedDigits = 0;
        for (let i = 0; i < formatted.length; i++) {
            if (/[0-9]/.test(formatted[i])) {
                countedDigits++;
            }
            if (countedDigits >= digitsBeforeCursor) {
                newCursor = i + 1;
                break;
            }
        }
        if (newCursor === 0) newCursor = formatted.length;
        input.setSelectionRange(newCursor, newCursor);
    });
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
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
            <input
                :id="id"
                :type="computedType"
                :inputmode="isCurrencyMode ? 'numeric' : undefined"
                :step="step"
                :min="min"
                :value="displayValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="handleInput"
                :class="[
                    'block w-full rounded-lg px-3.5 py-2 text-sm text-slate-900 transition-colors placeholder:text-slate-400 bg-white ring-1 ring-inset focus:ring-2 focus:ring-inset focus:outline-none',
                    type === 'password' ? 'pr-10' : '',
                    error
                        ? 'ring-rose-400 focus:ring-rose-600 bg-rose-50/20'
                        : 'ring-slate-300 focus:ring-emerald-600',
                    disabled ? 'bg-slate-100 text-slate-500 cursor-not-allowed ring-slate-200' : ''
                ]"
            />

            <!-- Password Toggle Eye Icon Button -->
            <button
                v-if="type === 'password'"
                type="button"
                @click="togglePassword"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer transition-colors"
                :title="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                tabindex="-1"
            >
                <!-- Eye-Slash (when visible / text) -->
                <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
                <!-- Eye (when hidden / dots) -->
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>

        <p v-if="error" class="mt-1.5 text-xs font-medium text-rose-600">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-500">
            {{ hint }}
        </p>
    </div>
</template>

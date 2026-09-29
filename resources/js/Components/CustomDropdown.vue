<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean, null],
        default: '',
    },
    options: {
        type: Array,
        required: true,
        // Each item: { value: String|Number, label: String, dotClass?: String }
    },
    placeholder: {
        type: String,
        default: 'Pilih...',
    },
    wrapperClass: {
        type: String,
        default: '',
    },
    buttonClass: {
        type: String,
        default: '',
    },
    panelClass: {
        type: String,
        default: '',
    },
    fullWidth: {
        type: Boolean,
        default: true,
    },
    showDot: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const selectedOption = computed(() => {
    return props.options.find((opt) => opt.value === props.modelValue);
});

const displayLabel = computed(() => {
    return selectedOption.value ? selectedOption.value.label : props.placeholder;
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const selectOption = (value) => {
    emit('update:modelValue', value);
    emit('change', value);
    closeDropdown();
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

const handleKeyDown = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div
        ref="dropdownRef"
        :class="[
            'relative text-left',
            fullWidth ? 'w-full block' : 'inline-block',
            wrapperClass,
        ]"
    >
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            :class="[
                'w-full flex items-center justify-between gap-2.5 bg-white px-3.5 py-2.5 text-sm font-medium rounded-xl transition-all cursor-pointer select-none ring-1 ring-inset shadow-xs text-left',
                isOpen
                    ? 'ring-2 ring-emerald-600 text-slate-900 border-transparent bg-white'
                    : 'ring-slate-300 hover:ring-slate-400 text-slate-800',
                buttonClass,
            ]"
            :aria-expanded="isOpen"
        >
            <div class="flex items-center gap-2.5 truncate">
                <slot name="icon" />
                <span
                    class="truncate"
                    :class="selectedOption ? 'font-medium text-slate-900' : 'text-slate-400 font-normal'"
                >
                    {{ displayLabel }}
                </span>
            </div>

            <!-- Rotating Chevron Icon -->
            <svg
                class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-2"
                :class="{ 'rotate-180 text-emerald-600': isOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu Panel -->
        <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform opacity-0 scale-95 -translate-y-1"
            enter-to-class="transform opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform opacity-100 scale-100 translate-y-0"
            leave-to-class="transform opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="isOpen"
                :class="[
                    'absolute left-0 mt-1.5 w-full rounded-xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 py-1.5 max-h-72 overflow-y-auto focus:outline-none z-50',
                    panelClass,
                ]"
            >
                <button
                    v-for="opt in options"
                    :key="opt.value"
                    type="button"
                    @click="selectOption(opt.value)"
                    :class="[
                        'w-full flex items-center justify-between px-3.5 py-2 text-sm transition-colors text-left cursor-pointer group select-none',
                        opt.value === modelValue
                            ? 'bg-emerald-50 text-emerald-800 font-semibold'
                            : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-normal',
                    ]"
                >
                    <div class="flex items-center gap-2.5 truncate">
                        <!-- Dot Indicator -->
                        <span
                            v-if="showDot"
                            class="w-2 h-2 rounded-full shrink-0"
                            :class="opt.dotClass || 'bg-emerald-500'"
                        ></span>
                        <span class="truncate">{{ opt.label }}</span>
                    </div>

                    <!-- Selected Checkmark SVG (same as Gambar 2) -->
                    <svg
                        v-if="opt.value === modelValue"
                        class="w-4 h-4 text-emerald-600 shrink-0 ml-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </transition>
    </div>
</template>

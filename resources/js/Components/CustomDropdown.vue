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
        // Each item: { value: String|Number, label: String }
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
                'inline-flex items-center justify-between gap-3 w-full bg-white px-3.5 py-2.5 text-sm font-medium rounded-xl transition-all cursor-pointer select-none',
                isOpen
                    ? 'border border-emerald-600 ring-2 ring-emerald-600/20 text-slate-900 shadow-xs'
                    : 'border border-emerald-600 text-slate-800 shadow-xs hover:bg-slate-50/50',
                buttonClass,
            ]"
            :aria-expanded="isOpen"
        >
            <span class="truncate">{{ displayLabel }}</span>
            <svg
                class="w-4 h-4 text-slate-600 shrink-0 transition-transform duration-200"
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
            enter-active-class="transition ease-out duration-150"
            enter-from-class="transform opacity-0 scale-95 -translate-y-1"
            enter-to-class="transform opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="transform opacity-100 scale-100 translate-y-0"
            leave-to-class="transform opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="isOpen"
                :class="[
                    'absolute left-0 mt-1.5 min-w-[190px] w-full bg-white rounded-lg border border-slate-200 shadow-xl overflow-hidden py-1 z-50',
                    panelClass,
                ]"
            >
                <button
                    v-for="opt in options"
                    :key="opt.value"
                    type="button"
                    @click="selectOption(opt.value)"
                    class="relative w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center cursor-pointer select-none group"
                    :class="[
                        opt.value === modelValue
                            ? 'text-slate-900 font-medium bg-slate-50/40'
                            : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900',
                    ]"
                >
                    <!-- Active indicator green bar on the left (matches user screenshot) -->
                    <span
                        v-if="opt.value === modelValue"
                        class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-600 rounded-r-xs"
                    ></span>

                    <span class="truncate pl-0.5">{{ opt.label }}</span>
                </button>
            </div>
        </transition>
    </div>
</template>

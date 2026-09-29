import { reactive, computed, watch } from 'vue';

const STORAGE_KEY = 'ecostore_cart_v1';

// Load initial state from LocalStorage
const loadCart = () => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        return saved ? JSON.parse(saved) : [];
    } catch (e) {
        return [];
    }
};

const state = reactive({
    items: loadCart(),
});

// Watch and persist changes
watch(
    () => state.items,
    (items) => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (e) {
            console.error('Failed to save cart to localStorage', e);
        }
    },
    { deep: true }
);

export const useCart = () => {
    const items = computed(() => state.items);

    const totalCount = computed(() => {
        return state.items.reduce((sum, item) => sum + item.quantity, 0);
    });

    const totalAmount = computed(() => {
        return state.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    });

    const addItem = (product, qty = 1) => {
        const existing = state.items.find((item) => item.id === product.id);
        if (existing) {
            existing.quantity += qty;
        } else {
            state.items.push({
                id: product.id,
                name: product.name,
                price: Number(product.price),
                image_url: product.image_url,
                slug: product.slug,
                quantity: qty,
            });
        }
    };

    const updateQuantity = (productId, qty) => {
        const index = state.items.findIndex((item) => item.id === productId);
        if (index !== -1) {
            if (qty <= 0) {
                state.items.splice(index, 1);
            } else {
                state.items[index].quantity = qty;
            }
        }
    };

    const removeItem = (productId) => {
        const index = state.items.findIndex((item) => item.id === productId);
        if (index !== -1) {
            state.items.splice(index, 1);
        }
    };

    const clearCart = () => {
        state.items.splice(0, state.items.length);
    };

    return {
        items,
        totalCount,
        totalAmount,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
    };
};

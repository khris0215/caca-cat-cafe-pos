<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Props passed from the controller
const props = defineProps({
    categories: Array,
    products: Array,
});

// Shopping cart state
const cart = ref([]);
const selectedCategory = ref(null);

// Computed: Filter products by selected category
const filteredProducts = computed(() => {
    if (!selectedCategory.value) {
        return props.products;
    }
    return props.products.filter(product => product.category_id === selectedCategory.value);
});

// Computed: Cart totals
const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const itemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

// Add product to cart
const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            notes: '',
        });
    }
};

// Remove item from cart
const removeFromCart = (productId) => {
    cart.value = cart.value.filter(item => item.id !== productId);
};

// Update quantity
const updateQuantity = (productId, delta) => {
    const item = cart.value.find(item => item.id === productId);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        }
    }
};

// Clear cart
const clearCart = () => {
    cart.value = [];
};

// Place order (for now, just log to console)
const placeOrder = () => {
    if (cart.value.length === 0) {
        alert('Cart is empty!');
        return;
    }
    console.log('Order placed:', cart.value);
    alert('Order placed! (Check console for data)');
    // We'll implement this fully in the next step
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-4rem)] gap-4 p-4 bg-gray-100">
            <!-- LEFT PANEL: Products -->
            <div class="flex-1 bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                <!-- Category Tabs -->
                <div class="flex gap-2 p-3 border-b bg-gray-50 overflow-x-auto">
                    <button 
                        @click="selectedCategory = null"
                        :class="['px-4 py-2 rounded-lg text-sm font-medium transition', 
                                selectedCategory === null ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300']"
                    >
                        All
                    </button>
                    <button 
                        v-for="category in categories" 
                        :key="category.id"
                        @click="selectedCategory = category.id"
                        :class="['px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap', 
                                selectedCategory === category.id ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300']"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <div v-if="filteredProducts.length === 0" class="text-center text-gray-500 mt-10">
                        No products available in this category.
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                        <button 
                            v-for="product in filteredProducts" 
                            :key="product.id"
                            @click="addToCart(product)"
                            class="p-3 bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-xl transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        >
                            <div class="text-xs text-gray-500">{{ product.category.name }}</div>
                            <div class="font-semibold text-sm">{{ product.name }}</div>
                            <div class="text-blue-700 font-bold text-sm">₱{{ product.price.toFixed(2) }}</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Cart -->
            <div class="w-96 bg-white rounded-lg shadow-lg flex flex-col">
                <!-- Cart Header -->
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h2 class="font-bold text-lg">🛒 Cart</h2>
                    <span class="text-sm text-gray-500">{{ itemCount }} items</span>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <div v-if="cart.length === 0" class="text-center text-gray-400 mt-10">
                        <p class="text-4xl mb-2">☕</p>
                        <p>Cart is empty</p>
                        <p class="text-sm">Tap products to add</p>
                    </div>
                    <div v-for="item in cart" :key="item.id" class="mb-3 p-3 bg-gray-50 rounded-lg border">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ item.name }}</div>
                                <div class="text-blue-700 font-bold">₱{{ (item.price * item.quantity).toFixed(2) }}</div>
                            </div>
                            <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-700 text-sm px-2">✕</button>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <button @click="updateQuantity(item.id, -1)" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full font-bold text-lg">−</button>
                            <span class="w-8 text-center font-bold">{{ item.quantity }}</span>
                            <button @click="updateQuantity(item.id, 1)" class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-full font-bold text-lg">+</button>
                            <input 
                                v-model="item.notes" 
                                placeholder="Notes (extra milk, etc.)" 
                                class="flex-1 text-xs px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-blue-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Cart Footer -->
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex justify-between text-lg font-bold mb-3">
                        <span>Total:</span>
                        <span class="text-blue-700">₱{{ cartTotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="clearCart" class="flex-1 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg font-medium transition">Clear</button>
                        <button @click="placeOrder" class="flex-2 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">✅ Place Order</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
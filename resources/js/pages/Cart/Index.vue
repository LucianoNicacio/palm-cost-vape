<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    cartItems: Array<{
        product: {
            id: number;
            name: string;
            price: number;
            image_url?: string;
            stock: number;
            formatted_price: string;
            category?: { name: string };
        };
        quantity: number;
        pricing: {
            subtotal: number;
            tax_amount: number;
            total_price: number;
        };
    }>;
    totals: {
        subtotal: number;
        tax_amount: number;
        total_price: number;
        item_count: number;
    };
    taxRate: number;
}>();

const page = usePage();
const cartCount = computed(() => page.props.cart?.item_count || 0);

const updating = ref<number | null>(null);

const updateQuantity = (productId: number, quantity: number) => {
    updating.value = productId;
    router.put(`/cart/update/${productId}`, { quantity }, {
        preserveScroll: true,
        onFinish: () => {
            updating.value = null;
        }
    });
};

const removeItem = (productId: number) => {
    updating.value = productId;
    router.delete(`/cart/remove/${productId}`, {
        preserveScroll: true,
        onFinish: () => {
            updating.value = null;
        }
    });
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        router.delete('/cart/clear');
    }
};
</script>

<template>
    <Head title="Your Cart" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <Link href="/" class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</Link>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-gray-700 hover:text-green-600">Home</Link>
                    <Link href="/shop" class="text-gray-700 hover:text-green-600">Shop</Link>
                    <Link href="/cart" class="relative p-2 text-green-600 font-medium">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span 
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 bg-green-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>
                </nav>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Your Cart</h1>
                <button
                    v-if="cartItems.length > 0"
                    @click="clearCart"
                    class="text-red-600 hover:underline text-sm"
                >
                    Clear Cart
                </button>
            </div>

            <!-- Empty Cart -->
            <div v-if="cartItems.length === 0" class="text-center py-16">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Add some products to get started!</p>
                <Link
                    href="/shop"
                    class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg"
                >
                    Browse Products
                </Link>
            </div>

            <!-- Cart Items -->
            <div v-else class="space-y-4">
                <div
                    v-for="item in cartItems"
                    :key="item.product.id"
                    class="bg-white rounded-xl shadow p-4 flex items-center gap-4"
                    :class="{ 'opacity-50': updating === item.product.id }"
                >
                    <!-- Product Image -->
                    <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <img 
                            v-if="item.product.image_url" 
                            :src="item.product.image_url" 
                            :alt="item.product.name"
                            class="w-full h-full object-cover rounded-lg"
                        />
                        <span v-else class="text-3xl">📦</span>
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ item.product.name }}</h3>
                        <p class="text-sm text-gray-500">{{ item.product.category?.name }}</p>
                        <p class="text-green-600 font-bold">{{ item.product.formatted_price }}</p>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="updateQuantity(item.product.id, item.quantity - 1)"
                            :disabled="updating === item.product.id"
                            class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center text-gray-900 font-bold text-lg"
                        >
                            -
                        </button>
                        <span class="w-8 text-center font-semibold text-gray-900">{{ item.quantity }}</span>
                        <button
                            @click="updateQuantity(item.product.id, item.quantity + 1)"
                            :disabled="updating === item.product.id || item.quantity >= item.product.stock"
                            class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center text-gray-900 font-bold text-lg"
                        >
                            +
                        </button>
                    </div>

                    <!-- Item Total -->
                    <div class="text-right w-24">
                        <p class="font-bold text-gray-900">${{ item.pricing.subtotal.toFixed(2) }}</p>
                        <p class="text-xs text-gray-500">+${{ item.pricing.tax_amount.toFixed(2) }} tax</p>
                    </div>

                    <!-- Remove Button -->
                    <button
                        @click="removeItem(item.product.id)"
                        :disabled="updating === item.product.id"
                        class="text-red-500 hover:text-red-700 disabled:opacity-50 p-2"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow p-6 mt-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-2 text-gray-600">
                        <div class="flex justify-between">
                            <span>Subtotal ({{ totals.item_count }} items)</span>
                            <span>${{ totals.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tax ({{ (taxRate * 100).toFixed(0) }}%)</span>
                            <span>${{ totals.tax_amount.toFixed(2) }}</span>
                        </div>
                        <div class="border-t pt-2 mt-2 flex justify-between text-xl font-bold text-gray-900">
                            <span>Total</span>
                            <span class="text-green-600">${{ totals.total_price.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Info Banner -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-6">
                        <p class="text-amber-800 text-sm">
                            <strong>📍 Reserve & Pickup Only</strong><br>
                            This is a reservation system. Complete your order to reserve these items for in-store pickup.
                        </p>
                    </div>

                    <!-- Checkout Button -->
                    <Link
                        href="/checkout"
                        class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-4 rounded-lg mt-6 transition"
                    >
                        Proceed to Checkout
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 mt-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
                <p>Palm Coast Vape and Glassware | 29 Old Kings Rd N, Suite 2-A | (386) 597-2838</p>
                <p class="mt-2">Must be 21+ to purchase. Please vape responsibly.</p>
            </div>
        </footer>
    </div>
</template>

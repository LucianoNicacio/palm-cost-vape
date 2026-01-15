<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    featuredProducts?: Array<any>;
    categories?: Array<any>;
    storeInfo?: {
        name: string;
        address: string;
        city: string;
        phone: string;
    };
    taxRate?: number;
}>();

const selectedProduct = ref(null);
const showModal = ref(false);
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <span class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</span>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-green-600 font-medium">Home</Link>
                    <Link href="/shop" class="text-gray-700 hover:text-green-600">Shop</Link>
                    <Link href="/cart" class="relative p-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-24">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    {{ storeInfo?.name || 'Palm Coast Vape and Glassware' }}
                </h1>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl">
                    Your local source for premium vapes, e-liquids, glassware, and accessories in Palm Coast, Florida.
                </p>
                <Link
                    href="/shop"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg inline-block transition"
                >
                    Shop Now →
                </Link>
            </div>
        </section>

        <!-- Info Banner -->
        <section class="bg-blue-600 text-white py-4 text-center">
            <p class="text-lg">
                🛒 <strong>Reserve Online, Pay at Pickup</strong> — No payment required online!
            </p>
        </section>

        <!-- Categories Section -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Shop by Category</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="`/shop?category=${category.slug}`"
                        class="bg-white rounded-xl shadow p-6 text-center hover:shadow-lg transition"
                    >
                        <div class="w-20 h-20 mx-auto mb-3 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                            <img
                                v-if="category.image_url"
                                :src="category.image_url"
                                :alt="category.name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-4xl">📦</span>
                        </div>
                        <h3 class="font-semibold text-black">{{ category.name }}</h3>
                        <p class="text-sm text-gray-500">{{ category.products_count }} items</p>
                    </Link>

                    <!-- Placeholder if no categories -->
                    <div v-if="!categories?.length" class="col-span-full text-center text-gray-500 py-8">
                        No categories yet. Import some products!
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-black">Featured Products</h2>
                    <Link href="/shop" class="text-green-600 font-semibold hover:underline">
                        View All →
                    </Link>
                </div>

                <div v-if="featuredProducts?.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div
                        v-for="product in featuredProducts"
                        :key="product.id"
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer"
                    >
                        <div class="aspect-square bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center relative">
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-5xl">📦</span>
                            <div v-if="!product.in_stock" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                Out of Stock
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 truncate">{{ product.name }}</h3>
                            <p class="text-lg font-bold text-green-600 mt-1">{{ product.formatted_price }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-gray-500">
                    <p class="mb-4">No products available yet.</p>
                    <Link href="/shop" class="text-green-600 font-semibold hover:underline">
                        Browse Shop →
                    </Link>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-16 bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold mb-6">About Us</h2>
                    <p class="text-gray-300 mb-4">
                        We are an e-cigarette and glassware retailer specializing in all things vape and glass.
                        Our knowledgeable staff is here to help you find the perfect products.
                    </p>
                    <p class="text-gray-300">
                        Located in the heart of Palm Coast, we've been serving our community with quality products and exceptional service.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-semibold mb-4">📍 Visit Us</h3>
                    <p class="text-gray-300">{{ storeInfo?.address || '29 Old Kings Rd N, Suite 2-A' }}</p>
                    <p class="text-gray-300">{{ storeInfo?.city || 'Palm Coast, FL 32137' }}</p>
                    <a :href="`tel:${(storeInfo?.phone || '(386) 597-2838').replace(/\D/g, '')}`"
                       class="text-green-400 font-semibold mt-2 block">{{ storeInfo?.phone || '(386) 597-2838' }}</a>
                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <p class="text-gray-300 text-sm">🕐 Mon-Sat: 10am-8pm</p>
                        <p class="text-gray-300 text-sm">🕐 Sun: 12pm-6pm</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
                <p>Palm Coast Vape and Glassware | 29 Old Kings Rd N, Suite 2-A | (386) 597-2838</p>
                <p class="mt-2">Must be 21+ to purchase. Please vape responsibly.</p>
            </div>
        </footer>
    </div>
</template>
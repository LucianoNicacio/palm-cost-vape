<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/layouts/MainLayout.vue';

const props = defineProps<{
    featuredProducts?: Array<any>;
    categories?: Array<any>;
    storeInfo?: {
        name: string;
        address: string;
        city: string;
        phone: string;
    };
    heroImage?: string;
}>();
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <!-- Hero Section -->
        <section
            class="relative text-white py-24 bg-cover bg-center"
            :class="heroImage ? '' : 'bg-gradient-to-r from-gray-900 to-gray-800'"
            :style="heroImage ? { backgroundImage: `url(${heroImage})` } : {}"
        >
            <!-- Dark overlay for readability -->
            <div v-if="heroImage" class="absolute inset-0 bg-black/50"></div>

            <div class="relative max-w-7xl mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    {{ storeInfo?.name || 'Palm Coast Vape and Glassware' }}
                </h1>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl">
                    Your local, American-owned source for premium vapes, e-liquids, glassware, and accessories in Palm Coast, Florida.
                </p>
                <Link
                    href="/shop"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg inline-block transition"
                >
                    Shop Now →
                </Link>
            </div>
        </section>

        <!-- Rewards Banner -->
        <section class="bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-6 text-center sm:text-left">
                <p class="text-lg font-semibold">
                    🎁 Earn <strong>$10 back</strong> for every $100 you reserve online!
                </p>
                <Link
                    href="/shop"
                    class="text-sm bg-white/20 hover:bg-white/30 px-4 py-1.5 rounded-full font-medium transition"
                >
                    Start Earning →
                </Link>
            </div>
        </section>

        <!-- Service Area Banner -->
        <section class="bg-green-50 border-b border-green-100 py-3 text-center">
            <p class="text-sm text-green-800">
                📍 Proudly serving <strong>Flagler County, FL</strong> — Reserve online, pay at pickup. No payment required online!
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
                    </Link>

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
                        <div class="aspect-square bg-gray-100 flex items-center justify-center relative">
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="w-full h-full object-contain p-3"
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

        <!-- How It Works / Rewards Section -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center text-black mb-4">Reserve Online, Earn Rewards</h2>
                <p class="text-center text-gray-500 mb-12 max-w-2xl mx-auto">
                    Skip the wait and earn store credit every time you reserve through our website.
                </p>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🛒</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-lg mb-2">1. Reserve Online</h3>
                        <p class="text-gray-500 text-sm">Browse our catalog, add products to your cart, and place a reservation. No payment needed online.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🏪</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-lg mb-2">2. Pick Up &amp; Pay In Store</h3>
                        <p class="text-gray-500 text-sm">We'll have your order ready. Stop by, pay in person, and you're all set.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🎁</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-lg mb-2">3. Earn $10 for Every $100</h3>
                        <p class="text-gray-500 text-sm">Rewards are added to your account automatically. Redeem them on your next online reservation.</p>
                    </div>
                </div>
                <div class="text-center mt-10">
                    <Link
                        href="/shop"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg inline-block transition"
                    >
                        Browse &amp; Reserve →
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
                        We are an American-owned e-cigarette and glassware retailer specializing in all things vape and glass.
                        Our knowledgeable staff is here to help you find the perfect products.
                    </p>
                    <p class="text-gray-300 mb-6">
                        Located in the heart of Palm Coast, we're committed to our local Flagler County community, providing personalized service you won't find from big box retailers.
                    </p>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-green-400 uppercase tracking-wide">Quality You Can Trust</p>
                        <ul class="text-gray-300 text-sm space-y-1">
                            <li>✓ Quality American-made products</li>
                            <li>✓ FDA-compliant, lab-tested e-liquids</li>
                            <li>✓ Knowledgeable local staff</li>
                        </ul>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-semibold mb-4">📍 Visit Us</h3>
                    <p class="text-gray-300">{{ storeInfo?.address || '29 Old Kings Rd N, Suite 2-A' }}</p>
                    <p class="text-gray-300">{{ storeInfo?.city || 'Palm Coast, FL 32137' }}</p>
                    <p class="mt-2">
                        <a href="tel:+13865972838" class="text-green-400 hover:text-green-300 font-semibold">
                            {{ storeInfo?.phone || '(386) 597-2838' }}
                        </a>
                    </p>
                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <p class="text-gray-300 text-sm">🕐 Mon-Sat: 10am-8pm</p>
                        <p class="text-gray-300 text-sm">🕐 Sun: 12pm-6pm</p>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
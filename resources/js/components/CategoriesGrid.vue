<script setup lang="ts">
/**
 * CategoriesGrid Component
 * 
 * Displays category cards on the homepage with images or fallback icons.
 * Replace your current categories section with this component.
 */
import { Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image_url: string | null;
    code: string;
    products_count?: number;
}

defineProps<{
    categories: Category[];
}>();

// Fallback icons based on category code
const getCategoryIcon = (code: string): string => {
    const icons: Record<string, string> = {
        'DISP': '💨',
        'ELIQ': '🧪',
        'PODS': '🔋',
        'MODS': '⚡',
        'COIL': '🔩',
        'ACCS': '🛠️',
        'NIC': '📦',
        'CBD': '🌿',
        'GLASS': '🪟',
    };
    return icons[code] || '📦';
};

// Gradient backgrounds for categories without images
const getCategoryGradient = (index: number): string => {
    const gradients = [
        'from-green-400 to-green-600',
        'from-blue-400 to-blue-600',
        'from-purple-400 to-purple-600',
        'from-orange-400 to-orange-600',
        'from-pink-400 to-pink-600',
        'from-teal-400 to-teal-600',
        'from-indigo-400 to-indigo-600',
        'from-red-400 to-red-600',
    ];
    return gradients[index % gradients.length];
};
</script>

<template>
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Shop by Category
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Browse our wide selection of premium vaping products
                </p>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <Link
                    v-for="(category, index) in categories"
                    :key="category.id"
                    :href="`/shop?category=${category.id}`"
                    class="group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
                >
                    <!-- Category Image or Gradient Background -->
                    <div class="aspect-square relative">
                        <!-- With Image -->
                        <img
                            v-if="category.image_url"
                            :src="category.image_url"
                            :alt="category.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        
                        <!-- Without Image - Gradient + Icon -->
                        <div
                            v-else
                            :class="[
                                'w-full h-full flex items-center justify-center bg-gradient-to-br',
                                getCategoryGradient(index)
                            ]"
                        >
                            <span class="text-6xl opacity-80 group-hover:scale-110 transition-transform duration-300">
                                {{ getCategoryIcon(category.code) }}
                            </span>
                        </div>
                        
                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                    </div>

                    <!-- Category Info -->
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors">
                            {{ category.name }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ category.products_count || 0 }} items
                        </p>
                    </div>
                </Link>
            </div>

            <!-- View All Link -->
            <div class="text-center mt-8">
                <Link
                    href="/shop"
                    class="inline-flex items-center text-green-600 hover:text-green-700 font-medium"
                >
                    View All Products
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </section>
</template>

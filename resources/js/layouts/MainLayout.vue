<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const page = usePage();
const cartCount = computed(() => page.props.cartCount || 0);
const flash = computed(() => page.props.flash || {});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Flash Messages -->
        <div
            v-if="flash.success"
            class="bg-green-500 text-white px-4 py-3 text-center"
        >
            {{ flash.success }}
        </div>
        <div
            v-if="flash.error"
            class="bg-red-500 text-white px-4 py-3 text-center"
        >
            {{ flash.error }}
        </div>

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div
                class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16"
            >
                <Link :href="route('home')" class="text-xl font-bold">
                    Palm Coast Vape
                </Link>

                <nav class="hidden md:flex items-center gap-6">
                    <Link
                        :href="route('home')"
                        class="text-gray-700 hover:text-green-600"
                    >
                        Home
                    </Link>
                    <Link
                        :href="route('shop')"
                        class="text-gray-700 hover:text-green-600"
                    >
                        Shop
                    </Link>
                </nav>

                <Link :href="route('cart.index')" class="relative p-2">
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-1 -right-1 bg-green-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8">
            <div
                class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm"
            >
                <p>
                    Palm Coast Vape and Glassware | 29 Old Kings Rd N, Suite 2-A
                    | (386) 597-2838
                </p>
                <p class="mt-2">Must be 21+ to purchase.</p>
            </div>
        </footer>
    </div>
</template>
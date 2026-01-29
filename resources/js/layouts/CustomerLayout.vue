<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const flash = computed(() => (page.props as any).flash || {});

const nav = [
    { name: 'Dashboard', href: '/account', icon: '📊' },
    { name: 'My Orders', href: '/account/orders', icon: '📦' },
    { name: 'Profile', href: '/account/profile', icon: '👤' },
];

const isActive = (href: string) => {
    const path = window.location.pathname;
    if (href === '/account') return path === '/account';
    return path.startsWith(href);
};

const logout = () => {
    router.post('/account/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <div class="flex items-center gap-8">
                    <Link href="/" class="text-xl font-bold text-gray-900">
                        🌴 Palm Coast Vape
                    </Link>
                    <nav class="hidden md:flex items-center gap-6">
                        <Link href="/" class="text-gray-600 hover:text-green-600">
                            Shop
                        </Link>
                        <Link href="/cart" class="text-gray-600 hover:text-green-600">
                            Cart
                        </Link>
                    </nav>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ user?.name }}</span>
                    <button
                        @click="logout"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar -->
                <aside class="md:w-64 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow p-4">
                        <h2 class="font-semibold text-gray-900 mb-4 px-3">My Account</h2>
                        <nav class="space-y-1">
                            <Link
                                v-for="item in nav"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-lg transition',
                                    isActive(item.href)
                                        ? 'bg-green-50 text-green-700'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <span>{{ item.icon }}</span>
                                <span>{{ item.name }}</span>
                            </Link>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1">
                    <!-- Flash Messages -->
                    <div
                        v-if="flash.success"
                        class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg"
                    >
                        {{ flash.success }}
                    </div>
                    <div
                        v-if="flash.error"
                        class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
                    >
                        {{ flash.error }}
                    </div>
                    <div
                        v-if="flash.warning"
                        class="mb-6 bg-yellow-100 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg"
                    >
                        {{ flash.warning }}
                    </div>

                    <slot />
                </main>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 mt-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
                <p>Palm Coast Vape and Glassware | 29 Old Kings Rd N, Suite 2-A | (386) 597-2838</p>
                <p class="mt-2">Must be 21+ to purchase.</p>
            </div>
        </footer>
    </div>
</template>
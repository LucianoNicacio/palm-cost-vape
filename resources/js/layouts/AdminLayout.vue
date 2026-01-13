<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const flash = computed(() => (page.props as any).flash || {});

const nav = [
    { name: 'Dashboard', href: '/admin', icon: '📊' },
    { name: 'Reservations', href: '/admin/reservations', icon: '📋' },
    { name: 'Products', href: '/admin/products', icon: '📦' },
    { name: 'Categories', href: '/admin/categories', icon: '📂' },
    { name: 'Customers', href: '/admin/customers', icon: '👥' },
    { name: 'Import Products', href: '/admin/products-import', icon: '📥' },
];

const isActive = (href: string) => {
    const path = window.location.pathname;
    if (href === '/admin') return path === '/admin';
    return path.startsWith(href);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900">
            <div class="h-16 flex items-center px-4 border-b border-gray-800">
                <span class="text-white font-bold text-lg">🛡️ PCV Admin</span>
            </div>
            <nav class="mt-4 px-2">
                <Link
                    v-for="item in nav"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-lg mb-1 transition',
                        isActive(item.href)
                            ? 'bg-green-600 text-white'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                    ]"
                >
                    <span>{{ item.icon }}</span>
                    <span>{{ item.name }}</span>
                </Link>
            </nav>
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
                <p class="text-white text-sm mb-2">{{ user?.name }}</p>
                <div class="flex gap-4">
                    <Link href="/" class="text-gray-400 text-sm hover:text-white">
                        ← View Store
                    </Link>
                    <button @click="logout" class="text-gray-400 text-sm hover:text-white">
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="ml-64">
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6">
                <h1 class="text-xl font-semibold text-gray-900">{{ title }}</h1>
            </header>

            <!-- Flash Messages -->
            <div
                v-if="flash.success"
                class="mx-6 mt-4 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mx-6 mt-4 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
            >
                {{ flash.error }}
            </div>

            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

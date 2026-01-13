<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Category {
    id: number;
    code: string;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    image_url: string | null;
    products_count: number;
}

interface PaginatedCategories {
    data: Category[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    categories: PaginatedCategories;
    filters: {
        search: string;
        status: string;
    };
}>();

// Local filter state
const search = ref(props.filters.search);
const status = ref(props.filters.status);

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout>;
const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
};

// Apply filters
const applyFilters = () => {
    router.get('/admin/categories', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get('/admin/categories');
};

// Delete category
const deleteCategory = (category: Category) => {
    if (category.products_count > 0) {
        alert(`Cannot delete "${category.name}". It has ${category.products_count} products assigned.`);
        return;
    }

    if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
        router.delete(`/admin/categories/${category.slug}`);
    }
};

// Category icons based on code (fallback if no image)
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
</script>

<template>
    <AdminLayout title="Categories">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                <p class="text-gray-600 mt-1">Manage product categories</p>
            </div>
            <Link
                href="/admin/categories/create"
                class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                <span class="mr-2">+</span>
                Add Category
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Search categories..."
                        class="w-full px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                </div>

                <!-- Status Filter -->
                <select
                    v-model="status"
                    @change="applyFilters"
                    class="px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <!-- Clear Filters -->
                <button
                    v-if="search || status"
                    @click="clearFilters"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Category
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Code
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Products
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr
                    v-for="category in categories.data"
                    :key="category.id"
                    class="hover:bg-gray-50"
                >
                    <!-- Category with Image -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <img
                                    v-if="category.image_url"
                                    :src="category.image_url"
                                    :alt="category.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-2xl">
                                        {{ getCategoryIcon(category.code) }}
                                    </span>
                            </div>
                            <div>
                                <Link
                                    :href="`/admin/categories/${category.slug}/edit`"
                                    class="font-medium text-gray-900 hover:text-green-600"
                                >
                                    {{ category.name }}
                                </Link>
                                <p v-if="category.description" class="text-sm text-gray-500 truncate max-w-xs">
                                    {{ category.description }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Code -->
                    <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-sm font-mono rounded">
                                {{ category.code }}
                            </span>
                    </td>

                    <!-- Products Count -->
                    <td class="px-6 py-4">
                        <Link
                            :href="`/admin/products?category=${category.id}`"
                            class="text-green-600 hover:text-green-700"
                        >
                            {{ category.products_count }} products
                        </Link>
                    </td>

                    <!-- Sort Order -->
                    <td class="px-6 py-4 text-gray-500">
                        {{ category.sort_order }}
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    category.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-800'
                                ]"
                            >
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="`/admin/categories/${category.slug}/edit`"
                                class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                title="Edit"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deleteCategory(category)"
                                :disabled="category.products_count > 0"
                                :class="[
                                        'p-2 rounded-lg transition',
                                        category.products_count > 0
                                            ? 'text-gray-300 cursor-not-allowed'
                                            : 'text-gray-600 hover:text-red-600 hover:bg-red-50'
                                    ]"
                                :title="category.products_count > 0 ? 'Cannot delete: has products' : 'Delete'"
                            >
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="categories.data.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="text-4xl mb-2">📂</div>
                        <p class="font-medium">No categories found</p>
                        <p class="text-sm">Create your first category to get started.</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="categories.last_page > 1" class="px-6 py-4 border-t bg-gray-50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Showing {{ categories.data.length }} of {{ categories.total }} categories
                    </p>
                    <nav class="flex gap-1">
                        <template v-for="(link, index) in categories.links" :key="index">
                            <span
                                v-if="!link.url"
                                class="px-3 py-1 text-gray-400"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 rounded',
                                    link.active
                                        ? 'bg-green-600 text-white'
                                        : 'text-gray-600 hover:bg-gray-200'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
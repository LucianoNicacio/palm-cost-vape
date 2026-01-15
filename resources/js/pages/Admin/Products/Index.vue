<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    price: number;
    formatted_price: string;
    stock: number;
    track_inventory: boolean;
    is_active: boolean;
    is_featured: boolean;
    in_stock: boolean;
    image_url: string | null;
    category: Category | null;
    brand: string | null;
}

interface PaginatedProducts {
    data: Product[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    products: PaginatedProducts;
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
        status?: string;
    };
    stats: {
        total: number;
        active: number;
        featured: number;
        low_stock: number;
        out_of_stock: number;
    };
}>();

const search = (e: Event) => {
    const target = e.target as HTMLInputElement;
    router.get('/admin/products', {
        ...props.filters,
        search: target.value || undefined,
    }, { preserveState: true });
};

const filterByCategory = (categoryId: string) => {
    router.get('/admin/products', {
        ...props.filters,
        category: categoryId || undefined,
    }, { preserveState: true });
};

const filterByStatus = (status: string) => {
    router.get('/admin/products', {
        ...props.filters,
        status: status || undefined,
    }, { preserveState: true });
};

const goToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

const deleteProduct = (product: Product) => {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
        router.delete(`/admin/products/${product.id}`);
    }
};
</script>

<template>
    <AdminLayout title="Products">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">Total Products</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">Active</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">⭐ Featured</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats.featured }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">Low Stock</p>
                <p class="text-2xl font-bold text-yellow-600">{{ stats.low_stock }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">Out of Stock</p>
                <p class="text-2xl font-bold text-red-600">{{ stats.out_of_stock }}</p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                placeholder="Search by name or SKU..."
                :value="filters.search"
                @input="search"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
            />
            <select
                :value="filters.category || ''"
                @change="filterByCategory(($event.target as HTMLSelectElement).value)"
                class="px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
            >
                <option value="">All Categories</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
            <select
                :value="filters.status || ''"
                @change="filterByStatus(($event.target as HTMLSelectElement).value)"
                class="px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="featured">⭐ Featured</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
            <a
                href="/admin/products/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center whitespace-nowrap"
            >
                + Add Product
            </a>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="w-10 h-10 rounded-lg object-cover"
                            />
                            <div v-else class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400">
                                📦
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">
                                    <span v-if="product.is_featured" class="text-yellow-500 mr-1">⭐</span>
                                    {{ product.name }}
                                </p>
                                <p v-if="product.brand" class="text-sm text-gray-500">{{ product.brand }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 font-mono text-sm">
                        {{ product.sku }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ product.category?.name || '-' }}
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900">
                        {{ product.formatted_price }}
                    </td>
                    <td class="px-6 py-4">
                        <span v-if="!product.track_inventory" class="text-gray-500">-</span>
                        <span
                            v-else
                            :class="[
                                    'font-medium',
                                    product.stock <= 0 ? 'text-red-600' :
                                    product.stock <= 5 ? 'text-yellow-600' : 'text-gray-900'
                                ]"
                        >
                                {{ product.stock }}
                            </span>
                    </td>
                    <td class="px-6 py-4">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    product.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600'
                                ]"
                            >
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a
                                :href="`/admin/products/${product.id}/edit`"
                                class="text-green-600 hover:text-green-800 font-medium"
                            >
                                Edit
                            </a>
                            <button
                                @click="deleteProduct(product)"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="products.data.length === 0">
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        No products found
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing {{ products.data.length }} of {{ products.total }} products
                </p>
                <div class="flex gap-2">
                    <button
                        v-for="link in products.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1 text-sm rounded',
                            link.active
                                ? 'bg-green-600 text-white'
                                : link.url
                                    ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
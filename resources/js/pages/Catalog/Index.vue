<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    products: {
        data: Array<any>;
        links: Array<any>;
        from: number;
        to: number;
        total: number;
        last_page: number;
    };
    categories: Array<any>;
    filters: {
        search?: string;
        category?: string;
        in_stock?: boolean;
        sort?: string;
    };
    taxRate: number;
}>();

// Get cart from shared props
const page = usePage();
const cartCount = computed(() => page.props.cart_count || 0);

// Modal state
const showModal = ref(false);
const selectedProduct = ref<any>(null);
const quantity = ref(1);
const adding = ref(false);

const openModal = (product: any) => {
    if (product.stock <= 0) return;
    selectedProduct.value = product;
    quantity.value = 1;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedProduct.value = null;
    quantity.value = 1;
};

const addToCart = () => {
    if (!selectedProduct.value) return;
    adding.value = true;
    
    router.post(`/cart/add/${selectedProduct.value.id}`, {
        quantity: quantity.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onFinish: () => {
            adding.value = false;
        }
    });
};

const itemTotal = computed(() => {
    if (!selectedProduct.value) return 0;
    const subtotal = selectedProduct.value.price * quantity.value;
    const tax = subtotal * props.taxRate;
    return (subtotal + tax).toFixed(2);
});

// Filter state
const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const inStockOnly = ref(props.filters?.in_stock || false);
const sortBy = ref(props.filters?.sort === 'price' || props.filters?.sort === 'created_at' ? props.filters.sort : '');

// Sort dropdown
const sortOpen = ref(false);
const sortOptions = [
    { value: '', label: 'Name (A-Z)' },
    { value: 'price', label: 'Price (Low-High)' },
    { value: 'created_at', label: 'Newest' },
];
const sortLabel = computed(() => sortOptions.find(o => o.value === sortBy.value)?.label || 'Name (A-Z)');
const selectSort = (value: string) => {
    sortBy.value = value;
    sortOpen.value = false;
};

// Debounce timer
let searchTimeout: ReturnType<typeof setTimeout>;

// Apply filters (resets to page 1 intentionally when filters change)
const applyFilters = () => {
    const params: Record<string, any> = {};

    if (selectedCategory.value) params.category = selectedCategory.value;
    if (search.value) params.search = search.value;
    if (inStockOnly.value) params.in_stock = '1';
    if (sortBy.value) params.sort = sortBy.value;

    router.get('/shop', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Watch for filter changes
watch([selectedCategory, inStockOnly, sortBy], applyFilters);

// Debounced search
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

// Clear all filters
const clearFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    inStockOnly.value = false;
    sortBy.value = '';
    router.get('/shop');
};

// Check if any filters active
const hasFilters = computed(() => {
    return search.value || selectedCategory.value || inStockOnly.value;
});
</script>

<template>
    <Head title="Shop" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <Link href="/" class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</Link>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-gray-700 hover:text-green-600">Home</Link>
                    <Link href="/shop" class="text-green-600 font-medium">Shop</Link>
                    <Link href="/cart" class="relative p-2 text-gray-700 hover:text-green-600">
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

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow p-6 sticky top-20">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Filters</h2>
                            <button
                                v-if="hasFilters"
                                @click="clearFilters"
                                class="text-sm text-red-600 hover:underline"
                            >
                                Clear
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search products..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-900"
                            />
                        </div>

                        <!-- Category -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select
                                v-model="selectedCategory"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 bg-white text-gray-900"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.slug"
                                >
                                    {{ cat.name }} ({{ cat.products_count }})
                                </option>
                            </select>
                        </div>

                        <!-- In Stock Only -->
                        <div class="mb-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="inStockOnly"
                                    type="checkbox"
                                    class="rounded text-green-600 focus:ring-green-500"
                                />
                                <span class="text-sm text-gray-700">In stock only</span>
                            </label>
                        </div>

                        <!-- Sort By -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                            <button
                                @click="sortOpen = !sortOpen"
                                class="w-full flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-900 text-sm focus:ring-2 focus:ring-green-500"
                            >
                                <span>{{ sortLabel }}</span>
                                <svg class="w-4 h-4 text-gray-400" :class="{ 'rotate-180': sortOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                v-if="sortOpen"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg py-1"
                            >
                                <button
                                    v-for="option in sortOptions"
                                    :key="option.value"
                                    @click="selectSort(option.value)"
                                    class="w-full text-left px-3 py-2 text-sm transition"
                                    :class="sortBy === option.value
                                        ? 'bg-green-50 text-green-700 font-medium'
                                        : 'text-gray-700 hover:bg-gray-50'"
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Products Grid -->
                <main class="flex-1">
                    <p class="text-gray-600 mb-6">
                        Showing {{ products?.from || 0 }}-{{ products?.to || 0 }} of {{ products?.total || 0 }} products
                    </p>

                    <div
                        v-if="products?.data?.length"
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
                    >
                        <div
                            v-for="product in products.data"
                            :key="product.id"
                            @click="openModal(product)"
                            class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer group"
                        >
                            <div class="aspect-square bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center relative">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-5xl">📦</span>
                                <div class="absolute bottom-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                    {{ product.category?.name || 'Uncategorized' }}
                                </div>
                                <div v-if="product.stock <= 0" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                    Out of Stock
                                </div>
                                <!-- Hover overlay -->
                                <div 
                                    v-if="product.stock > 0"
                                    class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <span class="bg-white text-green-600 font-semibold px-4 py-2 rounded-lg">
                                        + Add to Cart
                                    </span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 line-clamp-2">{{ product.name }}</h3>
                                <p class="text-lg font-bold text-green-600 mt-1">{{ product.formatted_price }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-16">
                        <p class="text-gray-500 mb-4">No products found</p>
                        <button
                            @click="clearFilters"
                            class="text-green-600 font-semibold hover:underline"
                        >
                            Clear filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products?.last_page > 1" class="flex justify-center mt-8">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, index) in products.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-2 rounded-lg text-sm transition"
                                    :class="link.active ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    :preserve-state="true"
                                    :preserve-scroll="true"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm text-gray-400"
                                />
                            </template>
                        </nav>
                    </div>
                </main>
            </div>
        </div>

        <!-- Product Modal -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @click.self="closeModal"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
            
            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
                <button 
                    @click="closeModal" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div v-if="selectedProduct" class="text-center">
                    <!-- Product Image -->
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center mb-4">
                        <img 
                            v-if="selectedProduct.image_url" 
                            :src="selectedProduct.image_url" 
                            :alt="selectedProduct.name"
                            class="w-full h-full object-cover rounded-xl"
                        />
                        <span v-else class="text-5xl">📦</span>
                    </div>

                    <!-- Product Info -->
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ selectedProduct.name }}</h3>
                    <p class="text-gray-500 text-sm mb-2">{{ selectedProduct.category?.name }}</p>
                    <p class="text-2xl font-bold text-green-600 mb-4">{{ selectedProduct.formatted_price }}</p>

                    <!-- Quantity Selector -->
                    <div class="flex items-center justify-center gap-4 mb-6">
                        <button
                            @click="quantity = Math.max(1, quantity - 1)"
                            class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xl font-bold text-gray-900"
                        >
                            −
                        </button>
                        <span class="text-2xl font-bold w-12 text-center text-gray-900">{{ quantity }}</span>
                        <button
                            @click="quantity = Math.min(selectedProduct.stock, quantity + 1)"
                            class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xl font-bold text-gray-900"
                        >
                            +
                        </button>
                    </div>

                    <!-- Total -->
                    <div class="bg-gray-100 rounded-lg p-4 mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Subtotal</span>
                            <span>${{ (selectedProduct.price * quantity).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Tax ({{ (taxRate * 100).toFixed(0) }}%)</span>
                            <span>${{ (selectedProduct.price * quantity * taxRate).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total</span>
                            <span class="text-green-600">${{ itemTotal }}</span>
                        </div>
                    </div>

                    <!-- Add to Cart Button -->
                    <button
                        @click="addToCart"
                        :disabled="adding"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 rounded-lg transition"
                    >
                        {{ adding ? 'Adding...' : 'Add to Cart' }}
                    </button>
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

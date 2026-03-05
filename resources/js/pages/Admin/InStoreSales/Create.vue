<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface SearchProduct {
    id: number;
    name: string;
    price: number;
    formatted_price: string;
    stock: number;
    track_inventory: boolean;
}

interface CartItem {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    max_stock: number;
    track_inventory: boolean;
}

const page = usePage();
const errors = computed(() => (page.props as any).errors || {});

const searchQuery = ref('');
const searchResults = ref<SearchProduct[]>([]);
const searchLoading = ref(false);
const showDropdown = ref(false);
const cart = ref<CartItem[]>([]);
const saving = ref(false);

let debounceTimer: ReturnType<typeof setTimeout>;

watch(searchQuery, (val) => {
    clearTimeout(debounceTimer);
    if (val.length < 1) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }
    debounceTimer = setTimeout(async () => {
        searchLoading.value = true;
        try {
            const response = await fetch(`/admin/products/search?q=${encodeURIComponent(val)}`);
            searchResults.value = await response.json();
            showDropdown.value = searchResults.value.length > 0;
        } catch {
            searchResults.value = [];
        }
        searchLoading.value = false;
    }, 300);
});

const addToCart = (product: SearchProduct) => {
    const existing = cart.value.find(item => item.product_id === product.id);
    if (existing) {
        const maxQty = product.track_inventory ? product.stock : 9999;
        if (existing.quantity < maxQty) {
            existing.quantity++;
        }
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            max_stock: product.track_inventory ? product.stock : 9999,
            track_inventory: product.track_inventory,
        });
    }
    searchQuery.value = '';
    searchResults.value = [];
    showDropdown.value = false;
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const totalItems = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));
const totalAmount = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

const fmt = (value: number) => '$' + value.toFixed(2);

const submit = () => {
    if (cart.value.length === 0) return;
    saving.value = true;

    router.post('/admin/in-store-sales', {
        items: cart.value.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
        })),
    }, {
        onFinish: () => { saving.value = false; },
    });
};

const closeDropdown = () => {
    setTimeout(() => { showDropdown.value = false; }, 200);
};
</script>

<template>
    <AdminLayout title="Record In-Store Sale">
        <div class="mb-4">
            <a href="/admin/in-store-sales" class="text-green-600 hover:text-green-800">
                ← Back to In-Store Sales
            </a>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white rounded-xl shadow p-6 space-y-6">
                <!-- Errors -->
                <div v-if="errors.stock" class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ errors.stock }}
                </div>
                <div v-if="errors.error" class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ errors.error }}
                </div>

                <!-- Product Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Type to search products by name or SKU..."
                            @blur="closeDropdown"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                        />
                        <div v-if="searchLoading" class="absolute right-3 top-2.5 text-gray-400 text-sm">
                            Searching...
                        </div>

                        <!-- Dropdown Results -->
                        <div
                            v-if="showDropdown"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <button
                                v-for="product in searchResults"
                                :key="product.id"
                                @mousedown.prevent="addToCart(product)"
                                class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between border-b last:border-b-0"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ product.name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ product.formatted_price }}
                                        <span v-if="product.track_inventory"> · {{ product.stock }} in stock</span>
                                        <span v-else> · Not tracking inventory</span>
                                    </p>
                                </div>
                                <span class="text-green-600 text-sm font-medium">+ Add</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart -->
                <div v-if="cart.length > 0">
                    <h3 class="font-semibold text-gray-900 mb-3">Sale Items</h3>
                    <div class="border border-gray-200 rounded-lg divide-y">
                        <div
                            v-for="(item, index) in cart"
                            :key="item.product_id"
                            class="p-4 flex items-center justify-between gap-4"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ item.name }}</p>
                                <p class="text-sm text-gray-500">{{ fmt(item.price) }} each</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    :max="item.max_stock"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-center text-gray-900 focus:ring-2 focus:ring-green-500"
                                />
                                <span class="w-20 text-right font-medium text-gray-900">
                                    {{ fmt(item.price * item.quantity) }}
                                </span>
                                <button
                                    @click="removeFromCart(index)"
                                    class="text-red-500 hover:text-red-700 text-sm font-medium"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-400">
                    Search and add products to record a sale.
                </div>

                <!-- Totals -->
                <div v-if="cart.length > 0" class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Items</span>
                        <span>{{ totalItems }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold text-gray-900">
                        <span>Total Amount</span>
                        <span>{{ fmt(totalAmount) }}</span>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-4 pt-4 border-t">
                    <button
                        @click="submit"
                        :disabled="saving || cart.length === 0"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-medium"
                    >
                        {{ saving ? 'Recording...' : 'Record Sale' }}
                    </button>
                    <a
                        href="/admin/in-store-sales"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

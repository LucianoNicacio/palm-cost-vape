<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/CustomerLayout.vue';

interface OrderItem {
    id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
    product: {
        id: number;
        image_url: string | null;
        in_stock: boolean;
    } | null;
}

interface Order {
    id: number;
    confirmation_number: string;
    status: string;
    status_label: string;
    total_price: number;
    item_count: number;
    items: OrderItem[];
    created_at: string;
    created_at_formatted: string;
}

interface PaginatedOrders {
    data: Order[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    orders: PaginatedOrders;
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700',
        confirmed: 'bg-blue-100 text-blue-700',
        ready: 'bg-green-100 text-green-700',
        completed: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const reorder = (orderId: number) => {
    router.post(`/account/orders/${orderId}/reorder`);
};
</script>

<template>
    <Head title="My Orders" />

    <CustomerLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
                <Link 
                    href="/shop" 
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                >
                    + New Order
                </Link>
            </div>

            <!-- Orders List -->
            <div v-if="orders.data.length > 0" class="space-y-4">
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="bg-white rounded-xl shadow overflow-hidden"
                >
                    <!-- Order Header -->
                    <div class="p-4 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-gray-900">{{ order.confirmation_number }}</span>
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(order.status)]">
                                    {{ order.status_label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ order.created_at_formatted }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-lg text-gray-900">{{ fmt(order.total_price) }}</span>
                        </div>
                    </div>

                    <!-- Order Items Preview -->
                    <div class="p-4">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <div
                                v-for="item in order.items.slice(0, 4)"
                                :key="item.id"
                                class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2"
                            >
                                <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center overflow-hidden">
                                    <img 
                                        v-if="item.product?.image_url" 
                                        :src="item.product.image_url" 
                                        :alt="item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-lg">📦</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-32">{{ item.product_name }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                                </div>
                            </div>
                            <div 
                                v-if="order.items.length > 4" 
                                class="flex items-center px-3 py-2 text-sm text-gray-500"
                            >
                                +{{ order.items.length - 4 }} more
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <Link
                                :href="`/account/orders/${order.id}`"
                                class="text-green-600 hover:text-green-700 font-medium text-sm"
                            >
                                View Details →
                            </Link>
                            <button
                                v-if="order.status === 'completed'"
                                @click="reorder(order.id)"
                                class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                            >
                                🔄 Reorder
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <template v-for="link in orders.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 rounded-lg text-sm',
                                link.active
                                    ? 'bg-green-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-100'
                            ]"
                            preserve-scroll
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-2 text-sm text-gray-400"
                        />
                    </template>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow p-12 text-center">
                <div class="text-6xl mb-4">📦</div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h2>
                <p class="text-gray-500 mb-6">When you place an order, it will appear here.</p>
                <Link 
                    href="/shop" 
                    class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700"
                >
                    Start Shopping
                </Link>
            </div>
        </div>
    </CustomerLayout>
</template>

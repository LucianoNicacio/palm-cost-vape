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
        name: string;
        image_url: string | null;
        price: number;
        in_stock: boolean;
        stock: number;
    } | null;
}

interface Order {
    id: number;
    confirmation_number: string;
    status: string;
    status_label: string;
    subtotal: number;
    tax_amount: number;
    total_price: number;
    notes: string | null;
    created_at: string;
    created_at_formatted: string;
    items: OrderItem[];
}

interface StoreInfo {
    name: string;
    address: string;
    city: string;
    phone: string;
}

defineProps<{
    order: Order;
    storeInfo: StoreInfo;
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        confirmed: 'bg-blue-100 text-blue-700 border-blue-200',
        ready: 'bg-green-100 text-green-700 border-green-200',
        completed: 'bg-gray-100 text-gray-700 border-gray-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const getStatusMessage = (status: string) => {
    const messages: Record<string, string> = {
        pending: '⏳ Your order is being reviewed by our team.',
        confirmed: '✅ Your order has been confirmed and is being prepared.',
        ready: '🎉 Your order is ready for pickup!',
        completed: '✓ This order has been picked up.',
        cancelled: '✗ This order was cancelled.',
    };
    return messages[status] || '';
};

const reorder = (orderId: number) => {
    router.post(`/account/orders/${orderId}/reorder`);
};
</script>

<template>
    <Head :title="`Order ${order.confirmation_number}`" />

    <CustomerLayout>
        <div class="space-y-6">
            <!-- Back Link -->
            <Link 
                href="/account/orders" 
                class="text-green-600 hover:text-green-700 inline-flex items-center gap-1"
            >
                ← Back to Orders
            </Link>

            <!-- Order Header -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ order.confirmation_number }}</h1>
                        <p class="text-gray-500 mt-1">{{ order.created_at_formatted }}</p>
                    </div>
                    <span :class="['px-4 py-2 rounded-lg border text-sm font-medium', getStatusColor(order.status)]">
                        {{ order.status_label }}
                    </span>
                </div>

                <!-- Status Message -->
                <div 
                    v-if="getStatusMessage(order.status)"
                    :class="[
                        'mt-4 p-4 rounded-lg',
                        order.status === 'ready' ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700'
                    ]"
                >
                    {{ getStatusMessage(order.status) }}
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Order Items -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow">
                    <div class="p-4 border-b">
                        <h2 class="font-semibold text-gray-900">Order Items</h2>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="p-4 flex items-center gap-4"
                        >
                            <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img 
                                    v-if="item.product?.image_url" 
                                    :src="item.product.image_url" 
                                    :alt="item.product_name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-2xl">📦</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ fmt(item.unit_price) }} × {{ item.quantity }}
                                </p>
                                <p 
                                    v-if="item.product && !item.product.in_stock" 
                                    class="text-xs text-red-500 mt-1"
                                >
                                    Currently out of stock
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ fmt(item.total_price) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="p-4 border-t bg-gray-50">
                        <div class="space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ fmt(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>{{ fmt(order.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                                <span>Total</span>
                                <span class="text-green-600">{{ fmt(order.total_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Pickup Info -->
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">📍 Pickup Location</h3>
                        <p class="text-gray-700">{{ storeInfo.name }}</p>
                        <p class="text-gray-700">{{ storeInfo.address }}</p>
                        <p class="text-gray-700">{{ storeInfo.city }}</p>
                        <a 
                            :href="`tel:${storeInfo.phone.replace(/\D/g, '')}`"
                            class="text-green-600 font-semibold mt-2 block hover:underline"
                        >
                            {{ storeInfo.phone }}
                        </a>

                        <div v-if="order.status === 'ready'" class="mt-4 pt-4 border-t">
                            <p class="text-sm text-green-700 font-medium">
                                🎉 Your order is ready! Please bring valid ID for age verification.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow p-6 space-y-3">
                        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                        
                        <button
                            @click="reorder(order.id)"
                            class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                        >
                            🔄 Order Again
                        </button>
                        
                        <Link
                            href="/shop"
                            class="block w-full py-2 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Continue Shopping
                        </Link>
                    </div>

                    <!-- Notes -->
                    <div v-if="order.notes" class="bg-white rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-2">Notes</h3>
                        <p class="text-gray-600 text-sm">{{ order.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/CustomerLayout.vue';

interface Order {
    id: number;
    confirmation_number: string;
    status: string;
    status_label: string;
    total_price: number;
    item_count: number;
    created_at: string;
}

interface Props {
    recentOrders: Order[];
    stats: {
        total_orders: number;
        total_spent: number;
        pending_orders: number;
    };
    customer: {
        name: string;
        email: string;
        phone: string | null;
        is_subscribed: boolean;
    } | null;
}

defineProps<Props>();

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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="My Account" />

    <CustomerLayout>
        <div class="space-y-6">
            <!-- Welcome -->
            <div class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, {{ customer?.name?.split(' ')[0] || 'there' }}! 👋
                </h1>
                <p class="text-gray-600 mt-1">
                    Here's what's happening with your orders.
                </p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow p-6">
                    <p class="text-gray-500 text-sm">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ stats.total_orders }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <p class="text-gray-500 text-sm">Total Spent</p>
                    <p class="text-3xl font-bold text-green-600">{{ fmt(stats.total_spent) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <p class="text-gray-500 text-sm">Pending Pickup</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ stats.pending_orders }}</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <Link 
                        href="/account/orders" 
                        class="text-green-600 text-sm hover:underline"
                    >
                        View All →
                    </Link>
                </div>
                <div class="divide-y" v-if="recentOrders.length > 0">
                    <Link
                        v-for="order in recentOrders"
                        :key="order.id"
                        :href="`/account/orders/${order.id}`"
                        class="p-4 flex items-center justify-between hover:bg-gray-50 block"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ order.confirmation_number }}</p>
                            <p class="text-sm text-gray-500">
                                {{ order.item_count }} items · {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ fmt(order.total_price) }}</p>
                            <span :class="['inline-block px-2 py-1 text-xs rounded-full', getStatusColor(order.status)]">
                                {{ order.status_label }}
                            </span>
                        </div>
                    </Link>
                </div>
                <div v-else class="p-8 text-center text-gray-500">
                    <p class="mb-4">You haven't placed any orders yet.</p>
                    <Link 
                        href="/shop" 
                        class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700"
                    >
                        Start Shopping →
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link 
                    href="/shop" 
                    class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center gap-4"
                >
                    <span class="text-4xl">🛒</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Browse Products</h3>
                        <p class="text-sm text-gray-500">Check out our latest inventory</p>
                    </div>
                </Link>
                <Link 
                    href="/account/profile" 
                    class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center gap-4"
                >
                    <span class="text-4xl">⚙️</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Account Settings</h3>
                        <p class="text-sm text-gray-500">Update your profile and preferences</p>
                    </div>
                </Link>
            </div>
        </div>
    </CustomerLayout>
</template>

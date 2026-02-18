<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps<{
    stats: {
        total_reservations: number;
        completed_reservations: number;
        pending_reservations: number;
        total_revenue: number;
        total_items_sold: number;
        average_order_value: number;
    };
    recentReservations: Array<{
        id: number;
        confirmation_number: string;
        customer?: { name: string };
        item_count: number;
        total_price: number;
        status: string;
        status_label: string;
    }>;
    statusCounts: {
        pending: number;
        ready: number;
        completed: number;
        cancelled: number;
    };
    dailyRevenue: Array<{ date: string; revenue: number }>;
    quickStats: {
        total_customers: number;
        new_customers_this_month: number;
        low_stock_products: number;
    };
    period: string;
    periodLabel: string;
    filters: { start_date?: string; end_date?: string };
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const changePeriod = (newPeriod: string) => {
    router.get('/admin', { period: newPeriod }, { preserveState: true });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700',
        ready: 'bg-green-100 text-green-700',
        completed: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const maxRevenue = Math.max(...props.dailyRevenue.map(d => d.revenue || 1), 1);
</script>

<template>
    <AdminLayout title="Dashboard">
        <!-- Period Filter -->
        <div class="mb-6 flex gap-2">
            <button
                v-for="p in ['today', 'week', 'month', 'year']"
                :key="p"
                @click="changePeriod(p)"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    period === p
                        ? 'bg-green-600 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                {{ p === 'today' ? 'Today' : p === 'week' ? 'This Week' : p === 'month' ? 'This Month' : 'This Year' }}
            </button>
        </div>

        <p class="text-gray-500 mb-6">Showing data for: <strong class="text-gray-900">{{ periodLabel }}</strong></p>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-500 text-sm">Total Reservations</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.total_reservations }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-500 text-sm">Revenue</p>
                <p class="text-3xl font-bold text-green-600">{{ fmt(stats.total_revenue) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-500 text-sm">Pending Orders</p>
                <p class="text-3xl font-bold text-yellow-600">{{ stats.pending_reservations }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-500 text-sm">Avg. Order Value</p>
                <p class="text-3xl font-bold text-gray-900">{{ fmt(stats.average_order_value) }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Recent Reservations -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="font-semibold text-gray-900">Recent Reservations</h2>
                    <a href="/admin/reservations" class="text-green-600 text-sm hover:underline">
                        View All →
                    </a>
                </div>
                <div class="divide-y">
                    <div
                        v-for="reservation in recentReservations"
                        :key="reservation.id"
                        class="p-4 flex items-center justify-between hover:bg-gray-50"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ reservation.confirmation_number }}</p>
                            <p class="text-sm text-gray-500">
                                {{ reservation.customer?.name || 'Unknown' }} · {{ reservation.item_count }} items
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ fmt(reservation.total_price) }}</p>
                            <span
                                :class="[
                                    'inline-block px-2 py-1 text-xs rounded-full',
                                    getStatusColor(reservation.status)
                                ]"
                            >
                                {{ reservation.status_label }}
                            </span>
                        </div>
                    </div>
                    <div v-if="recentReservations.length === 0" class="p-8 text-center text-gray-500">
                        No reservations yet
                    </div>
                </div>
            </div>

            <!-- Status Overview -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-4 border-b">
                    <h2 class="font-semibold text-gray-900">Order Status</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                            <span class="text-gray-700">Pending</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ statusCounts.pending }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-400"></span>
                            <span class="text-gray-700">Ready for Pickup</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ statusCounts.ready }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                            <span class="text-gray-700">Completed</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ statusCounts.completed }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="text-gray-700">Cancelled</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ statusCounts.cancelled }}</span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="p-4 border-t">
                    <h3 class="font-semibold text-gray-900 mb-3">Quick Stats</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Customers</span>
                            <span class="font-medium text-gray-900">{{ quickStats.total_customers }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">New This Month</span>
                            <span class="font-medium text-green-600">{{ quickStats.new_customers_this_month }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Low Stock Items</span>
                            <span class="font-medium text-orange-600">{{ quickStats.low_stock_products }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Revenue Chart -->
        <div class="mt-6 bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Last 7 Days Revenue</h2>
            <div class="flex items-end gap-2 h-40">
                <div
                    v-for="day in dailyRevenue"
                    :key="day.date"
                    class="flex-1 flex flex-col items-center"
                >
                    <div
                        class="w-full bg-green-500 rounded-t"
                        :style="{ height: Math.max(4, (day.revenue / maxRevenue) * 100) + '%' }"
                    ></div>
                    <span class="text-xs text-gray-500 mt-2">{{ day.date }}</span>
                    <span class="text-xs font-medium text-gray-900">{{ fmt(day.revenue) }}</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
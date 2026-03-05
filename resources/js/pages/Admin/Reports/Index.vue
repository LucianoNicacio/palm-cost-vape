<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface ReservationRow {
    id: number;
    confirmation_number: string;
    customer: { id: number; name: string } | null;
    item_count: number;
    subtotal: number;
    tax_amount: number;
    reward_discount: number;
    total_price: number;
    created_at: string;
}

interface InStoreSaleRow {
    id: number;
    total_items: number;
    total_amount: number;
    created_at: string;
    recorder: { id: number; name: string } | null;
}

interface TopProduct {
    name: string;
    total_quantity: number;
    total_revenue: number;
}

const props = defineProps<{
    stats: {
        total_revenue: number;
        reservation_revenue: number;
        in_store_revenue: number;
        total_orders: number;
        total_items_sold: number;
        average_order_value: number;
    };
    reservations: ReservationRow[];
    inStoreSales: InStoreSaleRow[];
    topProducts: TopProduct[];
    period: string;
    periodLabel: string;
    filters: {
        start_date?: string;
        end_date?: string;
    };
}>();

const customStart = ref(props.filters.start_date || '');
const customEnd = ref(props.filters.end_date || '');

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const changePeriod = (p: string) => {
    router.get('/admin/reports', { period: p }, { preserveState: true });
};

const applyCustomRange = () => {
    if (customStart.value && customEnd.value) {
        router.get('/admin/reports', {
            period: 'custom',
            start_date: customStart.value,
            end_date: customEnd.value,
        }, { preserveState: true });
    }
};

const exportUrl = (type: string) => {
    const params = new URLSearchParams();
    params.set('period', props.period);
    if (props.period === 'custom' && props.filters.start_date && props.filters.end_date) {
        params.set('start_date', props.filters.start_date);
        params.set('end_date', props.filters.end_date);
    }
    return `/admin/reports/export-${type}?${params.toString()}`;
};

// Compute totals for tables
const reservationTotals = {
    subtotal: props.reservations.reduce((s, r) => s + Number(r.subtotal), 0),
    tax: props.reservations.reduce((s, r) => s + Number(r.tax_amount), 0),
    discount: props.reservations.reduce((s, r) => s + Number(r.reward_discount), 0),
    total: props.reservations.reduce((s, r) => s + Number(r.total_price), 0),
};

const inStoreTotals = {
    items: props.inStoreSales.reduce((s, sale) => s + sale.total_items, 0),
    total: props.inStoreSales.reduce((s, sale) => s + Number(sale.total_amount), 0),
};
</script>

<template>
    <AdminLayout title="Reports">
        <!-- Period Filters -->
        <div class="mb-6 flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="p in [
                        { key: 'today', label: 'Today' },
                        { key: 'week', label: 'This Week' },
                        { key: 'month', label: 'This Month' },
                        { key: 'year', label: 'This Year' },
                    ]"
                    :key="p.key"
                    @click="changePeriod(p.key)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition',
                        period === p.key
                            ? 'bg-green-600 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-100'
                    ]"
                >
                    {{ p.label }}
                </button>
                <div class="flex items-center gap-2">
                    <input
                        v-model="customStart"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-green-500"
                    />
                    <span class="text-gray-400">to</span>
                    <input
                        v-model="customEnd"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-green-500"
                    />
                    <button
                        @click="applyCustomRange"
                        :disabled="!customStart || !customEnd"
                        class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800 disabled:opacity-50"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <a
                    :href="exportUrl('excel')"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 font-medium"
                >
                    📥 Excel
                </a>
                <a
                    :href="exportUrl('pdf')"
                    class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 font-medium"
                >
                    📄 PDF
                </a>
            </div>
        </div>

        <p class="text-gray-600 mb-6">Showing data for: <strong>{{ periodLabel }}</strong></p>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ fmt(stats.total_revenue) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Reservation Revenue</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ fmt(stats.reservation_revenue) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">In-Store Revenue</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ fmt(stats.in_store_revenue) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_orders }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Items Sold</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_items_sold }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Avg. Order Value</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ fmt(stats.average_order_value) }}</p>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-900">Reservations ({{ reservations.length }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confirmation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Discount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="r in reservations" :key="r.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(r.created_at) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ r.confirmation_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ r.customer?.name || 'Unknown' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ r.item_count }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(r.subtotal) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ fmt(r.tax_amount) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ fmt(r.reward_discount) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ fmt(r.total_price) }}</td>
                        </tr>
                        <tr v-if="reservations.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No completed reservations in this period.</td>
                        </tr>
                        <tr v-if="reservations.length > 0" class="bg-gray-50 font-semibold">
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-900">Total</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(reservationTotals.subtotal) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(reservationTotals.tax) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(reservationTotals.discount) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(reservationTotals.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- In-Store Sales Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-900">In-Store Sales ({{ inStoreSales.length }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="sale in inStoreSales" :key="sale.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(sale.created_at) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ sale.id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ sale.recorder?.name || 'Unknown' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ sale.total_items }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ fmt(sale.total_amount) }}</td>
                        </tr>
                        <tr v-if="inStoreSales.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No in-store sales in this period.</td>
                        </tr>
                        <tr v-if="inStoreSales.length > 0" class="bg-gray-50 font-semibold">
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-900">Total</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ inStoreTotals.items }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ fmt(inStoreTotals.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-900">Top Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="(product, index) in topProducts" :key="product.name" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ product.name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ product.total_quantity }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ fmt(product.total_revenue) }}</td>
                        </tr>
                        <tr v-if="topProducts.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No product data for this period.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

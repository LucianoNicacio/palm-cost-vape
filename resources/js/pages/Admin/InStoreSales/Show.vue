<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';

interface SaleItem {
    id: number;
    quantity: number;
    unit_price: number;
    subtotal: number;
    product: { id: number; name: string } | null;
}

interface Sale {
    id: number;
    total_items: number;
    total_amount: number;
    created_at: string;
    recorder: { id: number; name: string } | null;
    items: SaleItem[];
}

defineProps<{
    sale: Sale;
}>();

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
</script>

<template>
    <AdminLayout title="Sale Details">
        <div class="mb-4">
            <a href="/admin/in-store-sales" class="text-green-600 hover:text-green-800">
                ← Back to In-Store Sales
            </a>
        </div>

        <div class="max-w-2xl space-y-6">
            <!-- Sale Info -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">In-Store Sale #{{ sale.id }}</h2>
                        <p class="text-gray-500 mt-1">{{ formatDate(sale.created_at) }}</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                        Completed
                    </span>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    Recorded by <span class="font-medium text-gray-900">{{ sale.recorder?.name || 'Unknown' }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">Sale Items</h3>
                </div>
                <div class="divide-y">
                    <div
                        v-for="item in sale.items"
                        :key="item.id"
                        class="p-4 flex items-center justify-between"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ item.product?.name || 'Deleted Product' }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">{{ fmt(item.subtotal) }}</p>
                            <p class="text-sm text-gray-500">{{ fmt(item.unit_price) }} each</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex justify-between">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-xl text-gray-900">{{ fmt(sale.total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

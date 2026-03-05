<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Sale {
    id: number;
    total_items: number;
    total_amount: number;
    created_at: string;
    recorder: { id: number; name: string } | null;
}

interface PaginatedSales {
    data: Sale[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    sales: PaginatedSales;
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

const goToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AdminLayout title="In-Store Sales">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-gray-600">Record and track physical in-store sales.</p>
            <a
                href="/admin/in-store-sales/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
            >
                Record Sale
            </a>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900">
                            {{ formatDate(sale.created_at) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ sale.total_items }} {{ sale.total_items === 1 ? 'item' : 'items' }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ fmt(sale.total_amount) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ sale.recorder?.name || 'Unknown' }}
                        </td>
                        <td class="px-6 py-4">
                            <a
                                :href="`/admin/in-store-sales/${sale.id}`"
                                class="text-green-600 hover:text-green-800 font-medium"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                    <tr v-if="sales.data.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No in-store sales recorded yet.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="sales.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing {{ sales.data.length }} of {{ sales.total }} sales
                </p>
                <div class="flex gap-2">
                    <button
                        v-for="link in sales.links"
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

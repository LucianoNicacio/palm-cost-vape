<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    total_orders: number;
    total_spent: number;
    last_order_at: string | null;
    created_at: string;
}

interface PaginatedCustomers {
    data: Customer[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    customers: PaginatedCustomers;
    filters: {
        search?: string;
        sort?: string;
    };
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const search = (e: Event) => {
    const target = e.target as HTMLInputElement;
    router.get('/admin/customers', {
        ...props.filters,
        search: target.value || undefined,
    }, { preserveState: true });
};

const goToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AdminLayout title="Customers">
        <!-- Search and Export -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between">
            <input
                type="text"
                placeholder="Search by name, email, or phone..."
                :value="filters.search"
                @input="search"
                class="flex-1 max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <a
                href="/admin/customers-export"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center"
            >
                📥 Export CSV
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-gray-500 text-sm">Total Customers</p>
                <p class="text-2xl font-bold text-gray-900">{{ customers.total }}</p>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ customer.name }}</div>
                            <div class="text-sm text-gray-500">{{ customer.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ customer.phone || '-' }}
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-medium">
                            {{ customer.total_orders }}
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-medium">
                            {{ fmt(customer.total_spent) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ formatDate(customer.last_order_at) }}
                        </td>
                        <td class="px-6 py-4">
                            <a
                                :href="`/admin/customers/${customer.id}`"
                                class="text-green-600 hover:text-green-800 font-medium"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No customers found
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="customers.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing {{ customers.data.length }} of {{ customers.total }} customers
                </p>
                <div class="flex gap-2">
                    <button
                        v-for="link in customers.links"
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

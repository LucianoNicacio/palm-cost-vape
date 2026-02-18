<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Reservation {
    id: number;
    confirmation_number: string;
    customer: Customer | null;
    item_count: number;
    total_price: number;
    status: string;
    status_label: string;
    created_at: string;
}

interface PaginatedReservations {
    data: Reservation[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    reservations: PaginatedReservations;
    statusCounts: {
        all: number;
        pending: number;
        ready: number;
        completed: number;
        cancelled: number;
    };
    filters: {
        status?: string;
        date_filter?: string;
        search?: string;
    };
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
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

const filterByStatus = (status: string | null) => {
    router.get('/admin/reservations', {
        ...props.filters,
        status: status,
    }, { preserveState: true });
};

const search = (e: Event) => {
    const target = e.target as HTMLInputElement;
    router.get('/admin/reservations', {
        ...props.filters,
        search: target.value || undefined,
    }, { preserveState: true });
};

const goToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AdminLayout title="Reservations">
        <!-- Search and Filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                placeholder="Search by confirmation # or customer..."
                :value="filters.search"
                @input="search"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <select
                :value="filters.date_filter || ''"
                @change="router.get('/admin/reservations', { ...filters, date_filter: ($event.target as HTMLSelectElement).value || undefined }, { preserveState: true })"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
            >
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
        </div>

        <!-- Status Tabs -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button
                @click="filterByStatus(null)"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    !filters.status
                        ? 'bg-gray-900 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                All ({{ statusCounts.all }})
            </button>
            <button
                @click="filterByStatus('pending')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters.status === 'pending'
                        ? 'bg-yellow-500 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                Pending ({{ statusCounts.pending }})
            </button>
            <button
                @click="filterByStatus('ready')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters.status === 'ready'
                        ? 'bg-green-500 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                Ready ({{ statusCounts.ready }})
            </button>
            <button
                @click="filterByStatus('completed')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters.status === 'completed'
                        ? 'bg-gray-500 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                Completed ({{ statusCounts.completed }})
            </button>
            <button
                @click="filterByStatus('cancelled')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters.status === 'cancelled'
                        ? 'bg-red-500 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
            >
                Cancelled ({{ statusCounts.cancelled }})
            </button>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confirmation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ reservation.confirmation_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-900">{{ reservation.customer?.name || 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ reservation.customer?.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ reservation.item_count }} items
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ fmt(reservation.total_price) }}
                        </td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(reservation.status)]">
                                {{ reservation.status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ formatDate(reservation.created_at) }}
                        </td>
                        <td class="px-6 py-4">
                            <a
                                :href="`/admin/reservations/${reservation.id}`"
                                class="text-green-600 hover:text-green-800 font-medium"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                    <tr v-if="reservations.data.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No reservations found
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="reservations.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing {{ reservations.data.length }} of {{ reservations.total }} reservations
                </p>
                <div class="flex gap-2">
                    <button
                        v-for="link in reservations.links"
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

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Product {
    id: number;
    name: string;
    sku: string;
}

interface ReservationItem {
    id: number;
    product: Product | null;
    product_name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
}

interface Reservation {
    id: number;
    confirmation_number: string;
    customer: Customer;
    items: ReservationItem[];
    item_count: number;
    total_price: number;
    status: string;
    status_label: string;
    notes: string | null;
    created_at: string;
    processed_at: string | null;
    ready_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
}

interface CustomerHistory {
    id: number;
    confirmation_number: string;
    total_price: number;
    status_label: string;
    created_at: string;
}

const props = defineProps<{
    reservation: Reservation;
    customerHistory: CustomerHistory[];
    statuses: Record<string, string>;
}>();

const selectedStatus = ref(props.reservation.status);
const notes = ref(props.reservation.notes || '');
const updating = ref(false);

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

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        ready: 'bg-green-100 text-green-700 border-green-200',
        completed: 'bg-gray-100 text-gray-700 border-gray-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const updateStatus = () => {
    updating.value = true;
    router.put(`/admin/reservations/${props.reservation.id}/status`, {
        status: selectedStatus.value,
        notes: notes.value,
    }, {
        onFinish: () => updating.value = false,
    });
};
</script>

<template>
    <AdminLayout title="Reservation Details">
        <div class="mb-4">
            <a href="/admin/reservations" class="text-green-600 hover:text-green-800">
                ← Back to Reservations
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ reservation.confirmation_number }}
                            </h2>
                            <p class="text-gray-500 mt-1">
                                Created {{ formatDate(reservation.created_at) }}
                            </p>
                        </div>
                        <span :class="['px-4 py-2 rounded-lg border text-sm font-medium', getStatusColor(reservation.status)]">
                            {{ reservation.status_label }}
                        </span>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div v-if="reservation.ready_at || reservation.cancelled_at" class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Status Timeline</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Created</span>
                            <span class="font-medium text-gray-900">{{ formatDate(reservation.created_at) }}</span>
                        </div>
                        <div v-if="reservation.ready_at" class="flex justify-between">
                            <span class="text-gray-500">Marked Ready</span>
                            <span class="font-medium text-green-600">{{ formatDate(reservation.ready_at) }}</span>
                        </div>
                        <div v-if="reservation.ready_at && reservation.status === 'ready'" class="flex justify-between">
                            <span class="text-gray-500">Pickup Deadline</span>
                            <span class="font-medium text-orange-600">{{ formatDate(new Date(new Date(reservation.ready_at).getTime() + 24 * 60 * 60 * 1000).toISOString()) }}</span>
                        </div>
                        <div v-if="reservation.cancelled_at" class="flex justify-between">
                            <span class="text-gray-500">Cancelled</span>
                            <span class="font-medium text-red-600">{{ formatDate(reservation.cancelled_at) }}</span>
                        </div>
                        <div v-if="reservation.cancellation_reason" class="flex justify-between">
                            <span class="text-gray-500">Reason</span>
                            <span class="font-medium text-red-600">
                                {{ reservation.cancellation_reason === 'auto_expired' ? 'Auto-expired (24hr pickup window)' : reservation.cancellation_reason === 'admin_cancelled' ? 'Cancelled by admin' : reservation.cancellation_reason }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-900">Order Items</h3>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="item in reservation.items"
                            :key="item.id"
                            class="p-4 flex items-center justify-between"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                                <p class="text-sm text-gray-500">
                                    SKU: {{ item.product?.sku || 'N/A' }} · Qty: {{ item.quantity }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-900">{{ fmt(item.total_price) }}</p>
                                <p class="text-sm text-gray-500">{{ fmt(item.unit_price) }} each</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t bg-gray-50 flex justify-between">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-xl text-gray-900">{{ fmt(reservation.total_price) }}</span>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Update Status</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            >
                                <option v-for="(label, value) in statuses" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="notes"
                                rows="3"
                                placeholder="Add internal notes..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                        <button
                            @click="updateStatus"
                            :disabled="updating"
                            class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ updating ? 'Updating...' : 'Update Status' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Customer</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium text-gray-900">{{ reservation.customer.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-900">{{ reservation.customer.email }}</p>
                        </div>
                        <div v-if="reservation.customer.phone">
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="font-medium text-gray-900">{{ reservation.customer.phone }}</p>
                        </div>
                        <a
                            :href="`/admin/customers/${reservation.customer.id}`"
                            class="block text-center py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 mt-4"
                        >
                            View Customer
                        </a>
                    </div>
                </div>

                <!-- Customer History -->
                <div v-if="customerHistory.length > 0" class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Previous Orders</h3>
                    <div class="space-y-3">
                        <a
                            v-for="order in customerHistory"
                            :key="order.id"
                            :href="`/admin/reservations/${order.id}`"
                            class="block p-3 border rounded-lg hover:bg-gray-50"
                        >
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-900">{{ order.confirmation_number }}</span>
                                <span class="text-gray-600">{{ fmt(order.total_price) }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ order.status_label }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Reservation {
    id: number;
    confirmation_number: string;
    total_price: number;
    item_count: number;
    status: string;
    status_label: string;
    created_at: string;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    total_orders: number;
    total_spent: number;
    last_order_at: string | null;
    created_at: string;
    notes: string | null;
    reservations: Reservation[];
}

const props = defineProps<{
    customer: Customer;
}>();

const reservations = computed(() => props.customer.reservations || []);

const editing = ref(false);
const form = ref({
    name: '',
    email: '',
    phone: '',
    notes: '',
});
const saving = ref(false);

// Initialize form with customer data
const initForm = () => {
    form.value = {
        name: props.customer.name || '',
        email: props.customer.email || '',
        phone: props.customer.phone || '',
        notes: props.customer.notes || '',
    };
};

// Watch for customer changes and when editing starts
watch(() => props.customer, initForm, { immediate: true });
watch(editing, (isEditing) => {
    if (isEditing) initForm();
});

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
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
        pending: 'bg-yellow-100 text-yellow-700',
        ready: 'bg-green-100 text-green-700',
        completed: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const save = () => {
    saving.value = true;
    router.put(`/admin/customers/${props.customer.id}`, form.value, {
        onSuccess: () => editing.value = false,
        onFinish: () => saving.value = false,
    });
};

const cancelEdit = () => {
    editing.value = false;
    initForm();
};
</script>

<template>
    <AdminLayout title="Customer Details">
        <div class="mb-4">
            <a href="/admin/customers" class="text-green-600 hover:text-green-800">
                ← Back to Customers
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Customer Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Customer Info</h3>
                        <button
                            @click="editing ? cancelEdit() : editing = true"
                            class="text-sm text-green-600 hover:text-green-800"
                        >
                            {{ editing ? 'Cancel' : 'Edit' }}
                        </button>
                    </div>

                    <div v-if="!editing" class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium text-gray-900">{{ customer.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-900">{{ customer.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="font-medium text-gray-900">{{ customer.phone || 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Customer Since</p>
                            <p class="font-medium text-gray-900">{{ formatDate(customer.created_at) }}</p>
                        </div>
                        <div v-if="customer.notes">
                            <p class="text-sm text-gray-500">Notes</p>
                            <p class="text-gray-700">{{ customer.notes }}</p>
                        </div>
                    </div>

                    <form v-else @submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </form>
                </div>

                <!-- Stats -->
                <div class="bg-white rounded-xl shadow p-6 mt-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Orders</span>
                            <span class="font-semibold text-gray-900">{{ customer.total_orders || reservations.length }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Spent</span>
                            <span class="font-semibold text-green-600">{{ fmt(customer.total_spent || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Last Order</span>
                            <span class="font-medium text-gray-900">{{ formatDate(customer.last_order_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-900">Order History</h3>
                    </div>
                    <div class="divide-y">
                        <a
                            v-for="reservation in reservations"
                            :key="reservation.id"
                            :href="`/admin/reservations/${reservation.id}`"
                            class="p-4 flex items-center justify-between hover:bg-gray-50 block"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ reservation.confirmation_number }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ reservation.item_count }} items · {{ formatDate(reservation.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ fmt(reservation.total_price) }}</p>
                                <span :class="['inline-block px-2 py-1 text-xs rounded-full', getStatusColor(reservation.status)]">
                                    {{ reservation.status_label }}
                                </span>
                            </div>
                        </a>
                        <div v-if="reservations.length === 0" class="p-8 text-center text-gray-500">
                            No orders yet
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

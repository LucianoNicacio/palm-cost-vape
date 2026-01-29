<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface ReservationItem {
    id: number;
    product_name: string;
    quantity: number;
    total_price: number;
}

interface Props {
    reservation: {
        confirmation_number: string;
        total_price: number;
        customer: {
            name: string;
            email: string;
        };
        items: ReservationItem[];
    };
    storeInfo: {
        name: string;
        address: string;
        city: string;
        phone: string;
    };
    showCreateAccount: boolean;
    customerEmail: string;
}

const props = defineProps<Props>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const phoneLink = computed(() => {
    return 'tel:' + props.storeInfo.phone.replace(/\D/g, '');
});

// Account creation form for guests
const accountForm = useForm({
    name: props.reservation.customer.name,
    email: props.customerEmail,
    password: '',
    password_confirmation: '',
    phone: '',
});

const createAccount = () => {
    accountForm.post('/account/register', {
        onSuccess: () => {
            // Redirect to customer dashboard after account creation
        },
    });
};
</script>

<template>
    <Head title="Order Confirmed" />

    <div class="min-h-screen bg-gray-50">
        <!-- Simple Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
                <a href="/" class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</a>
                <a href="/shop" class="text-green-600 hover:underline">Continue Shopping</a>
            </div>
        </header>

        <div class="max-w-3xl mx-auto py-12 px-4">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">✓</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
                <p class="text-gray-600">
                    Thank you, {{ reservation.customer.name }}. We've received your reservation.
                </p>
            </div>

            <!-- Confirmation Details -->
            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <div class="text-center border-b pb-4 mb-4">
                    <p class="text-sm text-gray-500">Confirmation Number</p>
                    <p class="text-2xl font-bold text-green-600 tracking-wide">
                        {{ reservation.confirmation_number }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in reservation.items"
                        :key="item.id"
                        class="flex justify-between"
                    >
                        <span class="text-gray-600">
                            {{ item.product_name }} × {{ item.quantity }}
                        </span>
                        <span class="text-gray-900">{{ fmt(item.total_price) }}</span>
                    </div>
                </div>

                <div class="border-t mt-4 pt-4 flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-green-600">{{ fmt(reservation.total_price) }}</span>
                </div>
            </div>

            <!-- Pickup Info -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 mb-6">
                <h2 class="font-semibold text-blue-900 mb-3">📍 Pickup Location</h2>
                <p class="text-blue-800">{{ storeInfo.name }}</p>
                <p class="text-blue-800">{{ storeInfo.address }}</p>
                <p class="text-blue-800">{{ storeInfo.city }}</p>
                <p>
                    <a :href="phoneLink" class="text-blue-600 font-semibold hover:underline">{{ storeInfo.phone }}</a>
                </p>
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <p class="text-sm text-blue-700">
                        <strong>Important:</strong> You'll receive an email when your order is ready for pickup.
                        Please bring valid ID for age verification.
                    </p>
                </div>
            </div>

            <!-- Create Account Prompt (for guests) -->
            <div v-if="showCreateAccount" class="bg-white rounded-xl shadow p-6 mb-6">
                <h2 class="font-semibold text-gray-900 mb-2">🔐 Save Your Order History</h2>
                <p class="text-gray-600 text-sm mb-4">
                    Create an account to track this order and easily reorder in the future.
                    We'll link all orders placed with {{ customerEmail }} to your account.
                </p>

                <form @submit.prevent="createAccount" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Password
                            </label>
                            <input
                                v-model="accountForm.password"
                                type="password"
                                required
                                placeholder="Create a password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                :class="{ 'border-red-500': accountForm.errors.password }"
                            />
                            <p v-if="accountForm.errors.password" class="mt-1 text-sm text-red-500">
                                {{ accountForm.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password
                            </label>
                            <input
                                v-model="accountForm.password_confirmation"
                                type="password"
                                required
                                placeholder="Confirm password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="accountForm.processing"
                        class="w-full py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 disabled:opacity-50 transition"
                    >
                        {{ accountForm.processing ? 'Creating Account...' : 'Create Account' }}
                    </button>
                </form>
            </div>

            <!-- Already Logged In - Link to Dashboard -->
            <div v-else class="bg-white rounded-xl shadow p-6 mb-6 text-center">
                <p class="text-gray-600 mb-4">
                    You can track your order status in your account dashboard.
                </p>
                <Link
                    href="/account/orders"
                    class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition"
                >
                    View My Orders
                </Link>
            </div>

            <!-- Continue Shopping -->
            <div class="text-center">
                <Link
                    href="/shop"
                    class="text-green-600 hover:underline font-medium"
                >
                    ← Continue Shopping
                </Link>
            </div>
        </div>
    </div>
</template>
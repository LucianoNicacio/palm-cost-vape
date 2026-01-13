<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps<{
    reservation: {
        confirmation_number: string;
        total_price: number;
        customer: {
            email: string;
            name: string;
        };
        items: Array<{
            id: number;
            product_name: string;
            quantity: number;
            total_price: number;
        }>;
    };
    storeInfo: {
        name: string;
        address: string;
        city: string;
        phone: string;
    };
}>();

const fmt = (value: number) => '$' + parseFloat(String(value)).toFixed(2);
</script>

<template>
    <Head title="Reservation Confirmed" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <Link href="/" class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</Link>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-gray-700 hover:text-green-600">Home</Link>
                    <Link href="/shop" class="text-gray-700 hover:text-green-600">Shop</Link>
                </nav>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-8 text-center">
            <div class="text-6xl mb-4">✅</div>
            <h1 class="text-3xl font-bold text-green-600 mb-2">Reservation Confirmed!</h1>
            <p class="text-gray-600 mb-6">
                Thank you for your order. A confirmation email has been sent to {{ reservation.customer.email }}.
            </p>

            <!-- Confirmation Number -->
            <div class="bg-gray-100 rounded-xl p-6 mb-6">
                <p class="text-sm text-gray-500 mb-1">Confirmation Number</p>
                <p class="text-3xl font-bold font-mono text-gray-900">{{ reservation.confirmation_number }}</p>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-xl shadow p-6 text-left mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Order Details</h2>
                <div class="space-y-2 mb-4">
                    <div
                        v-for="item in reservation.items"
                        :key="item.id"
                        class="flex justify-between text-gray-700"
                    >
                        <span>{{ item.product_name }} × {{ item.quantity }}</span>
                        <span>{{ fmt(item.total_price) }}</span>
                    </div>
                </div>
                <div class="border-t pt-2 mt-4 flex justify-between text-lg font-semibold">
                    <span class="text-gray-900">Total Due at Pickup</span>
                    <span class="text-green-600">{{ fmt(reservation.total_price) }}</span>
                </div>
            </div>

            <!-- Pickup Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-left mb-6">
                <h2 class="font-semibold text-gray-900 mb-2">📍 Pickup Location</h2>
                <p class="text-gray-700">{{ storeInfo.name }}</p>
                <p class="text-gray-700">{{ storeInfo.address }}</p>
                <p class="text-gray-700">{{ storeInfo.city }}</p>
                <p class="text-green-600 font-semibold mt-2">{{ storeInfo.phone }}</p>
                <p class="mt-4 text-sm text-blue-700">
                    📝 Please bring valid ID for age verification.
                </p>
            </div>

            <Link
                href="/shop"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg inline-block transition"
            >
                Continue Shopping
            </Link>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 mt-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
                <p>Palm Coast Vape and Glassware | 29 Old Kings Rd N, Suite 2-A | (386) 597-2838</p>
                <p class="mt-2">Must be 21+ to purchase. Please vape responsibly.</p>
            </div>
        </footer>
    </div>
</template>

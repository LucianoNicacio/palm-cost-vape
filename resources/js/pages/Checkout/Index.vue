<script setup lang="ts">
import { computed } from 'vue';
import { useForm, Link, Head, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    cartItems: Array<{
        product: {
            id: number;
            name: string;
            price: number;
            formatted_price: string;
            category?: { name: string };
        };
        quantity: number;
        pricing: {
            subtotal: number;
            tax_amount: number;
            total_price: number;
        };
    }>;
    totals: {
        subtotal: number;
        tax_amount: number;
        total_price: number;
        item_count: number;
    };
    ageRequirement: number;
    taxRate: number;
}>();

const page = usePage();
const cartCount = computed(() => page.props.cart?.item_count || 0);

const form = useForm({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_dob: '',
    is_subscribed: false,
});

const submit = () => {
    form.post('/checkout');
};

const fmt = (value: number) => '$' + parseFloat(String(value)).toFixed(2);

// Calculate max date (must be 21+ years old)
const maxDob = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - props.ageRequirement);
    return date.toISOString().split('T')[0];
});

// Phone formatting
const formatPhone = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '');
    if (digits.length <= 3) {
        form.customer_phone = digits;
    } else if (digits.length <= 6) {
        form.customer_phone = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
    } else {
        form.customer_phone = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
    }
};
</script>

<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
                <Link href="/" class="text-xl font-bold text-gray-900">🌴 Palm Coast Vape</Link>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-gray-700 hover:text-green-600">Home</Link>
                    <Link href="/shop" class="text-gray-700 hover:text-green-600">Shop</Link>
                    <Link href="/cart" class="relative p-2 text-gray-700 hover:text-green-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span 
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 bg-green-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>
                </nav>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 py-8">
            <Link
                href="/cart"
                class="text-green-600 mb-6 inline-block hover:underline"
            >
                ← Back to Cart
            </Link>

            <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Customer Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input
                            v-model="form.customer_name"
                            type="text"
                            required
                            placeholder="John Doe"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        />
                        <p v-if="form.errors.customer_name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input
                            v-model="form.customer_email"
                            type="email"
                            required
                            placeholder="john@example.com"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        />
                        <p v-if="form.errors.customer_email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_email }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input
                            :value="form.customer_phone"
                            @input="formatPhone"
                            type="tel"
                            required
                            placeholder="(386) 555-1234"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        />
                        <p v-if="form.errors.customer_phone" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_phone }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                        <input
                            v-model="form.customer_dob"
                            type="date"
                            :max="maxDob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-900"
                        />
                        <p class="text-sm text-gray-500 mt-1">
                            Must be {{ ageRequirement }}+. ID verified at pickup.
                        </p>
                        <p v-if="form.errors.customer_dob" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_dob }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="form.is_subscribed"
                            type="checkbox"
                            class="rounded text-green-600 focus:ring-green-500"
                        />
                        <span class="text-sm text-gray-700">Subscribe to email updates & promotions</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-4 rounded-lg mt-4 transition"
                    >
                        {{ form.processing ? 'Processing...' : 'Complete Reservation →' }}
                    </button>

                    <p class="text-center text-sm text-gray-500">
                        💡 No payment required now. Pay at pickup.
                    </p>
                </form>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-xl p-6 h-fit">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                    <div class="space-y-3 mb-4">
                        <div
                            v-for="item in cartItems"
                            :key="item.product.id"
                            class="flex justify-between text-gray-700"
                        >
                            <span>{{ item.product.name }} × {{ item.quantity }}</span>
                            <span>{{ fmt(item.pricing.total_price) }}</span>
                        </div>
                    </div>

                    <div class="border-t pt-4 mt-4 space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>{{ fmt(totals.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax ({{ (taxRate * 100).toFixed(0) }}%)</span>
                            <span>{{ fmt(totals.tax_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 pt-2">
                            <span>Total Due at Pickup</span>
                            <span class="text-green-600">{{ fmt(totals.total_price) }}</span>
                        </div>
                    </div>
                </div>
            </div>
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

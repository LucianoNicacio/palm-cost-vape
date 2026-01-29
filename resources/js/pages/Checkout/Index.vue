<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

interface CartItem {
    product: {
        id: number;
        name: string;
        price: number;
        formatted_price: string;
        category: { name: string } | null;
    };
    quantity: number;
    pricing: {
        subtotal: number;
        tax_amount: number;
        total_price: number;
    };
}

interface Props {
    cartItems: CartItem[];
    totals: {
        subtotal: number;
        tax_amount: number;
        total_price: number;
        item_count: number;
    };
    taxRate: number;
    ageRequirement: number;
    isLoggedIn: boolean;
    prefill: {
        customer_name: string;
        customer_email: string;
        customer_phone: string;
    } | null;
}

const props = defineProps<Props>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

// Checkout mode: 'guest' | 'login' | 'logged_in'
const checkoutMode = ref<'guest' | 'login' | 'logged_in'>(props.isLoggedIn ? 'logged_in' : 'guest');

// Guest checkout form
const form = useForm({
    customer_name: props.prefill?.customer_name || '',
    customer_email: props.prefill?.customer_email || '',
    customer_phone: props.prefill?.customer_phone || '',
    customer_dob: '',
    is_subscribed: false,
    website: '',
});

// Login form
const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitOrder = () => {
    form.post('/checkout');
};

const submitLogin = () => {
    loginForm.post('/account/login', {
        onSuccess: () => {
            // After login, the page will refresh with prefill data
        },
        preserveScroll: true,
    });
};

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

// DOB validation
const maxDate = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - props.ageRequirement);
    return date.toISOString().split('T')[0];
});
</script>

<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Already Logged In -->
                    <div v-if="checkoutMode === 'logged_in'" class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-2xl">✓</span>
                            <div>
                                <h2 class="font-semibold text-gray-900">Welcome back!</h2>
                                <p class="text-sm text-gray-500">You're logged in as {{ prefill?.customer_email }}</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitOrder" class="space-y-4">
                            <!-- Honeypot - hidden from real users -->
                            <input
                                type="text"
                                name="website"
                                v-model="form.website"
                                class="hidden"
                                tabindex="-1"
                                autocomplete="off"
                            />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number (for pickup notification)
                                </label>
                                <input
                                    :value="form.customer_phone"
                                    @input="formatPhone"
                                    type="tel"
                                    required
                                    placeholder="(386) 555-1234"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                />
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.is_subscribed"
                                    type="checkbox"
                                    class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                                />
                                <span class="text-gray-700 text-sm">Subscribe to promotions & updates</span>
                            </label>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                            >
                                {{ form.processing ? 'Processing...' : 'Complete Reservation' }}
                            </button>
                        </form>
                    </div>

                    <!-- Guest/Login Toggle -->
                    <div v-else class="bg-white rounded-xl shadow overflow-hidden">
                        <!-- Tabs -->
                        <div class="flex border-b">
                            <button
                                @click="checkoutMode = 'guest'"
                                :class="[
                                    'flex-1 py-4 text-center font-medium transition',
                                    checkoutMode === 'guest'
                                        ? 'text-green-600 border-b-2 border-green-600 bg-green-50'
                                        : 'text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                Guest Checkout
                            </button>
                            <button
                                @click="checkoutMode = 'login'"
                                :class="[
                                    'flex-1 py-4 text-center font-medium transition',
                                    checkoutMode === 'login'
                                        ? 'text-green-600 border-b-2 border-green-600 bg-green-50'
                                        : 'text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                Sign In
                            </button>
                        </div>

                        <!-- Guest Checkout Form -->
                        <div v-if="checkoutMode === 'guest'" class="p-6">
                            <p class="text-sm text-gray-600 mb-4">
                                You can checkout as a guest. Create an account later to view your order history.
                            </p>

                            <form @submit.prevent="submitOrder" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Name *
                                    </label>
                                    <input
                                        v-model="form.customer_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': form.errors.customer_name }"
                                    />
                                    <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.customer_name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address *
                                    </label>
                                    <input
                                        v-model="form.customer_email"
                                        type="email"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': form.errors.customer_email }"
                                    />
                                    <p v-if="form.errors.customer_email" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.customer_email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number *
                                    </label>
                                    <input
                                        :value="form.customer_phone"
                                        @input="formatPhone"
                                        type="tel"
                                        required
                                        placeholder="(386) 555-1234"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': form.errors.customer_phone }"
                                    />
                                    <p v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.customer_phone }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Date of Birth * (Must be {{ ageRequirement }}+)
                                    </label>
                                    <input
                                        v-model="form.customer_dob"
                                        type="date"
                                        required
                                        :max="maxDate"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': form.errors.customer_dob }"
                                    />
                                    <p v-if="form.errors.customer_dob" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.customer_dob }}
                                    </p>
                                </div>

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.is_subscribed"
                                        type="checkbox"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                                    />
                                    <span class="text-gray-700 text-sm">Subscribe to promotions & updates</span>
                                </label>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                                >
                                    {{ form.processing ? 'Processing...' : 'Complete Reservation' }}
                                </button>
                            </form>
                        </div>

                        <!-- Login Form -->
                        <div v-else-if="checkoutMode === 'login'" class="p-6">
                            <p class="text-sm text-gray-600 mb-4">
                                Sign in to checkout faster and view your order history.
                            </p>

                            <form @submit.prevent="submitLogin" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address
                                    </label>
                                    <input
                                        v-model="loginForm.email"
                                        type="email"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': loginForm.errors.email }"
                                    />
                                    <p v-if="loginForm.errors.email" class="mt-1 text-sm text-red-500">
                                        {{ loginForm.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Password
                                    </label>
                                    <input
                                        v-model="loginForm.password"
                                        type="password"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                                        :class="{ 'border-red-500': loginForm.errors.password }"
                                    />
                                    <p v-if="loginForm.errors.password" class="mt-1 text-sm text-red-500">
                                        {{ loginForm.errors.password }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input
                                            v-model="loginForm.remember"
                                            type="checkbox"
                                            class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                                    </label>
                                    <Link href="/forgot-password" class="text-sm text-green-600 hover:underline">
                                        Forgot password?
                                    </Link>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="loginForm.processing"
                                    class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                                >
                                    {{ loginForm.processing ? 'Signing in...' : 'Sign In & Continue' }}
                                </button>
                            </form>

                            <div class="mt-4 text-center">
                                <p class="text-sm text-gray-600">
                                    Don't have an account?
                                    <Link href="/account/register" class="text-green-600 hover:underline">
                                        Create one
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <h3 class="font-medium text-blue-800 mb-2">📍 In-Store Pickup Only</h3>
                        <p class="text-sm text-blue-700">
                            This is a reservation for in-store pickup. You'll receive a confirmation email when your order is ready.
                            Please bring valid ID for age verification when picking up.
                        </p>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-3 mb-4">
                            <div
                                v-for="item in cartItems"
                                :key="item.product.id"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">
                                    {{ item.product.name }} × {{ item.quantity }}
                                </span>
                                <span class="text-gray-900">{{ fmt(item.pricing.subtotal) }}</span>
                            </div>
                        </div>

                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span>{{ fmt(totals.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax ({{ (taxRate * 100).toFixed(0) }}%)</span>
                                <span>{{ fmt(totals.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg pt-2 border-t">
                                <span>Total</span>
                                <span class="text-green-600">{{ fmt(totals.total_price) }}</span>
                            </div>
                        </div>

                        <Link
                            href="/cart"
                            class="block text-center text-sm text-green-600 hover:underline mt-4"
                        >
                            ← Edit Cart
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

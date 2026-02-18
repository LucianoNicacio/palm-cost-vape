<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    dob: '',
});

// Must be 21+ to register
const maxDob = (() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 21);
    return date.toISOString().split('T')[0];
})();

const submit = () => {
    form.post('/account/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// Phone formatting
const formatPhone = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '');
    if (digits.length <= 3) {
        form.phone = digits;
    } else if (digits.length <= 6) {
        form.phone = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
    } else {
        form.phone = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
    }
};
</script>

<template>
    <Head title="Create Account" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link href="/" class="flex justify-center">
                <span class="text-4xl">🌴</span>
            </Link>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Already have an account?
                <Link href="/account/login" class="font-medium text-green-600 hover:text-green-500">
                    Sign in
                </Link>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- Benefits -->
                <div class="mb-6 p-4 bg-green-50 rounded-lg">
                    <h3 class="font-medium text-green-800 mb-2">Benefits of creating an account:</h3>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>✓ View your order history</li>
                        <li>✓ Track order status</li>
                        <li>✓ Quick reorder from previous orders</li>
                        <li>✓ Faster checkout experience</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            If you've ordered before, use the same email to see your order history.
                        </p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Phone Number <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            id="phone"
                            :value="form.phone"
                            @input="formatPhone"
                            type="tel"
                            autocomplete="tel"
                            placeholder="(386) 555-1234"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700">
                            Date of Birth (Must be 21+)
                        </label>
                        <input
                            id="dob"
                            v-model="form.dob"
                            type="date"
                            required
                            :max="maxDob"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.dob }"
                        />
                        <p v-if="form.errors.dob" class="mt-1 text-sm text-red-500">
                            {{ form.errors.dob }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Creating account...' : 'Create Account' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Link
                            href="/shop"
                            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        >
                            Continue as guest →
                        </Link>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                You must be 21 years or older to create an account.
            </p>
        </div>
    </div>
</template>

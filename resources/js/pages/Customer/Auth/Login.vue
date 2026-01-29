<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/account/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link href="/" class="flex justify-center">
                <span class="text-4xl">🌴</span>
            </Link>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <Link href="/account/register" class="font-medium text-green-600 hover:text-green-500">
                    create a new account
                </Link>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-4 p-3 bg-green-100 border border-green-200 rounded-lg text-sm text-green-700"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
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
                            autocomplete="current-password"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                            />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            href="/forgot-password"
                            class="text-sm text-green-600 hover:text-green-500"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue as guest</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Link
                            href="/shop"
                            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        >
                            Continue shopping →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Admin Login Link -->
            <p class="mt-6 text-center text-xs text-gray-500">
                Store administrator?
                <Link href="/login" class="text-gray-600 hover:text-gray-800">
                    Admin login
                </Link>
            </p>
        </div>
    </div>
</template>

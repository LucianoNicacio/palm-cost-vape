<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/CustomerLayout.vue';

interface Props {
    user: {
        name: string;
        email: string;
    };
    customer: {
        phone: string | null;
        is_subscribed: boolean;
    } | null;
}

const props = defineProps<Props>();

// Profile form
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.customer?.phone || '',
    is_subscribed: props.customer?.is_subscribed || false,
});

const updateProfile = () => {
    profileForm.put('/account/profile', {
        preserveScroll: true,
    });
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/account/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Phone formatting
const formatPhone = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '');
    if (digits.length <= 3) {
        profileForm.phone = digits;
    } else if (digits.length <= 6) {
        profileForm.phone = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
    } else {
        profileForm.phone = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
    }
};
</script>

<template>
    <Head title="Profile Settings" />

    <CustomerLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>

            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Profile Information</h2>
                <form @submit.prevent="updateProfile" class="space-y-4 max-w-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': profileForm.errors.name }"
                        />
                        <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-500">
                            {{ profileForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address
                        </label>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': profileForm.errors.email }"
                        />
                        <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-500">
                            {{ profileForm.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input
                            :value="profileForm.phone"
                            @input="formatPhone"
                            type="tel"
                            placeholder="(386) 555-1234"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': profileForm.errors.phone }"
                        />
                        <p v-if="profileForm.errors.phone" class="mt-1 text-sm text-red-500">
                            {{ profileForm.errors.phone }}
                        </p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="profileForm.is_subscribed"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Subscribe to email updates & promotions</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="profileForm.processing"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                    >
                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Change Password</h2>
                <form @submit.prevent="updatePassword" class="space-y-4 max-w-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Current Password
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': passwordForm.errors.current_password }"
                        />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            New Password
                        </label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': passwordForm.errors.password }"
                        />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-500">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm New Password
                        </label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 disabled:opacity-50 transition"
                    >
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>
                </form>
            </div>
        </div>
    </CustomerLayout>
</template>

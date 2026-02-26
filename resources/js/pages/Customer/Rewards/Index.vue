<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/CustomerLayout.vue';

interface Reward {
    id: number;
    amount: number;
    type: 'earned' | 'redeemed';
    description: string | null;
    reservation: {
        id: number;
        confirmation_number: string;
    } | null;
    created_at: string;
}

interface PaginatedRewards {
    data: Reward[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    rewards: PaginatedRewards;
    balance: number;
    progress: {
        total_spent: number;
        toward_next: number;
        remaining: number;
    };
}>();

const fmt = (value: number) => '$' + parseFloat(String(value || 0)).toFixed(2);

const progressPercent = computed(() => {
    return Math.min(100, Math.round((props.progress.toward_next / 100) * 100));
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="My Rewards" />

    <CustomerLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">My Rewards</h1>

            <!-- Balance & Progress -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Balance Card -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow p-6 text-white">
                    <p class="text-green-100 text-sm">Available Balance</p>
                    <p class="text-4xl font-bold mt-1">{{ fmt(balance) }}</p>
                    <p class="text-green-200 text-sm mt-3">
                        Redeem $10 at a time during checkout.
                    </p>
                </div>

                <!-- Progress Card -->
                <div class="bg-white rounded-xl shadow p-6">
                    <p class="text-gray-500 text-sm">Progress to Next Reward</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ fmt(progress.toward_next) }}
                        <span class="text-base font-normal text-gray-500">/ $100.00</span>
                    </p>

                    <!-- Progress Bar -->
                    <div class="mt-3 w-full bg-gray-200 rounded-full h-3">
                        <div
                            class="bg-green-500 h-3 rounded-full transition-all duration-500"
                            :style="{ width: progressPercent + '%' }"
                        />
                    </div>

                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                        <span>{{ fmt(progress.toward_next) }} spent</span>
                        <span>{{ fmt(progress.remaining) }} to go</span>
                    </div>

                    <p class="text-gray-400 text-xs mt-3">
                        Lifetime spending: {{ fmt(progress.total_spent) }}
                    </p>
                </div>
            </div>

            <!-- How It Works -->
            <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                <h3 class="font-medium text-green-800 mb-2">How It Works</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-green-700">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">🛒</span>
                        <p>Earn $10 for every $100 spent (before tax)</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-lg">✅</span>
                        <p>Rewards added after your order is completed</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-lg">💰</span>
                        <p>Redeem $10 at checkout — no limit on how much you save</p>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-4 border-b">
                    <h2 class="font-semibold text-gray-900">Transaction History</h2>
                </div>

                <div v-if="rewards.data.length > 0">
                    <!-- Table for desktop -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="reward in rewards.data" :key="reward.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                        {{ formatDate(reward.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ reward.description }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <Link
                                            v-if="reward.reservation"
                                            :href="`/account/orders/${reward.reservation.id}`"
                                            class="text-green-600 hover:underline"
                                        >
                                            {{ reward.reservation.confirmation_number }}
                                        </Link>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-medium whitespace-nowrap">
                                        <span :class="reward.type === 'earned' ? 'text-green-600' : 'text-red-600'">
                                            {{ reward.type === 'earned' ? '+' : '-' }}{{ fmt(reward.amount) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards for mobile -->
                    <div class="sm:hidden divide-y">
                        <div v-for="reward in rewards.data" :key="reward.id" class="p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-900">{{ reward.description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ formatDate(reward.created_at) }}</p>
                                    <Link
                                        v-if="reward.reservation"
                                        :href="`/account/orders/${reward.reservation.id}`"
                                        class="text-xs text-green-600 hover:underline mt-1 inline-block"
                                    >
                                        {{ reward.reservation.confirmation_number }}
                                    </Link>
                                </div>
                                <span
                                    class="font-medium text-sm"
                                    :class="reward.type === 'earned' ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ reward.type === 'earned' ? '+' : '-' }}{{ fmt(reward.amount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="rewards.last_page > 1" class="flex justify-center gap-2 p-4 border-t">
                        <template v-for="link in rewards.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 rounded-lg text-sm',
                                    link.active
                                        ? 'bg-green-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-100'
                                ]"
                                preserve-scroll
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-2 text-sm text-gray-400"
                            />
                        </template>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8 text-center text-gray-500">
                    <p class="font-medium text-gray-900">No transactions yet</p>
                    <p class="text-sm mt-1">Your reward earnings and redemptions will appear here.</p>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

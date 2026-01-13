<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    ageRequirement: number | string;
    shopName: string;
}>();

// Convert to number in case it comes as string from config
const ageNumber = computed(() => Number(props.ageRequirement));

const form = useForm({
    confirmed: false,
});

const submit = () => {
    form.confirmed = true;
    // Use direct URL since Wayfinder might not have this route
    form.post('/age-verification');
};

const exitSite = () => {
    window.location.href = 'https://google.com';
};
</script>

<template>
    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
            <div class="text-6xl mb-4">🔞</div>
            <h1 class="text-2xl font-bold mb-2">{{ shopName }}</h1>
            <h2 class="text-lg text-gray-600 mb-6">Age Verification Required</h2>

            <p class="text-gray-600 mb-8">
                You must be
                <span class="font-bold text-green-600">{{ ageNumber }} years or older</span>
                to enter this website.
            </p>

            <div class="space-y-3">
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full bg-green-600 text-white py-4 rounded-lg font-semibold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Verifying...</span>
                    <span v-else>✓ Yes, I am {{ ageNumber }} or older</span>
                </button>

                <button
                    @click="exitSite"
                    class="w-full bg-gray-200 text-gray-700 py-4 rounded-lg font-semibold hover:bg-gray-300 transition"
                >
                    ✗ No, I am under {{ ageNumber }}
                </button>
            </div>

            <p class="text-xs text-gray-400 mt-6">
                By entering, you confirm you are of legal age to purchase tobacco and vape products.
            </p>

            <!-- Show errors if any -->
            <div v-if="form.errors.confirmed" class="mt-4 text-red-500 text-sm">
                {{ form.errors.confirmed }}
            </div>
        </div>
    </div>
</template>
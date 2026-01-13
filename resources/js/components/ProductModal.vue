<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    product: Object,
    taxRate: {
        type: Number,
        default: 0.06,
    },
});

const emit = defineEmits(['close']);

const quantity = ref(1);
const isAdding = ref(false);

const pricing = computed(() => {
    if (!props.product) return null;

    const subtotal = props.product.price * quantity.value;
    const rate = props.product.is_taxable ? props.taxRate : 0;
    const taxAmount = Math.round(subtotal * rate * 100) / 100;

    return {
        unitPrice: props.product.price,
        subtotal,
        taxRate: rate,
        taxAmount,
        totalPrice: subtotal + taxAmount,
    };
});

const fmt = (v) => '$' + parseFloat(v).toFixed(2);

const addToCart = () => {
    isAdding.value = true;

    router.post(
        route('cart.add', props.product.id),
        { quantity: quantity.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                quantity.value = 1;
                emit('close');
            },
            onFinish: () => {
                isAdding.value = false;
            },
        }
    );
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show && product"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-black/70"
                @click="$emit('close')"
            ></div>

            <!-- Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden"
                >
                    <!-- Close Button -->
                    <button
                        @click="$emit('close')"
                        class="absolute top-4 right-4 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100"
                    >
                        ✕
                    </button>

                    <div class="grid md:grid-cols-2">
                        <!-- Product Image -->
                        <div
                            class="bg-gray-100 aspect-square flex items-center justify-center p-8"
                        >
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="max-w-full max-h-full object-contain"
                            />
                        </div>

                        <!-- Product Details -->
                        <div class="p-6 flex flex-col">
                            <span
                                v-if="product.category"
                                class="text-xs bg-gray-200 px-2 py-1 rounded w-fit mb-2"
                            >
                                {{ product.category.name }}
                            </span>

                            <h2 class="text-2xl font-bold mb-1">
                                {{ product.name }}
                            </h2>
                            <p class="text-sm text-gray-500 mb-4">
                                SKU: {{ product.sku }}
                            </p>

                            <!-- Stock Status -->
                            <div class="mb-4">
                                <span
                                    v-if="product.in_stock"
                                    class="text-green-600"
                                >
                                    ✓ In Stock
                                </span>
                                <span v-else class="text-red-600">
                                    ✗ Out of Stock
                                </span>
                            </div>

                            <!-- Quantity Selector -->
                            <div v-if="product.in_stock" class="mb-4">
                                <label class="block text-sm font-medium mb-2">
                                    Quantity
                                </label>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="quantity > 1 && quantity--"
                                        class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300"
                                    >
                                        −
                                    </button>
                                    <span
                                        class="w-12 text-center text-xl font-semibold"
                                    >
                                        {{ quantity }}
                                    </span>
                                    <button
                                        @click="quantity++"
                                        class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- Pricing Breakdown -->
                            <div
                                v-if="pricing"
                                class="bg-gray-50 rounded-lg p-4 mb-4"
                            >
                                <div class="flex justify-between mb-2">
                                    <span>
                                        {{ quantity }} × {{ fmt(pricing.unitPrice) }}
                                    </span>
                                    <span>{{ fmt(pricing.subtotal) }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-500 mb-2"
                                >
                                    <span>Tax</span>
                                    <span>{{ fmt(pricing.taxAmount) }}</span>
                                </div>
                                <div
                                    class="border-t pt-2 flex justify-between font-semibold text-lg"
                                >
                                    <span>Total</span>
                                    <span class="text-green-600">
                                        {{ fmt(pricing.totalPrice) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Add to Cart Button -->
                            <button
                                v-if="product.in_stock"
                                @click="addToCart"
                                :disabled="isAdding"
                                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 rounded-lg mt-auto"
                            >
                                {{ isAdding ? 'Adding...' : 'Add to Cart' }}
                            </button>
                            <button
                                v-else
                                disabled
                                class="w-full bg-gray-400 text-white py-3 rounded-lg mt-auto"
                            >
                                Out of Stock
                            </button>

                            <p class="text-center text-sm text-gray-500 mt-3">
                                💡 Reserve now, pay at pickup.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
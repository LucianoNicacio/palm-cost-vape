<script setup>
defineProps({
    product: Object,
});

defineEmits(['click']);
</script>

<template>
    <div
        @click="$emit('click')"
        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl cursor-pointer group"
    >
        <div class="aspect-square bg-gray-100 relative overflow-hidden">
            <img
                v-if="product.image_url"
                :src="product.image_url"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition"
            />

            <div
                v-if="!product.in_stock"
                class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded"
            >
                Out of Stock
            </div>

            <div
                v-if="product.category"
                class="absolute bottom-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded"
            >
                {{ product.category.name }}
            </div>
        </div>

        <div class="p-4">
            <h3 class="font-semibold text-gray-900 truncate">
                {{ product.name }}
            </h3>
            <p class="text-lg font-bold text-green-600 mt-1">
                {{ product.formatted_price }}
            </p>
        </div>
    </div>
</template>
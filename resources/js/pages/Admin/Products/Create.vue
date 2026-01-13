<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Category {
    id: number;
    name: string;
}

defineProps<{
    categories: Category[];
}>();

const form = ref({
    name: '',
    sku: '',
    description: '',
    price: '',
    category_id: '',
    brand: '',
    stock: '0',
    track_inventory: true,
    is_taxable: true,
    is_active: true,
    is_featured: false,
    age_restricted: true,
});

const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const saving = ref(false);
const errors = ref<Record<string, string>>({});

const handleImageSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        imageFile.value = target.files[0];
        imagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const removeImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
};

const submit = () => {
    saving.value = true;
    errors.value = {};

    const formData = new FormData();
    Object.entries(form.value).forEach(([key, value]) => {
        if (typeof value === 'boolean') {
            formData.append(key, value ? '1' : '0');
        } else if (value !== '' && value !== null) {
            formData.append(key, String(value));
        }
    });

    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    router.post('/admin/products', formData, {
        forceFormData: true,
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout title="Add Product">
        <div class="mb-4">
            <a href="/admin/products" class="text-green-600 hover:text-green-800">
                ← Back to Products
            </a>
        </div>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-6 space-y-6">
                <!-- Basic Info -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Product Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    SKU <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.sku"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900 font-mono"
                                    :class="{ 'border-red-500': errors.sku }"
                                />
                                <p v-if="errors.sku" class="text-red-500 text-sm mt-1">{{ errors.sku }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Price <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full pl-7 pr-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                                        :class="{ 'border-red-500': errors.price }"
                                    />
                                </div>
                                <p v-if="errors.price" class="text-red-500 text-sm mt-1">{{ errors.price }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                                >
                                    <option value="">No Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input
                                    v-model="form.brand"
                                    type="text"
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Image -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Product Image</h3>
                    <div v-if="!imagePreview" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <label class="cursor-pointer">
                            <span class="text-gray-600">Click to upload image</span>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleImageSelect"
                                class="hidden"
                            />
                        </label>
                    </div>
                    <div v-else class="flex items-center gap-4">
                        <img :src="imagePreview" class="w-24 h-24 rounded-lg object-cover" />
                        <button
                            type="button"
                            @click="removeImage"
                            class="text-red-600 hover:text-red-800"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Inventory -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Inventory</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.track_inventory"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Track inventory</span>
                        </label>

                        <div v-if="form.track_inventory">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                            <input
                                v-model="form.stock"
                                type="number"
                                min="0"
                                class="w-32 px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-gray-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Settings</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Active (visible in store)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">⭐ Featured (show on homepage)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.is_taxable"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Taxable</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.age_restricted"
                                type="checkbox"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Age restricted (21+)</span>
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-4 pt-4 border-t">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : 'Create Product' }}
                    </button>
                    <a
                        href="/admin/products"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
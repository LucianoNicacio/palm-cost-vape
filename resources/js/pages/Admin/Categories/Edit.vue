<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Category {
    id: number;
    code: string;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    image: string | null;
    image_url: string | null;
    products_count: number;
}

const props = defineProps<{
    category: Category;
}>();

// Form data
const form = useForm({
    code: props.category.code,
    name: props.category.name,
    description: props.category.description || '',
    sort_order: props.category.sort_order,
    is_active: props.category.is_active,
    image: null as File | null,
    remove_image: false,
});

// Image preview (starts with existing image)
const imagePreview = ref<string | null>(props.category.image_url);
const hasExistingImage = ref<boolean>(!!props.category.image);

// Handle image selection
const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;
        form.remove_image = false;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Remove image
const removeImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;

    // Reset file input
    const input = document.getElementById('image') as HTMLInputElement;
    if (input) input.value = '';
};

// Restore original image
const restoreImage = () => {
    form.image = null;
    form.remove_image = false;
    imagePreview.value = props.category.image_url;
};

// Submit form
const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/admin/categories/${props.category.slug}`, {
        forceFormData: true,
    });
};

// Delete category
const deleteCategory = () => {
    if (props.category.products_count > 0) {
        alert(`Cannot delete this category. It has ${props.category.products_count} products assigned.`);
        return;
    }

    if (confirm(`Are you sure you want to delete "${props.category.name}"? This action cannot be undone.`)) {
        router.delete(`/admin/categories/${props.category.slug}`);
    }
};
</script>

<template>
    <AdminLayout :title="`Edit ${category.name}`">
        <!-- Header -->
        <div class="mb-6">
            <Link
                href="/admin/categories"
                class="text-green-600 hover:text-green-700 text-sm"
            >
                ← Back to Categories
            </Link>
            <div class="flex items-center justify-between mt-2">
                <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
                <span class="text-sm text-gray-500">
                    {{ category.products_count }} products in this category
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form -->
            <form @submit.prevent="submit" class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Category Name *
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                            Category Code *
                        </label>
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            required
                            maxlength="10"
                            class="w-full px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono uppercase"
                            :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Slug: <code class="bg-gray-100 px-1 rounded">{{ category.slug }}</code>
                        </p>
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-500">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Category Image
                        </label>

                        <!-- Preview -->
                        <div v-if="imagePreview" class="relative inline-block mb-3">
                            <img
                                :src="imagePreview"
                                alt="Preview"
                                class="w-32 h-32 object-cover rounded-lg border"
                            />
                            <button
                                type="button"
                                @click="removeImage"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-sm hover:bg-red-600"
                                title="Remove image"
                            >
                                ×
                            </button>
                        </div>

                        <!-- Restore button (if had image and removed it) -->
                        <div v-if="!imagePreview && hasExistingImage && form.remove_image" class="mb-3">
                            <button
                                type="button"
                                @click="restoreImage"
                                class="text-sm text-green-600 hover:text-green-700"
                            >
                                ↩️ Restore original image
                            </button>
                        </div>

                        <!-- Upload Button -->
                        <div class="flex items-center gap-4">
                            <label
                                for="image"
                                class="cursor-pointer px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 transition bg-white"
                            >
                                <span class="text-gray-600">
                                    {{ imagePreview ? '📷 Change Image' : '📷 Choose Image' }}
                                </span>
                                <input
                                    id="image"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleImageChange"
                                />
                            </label>
                            <span class="text-sm text-gray-500">PNG, JPG, GIF up to 2MB</span>
                        </div>

                        <p v-if="form.errors.image" class="mt-1 text-sm text-red-500">
                            {{ form.errors.image }}
                        </p>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                            Sort Order
                        </label>
                        <input
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            class="w-32 px-4 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.sort_order }"
                        />
                        <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-500">
                            {{ form.errors.sort_order }}
                        </p>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500"
                            />
                            <span class="text-gray-700">Active</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500 ml-8">
                            Inactive categories won't appear on the storefront
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link
                        href="/admin/categories"
                        class="px-6 py-2 text-gray-600 hover:text-gray-800"
                    >
                        Cancel
                    </Link>
                </div>
            </form>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Info Card -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Category Info</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">ID</dt>
                            <dd class="font-medium">{{ category.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Slug</dt>
                            <dd class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                {{ category.slug }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Products</dt>
                            <dd>
                                <Link
                                    :href="`/admin/products?category=${category.id}`"
                                    class="text-green-600 hover:text-green-700"
                                >
                                    {{ category.products_count }} products →
                                </Link>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-xl shadow p-6 border-l-4 border-red-500">
                    <h3 class="font-semibold text-red-600 mb-2">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Deleting a category is permanent. You must reassign or delete all products first.
                    </p>
                    <button
                        @click="deleteCategory"
                        :disabled="category.products_count > 0"
                        :class="[
                            'w-full py-2 rounded-lg text-sm font-medium transition',
                            category.products_count > 0
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                : 'bg-red-100 text-red-600 hover:bg-red-200'
                        ]"
                    >
                        {{ category.products_count > 0 ? 'Has Products - Cannot Delete' : 'Delete Category' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
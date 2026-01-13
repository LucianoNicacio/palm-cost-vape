<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps<{
    nextSortOrder: number;
}>();

// Form data
const form = useForm({
    code: '',
    name: '',
    description: '',
    sort_order: props.nextSortOrder,
    is_active: true,
    image: null as File | null,
});

// Image preview
const imagePreview = ref<string | null>(null);

// Handle image selection
const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Remove selected image
const removeImage = () => {
    form.image = null;
    imagePreview.value = null;

    // Reset file input
    const input = document.getElementById('image') as HTMLInputElement;
    if (input) input.value = '';
};

// Auto-generate code from name
watch(() => form.name, (newName) => {
    if (newName && !form.code) {
        // Generate code: first 4-6 uppercase letters
        const words = newName.split(' ');
        if (words.length > 1) {
            // Multiple words: take first letter of each (up to 4)
            form.code = words
                .slice(0, 4)
                .map(w => w[0])
                .join('')
                .toUpperCase();
        } else {
            // Single word: take first 4 letters
            form.code = newName.substring(0, 4).toUpperCase();
        }
    }
});

// Submit form
const submit = () => {
    form.post('/admin/categories', {
        forceFormData: true, // Required for file uploads
    });
};
</script>

<template>
    <AdminLayout title="Create Category">
        <!-- Header -->
        <div class="mb-6">
            <Link
                href="/admin/categories"
                class="text-green-600 hover:text-green-700 text-sm"
            >
                ← Back to Categories
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Create Category</h1>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="max-w-2xl">
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
                        placeholder="e.g., Disposable Vapes"
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
                        placeholder="e.g., DISP"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Short unique code (max 10 characters, letters and numbers only)
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
                        placeholder="Brief description of this category..."
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

                    <!-- Preview or Upload Area -->
                    <div v-if="imagePreview" class="relative inline-block">
                        <img
                            :src="imagePreview"
                            alt="Preview"
                            class="w-32 h-32 object-cover rounded-lg border"
                        />
                        <button
                            type="button"
                            @click="removeImage"
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-sm hover:bg-red-600"
                        >
                            ×
                        </button>
                    </div>

                    <div v-else class="flex items-center gap-4">
                        <label
                            for="image"
                            class="cursor-pointer px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 transition bg-white"
                        >
                            <span class="text-gray-600">📷 Choose Image</span>
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
                    <p class="mt-1 text-sm text-gray-500">
                        Lower numbers appear first
                    </p>
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
                    {{ form.processing ? 'Creating...' : 'Create Category' }}
                </button>
                <Link
                    href="/admin/categories"
                    class="px-6 py-2 text-gray-600 hover:text-gray-800"
                >
                    Cancel
                </Link>
            </div>
        </form>
    </AdminLayout>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps<{
    results?: {
        created: number;
        updated: number;
        errors: string[];
    };
}>();

const file = ref<File | null>(null);
const uploading = ref(false);
const dragOver = ref(false);

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        file.value = target.files[0];
    }
};

const handleDrop = (e: DragEvent) => {
    dragOver.value = false;
    if (e.dataTransfer?.files?.length) {
        file.value = e.dataTransfer.files[0];
    }
};

const upload = () => {
    if (!file.value) return;
    
    uploading.value = true;
    router.post('/admin/products-import', {
        file: file.value,
    }, {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;
            file.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout title="Import Products">
        <div class="max-w-2xl">
            <!-- Instructions -->
            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Import Products from CSV</h2>
                <p class="text-gray-600 mb-4">
                    Upload a CSV file to bulk import or update products. The CSV should have the following columns:
                </p>
                <ul class="text-sm text-gray-600 space-y-1 mb-4">
                    <li><strong>sku</strong> - Unique product identifier (required)</li>
                    <li><strong>name</strong> - Product name (required)</li>
                    <li><strong>description</strong> - Product description</li>
                    <li><strong>price</strong> - Price in dollars (required)</li>
                    <li><strong>stock</strong> - Inventory quantity</li>
                    <li><strong>category</strong> - Category name</li>
                    <li><strong>brand</strong> - Brand name</li>
                    <li><strong>active</strong> - 1 for active, 0 for inactive</li>
                </ul>
                <a
                    href="/admin/products-template"
                    class="inline-flex items-center text-green-600 hover:text-green-800"
                >
                    📥 Download CSV Template
                </a>
            </div>

            <!-- Results -->
            <div v-if="results" class="mb-6">
                <div v-if="results.created > 0 || results.updated > 0" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                    <p class="text-green-800">
                        ✅ Import complete: {{ results.created }} created, {{ results.updated }} updated
                    </p>
                </div>
                <div v-if="results.errors.length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-red-800 font-medium mb-2">Errors:</p>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li v-for="(error, i) in results.errors" :key="i">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="bg-white rounded-xl shadow p-6">
                <div
                    @dragover.prevent="dragOver = true"
                    @dragleave="dragOver = false"
                    @drop.prevent="handleDrop"
                    :class="[
                        'border-2 border-dashed rounded-xl p-8 text-center transition',
                        dragOver ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'
                    ]"
                >
                    <div v-if="!file">
                        <p class="text-gray-600 mb-2">Drag and drop your CSV file here, or</p>
                        <label class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg cursor-pointer hover:bg-green-700">
                            Browse Files
                            <input
                                type="file"
                                accept=".csv"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                        </label>
                    </div>
                    <div v-else>
                        <p class="text-gray-900 font-medium mb-2">📄 {{ file.name }}</p>
                        <p class="text-gray-500 text-sm mb-4">{{ (file.size / 1024).toFixed(1) }} KB</p>
                        <div class="flex gap-4 justify-center">
                            <button
                                @click="file = null"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                Remove
                            </button>
                            <button
                                @click="upload"
                                :disabled="uploading"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ uploading ? 'Uploading...' : 'Import Products' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

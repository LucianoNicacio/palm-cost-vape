<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { home } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: home().url,
    },
];
</script>

<template>
    <Head title="Home" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1 class="text-2xl font-bold">Welcome to Palm Coast Vape</h1>
            <p class="text-gray-600">This is a placeholder. The real Home page will be built separately.</p>
        </div>
    </AppLayout>
</template>
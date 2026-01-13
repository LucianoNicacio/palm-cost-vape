<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <nav class="flex justify-center">
        <ul class="flex items-center gap-1">
            <li v-for="(link, index) in links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-2 rounded-lg text-sm transition"
                    :class="
                        link.active
                            ? 'bg-green-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    "
                    preserve-scroll
                />
                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-2 text-sm text-gray-400"
                />
            </li>
        </ul>
    </nav>
</template>
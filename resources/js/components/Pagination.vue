<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" 
                 class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
                 v-html="link.label">
            </div>
            <Link v-else
                  :href="link.url"
                  class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-100 focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                  :class="{ 'bg-indigo-500 text-white hover:bg-indigo-600': link.active }"
                  v-html="link.label">
            </Link>
        </template>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    type?: 'submit' | 'button' | 'reset';
    disabled?: boolean;
}>();
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
    >
        <slot />
    </button>
</template>

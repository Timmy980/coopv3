<script setup lang="ts">
defineProps<{
    value: string;
    htmlFor?: string;
}>();
</script>

<template>
    <label :for="htmlFor" class="block text-sm font-medium text-gray-700">
        {{ value }}
    </label>
</template>

<template>
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ title }}
      </h2>
      <div class="flex items-center space-x-2">
        <StatusBadge type="status" :value="status" />
        <StatusBadge type="source" :value="source" />
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import StatusBadge from './StatusBadge.vue';
  
  defineProps<{
    title: string;
    status: string;
    source: string;
  }>();
  </script>
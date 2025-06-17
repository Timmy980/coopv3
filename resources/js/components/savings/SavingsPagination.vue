<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
      <!-- Results info -->
      <div class="text-sm text-gray-600 order-2 sm:order-1">
        Showing {{ savings.from }} - {{ savings.to }} of {{ savings.total }} savings
      </div>
      
      <!-- Pagination controls -->
      <div class="flex items-center gap-1 order-1 sm:order-2">
        <!-- Previous button -->
        <Link
          v-if="savings.prev_page_url"
          :href="savings.prev_page_url"
          class="px-2 sm:px-3 py-2 rounded text-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span class="hidden sm:inline">Previous</span>
        </Link>
        <span
          v-else
          class="px-2 sm:px-3 py-2 rounded text-sm bg-gray-100 border border-gray-200 text-gray-400 flex items-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span class="hidden sm:inline">Previous</span>
        </span>
  
        <!-- Page numbers (hide on mobile, show only current page) -->
        <div class="hidden sm:flex items-center gap-1">
          <template v-for="(link, i) in savings.links" :key="i">
            <Link
              v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
              :href="link.url"
              class="px-3 py-2 rounded text-sm transition-colors duration-150"
              :class="{ 
                'bg-blue-500 text-white': link.active,
                'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50': !link.active
              }"
            >
              {{ link.label }}
            </Link>
            <span
              v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
              class="px-3 py-2 rounded text-sm bg-gray-100 border border-gray-200 text-gray-400"
            >
              {{ link.label }}
            </span>
          </template>
        </div>
  
        <!-- Mobile page info -->
        <div class="sm:hidden px-3 py-2 bg-blue-500 text-white rounded text-sm font-medium">
          {{ savings.current_page }} / {{ savings.last_page }}
        </div>
  
        <!-- Next button -->
        <Link
          v-if="savings.next_page_url"
          :href="savings.next_page_url"
          class="px-2 sm:px-3 py-2 rounded text-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-1"
        >
          <span class="hidden sm:inline">Next</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
        <span
          v-else
          class="px-2 sm:px-3 py-2 rounded text-sm bg-gray-100 border border-gray-200 text-gray-400 flex items-center gap-1"
        >
          <span class="hidden sm:inline">Next</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </span>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';
  
  interface Props {
    savings: {
      from: number;
      to: number;
      total: number;
      current_page: number;
      last_page: number;
      prev_page_url: string | null;
      next_page_url: string | null;
      links: Array<{
        url: string | null;
        label: string;
        active: boolean;
      }>;
    };
  }
  
  defineProps<Props>();
  </script>
<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <InputLabel for="status" value="Status" />
          <select
            id="status"
            :value="filters.status"
            @input="$emit('update:status', ($event.target as HTMLSelectElement).value)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="failed">Failed</option>
          </select>
        </div>
  
        <div>
          <InputLabel for="source" value="Source" />
          <select
            id="source"
            :value="filters.source"
            @input="$emit('update:source', ($event.target as HTMLSelectElement).value)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
            <option value="">All Sources</option>
            <option value="member_gateway">Gateway Payment</option>
            <option value="member_deposit">Bank Deposit</option>
            <option value="admin_single">Admin Entry</option>
            <option value="admin_bulk">{{ bulkUploadLabel }}</option>
          </select>
        </div>
  
        <div>
          <InputLabel for="date_from" value="Date From" />
          <TextInput
            id="date_from"
            type="date"
            :model-value="filters.date_from"
            @update:model-value="$emit('update:date_from', $event)"
            class="mt-1 block w-full"
          />
        </div>
  
        <div>
          <InputLabel for="date_to" value="Date To" />
          <TextInput
            id="date_to"
            type="date"
            :model-value="filters.date_to"
            @update:model-value="$emit('update:date_to', $event)"
            class="mt-1 block w-full"
          />
        </div>
  
        <div class="md:col-span-4 flex justify-end">
          <SecondaryButton @click="$emit('clear-filters')">
            Clear Filters
          </SecondaryButton>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import InputLabel from '@/components/InputLabel.vue';
  import TextInput from '@/components/TextInput.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  
  interface Props {
    filters: {
      status: string;
      source: string;
      date_from: string;
      date_to: string;
    };
    bulkUploadLabel?: string;
  }
  
  withDefaults(defineProps<Props>(), {
    bulkUploadLabel: 'Admin Upload'
  });
  
  defineEmits<{
    'update:status': [value: string];
    'update:source': [value: string];
    'update:date_from': [value: string];
    'update:date_to': [value: string];
    'clear-filters': [];
  }>();
  </script>
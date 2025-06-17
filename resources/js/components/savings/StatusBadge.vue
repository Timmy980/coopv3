<template>
    <span :class="badgeClass">
      {{ displayText }}
    </span>
  </template>
  
  <script setup lang="ts">
  import { computed } from 'vue';
  
  const props = defineProps<{
    type: 'status' | 'source';
    value: string;
  }>();
  
  const badgeClass = computed(() => {
    if (props.type === 'status') {
      return getStatusClass(props.value);
    } else {
      return getSourceClass(props.value);
    }
  });
  
  const displayText = computed(() => {
    if (props.type === 'source') {
      return formatSource(props.value);
    }
    return props.value;
  });
  
  const getStatusClass = (status: string) => {
    switch (status) {
      case 'pending':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
      case 'approved':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
      case 'rejected':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
      case 'failed':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
      default:
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
    }
  };
  
  const getSourceClass = (source: string) => {
    switch (source) {
      case 'member_gateway':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
      case 'member_deposit':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
      case 'admin_single':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800';
      case 'admin_bulk':
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-pink-100 text-pink-800';
      default:
        return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
    }
  };
  
  const formatSource = (source: string) => {
    const sources = {
      member_gateway: 'Gateway Payment',
      member_deposit: 'Bank Deposit',
      admin_single: 'Admin Entry',
      admin_bulk: 'Admin Upload'
    };
    return sources[source as keyof typeof sources] || source;
  };
  </script>
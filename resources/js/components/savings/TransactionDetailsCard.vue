<template>
    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Transaction Details
        </h3>
      </div>
      <div class="px-6 py-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <dt class="text-sm font-medium text-gray-500 mb-1">Amount</dt>
              <dd class="text-2xl font-bold text-gray-900">{{ formatAmount(amount) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Transaction Date</dt>
              <dd class="text-sm text-gray-900 font-medium">{{ formatDate(transactionDate) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Reference Number</dt>
              <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">
                {{ referenceNumber || 'N/A' }}
              </dd>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Payment Source</dt>
              <dd class="mt-1">
                <StatusBadge type="source" :value="source" />
              </dd>
            </div>
            <div v-if="showTransactionId">
              <dt class="text-sm font-medium text-gray-500 mb-1">Transaction ID</dt>
              <dd class="text-sm text-gray-900 font-mono">{{ transactionId }}</dd>
            </div>
            <div v-if="createdAt">
              <dt class="text-sm font-medium text-gray-500 mb-1">Submitted On</dt>
              <dd class="text-sm text-gray-900">{{ formatDate(createdAt) }}</dd>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import StatusBadge from './StatusBadge.vue';
  
  defineProps<{
    amount: number;
    transactionDate: string;
    referenceNumber?: string;
    source: string;
    transactionId?: string | number;
    createdAt?: string;
    showTransactionId?: boolean;
  }>();
  
  const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency: 'NGN'
    }).format(amount || 0);
  };
  
  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-NG', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };
  </script>
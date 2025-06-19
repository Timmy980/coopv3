<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ memberColumnLabel }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="saving in savings.data" :key="saving.id">
              <td class="px-6 py-4 whitespace-nowrap">
                {{ formatDate(saving.transaction_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ saving.reference_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="showMemberDetails" class="text-sm font-medium text-gray-900">
                  {{ saving.member_account?.user?.first_name }} {{ saving.member_account?.user?.last_name }}
                </div>
                <div class="text-sm" :class="showMemberDetails ? 'text-gray-500' : 'font-medium text-gray-900'">
                  {{ saving.member_account?.account_number }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ formatAmount(saving.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getSourceClass(saving.source)">
                  {{ formatSource(saving.source) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(saving.status)">
                  {{ saving.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex gap-2">
                  <Link v-if="isAdmin" :href="route('admin.savings.show', saving.id)" class="text-indigo-600 hover:text-indigo-900">
                    View
                  </Link>
                  <Link v-else :href="route('member.savings.show', saving.id)" class="text-indigo-600 hover:text-indigo-900">
                    View
                  </Link>
                  <button
                    v-if="showUploadProofButton && saving.status === 'pending' && saving.source === 'member_deposit'"
                    @click="$emit('upload-proof', saving)"
                    class="ml-3 text-blue-600 hover:text-blue-900"
                  >
                    Upload Proof
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <SavingsPagination 
        v-if="savings.data.length > 0"
        :savings="savings"
        class="mt-6"
      />
      
      <div v-else-if="hasActiveFilters" class="text-center py-4 text-gray-500">
        No savings found matching your filters
      </div>
      <div v-else class="text-center py-4 text-gray-500">
        No savings available
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';
  import SavingsPagination from './SavingsPagination.vue';
  
  interface Props {
    savings: any;
    showMemberDetails?: boolean;
    showUploadProofButton?: boolean;
    memberColumnLabel?: string;
    hasActiveFilters?: boolean;
    isAdmin: boolean;
  }
  
  withDefaults(defineProps<Props>(), {
    showMemberDetails: false,
    showUploadProofButton: false,
    memberColumnLabel: 'Account',
    hasActiveFilters: false,
    isAdmin: false
  });
  
  defineEmits<{
    'upload-proof': [saving: any];
  }>();
  
  // Utility functions
  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
  };
  
  const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency: 'NGN'
    }).format(amount || 0);
  };
  
  const formatSource = (source: string) => {
    const sources = {
      member_gateway: 'Gateway Payment',
      member_deposit: 'Bank Deposit',
      admin_single: 'Admin Entry',
      admin_bulk: 'Bulk Upload'
    };
    return sources[source as keyof typeof sources] || source;
  };
  
  const getSourceClass = (source: string) => {
    switch (source) {
      case 'member_gateway':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
      case 'member_deposit':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
      case 'admin_single':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800';
      case 'admin_bulk':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800';
      default:
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
    }
  };
  
  const getStatusClass = (status: string) => {
    switch (status) {
      case 'pending':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
      case 'approved':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
      case 'rejected':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
      case 'failed':
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
      default:
        return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
    }
  };
  </script>
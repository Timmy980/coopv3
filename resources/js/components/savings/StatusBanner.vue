<template>
    <div :class="[
      'p-6 rounded-lg shadow-sm border-l-4',
      statusColors.bg,
      statusColors.border
    ]">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg 
            :class="['h-6 w-6', statusColors.icon]" 
            xmlns="http://www.w3.org/2000/svg" 
            viewBox="0 0 20 20" 
            fill="currentColor"
          >
            <path 
              fill-rule="evenodd" 
              :d="statusIcon" 
              clip-rule="evenodd" 
            />
          </svg>
        </div>
        <div class="ml-4 flex-1">
          <h3 :class="['text-lg font-medium', statusColors.title]">
            {{ statusMessage }}
          </h3>
          <div v-if="status === 'rejected' && rejectionReason" class="mt-2">
            <p :class="['text-sm', statusColors.text]">
              <strong>Reason:</strong> {{ rejectionReason }}
            </p>
          </div>
          <div v-if="status === 'approved' && approvedAt" class="mt-2">
            <p :class="['text-sm', statusColors.text]">
              <strong>Approved on:</strong> {{ formatDate(approvedAt) }}
              <span v-if="approvedBy">
                by {{ approvedBy.first_name }} {{ approvedBy.last_name }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { computed } from 'vue';
  
  interface ApprovedBy {
    first_name: string;
    last_name: string;
  }
  
  const props = defineProps<{
    status: string;
    rejectionReason?: string;
    approvedAt?: string;
    approvedBy?: ApprovedBy;
  }>();
  
  const statusMessage = computed(() => {
    const messages = {
      pending: 'This saving is pending approval',
      approved: 'This saving has been approved',
      rejected: 'This saving has been rejected',
      failed: 'This saving transaction failed'
    };
    return messages[props.status as keyof typeof messages] || props.status;
  });
  
  const statusIcon = computed(() => {
    switch (props.status) {
      case 'pending':
        return 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z';
      case 'approved':
        return 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z';
      case 'rejected':
      case 'failed':
        return 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z';
      default:
        return 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z';
    }
  });
  
  const statusColors = computed(() => {
    switch (props.status) {
      case 'pending':
        return {
          bg: 'bg-yellow-50',
          border: 'border-yellow-200',
          icon: 'text-yellow-500',
          title: 'text-yellow-800',
          text: 'text-yellow-700'
        };
      case 'approved':
        return {
          bg: 'bg-green-50',
          border: 'border-green-200',
          icon: 'text-green-500',
          title: 'text-green-800',
          text: 'text-green-700'
        };
      case 'rejected':
        return {
          bg: 'bg-red-50',
          border: 'border-red-200',
          icon: 'text-red-500',
          title: 'text-red-800',
          text: 'text-red-700'
        };
      case 'failed':
        return {
          bg: 'bg-gray-50',
          border: 'border-gray-200',
          icon: 'text-gray-500',
          title: 'text-gray-800',
          text: 'text-gray-700'
        };
      default:
        return {
          bg: 'bg-gray-50',
          border: 'border-gray-200',
          icon: 'text-gray-500',
          title: 'text-gray-800',
          text: 'text-gray-700'
        };
    }
  });
  
  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-NG', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };
  </script>
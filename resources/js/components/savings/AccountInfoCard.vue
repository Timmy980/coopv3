<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          Account Information
        </h3>
      </div>
      <div class="px-6 py-5 space-y-4">
        <div>
          <dt class="text-sm font-medium text-gray-500 mb-1">Member Account</dt>
          <dd class="text-sm text-gray-900">
            <div class="font-medium">{{ memberAccount.account_number }}</div>
            <div v-if="memberAccount.account_type" class="text-xs text-gray-500">
              {{ memberAccount.account_type.name }}
            </div>
          </dd>
        </div>
        <div v-if="memberAccount.user && showMemberName">
          <dt class="text-sm font-medium text-gray-500 mb-1">Member</dt>
          <dd class="text-sm text-gray-900 font-medium">
            {{ memberAccount.user.first_name }} {{ memberAccount.user.last_name }}
          </dd>
        </div>
        <div>
          <dt class="text-sm font-medium text-gray-500 mb-1">Cooperative Account</dt>
          <dd class="text-sm text-gray-900 font-medium">{{ cooperativeAccount.account_name }}</dd>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  interface AccountType {
    name: string;
  }
  
  interface User {
    first_name: string;
    last_name: string;
  }
  
  interface MemberAccount {
    account_number: string;
    account_type?: AccountType;
    user?: User;
  }
  
  interface CooperativeAccount {
    account_name: string;
  }
  
  const props = withDefaults(defineProps<{
    memberAccount: MemberAccount;
    cooperativeAccount: CooperativeAccount;
    showMemberName?: boolean;
  }>(), {
    showMemberName: true
  });
  </script>
<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <InputLabel :for="memberAccountId" value="Member Account" />
        <select
          :id="memberAccountId"
          :value="modelValue.member_account_id"
          @input="$emit('update:modelValue', { ...modelValue, member_account_id: ($event.target as HTMLSelectElement).value })"
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
          <option value="">Select Account</option>
          <option v-for="account in memberAccounts" :key="account.id" :value="account.id">
            <template v-if="showMemberNames">
              {{ account.user?.first_name }} {{ account.user?.last_name }} - {{ account.account_type?.name }} ({{ account.account_number }})
            </template>
            <template v-else>
              {{ account.account_type?.name }} - {{ account.account_number }}
            </template>
          </option>
        </select>
        <InputError :message="errors.member_account_id" class="mt-2" />
      </div>
  
      <div>
        <InputLabel :for="cooperativeAccountId" value="Cooperative Account" />
        <select
          :id="cooperativeAccountId"
          :value="modelValue.cooperative_account_id"
          @input="$emit('update:modelValue', { ...modelValue, cooperative_account_id: ($event.target as HTMLSelectElement).value })"
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
          <option value="">Select Account</option>
          <option v-for="account in cooperativeAccounts" :key="account.id" :value="account.id">
            {{ account.account_name }}
          </option>
        </select>
        <InputError :message="errors.cooperative_account_id" class="mt-2" />
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import InputLabel from '@/components/InputLabel.vue';
  import InputError from '@/components/InputError.vue';
  
  interface Props {
    modelValue: {
      member_account_id: string;
      cooperative_account_id: string;
    };
    memberAccounts: Array<any>;
    cooperativeAccounts: Array<any>;
    errors: {
      member_account_id?: string;
      cooperative_account_id?: string;
    };
    showMemberNames?: boolean;
    memberAccountId?: string;
    cooperativeAccountId?: string;
  }
  
  withDefaults(defineProps<Props>(), {
    showMemberNames: false,
    memberAccountId: 'member_account',
    cooperativeAccountId: 'cooperative_account'
  });
  
  defineEmits<{
    'update:modelValue': [value: { member_account_id: string; cooperative_account_id: string }];
  }>();
  </script>
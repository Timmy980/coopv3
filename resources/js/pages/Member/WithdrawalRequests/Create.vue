<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';

interface AccountType {
    name: string;
    withdrawal_rules: {
        max_percentage_per_withdrawal?: number;
        minimum_balance_required?: number;
    };
}

interface MemberAccount {
    id: string | number;
    balance: number;
    account_type: AccountType;
}

interface Props {
    memberAccounts: MemberAccount[];
}

const props = defineProps<Props>();

const form = useForm({
    member_account_id: '',
    requested_amount: '',
    member_bank_account_for_receipt: ''
});

const selectedAccount = computed(() => {
    return props.memberAccounts.find(account => account.id === form.member_account_id);
});

const maxWithdrawalAmount = computed(() => {
    if (!selectedAccount.value || !selectedAccount.value.account_type.withdrawal_rules.max_percentage_per_withdrawal) {
        return null;
    }
    return selectedAccount.value.balance * (selectedAccount.value.account_type.withdrawal_rules.max_percentage_per_withdrawal / 100);
});

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
};

const submit = (): void => {
    form.post(route('member.withdrawal-requests.store'));
};
</script>

<template>
    <Head :title="`New Withdrawal Request`" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                New Withdrawal Request
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Account Selection -->
                            <div class="mb-6">
                                <InputLabel for="member_account_id" value="Select Account" />
                                <select
                                    id="member_account_id"
                                    v-model="form.member_account_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    required
                                >
                                    <option value="">Select an account</option>
                                    <option v-for="account in memberAccounts" :key="account.id" :value="account.id">
                                        {{ account.account_type.name }} - Balance: {{ formatCurrency(account.balance) }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.member_account_id" class="mt-2" />
                            </div>

                            <!-- Amount -->
                            <div class="mb-6">
                                <InputLabel for="requested_amount" value="Withdrawal Amount" />
                                <TextInput
                                    id="requested_amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    v-model="form.requested_amount"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.requested_amount" class="mt-2" />
                                
                                <!-- Show withdrawal limits if an account is selected -->
                                <div v-if="selectedAccount" class="mt-2 text-sm text-gray-600">
                                    <div v-if="selectedAccount.account_type.withdrawal_rules.max_percentage_per_withdrawal">
                                        Maximum withdrawal: {{ formatCurrency(maxWithdrawalAmount) }}
                                        ({{ selectedAccount.account_type.withdrawal_rules.max_percentage_per_withdrawal }}% of balance)
                                    </div>
                                    <div v-if="selectedAccount.account_type.withdrawal_rules.minimum_balance_required">
                                        Minimum balance required: {{ formatCurrency(selectedAccount.account_type.withdrawal_rules.minimum_balance_required) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Account Details -->
                            <div class="mb-6">
                                <InputLabel for="member_bank_account_for_receipt" value="Bank Account for Receipt" />
                                <TextInput
                                    id="member_bank_account_for_receipt"
                                    type="text"
                                    v-model="form.member_bank_account_for_receipt"
                                    class="mt-1 block w-full"
                                    placeholder="Bank Name - Account Number - Account Name"
                                    required
                                />
                                <InputError :message="form.errors.member_bank_account_for_receipt" class="mt-2" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end">
                                <Link
                                    :href="route('member.withdrawal-requests.index')"
                                    class="mr-4 text-gray-600 hover:text-gray-900"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :disabled="form.processing">
                                    Submit Withdrawal Request
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
    account: {
        type: Object,
        required: true
    },
    summary: {
        type: Object,
        required: true
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        case 'suspended':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getAccountTypeLabel = (type) => {
    const types = {
        'savings': 'Savings Account',
        'current': 'Current Account',
        'fixed_deposit': 'Fixed Deposit',
        'loan_disbursement': 'Loan Disbursement Account',
        'operational': 'Operational Account',
        'reserve': 'Reserve Account',
    };
    return types[type] || type;
};
</script>

<template>
    <Head :title="`Account: ${account.account_name}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cooperative Account Details
                </h2>
                <Link :href="route('cooperative_accounts.index')">
                    <SecondaryButton>Back to Accounts</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Account Information Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ account.account_name }}</h3>
                                <p class="text-gray-600 mt-1">{{ getAccountTypeLabel(account.account_type) }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 rounded-full text-sm font-medium" :class="getStatusClass(account.status)">
                                    {{ account.status.charAt(0).toUpperCase() + account.status.slice(1) }}
                                </span>
                                <Link :href="route('cooperative_accounts.edit', account.id)">
                                    <PrimaryButton>Edit Account</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Account Number</h4>
                                <p class="text-lg font-semibold text-gray-900">{{ account.account_number }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg" v-if="account.bank_name">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Bank Name</h4>
                                <p class="text-lg font-semibold text-gray-900">{{ account.bank_name }}</p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Current Balance</h4>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(account.balance) }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">Created:</span>
                                <span class="ml-2">{{ formatDate(account.created_at) }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Updated:</span>
                                <span class="ml-2">{{ formatDate(account.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Summary Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Transaction Summary</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-700 mb-2">Total Savings</h4>
                                <p class="text-xl font-bold text-blue-600">{{ formatCurrency(summary.total_savings) }}</p>
                                <p class="text-sm text-blue-500 mt-1">{{ account.savings?.length || 0 }} transactions</p>
                            </div>
                            
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-red-700 mb-2">Total Disbursements</h4>
                                <p class="text-xl font-bold text-red-600">{{ formatCurrency(summary.total_disbursements) }}</p>
                                <p class="text-sm text-red-500 mt-1">
                                    {{ (account.disbursed_loans?.length || 0) + (account.disbursed_withdrawals?.length || 0) }} transactions
                                </p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-700 mb-2">Total Repayments</h4>
                                <p class="text-xl font-bold text-green-600">{{ formatCurrency(summary.total_repayments) }}</p>
                                <p class="text-sm text-green-500 mt-1">{{ account.received_loan_repayments?.length || 0 }} transactions</p>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-purple-700 mb-2">Net Cash Flow</h4>
                                <p class="text-xl font-bold" :class="summary.net_flow >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(summary.net_flow) }}
                                </p>
                                <p class="text-sm text-purple-500 mt-1">
                                    {{ summary.net_flow >= 0 ? 'Positive' : 'Negative' }} flow
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Savings -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Savings</h3>
                            <div class="space-y-3">
                                <div v-for="saving in account.savings?.slice(0, 5)" :key="saving.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ formatCurrency(saving.amount) }}</p>
                                        <p class="text-sm text-gray-500">{{ formatDate(saving.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="!account.savings?.length" class="text-center py-4 text-gray-500">
                                    No savings transactions yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Loan Disbursements -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Loan Disbursements</h3>
                            <div class="space-y-3">
                                <div v-for="loan in account.disbursed_loans?.slice(0, 5)" :key="loan.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ formatCurrency(loan.amount) }}</p>
                                        <p class="text-sm text-gray-500">{{ formatDate(loan.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="!account.disbursed_loans?.length" class="text-center py-4 text-gray-500">
                                    No loan disbursements yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Loan Repayments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Loan Repayments</h3>
                            <div class="space-y-3">
                                <div v-for="repayment in account.received_loan_repayments?.slice(0, 5)" :key="repayment.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ formatCurrency(repayment.amount) }}</p>
                                        <p class="text-sm text-gray-500">{{ formatDate(repayment.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="!account.received_loan_repayments?.length" class="text-center py-4 text-gray-500">
                                    No loan repayments yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Withdrawals -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Withdrawals</h3>
                            <div class="space-y-3">
                                <div v-for="withdrawal in account.disbursed_withdrawals?.slice(0, 5)" :key="withdrawal.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ formatCurrency(withdrawal.amount) }}</p>
                                        <p class="text-sm text-gray-500">{{ formatDate(withdrawal.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="!account.disbursed_withdrawals?.length" class="text-center py-4 text-gray-500">
                                    No withdrawal transactions yet
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


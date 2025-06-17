<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
    account: {
        type: Object,
        required: true
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

const getUserFullName = (user) => {
    return `${user.first_name} ${user.last_name}`;
};

const getTransactionStatusClass = (status) => {
    switch (status) {
        case 'completed':
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head :title="`${getUserFullName(account.user)} - ${account.account_type.name} Account`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Member Account Details
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ getUserFullName(account.user) }} - {{ account.account_type.name }}
                    </p>
                </div>
                <Link :href="route('member_accounts.index')">
                    <SecondaryButton>← Back to Accounts</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Account Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Account Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm text-blue-600 font-medium">Account Number</div>
                                <div class="text-2xl font-bold text-blue-900">{{ account.account_number }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm text-green-600 font-medium">Current Balance</div>
                                <div class="text-2xl font-bold text-green-900">{{ formatCurrency(account.balance) }}</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm text-purple-600 font-medium">Account Type</div>
                                <div class="text-lg font-bold text-purple-900">{{ account.account_type.name }}</div>
                                <div class="text-sm text-purple-700">{{ account.account_type.interest_rate }}% Interest</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 font-medium">Status</div>
                                <div class="mt-2">
                                    <span class="px-2 py-1 rounded text-sm" :class="getStatusClass(account.status)">
                                        {{ account.status.charAt(0).toUpperCase() + account.status.slice(1) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Member Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm text-gray-600 font-medium">Full Name</div>
                                <div class="text-lg">{{ getUserFullName(account.user) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium">Email Address</div>
                                <div class="text-lg">{{ account.user.email }}</div>
                            </div>
                            <div v-if="account.user.phone_number">
                                <div class="text-sm text-gray-600 font-medium">Phone Number</div>
                                <div class="text-lg">{{ account.user.phone_number }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium">Account Created</div>
                                <div class="text-lg">{{ formatDate(account.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Type Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Account Type Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm text-gray-600 font-medium">Account Type</div>
                                <div class="text-lg">{{ account.account_type.name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium">Interest Rate</div>
                                <div class="text-lg">{{ account.account_type.interest_rate }}% per annum</div>
                            </div>
                            <div v-if="account.account_type.description" class="md:col-span-2">
                                <div class="text-sm text-gray-600 font-medium">Description</div>
                                <div class="text-lg">{{ account.account_type.description }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Savings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Recent Savings</h3>
                            <div class="text-sm text-gray-600">
                                Total: {{ account.savings_count }} transactions
                            </div>
                        </div>
                        
                        <div v-if="account.savings && account.savings.length > 0" class="space-y-3">
                            <div v-for="saving in account.savings" :key="saving.id" 
                                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-grow">
                                    <div class="flex items-center gap-4">
                                        <div class="font-semibold text-green-600">
                                            {{ formatCurrency(saving.amount) }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ formatDate(saving.created_at) }}
                                        </div>
                                        <span v-if="saving.status" class="px-2 py-1 rounded text-xs" 
                                              :class="getTransactionStatusClass(saving.status)">
                                            {{ saving.status.charAt(0).toUpperCase() + saving.status.slice(1) }}
                                        </span>
                                    </div>
                                    <div v-if="saving.description" class="text-sm text-gray-600 mt-1">
                                        {{ saving.description }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            No savings transactions found
                        </div>
                    </div>
                </div>

                <!-- Recent Withdrawal Requests -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Recent Withdrawal Requests</h3>
                            <div class="text-sm text-gray-600">
                                Total: {{ account.withdrawal_requests_count }} requests
                            </div>
                        </div>
                        
                        <div v-if="account.withdrawal_requests && account.withdrawal_requests.length > 0" class="space-y-3">
                            <div v-for="withdrawal in account.withdrawal_requests" :key="withdrawal.id" 
                                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-grow">
                                    <div class="flex items-center gap-4">
                                        <div class="font-semibold text-red-600">
                                            {{ formatCurrency(withdrawal.amount) }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ formatDate(withdrawal.created_at) }}
                                        </div>
                                        <span v-if="withdrawal.status" class="px-2 py-1 rounded text-xs" 
                                              :class="getTransactionStatusClass(withdrawal.status)">
                                            {{ withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1) }}
                                        </span>
                                    </div>
                                    <div v-if="withdrawal.reason" class="text-sm text-gray-600 mt-1">
                                        Reason: {{ withdrawal.reason }}
                                    </div>
                                    <div v-if="withdrawal.admin_notes" class="text-sm text-blue-600 mt-1">
                                        Admin Notes: {{ withdrawal.admin_notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            No withdrawal requests found
                        </div>
                    </div>
                </div>

                <!-- Transaction Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Transaction Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-sm text-green-600 font-medium">Total Savings</div>
                                <div class="text-2xl font-bold text-green-900">{{ account.savings_count }}</div>
                                <div class="text-sm text-green-700">transactions</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg text-center">
                                <div class="text-sm text-orange-600 font-medium">Withdrawal Requests</div>
                                <div class="text-2xl font-bold text-orange-900">{{ account.withdrawal_requests_count }}</div>
                                <div class="text-sm text-orange-700">requests</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-sm text-blue-600 font-medium">Account Age</div>
                                <div class="text-lg font-bold text-blue-900">
                                    {{ Math.floor((new Date() - new Date(account.created_at)) / (1000 * 60 * 60 * 24)) }}
                                </div>
                                <div class="text-sm text-blue-700">days</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
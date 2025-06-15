<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type MemberAccount } from '@/types';

const props = defineProps<{
    account: MemberAccount & {
        savings: Array<{
            id: number;
            amount: number;
            status: string;
            transaction_date: string;
        }>;
        withdrawal_requests: Array<{
            id: number;
            amount: number;
            status: string;
            requested_date: string;
        }>;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Accounts',
        href: route('member.accounts.index'),
    },
    {
        title: props.account.account_type.name,
        href: route('member.accounts.show', props.account.id),
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
        case 'completed':
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'suspended':
        case 'rejected':
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head :title="`${account.account_type.name} Account Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Account Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-blue-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-blue-600 font-medium">Account Number</div>
                    <div class="text-2xl font-bold text-blue-900">{{ account.account_number }}</div>
                </div>
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-green-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-green-600 font-medium">Current Balance</div>
                    <div class="text-2xl font-bold text-green-900">{{ formatCurrency(account.balance) }}</div>
                </div>
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-purple-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-purple-600 font-medium">Account Type</div>
                    <div class="text-lg font-bold text-purple-900">{{ account.account_type.name }}</div>
                    <div class="text-sm text-purple-700">{{ account.account_type.interest_rate }}% Interest</div>
                </div>
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-gray-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-gray-600 font-medium">Status</div>
                    <div class="mt-2">
                        <span class="px-2 py-1 rounded text-sm" :class="getStatusClass(account.status)">
                            {{ account.status.charAt(0).toUpperCase() + account.status.slice(1) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Savings -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Savings</h3>
                        <Link 
                            :href="route('savings.index')"
                            class="text-sm text-primary hover:text-primary-dark"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="saving in account.savings" :key="saving.id" 
                             class="flex items-center justify-between rounded-lg bg-gray-50 p-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ formatCurrency(saving.amount) }}</div>
                                <div class="text-sm text-gray-500">{{ formatDate(saving.transaction_date) }}</div>
                            </div>
                            <span class="rounded px-2 py-1 text-xs" :class="getStatusClass(saving.status)">
                                {{ saving.status.charAt(0).toUpperCase() + saving.status.slice(1) }}
                            </span>
                        </div>
                        <div v-if="account.savings.length === 0" class="text-center py-4 text-gray-500">
                            No recent savings
                        </div>
                    </div>
                </div>

                <!-- Recent Withdrawals -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Withdrawals</h3>
                        <Link 
                            :href="route('member.withdrawal-requests.index')"
                            class="text-sm text-primary hover:text-primary-dark"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="withdrawal in account.withdrawal_requests" :key="withdrawal.id" 
                             class="flex items-center justify-between rounded-lg bg-gray-50 p-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ formatCurrency(withdrawal.amount) }}</div>
                                <div class="text-sm text-gray-500">{{ formatDate(withdrawal.requested_date) }}</div>
                            </div>
                            <span class="rounded px-2 py-1 text-xs" :class="getStatusClass(withdrawal.status)">
                                {{ withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1) }}
                            </span>
                        </div>
                        <div v-if="account.withdrawal_requests.length === 0" class="text-center py-4 text-gray-500">
                            No recent withdrawals
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
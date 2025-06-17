<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type MemberAccount } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    accounts: MemberAccount[]
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
};

const getStatusClass = (status: string) => {
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

const totalBalance = props.accounts.reduce((sum, account) => sum + account.balance, 0);
const totalSavings = props.accounts.reduce((sum, account) => sum + account.savings_count, 0);
const totalWithdrawals = props.accounts.reduce((sum, account) => sum + account.withdrawal_requests_count, 0);
</script>

<template>
    <Head title="Member Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Overview Stats -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-green-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-green-600 font-medium">Total Balance</div>
                    <div class="text-2xl font-bold text-green-900">{{ formatCurrency(totalBalance) }}</div>
                </div>
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-blue-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-blue-600 font-medium">Total Savings</div>
                    <div class="text-2xl font-bold text-blue-900">{{ totalSavings }}</div>
                </div>
                <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-purple-50 p-6 dark:border-sidebar-border">
                    <div class="text-sm text-purple-600 font-medium">Total Withdrawals</div>
                    <div class="text-2xl font-bold text-purple-900">{{ totalWithdrawals }}</div>
                </div>
            </div>

            <!-- Accounts List -->
            <div class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">My Accounts</h2>
                        <Link 
                            :href="route('member.accounts.index')"
                            class="text-sm text-primary hover:text-primary-dark"
                        >
                            View All
                        </Link>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="account in accounts" :key="account.id" 
                             class="flex items-center justify-between rounded-lg bg-gray-50 p-4">
                            <div class="flex-grow">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-medium text-gray-900">{{ account.account_type.name }}</h3>
                                    <span class="rounded px-2 py-1 text-xs" :class="getStatusClass(account.status)">
                                        {{ account.status.charAt(0).toUpperCase() + account.status.slice(1) }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">{{ account.account_number }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ account.savings_count }} savings · {{ account.withdrawal_requests_count }} withdrawals
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
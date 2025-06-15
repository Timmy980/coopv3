<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
    accounts: {
        type: Array,
        required: true
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Accounts',
        href: route('member.accounts.index'),
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
</script>

<template>
    <Head title="My Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid gap-4 md:grid-cols-3">
                <div v-for="account in accounts" :key="account.id" 
                     class="flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ account.account_type.name }}</h3>
                            <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                        </div>
                        <span class="rounded px-2 py-1 text-xs" :class="getStatusClass(account.status)">
                            {{ account.status.charAt(0).toUpperCase() + account.status.slice(1) }}
                        </span>
                    </div>
                    
                    <div class="mb-6">
                        <div class="text-sm text-gray-500">Current Balance</div>
                        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(account.balance) }}</div>
                    </div>

                    <div class="mb-6 grid grid-cols-2 gap-4 text-center">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-sm font-medium text-gray-500">Savings</div>
                            <div class="text-lg font-semibold text-gray-900">{{ account.savings_count }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-sm font-medium text-gray-500">Withdrawals</div>
                            <div class="text-lg font-semibold text-gray-900">{{ account.withdrawal_requests_count }}</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <Link 
                            :href="route('member.accounts.show', account.id)"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            View Details
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
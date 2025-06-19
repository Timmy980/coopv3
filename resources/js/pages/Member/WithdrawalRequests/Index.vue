<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import type { PaginatedData, WithdrawalRequest } from '@/types';

interface Props {
    withdrawalRequests: PaginatedData<WithdrawalRequest>;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
        disbursed: 'bg-green-100 text-green-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Withdrawal Requests" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Withdrawal Requests
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <Link :href="route('member.withdrawals.create')">
                            <PrimaryButton>
                                New Withdrawal Request
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>

                <!-- Withdrawal Requests -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Withdrawal Requests List -->
                        <div v-if="withdrawalRequests.data.length > 0" class="space-y-6">
                            <div v-for="request in withdrawalRequests.data" :key="request.id" 
                                 class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="p-6">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-lg font-semibold text-gray-900">
                                                    {{ formatCurrency(request.requested_amount) }}
                                                </span>
                                                <span :class="getStatusClass(request.status)" 
                                                      class="px-3 py-1 rounded-full text-xs font-medium">
                                                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600">
                                                From: {{ request.member_account.account_type.name }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500">
                                                Requested on: {{ formatDate(request.request_date) }}
                                            </div>
                                        </div>
                                        <Link
                                            :href="route('member.withdrawals.show', request.id)"
                                            class="text-primary hover:text-primary-dark"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <div class="text-gray-500">No withdrawal requests found</div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="withdrawalRequests.data.length > 0" class="mt-6">
                            <Pagination :links="withdrawalRequests.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
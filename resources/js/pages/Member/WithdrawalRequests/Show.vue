<template>
    <AppLayout :title="`Withdrawal Request #${withdrawalRequest.id}`">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Withdrawal Request Details
                </h2>
                <Link
                    :href="route('member.withdrawals.index')"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Back to Requests
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Status Banner -->
                        <div :class="getStatusBannerClass(withdrawalRequest.status)" class="mb-6 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <component :is="getStatusIcon(withdrawalRequest.status)" class="h-5 w-5" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium">
                                        {{ getStatusMessage(withdrawalRequest.status) }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- Request Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Request Information</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm text-gray-500">Amount Requested</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(withdrawalRequest.requested_amount) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Request Date</dt>
                                        <dd class="text-gray-900">{{ formatDate(withdrawalRequest.request_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Status</dt>
                                        <dd>
                                            <span :class="getStatusClass(withdrawalRequest.status)" 
                                                  class="px-3 py-1 rounded-full text-xs font-medium">
                                                {{ withdrawalRequest.status.charAt(0).toUpperCase() + withdrawalRequest.status.slice(1) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="withdrawalRequest.approval_date">
                                        <dt class="text-sm text-gray-500">Approval Date</dt>
                                        <dd class="text-gray-900">{{ formatDate(withdrawalRequest.approval_date) }}</dd>
                                    </div>
                                    <div v-if="withdrawalRequest.disbursement_date">
                                        <dt class="text-sm text-gray-500">Disbursement Date</dt>
                                        <dd class="text-gray-900">{{ formatDate(withdrawalRequest.disbursement_date) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-4">Account Information</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm text-gray-500">Account Type</dt>
                                        <dd class="text-gray-900">{{ withdrawalRequest.member_account.account_type.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Bank Account for Receipt</dt>
                                        <dd class="text-gray-900">{{ withdrawalRequest.member_bank_account_for_receipt }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    CircleCheck, 
    CircleX, 
    Clock, 
    Banknote 
} from 'lucide-vue-next';

const props = defineProps({
    withdrawalRequest: {
        type: Object,
        required: true
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
        disbursed: 'bg-green-100 text-green-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusBannerClass = (status) => {
    const classes = {
        pending: 'bg-yellow-50 border-l-4 border-yellow-400',
        approved: 'bg-blue-50 border-l-4 border-blue-400',
        rejected: 'bg-red-50 border-l-4 border-red-400',
        disbursed: 'bg-green-50 border-l-4 border-green-400'
    };
    return classes[status] || 'bg-gray-50 border-l-4 border-gray-400';
};

const getStatusIcon = (status) => {
    const icons = {
        pending: Clock,
        approved: CircleCheck,
        rejected: CircleX,
        disbursed: Banknote
    };
    return icons[status] || Clock;
};

const getStatusMessage = (status) => {
    const messages = {
        pending: 'Your withdrawal request is being reviewed',
        approved: 'Your withdrawal request has been approved and is awaiting disbursement',
        rejected: 'Your withdrawal request has been rejected',
        disbursed: 'Funds have been disbursed to your bank account'
    };
    return messages[status] || 'Status unknown';
};
</script> 
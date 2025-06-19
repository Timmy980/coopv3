<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import lodash from 'lodash';
import InputLabel from '@/components/InputLabel.vue';
import InputError from '@/components/InputError.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import Modal from '@/components/Modal.vue';
import Pagination from '@/components/Pagination.vue';
import type { PaginatedData, WithdrawalRequest, WithdrawalFilters } from '@/types';

interface Props {
    withdrawalRequests: PaginatedData<WithdrawalRequest>;
    filters: WithdrawalFilters;
}

const props = defineProps<Props>();

// Reactive filter state
const statusFilter = ref(props.filters?.status ?? 'pending');
const startDateFilter = ref(props.filters?.start_date ?? '');
const endDateFilter = ref(props.filters?.end_date ?? '');
const searchFilter = ref(props.filters?.search ?? '');

// Modal states
const showingRejectModal = ref(false);
const selectedRequest = ref<WithdrawalRequest | null>(null);

// Forms
const rejectForm = useForm({
    rejection_reason: ''
});

// Filter functions
const performFilter = () => {
    router.get(
        route('admin.withdrawals.index'),
        {
            status: statusFilter.value,
            start_date: startDateFilter.value,
            end_date: endDateFilter.value,
            search: searchFilter.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedFilter = lodash.debounce(performFilter, 300);
watch([statusFilter, startDateFilter, endDateFilter, searchFilter], () => {
    debouncedFilter();
});

// Action functions
const approve = (request: WithdrawalRequest) => {
    if (confirm('Are you sure you want to approve this withdrawal request?')) {
        router.patch(route('admin.withdrawals.approvals.approve', request.id), {}, {
            preserveScroll: true
        });
    }
};

const showRejectModal = (request: WithdrawalRequest) => {
    selectedRequest.value = request;
    showingRejectModal.value = true;
};

const closeRejectModal = () => {
    showingRejectModal.value = false;
    selectedRequest.value = null;
    rejectForm.reset();
};

const submitReject = () => {
    if (selectedRequest.value) {
        rejectForm.patch(route('admin.withdrawals.approvals.reject', selectedRequest.value.id), {
            onSuccess: () => closeRejectModal()
        });
    }
};

// Utility functions
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
};

const formatStatus = (status: string) => {
    const statuses: Record<string, string> = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected'
    };
    return statuses[status] || status;
};

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
        approved: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
        rejected: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'
    };
    return classes[status] || '';
};

const clearFilters = () => {
    statusFilter.value = 'pending';
    startDateFilter.value = '';
    endDateFilter.value = '';
    searchFilter.value = '';
};
</script>

<template>
    <Head title="Withdrawal Requests" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Withdrawal Requests
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="statusFilter"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    @change="performFilter"
                                >
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="start_date" value="Start Date" />
                                <input
                                    type="date"
                                    id="start_date"
                                    v-model="startDateFilter"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    @change="performFilter"
                                />
                            </div>
                            <div>
                                <InputLabel for="end_date" value="End Date" />
                                <input
                                    type="date"
                                    id="end_date"
                                    v-model="endDateFilter"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    @change="performFilter"
                                />
                            </div>
                            <div>
                                <InputLabel for="search" value="Search Account" />
                                <input
                                    type="text"
                                    id="search"
                                    v-model="searchFilter"
                                    placeholder="Search by account number..."
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    @input="debouncedFilter"
                                />
                            </div>

                            <div class="md:col-span-4 flex justify-end">
                                <SecondaryButton @click="clearFilters">
                                    Clear Filters
                                </SecondaryButton>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in withdrawalRequests.data" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(request.request_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.member_account.account_number }}
                                            <div class="text-sm text-gray-500">
                                                {{ request.user.first_name }} {{ request.user.last_name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.member_account.account_type.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatAmount(request.requested_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.member_bank_account_for_receipt }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="statusClass(request.status)">
                                                {{ formatStatus(request.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link 
                                                :href="route('admin.withdrawals.show', request.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </Link>
                                            <button
                                                v-if="request.status === 'pending'"
                                                @click="approve(request)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                v-if="request.status === 'pending'"
                                                @click="showRejectModal(request)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="withdrawalRequests.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showingRejectModal" @close="closeRejectModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Reject Withdrawal Request
                </h2>

                <form @submit.prevent="submitReject" class="mt-6">
                    <div>
                        <InputLabel for="rejection_reason" value="Rejection Reason" />
                        <textarea
                            id="rejection_reason"
                            v-model="rejectForm.rejection_reason"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300"
                            required
                        ></textarea>
                        <InputError :message="rejectForm.errors.rejection_reason" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeRejectModal">
                            Cancel
                        </SecondaryButton>
                        <DangerButton type="submit" :disabled="rejectForm.processing">
                            Reject
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
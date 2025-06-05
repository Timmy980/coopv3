<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
    saving: {
        type: Object,
        required: true
    }
});

const appUrl = usePage().props.appUrl;

const showRejectModal = ref(false);
const rejectForm = useForm({
    rejection_reason: ''
});

const errorMessage = ref('');

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
};

const formatSource = (source) => {
    const sources = {
        member_gateway: 'Gateway Payment',
        member_deposit: 'Bank Deposit',
        admin_single: 'Admin Entry',
        admin_bulk: 'Bulk Upload'
    };
    return sources[source] || source;
};

const statusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        failed: 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const sourceClass = (source) => {
    const classes = {
        member_gateway: 'bg-blue-100 text-blue-800',
        member_deposit: 'bg-purple-100 text-purple-800',
        admin_single: 'bg-indigo-100 text-indigo-800',
        admin_bulk: 'bg-pink-100 text-pink-800'
    };
    return classes[source] || 'bg-gray-100 text-gray-800';
};

const approveSaving = () => {
    errorMessage.value = ''; // Clear any previous error
    router.put(route('savings.approve', props.saving.id), {
        status: 'approved'
    }, {
        preserveScroll: true,
        onError: (errors) => {
            errorMessage.value = errors.saving || 'An error occurred while approving the saving.';
        }
    });
};

const rejectSaving = () => {
    rejectForm.put(route('savings.reject', props.saving.id), {
        status: 'rejected'
    }, {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Saving Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Status Banner -->
                        <div class="mb-6">
                            <!-- Error Message -->
                            <div v-if="errorMessage" class="mb-4 p-4 rounded-lg bg-red-100 text-red-800">
                                {{ errorMessage }}
                            </div>
                            
                            <div class="p-4 rounded-lg" :class="statusClass(saving.status)">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg v-if="saving.status === 'pending'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else-if="saving.status === 'approved'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else-if="saving.status === 'rejected'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium capitalize">
                                            {{ saving.status }}
                                        </h3>
                                        <div class="mt-2 text-sm" v-if="saving.status === 'rejected'">
                                            <p>Reason: {{ saving.rejection_reason }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Transaction Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Details</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatAmount(saving.amount) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Transaction Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(saving.transaction_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.reference_number || 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Source</dt>
                                        <dd class="mt-1">
                                            <span :class="[sourceClass(saving.source), 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']">
                                                {{ formatSource(saving.source) }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Account Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Details</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Member Account</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ saving.member_account.account_type.name }}
                                            <span class="text-gray-500">({{ saving.member_account.account_number }})</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Member</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.member_account.user.first_name }} {{ saving.member_account.user.last_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Cooperative Account</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.cooperative_account.account_name }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Additional Information -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                                <dl class="space-y-4">
                                    <div v-if="saving.notes">
                                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.notes }}</dd>
                                    </div>
                                    <div v-if="saving.payment_proof_path">
                                        <dt class="text-sm font-medium text-gray-500">Payment Proof</dt>
                                        <dd class="mt-1">
                                            <a :href="appUrl + '/storage/' + saving.payment_proof_path" target="_blank" class="text-indigo-600 hover:text-indigo-900">View Document</a>
                                        </dd>
                                    </div>
                                    <div v-if="saving.initiated_by">
                                        <dt class="text-sm font-medium text-gray-500">Initiated By</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.initiated_by.name }} on {{ formatDate(saving.created_at) }}</dd>
                                    </div>
                                    <div v-if="saving.approved_by">
                                        <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.approved_by.name }} on {{ formatDate(saving.approved_at) }}</dd>
                                    </div>
                                    <div v-if="saving.rejected_by">
                                        <dt class="text-sm font-medium text-gray-500">Rejected By</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.rejected_by.name }} on {{ formatDate(saving.rejected_at) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-between">
                            <Link
                                :href="route('savings.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Back to Savings
                            </Link>
                            
                            <div v-if="saving.status === 'pending'" class="flex space-x-3">
                                <PrimaryButton @click="approveSaving">
                                    Approve
                                </PrimaryButton>
                                <SecondaryButton @click="showRejectModal = true">
                                    Reject
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Reject Saving
                </h2>

                <form @submit.prevent="rejectSaving" class="mt-6">
                    <div>
                        <InputLabel for="rejection_reason" value="Rejection Reason" />
                        <textarea
                            id="rejection_reason"
                            v-model="rejectForm.rejection_reason"
                            class="mt-1 block w-full rounded-md border-gray-300"
                            rows="3"
                            required
                        ></textarea>
                        <InputError :message="rejectForm.errors.rejection_reason" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="showRejectModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="rejectForm.processing">
                            Reject
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template> 
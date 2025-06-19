<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import InputLabel from '@/components/InputLabel.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import SavingsHeader from '@/components/savings/SavingsHeader.vue';
import StatusBanner from '@/components/savings/StatusBanner.vue';
import TransactionDetailsCard from '@/components/savings/TransactionDetailsCard.vue';
import AccountInfoCard from '@/components/savings/AccountInfoCard.vue';
import PaymentProofCard from '@/components/savings/PaymentProofCard.vue';
import { useSavingsFormatters } from '@/composables/useSavingsFormatters';

const props = defineProps({
    saving: {
        type: Object,
        required: true
    }
});

const page = usePage();
const appUrl = page.props.appUrl;

// Modal and form state
const showRejectModal = ref(false);
const rejectForm = useForm({
    rejection_reason: ''
});

const errorMessage = ref('');
const rejectErrorMessage = ref('');

// Action functions
const approveSaving = () => {
    errorMessage.value = '';
    router.patch(route('admin.savings.approvals.approve', props.saving.id), {
        status: 'approved'
    }, {
        preserveScroll: true,
        onError: (errors) => {
            errorMessage.value = errors.saving || 'An error occurred while approving the saving.';
        }
    });
};

const rejectSaving = () => {
    rejectErrorMessage.value = '';
    rejectForm.patch(route('admin.savings.approvals.reject', props.saving.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
        preserveScroll: true,
        onError: (errors) => {
            rejectErrorMessage.value = errors.saving || errors.rejection_reason || 'An error occurred while rejecting the saving.';
        }
    });
};

const { formatDate } = useSavingsFormatters();
</script>

<template>
    <Head title="Saving Details" />

    <AppLayout>
        <template #header>
            <SavingsHeader 
                title="Saving Details"
                :status="saving.status"
                :source="saving.source"
            />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Error Message -->
                <div v-if="errorMessage" class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ errorMessage }}
                </div>

                <!-- Status Banner -->
                <StatusBanner 
                    :status="saving.status"
                    :rejection-reason="saving.rejection_reason"
                    :approved-at="saving.approved_at"
                    :approved-by="saving.approved_by"
                />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Transaction Details Card -->
                    <div class="lg:col-span-2">
                        <TransactionDetailsCard 
                            :amount="saving.amount"
                            :transaction-date="saving.transaction_date"
                            :reference-number="saving.reference_number"
                            :source="saving.source"
                            :transaction-id="saving.id"
                            :created-at="saving.created_at"
                            :show-transaction-id="true"
                        />
                    </div>

                    <!-- Account Information Card -->
                    <AccountInfoCard 
                        :member-account="saving.member_account"
                        :cooperative-account="saving.cooperative_account"
                        :show-member-name="true"
                    />
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Payment Proof Card -->
                    <PaymentProofCard 
                        v-if="saving.payment_proof_path" 
                        :payment-proof-path="saving.payment_proof_path"
                        :app-url="appUrl" 
                    />

                    <!-- Activity & Notes Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Activity & Notes
                            </h3>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div v-if="saving.notes">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Notes</dt>
                                <dd class="text-sm text-gray-600 leading-relaxed">{{ saving.notes }}</dd>
                            </div>
                            <div v-if="saving.initiated_by">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Initiated By</dt>
                                <dd class="text-sm text-gray-900">{{ saving.initiated_by.first_name }} {{ saving.initiated_by.last_name }}</dd>
                            </div>
                            <div v-if="saving.approved_by">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Approved By</dt>
                                <dd class="text-sm text-gray-900">{{ saving.approved_by.first_name }} {{ saving.approved_by.last_name }}</dd>
                            </div>
                            <div v-if="saving.rejected_by">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Rejected By</dt>
                                <dd class="text-sm text-gray-900">{{ saving.rejected_by.first_name }} {{ saving.rejected_by.last_name }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                Last updated: {{ formatDate(saving.updated_at) }}
                            </div>
                            <div class="flex items-center space-x-3">
                                <Link
                                    :href="route('admin.savings.index')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                >
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Back to Savings
                                </Link>
                                
                                <div v-if="saving.status === 'pending'" class="flex space-x-3">
                                    <PrimaryButton @click="approveSaving" class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </PrimaryButton>
                                    <SecondaryButton @click="showRejectModal = true" class="text-red-600 border-red-300 hover:bg-red-50">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Reject Saving
                </h2>
                
                <p class="text-sm text-gray-500 text-center mb-6">
                    Please provide a reason for rejecting this saving transaction.
                </p>

                <!-- Error Message -->
                <div v-if="rejectErrorMessage" class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ rejectErrorMessage }}
                </div>

                <form @submit.prevent="rejectSaving">
                    <div>
                        <InputLabel for="rejection_reason" value="Rejection Reason" />
                        <textarea
                            id="rejection_reason"
                            v-model="rejectForm.rejection_reason"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="4"
                            placeholder="Please provide a detailed reason for rejection..."
                            required
                        ></textarea>
                        <InputError :message="rejectForm.errors.rejection_reason" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="showRejectModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            type="submit" 
                            :disabled="rejectForm.processing"
                            class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
                        >
                            <svg v-if="rejectForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Reject Saving
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
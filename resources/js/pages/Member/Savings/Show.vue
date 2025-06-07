<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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

const { formatDate } = useSavingsFormatters();
</script>

<template>
    <Head title="View Saving" />

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
                            :created-at="saving.created_at"
                        />
                    </div>

                    <!-- Account Information Card -->
                    <AccountInfoCard 
                        :member-account="saving.member_account"
                        :cooperative-account="saving.cooperative_account"
                    />
                </div>

                <!-- Payment Proof & Notes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Payment Proof Card -->
                    <PaymentProofCard 
                        v-if="saving.payment_proof_path" 
                        :payment-proof-path="saving.payment_proof_path"
                        :app-url="appUrl" 
                    />

                    <!-- Notes Card -->
                    <div v-if="saving.notes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                Notes
                            </h3>
                        </div>
                        <div class="px-6 py-5">
                            <p class="text-sm text-gray-600 leading-relaxed">{{ saving.notes }}</p>
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
                            <Link
                                :href="route('savings.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Savings
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
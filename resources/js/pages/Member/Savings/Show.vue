<template>
    <AppLayout title="View Saving">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Saving Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Status Banner -->
                        <div :class="[
                            'mb-6 p-4 rounded-md',
                            {
                                'bg-yellow-50 border border-yellow-200': saving.status === 'pending',
                                'bg-green-50 border border-green-200': saving.status === 'approved',
                                'bg-red-50 border border-red-200': saving.status === 'rejected',
                                'bg-gray-50 border border-gray-200': saving.status === 'failed'
                            }
                        ]">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg v-if="saving.status === 'pending'" class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="saving.status === 'approved'" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="saving.status === 'rejected'" class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 :class="[
                                        'text-sm font-medium',
                                        {
                                            'text-yellow-800': saving.status === 'pending',
                                            'text-green-800': saving.status === 'approved',
                                            'text-red-800': saving.status === 'rejected',
                                            'text-gray-800': saving.status === 'failed'
                                        }
                                    ]">
                                        {{ statusMessage }}
                                    </h3>
                                    <div class="mt-2 text-sm" :class="{
                                        'text-yellow-700': saving.status === 'pending',
                                        'text-green-700': saving.status === 'approved',
                                        'text-red-700': saving.status === 'rejected',
                                        'text-gray-700': saving.status === 'failed'
                                    }">
                                        <p v-if="saving.status === 'rejected'">
                                            Reason: {{ saving.rejection_reason }}
                                        </p>
                                        <p v-if="saving.status === 'approved'">
                                            Approved on {{ formatDate(saving.approved_at) }} by {{ saving.approved_by.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Transaction Details</h3>
                                <dl class="mt-4 space-y-4">
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
                                            <span :class="sourceClass(saving.source)">
                                                {{ formatSource(saving.source) }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Account Details</h3>
                                <dl class="mt-4 space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Member Account</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.member_account.account_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Cooperative Account</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ saving.cooperative_account.name }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Payment Proof -->
                        <div v-if="saving.payment_proof_path" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900">Payment Proof</h3>
                            <div class="mt-4">
                                <a :href="'/storage/' + saving.payment_proof_path" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                    </svg>
                                    View Payment Proof
                                </a>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="saving.notes" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900">Notes</h3>
                            <div class="mt-4 text-sm text-gray-600">
                                {{ saving.notes }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-end">
                            <Link
                                :href="route('savings.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Back to Savings
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

export default {
    components: {
        AppLayout,
        Link
    },

    props: {
        saving: Object
    },

    setup(props) {
        const statusMessage = computed(() => {
            const messages = {
                pending: 'This saving is pending approval',
                approved: 'This saving has been approved',
                rejected: 'This saving has been rejected',
                failed: 'This saving transaction failed'
            }
            return messages[props.saving.status] || props.saving.status
        })

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString()
        }

        const formatAmount = (amount) => {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN'
            }).format(amount)
        }

        const formatSource = (source) => {
            const sources = {
                member_gateway: 'Gateway Payment',
                member_deposit: 'Bank Deposit'
            }
            return sources[source] || source
        }

        const sourceClass = (source) => {
            const classes = {
                member_gateway: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800',
                member_deposit: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'
            }
            return classes[source] || ''
        }

        return {
            statusMessage,
            formatDate,
            formatAmount,
            formatSource,
            sourceClass
        }
    }
}
</script> 
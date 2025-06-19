<template>
    <AppLayout :title="'Withdrawal Request Details'">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Withdrawal Request Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Request Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-4">Request Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Request Date</p>
                                        <p class="font-medium">{{ formatDate(withdrawalRequest.request_date) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status</p>
                                        <span :class="statusClass(withdrawalRequest.status)">
                                            {{ formatStatus(withdrawalRequest.status) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Requested Amount</p>
                                        <p class="font-medium">{{ formatAmount(withdrawalRequest.requested_amount) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Bank Details</p>
                                        <p class="font-medium">{{ withdrawalRequest.member_bank_account_for_receipt }}</p>
                                    </div>
                                    <div v-if="withdrawalRequest.rejection_reason">
                                        <p class="text-sm text-gray-600">Rejection Reason</p>
                                        <p class="font-medium text-red-600">{{ withdrawalRequest.rejection_reason }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Member Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-4">Member Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Member Name</p>
                                        <p class="font-medium">{{ withdrawalRequest.user.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Account Number</p>
                                        <p class="font-medium">{{ withdrawalRequest.member_account.account_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Account Type</p>
                                        <p class="font-medium">{{ withdrawalRequest.member_account.account_type.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Current Balance</p>
                                        <p class="font-medium">{{ formatAmount(withdrawalRequest.member_account.balance) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Withdrawal Rules -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-4">Withdrawal Rules</h3>
                                <div class="space-y-2">
                                    <div v-for="(rule, key) in withdrawalRequest.member_account.account_type.withdrawal_rules" :key="key">
                                        <p class="text-sm">
                                            <span class="font-medium">{{ formatRuleKey(key) }}:</span>
                                            {{ rule }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Transactions -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-4">Recent Transactions</h3>
                                <div class="space-y-2">
                                    <div v-for="transaction in withdrawalRequest.member_account.savings" :key="transaction.id" 
                                         class="flex justify-between items-center text-sm">
                                        <div>
                                            <p class="font-medium">{{ transaction.status }}</p>
                                            <p class="text-gray-600">{{ formatDate(transaction.created_at) }}</p>
                                        </div>
                                        <p :class="transaction.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatAmount(transaction.amount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <Link
                                :href="route('admin.withdrawals.index')"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Back to List
                            </Link>
                            <button
                                v-if="withdrawalRequest.status === 'pending'"
                                @click="approve"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Approve
                            </button>
                            <button
                                v-if="withdrawalRequest.status === 'pending'"
                                @click="showRejectModal"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Reject
                            </button>
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

<script>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import InputLabel from '@/components/InputLabel.vue'
import InputError from '@/components/InputError.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'

export default {
    components: {
        AppLayout,
        Link,
        Modal,
        InputLabel,
        InputError,
        SecondaryButton,
        DangerButton
    },

    props: {
        withdrawalRequest: Object
    },

    setup(props) {
        const showingRejectModal = ref(false)
        const rejectForm = useForm({
            rejection_reason: ''
        })

        const approve = () => {
            if (confirm('Are you sure you want to approve this withdrawal request?')) {
                router.patch(route('admin.withdrawals.approvals.approve', props.withdrawalRequest.id), {}, {
                    preserveScroll: true
                })
            }
        }

        const showRejectModal = () => {
            showingRejectModal.value = true
        }

        const closeRejectModal = () => {
            showingRejectModal.value = false
            rejectForm.reset()
        }

        const submitReject = () => {
            rejectForm.patch(route('admin.withdrawals.approvals.reject', props.withdrawalRequest.id), {
                onSuccess: () => closeRejectModal()
            })
        }

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString()
        }

        const formatAmount = (amount) => {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN'
            }).format(amount)
        }

        const formatStatus = (status) => {
            const statuses = {
                pending: 'Pending',
                approved: 'Approved',
                rejected: 'Rejected'
            }
            return statuses[status] || status
        }

        const statusClass = (status) => {
            const classes = {
                pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
                approved: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
                rejected: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'
            }
            return classes[status] || ''
        }

        const formatRuleKey = (key) => {
            return key.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ')
        }

        return {
            showingRejectModal,
            rejectForm,
            approve,
            showRejectModal,
            closeRejectModal,
            submitReject,
            formatDate,
            formatAmount,
            formatStatus,
            statusClass,
            formatRuleKey
        }
    }
}
</script> 
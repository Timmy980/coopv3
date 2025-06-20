<template>
    <AppLayout title="Pending Approvals">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pending Savings Approvals
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initiated By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="saving in savings.data" :key="saving.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(saving.transaction_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ saving.reference_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ saving.member_account.account_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatAmount(saving.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="sourceClass(saving.source)">
                                                {{ formatSource(saving.source) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ saving.initiated_by.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link 
                                                :href="route('savings.show', saving.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </Link>
                                            <button
                                                @click="approve(saving)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                @click="showRejectModal(saving)"
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
                            <Pagination :links="savings.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showingRejectModal" @close="closeRejectModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Reject Saving
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
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import InputLabel from '@/components/InputLabel.vue'
import InputError from '@/components/InputError.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'
import Pagination from '@/components/Pagination.vue'

export default {
    components: {
        AppLayout,
        Link,
        Modal,
        InputLabel,
        InputError,
        SecondaryButton,
        DangerButton,
        Pagination
    },

    props: {
        savings: Object
    },

    setup() {
        const showingRejectModal = ref(false)
        const selectedSaving = ref(null)

        const rejectForm = useForm({
            rejection_reason: ''
        })

        const approve = (saving) => {
            if (confirm('Are you sure you want to approve this saving?')) {
                router.patch(route('savings.approve', saving.id), {}, {
                    preserveScroll: true
                })
            }
        }

        const showRejectModal = (saving) => {
            selectedSaving.value = saving
            showingRejectModal.value = true
        }

        const closeRejectModal = () => {
            showingRejectModal.value = false
            selectedSaving.value = null
            rejectForm.reset()
        }

        const submitReject = () => {
            if (selectedSaving.value) {
                rejectForm.patch(route('savings.reject', selectedSaving.value.id), {
                    onSuccess: () => closeRejectModal()
                })
            }
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

        const formatSource = (source) => {
            const sources = {
                member_gateway: 'Gateway Payment',
                member_deposit: 'Bank Deposit',
                admin_single: 'Admin Entry',
                admin_bulk: 'Bulk Upload'
            }
            return sources[source] || source
        }

        const sourceClass = (source) => {
            const classes = {
                member_gateway: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800',
                member_deposit: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
                admin_single: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800',
                admin_bulk: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800'
            }
            return classes[source] || ''
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
            formatSource,
            sourceClass
        }
    }
}
</script> 
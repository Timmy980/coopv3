<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Savings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex space-x-4">
                        <PrimaryButton @click="showGatewayModal = true">
                            Pay via Gateway
                        </PrimaryButton>
                        <SecondaryButton @click="showDepositModal = true">
                            Submit Bank Deposit
                        </SecondaryButton>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <form @submit.prevent="filter" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source</label>
                            <select v-model="filters.source" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">All Sources</option>
                                <option value="member_gateway">Gateway Payment</option>
                                <option value="member_deposit">Bank Deposit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" v-model="filters.date_from" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" v-model="filters.date_to" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>

                        <div class="md:col-span-4 flex justify-end space-x-3">
                            <SecondaryButton type="button" @click="resetFilters">Reset</SecondaryButton>
                            <PrimaryButton type="submit">Apply Filters</PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Savings Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                                            <span :class="statusClass(saving.status)">
                                                {{ saving.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('savings.show', saving.id)" class="text-indigo-600 hover:text-indigo-900">
                                                View
                                            </Link>
                                            <button
                                                v-if="saving.status === 'pending' && saving.source === 'member_deposit'"
                                                @click="showUploadProofModal(saving)"
                                                class="ml-3 text-blue-600 hover:text-blue-900"
                                            >
                                                Upload Proof
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

        <!-- Gateway Payment Modal -->
        <Modal :show="showGatewayModal" @close="closeGatewayModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Pay via Gateway
                </h2>

                <form @submit.prevent="submitGatewayPayment" class="mt-6">
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <InputLabel for="member_account" value="Member Account" />
                            <select v-model="gatewayForm.member_account_id" id="member_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in memberAccounts" :key="account.id" :value="account.id">
                                    {{ account.account_number }}
                                </option>
                            </select>
                            <InputError :message="gatewayForm.errors.member_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="cooperative_account" value="Cooperative Account" />
                            <select v-model="gatewayForm.cooperative_account_id" id="cooperative_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in cooperativeAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }}
                                </option>
                            </select>
                            <InputError :message="gatewayForm.errors.cooperative_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="amount" value="Amount" />
                            <TextInput
                                id="amount"
                                type="number"
                                step="0.01"
                                v-model="gatewayForm.amount"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="gatewayForm.errors.amount" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeGatewayModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="gatewayForm.processing">
                            Proceed to Payment
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Bank Deposit Modal -->
        <Modal :show="showDepositModal" @close="closeDepositModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Submit Bank Deposit
                </h2>

                <form @submit.prevent="submitBankDeposit" class="mt-6">
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <InputLabel for="deposit_member_account" value="Member Account" />
                            <select v-model="depositForm.member_account_id" id="deposit_member_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in memberAccounts" :key="account.id" :value="account.id">
                                    {{ account.account_type.name +' - '+ account.account_number }}
                                </option>
                            </select>
                            <InputError :message="depositForm.errors.member_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="deposit_cooperative_account" value="Cooperative Account" />
                            <select v-model="depositForm.cooperative_account_id" id="deposit_cooperative_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in cooperativeAccounts" :key="account.id" :value="account.id">
                                    {{ account.account_name }}
                                </option>
                            </select>
                            <InputError :message="depositForm.errors.cooperative_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="deposit_amount" value="Amount" />
                            <TextInput
                                id="deposit_amount"
                                type="number"
                                step="0.01"
                                v-model="depositForm.amount"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="depositForm.errors.amount" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="transaction_date" value="Transaction Date" />
                            <TextInput
                                id="transaction_date"
                                type="date"
                                v-model="depositForm.transaction_date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="depositForm.errors.transaction_date" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="reference_number" value="Reference Number" />
                            <TextInput
                                id="reference_number"
                                type="text"
                                v-model="depositForm.reference_number"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="depositForm.errors.reference_number" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="payment_proof" value="Payment Proof" />
                            <input
                                type="file"
                                id="payment_proof"
                                ref="payment_proof"
                                class="mt-1 block w-full"
                                @change="depositForm.payment_proof = $event.target.files[0]"
                                accept="image/*,.pdf"
                            />
                            <InputError :message="depositForm.errors.payment_proof" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="notes" value="Notes" />
                            <textarea
                                id="notes"
                                v-model="depositForm.notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300"
                            ></textarea>
                            <InputError :message="depositForm.errors.notes" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeDepositModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="depositForm.processing">
                            Submit Deposit
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Upload Proof Modal -->
        <Modal :show="showProofModal" @close="closeProofModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Upload Payment Proof
                </h2>

                <form @submit.prevent="submitProof" class="mt-6">
                    <div>
                        <InputLabel for="new_payment_proof" value="Payment Proof" />
                        <input
                            type="file"
                            id="new_payment_proof"
                            ref="new_payment_proof"
                            class="mt-1 block w-full"
                            @change="proofForm.payment_proof = $event.target.files[0]"
                            accept="image/*,.pdf"
                            required
                        />
                        <InputError :message="proofForm.errors.payment_proof" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeProofModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="proofForm.processing">
                            Upload Proof
                        </PrimaryButton>
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
import TextInput from '@/components/TextInput.vue'
import InputError from '@/components/InputError.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import Pagination from '@/components/Pagination.vue'

export default {
    components: {
        AppLayout,
        Link,
        Modal,
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
        SecondaryButton,
        Pagination
    },

    props: {
        savings: Object,
        filters: Object,
        memberAccounts: Array,
        cooperativeAccounts: Array
    },

    setup(props) {
        const showGatewayModal = ref(false)
        const showDepositModal = ref(false)
        const showProofModal = ref(false)
        const selectedSaving = ref(null)

        const filters = ref({
            status: props.filters?.status || '',
            source: props.filters?.source || '',
            date_from: props.filters?.date_from || '',
            date_to: props.filters?.date_to || ''
        })

        const gatewayForm = useForm({
            member_account_id: '',
            cooperative_account_id: '',
            amount: ''
        })

        const depositForm = useForm({
            member_account_id: '',
            cooperative_account_id: '',
            amount: '',
            transaction_date: '',
            reference_number: '',
            payment_proof: null,
            notes: ''
        })

        const proofForm = useForm({
            payment_proof: null
        })

        const filter = () => {
            router.get(route('savings.index'), filters.value, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const resetFilters = () => {
            filters.value = {
                status: '',
                source: '',
                date_from: '',
                date_to: ''
            }
            filter()
        }

        const closeGatewayModal = () => {
            showGatewayModal.value = false
            gatewayForm.reset()
        }

        const submitGatewayPayment = () => {
            gatewayForm.post(route('savings.gateway.initiate'), {
                onSuccess: (response) => {
                    closeGatewayModal()
                    // Redirect to payment gateway
                    window.location.href = response.authorization_url
                }
            })
        }

        const closeDepositModal = () => {
            showDepositModal.value = false
            depositForm.reset()
        }

        const submitBankDeposit = () => {
            depositForm.post(route('savings.deposit.store'), {
                onSuccess: () => closeDepositModal()
            })
        }

        const showUploadProofModal = (saving) => {
            selectedSaving.value = saving
            showProofModal.value = true
        }

        const closeProofModal = () => {
            showProofModal.value = false
            selectedSaving.value = null
            proofForm.reset()
        }

        const submitProof = () => {
            if (selectedSaving.value) {
                proofForm.post(route('savings.deposit.proof', selectedSaving.value.id), {
                    onSuccess: () => closeProofModal()
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

        const statusClass = (status) => {
            const classes = {
                pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
                approved: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
                rejected: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
                failed: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800'
            }
            return classes[status] || ''
        }

        return {
            filters,
            showGatewayModal,
            showDepositModal,
            showProofModal,
            gatewayForm,
            depositForm,
            proofForm,
            filter,
            resetFilters,
            closeGatewayModal,
            submitGatewayPayment,
            closeDepositModal,
            submitBankDeposit,
            showUploadProofModal,
            closeProofModal,
            submitProof,
            formatDate,
            formatAmount,
            formatSource,
            sourceClass,
            statusClass
        }
    }
}
</script> 
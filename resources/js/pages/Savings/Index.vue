<template>
    <AppLayout title="Savings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Savings Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                <option value="admin_single">Admin Entry</option>
                                <option value="admin_bulk">Bulk Upload</option>
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

                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex justify-between items-center">
                        <div class="space-x-3">
                            <PrimaryButton @click="showSingleEntryModal = true">
                                New Single Entry
                            </PrimaryButton>
                            <Link :href="route('savings.bulk.form')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Bulk Upload
                            </Link>
                        </div>
                        <Link :href="route('savings.pending')" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500">
                            Pending Approval
                        </Link>
                    </div>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
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

        <!-- Single Entry Modal -->
        <Modal :show="showSingleEntryModal" @close="showSingleEntryModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    New Single Saving Entry
                </h2>

                <form @submit.prevent="submitSingleEntry" class="mt-6">
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <InputLabel for="member_account" value="Member Account" />
                            <select v-model="form.member_account_id" id="member_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in memberAccounts" :key="account.id" :value="account.id">
                                    {{ account.user.first_name }} {{ account.user.last_name }} - {{ account.account_type.name }} ({{ account.account_number }})
                                </option>
                            </select>
                            <InputError :message="form.errors.member_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="cooperative_account" value="Cooperative Account" />
                            <select v-model="form.cooperative_account_id" id="cooperative_account" class="mt-1 block w-full rounded-md border-gray-300">
                                <option v-for="account in cooperativeAccounts" :key="account.id" :value="account.id">
                                    {{ account.account_name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.cooperative_account_id" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="amount" value="Amount" />
                            <TextInput
                                id="amount"
                                type="number"
                                step="0.01"
                                v-model="form.amount"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <InputLabel for="transaction_date" value="Transaction Date" />
                            <TextInput
                                id="transaction_date"
                                type="date"
                                v-model="form.transaction_date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.transaction_date" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="reference_number" value="Reference Number" />
                            <TextInput
                                id="reference_number"
                                type="text"
                                v-model="form.reference_number"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.reference_number" class="mt-2" />
                        </div>

                        <div class="sm:col-span-6">
                            <InputLabel for="notes" value="Notes" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300"
                            ></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="showSingleEntryModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            Create Entry
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>

<script>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
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
        const showSingleEntryModal = ref(false)
        const filters = ref({
            status: props.filters?.status || '',
            source: props.filters?.source || '',
            date_from: props.filters?.date_from || '',
            date_to: props.filters?.date_to || ''
        })

        const form = useForm({
            member_account_id: '',
            cooperative_account_id: '',
            amount: '',
            transaction_date: '',
            reference_number: '',
            notes: ''
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

        const submitSingleEntry = () => {
            form.post(route('savings.single.store'), {
                onSuccess: () => {
                    showSingleEntryModal.value = false
                    form.reset()
                }
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
            showSingleEntryModal,
            filters,
            form,
            filter,
            resetFilters,
            submitSingleEntry,
            formatDate,
            formatAmount,
            formatSource,
            sourceClass,
            statusClass
        }
    }
}
</script> 
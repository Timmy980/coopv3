<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import lodash from 'lodash';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
    accounts: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    accountTypes: {
        type: Object,
        required: true
    },
    statuses: {
        type: Object,
        required: true
    }
});

const search = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');

// Debounced search function
const performSearch = () => {
    router.get(
        route('cooperative_accounts.index'),
        { 
            search: search.value,
            status: statusFilter.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedSearch = lodash.debounce(performSearch, 300);

watch([search, statusFilter], () => {
    debouncedSearch();
});

const createForm = useForm({
    account_name: '',
    account_type: '',
    bank_name: '',
    account_number: '',
    balance: ''
});

const editForm = useForm({
    id: '',
    account_name: '',
    account_type: '',
    bank_name: '',
    account_number: '',
    balance: ''
});

const showCreateModal = ref(false);
const showEditModal = ref(false);

const submitCreate = () => {
    createForm.post(route('cooperative_accounts.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const openEdit = (account) => {
    editForm.id = account.id;
    editForm.account_name = account.account_name;
    editForm.account_type = account.account_type;
    editForm.bank_name = account.bank_name || '';
    editForm.account_number = account.account_number;
    editForm.balance = account.balance;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('cooperative_accounts.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAccount = (account) => {
    if (confirm(`Are you sure you want to delete "${account.account_name}"? This action cannot be undone.`)) {
        router.delete(route('cooperative_accounts.destroy', account.id));
    }
};

const toggleStatus = (account) => {
    const action = account.status === 'active' ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} "${account.account_name}"?`)) {
        router.patch(route('cooperative_accounts.toggle_status', account.id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        case 'suspended':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount || 0);
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    performSearch();
};
</script>

<template>
    <Head title="Cooperative Accounts Management" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cooperative Accounts Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <h3 class="text-lg font-semibold">Cooperative Accounts</h3>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <PrimaryButton @click="showCreateModal = true">Create Account</PrimaryButton>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="search" value="Search" />
                            <TextInput
                                id="search"
                                type="text"
                                :modelValue="search"
                                @update:modelValue="(value) => search = value"
                                class="w-full"
                                placeholder="Search by name, account number, or bank..."
                            />
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="statusFilter"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">All Statuses</option>
                                <option v-for="(label, status) in statuses" :key="status" :value="status">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <SecondaryButton @click="clearFilters" class="w-full">Clear Filters</SecondaryButton>
                        </div>
                    </div>

                    <!-- Accounts List -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-4">
                            <div v-for="account in accounts.data" :key="account.id" class="bg-white p-4 rounded shadow-sm">
                                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                                    <div class="flex-grow">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                            <h4 class="font-semibold text-lg text-gray-800">{{ account.account_name }}</h4>
                                            <div class="flex items-center gap-4 mt-2 sm:mt-0">
                                                <span class="text-sm px-2 py-1 rounded" :class="getStatusClass(account.status)">
                                                    {{ statuses[account.status] }}
                                                </span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ accountTypes[account.account_type] }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                                            <div>
                                                <span class="font-medium">Account Number:</span>
                                                <span class="ml-1">{{ account.account_number }}</span>
                                            </div>
                                            <div v-if="account.bank_name">
                                                <span class="font-medium">Bank:</span>
                                                <span class="ml-1">{{ account.bank_name }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Balance:</span>
                                                <span class="ml-1 font-semibold text-green-600">{{ formatCurrency(account.balance) }}</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2 text-xs">
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.savings_count }} Savings</span>
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.disbursed_loans_count }} Loans</span>
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.received_loan_repayments_count }} Repayments</span>
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.disbursed_withdrawals_count }} Withdrawals</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <Link :href="route('cooperative_accounts.show', account.id)">
                                            <SecondaryButton>View</SecondaryButton>
                                        </Link>
                                        <PrimaryButton @click="openEdit(account)">Edit</PrimaryButton>
                                        <SecondaryButton 
                                            @click="toggleStatus(account)"
                                            :class="account.status === 'active' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'"
                                        >
                                            {{ account.status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </SecondaryButton>
                                        <DangerButton 
                                            @click="deleteAccount(account)"
                                            :disabled="account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0"
                                            :title="(account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0) ? 'Cannot delete account with existing transactions' : ''"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6" v-if="accounts.data.length > 0">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Showing {{ accounts.from }} - {{ accounts.to }} of {{ accounts.total }} accounts
                                </div>
                                <div class="flex gap-1">
                                    <Link
                                        v-for="(link, i) in accounts.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-1 rounded text-sm"
                                        :class="{ 
                                            'bg-blue-500 text-white': link.active,
                                            'text-gray-600 hover:bg-gray-100': !link.active && link.url,
                                            'text-gray-400': !link.url 
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else-if="search || statusFilter" class="text-center py-4 text-gray-500">
                            No accounts found matching your filters
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            No cooperative accounts available
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Account Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Create New Cooperative Account</h2>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="createAccountName" value="Account Name" />
                            <TextInput
                                id="createAccountName"
                                type="text"
                                v-model="createForm.account_name"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="createForm.errors.account_name" class="text-red-600 text-sm mt-1">{{ createForm.errors.account_name }}</div>
                        </div>
                        <div>
                            <InputLabel for="createAccountType" value="Account Type" />
                            <select
                                id="createAccountType"
                                v-model="createForm.account_type"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">Select Account Type</option>
                                <option v-for="(label, type) in accountTypes" :key="type" :value="type">
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="createForm.errors.account_type" class="text-red-600 text-sm mt-1">{{ createForm.errors.account_type }}</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="createBankName" value="Bank Name (Optional)" />
                            <TextInput
                                id="createBankName"
                                type="text"
                                v-model="createForm.bank_name"
                                class="mt-1 block w-full"
                            />
                            <div v-if="createForm.errors.bank_name" class="text-red-600 text-sm mt-1">{{ createForm.errors.bank_name }}</div>
                        </div>
                        <div>
                            <InputLabel for="createAccountNumber" value="Account Number" />
                            <TextInput
                                id="createAccountNumber"
                                type="text"
                                v-model="createForm.account_number"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="createForm.errors.account_number" class="text-red-600 text-sm mt-1">{{ createForm.errors.account_number }}</div>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="createBalance" value="Initial Balance (Optional)" />
                        <TextInput
                            id="createBalance"
                            type="number"
                            step="0.01"
                            min="0"
                            v-model="createForm.balance"
                            class="mt-1 block w-full"
                        />
                        <div v-if="createForm.errors.balance" class="text-red-600 text-sm mt-1">{{ createForm.errors.balance }}</div>
                    </div>
                    
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="createForm.processing">Create Account</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Account Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Edit Cooperative Account</h2>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="editAccountName" value="Account Name" />
                            <TextInput
                                id="editAccountName"
                                type="text"
                                v-model="editForm.account_name"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="editForm.errors.account_name" class="text-red-600 text-sm mt-1">{{ editForm.errors.account_name }}</div>
                        </div>
                        <div>
                            <InputLabel for="editAccountType" value="Account Type" />
                            <select
                                id="editAccountType"
                                v-model="editForm.account_type"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">Select Account Type</option>
                                <option v-for="(label, type) in accountTypes" :key="type" :value="type">
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="editForm.errors.account_type" class="text-red-600 text-sm mt-1">{{ editForm.errors.account_type }}</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="editBankName" value="Bank Name (Optional)" />
                            <TextInput
                                id="editBankName"
                                type="text"
                                v-model="editForm.bank_name"
                                class="mt-1 block w-full"
                            />
                            <div v-if="editForm.errors.bank_name" class="text-red-600 text-sm mt-1">{{ editForm.errors.bank_name }}</div>
                        </div>
                        <div>
                            <InputLabel for="editAccountNumber" value="Account Number" />
                            <TextInput
                                id="editAccountNumber"
                                type="text"
                                v-model="editForm.account_number"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="editForm.errors.account_number" class="text-red-600 text-sm mt-1">{{ editForm.errors.account_number }}</div>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="editBalance" value="Balance" />
                        <TextInput
                            id="editBalance"
                            type="number"
                            step="0.01"
                            min="0"
                            v-model="editForm.balance"
                            class="mt-1 block w-full"
                        />
                        <div v-if="editForm.errors.balance" class="text-red-600 text-sm mt-1">{{ editForm.errors.balance }}</div>
                    </div>
                    
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="editForm.processing">Update Account</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
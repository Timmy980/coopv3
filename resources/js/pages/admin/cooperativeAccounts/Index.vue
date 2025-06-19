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
const showMobileFilters = ref(false);

// Debounced search function
const performSearch = () => {
    router.get(
        route('admin.accounts.cooperative.index'),
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
    createForm.post(route('admin.accounts.cooperative.store'), {
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
    editForm.put(route('admin.accounts.cooperative.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAccount = (account) => {
    if (confirm(`Are you sure you want to delete "${account.account_name}"? This action cannot be undone.`)) {
        router.delete(route('admin.accounts.cooperative.destroy', account.id));
    }
};

const toggleStatus = (account) => {
    const action = account.status === 'active' ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} "${account.account_name}"?`)) {
        router.patch(route('admin.accounts.cooperative.toggle-status', account.id));
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
    showMobileFilters.value = false;
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || statusFilter.value;
});
</script>

<template>
    <Head title="Cooperative Accounts Management" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cooperative Accounts Management
                </h2>
                <div class="flex items-center gap-2">
                    <!-- Mobile filter toggle -->
                    <SecondaryButton 
                        @click="showMobileFilters = !showMobileFilters"
                        class="sm:hidden"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                        </svg>
                        Filters
                        <span v-if="hasActiveFilters" class="ml-1 bg-blue-500 text-white text-xs rounded-full px-2 py-0.5">
                            {{ (search ? 1 : 0) + (statusFilter ? 1 : 0) }}
                        </span>
                    </SecondaryButton>
                    <PrimaryButton @click="showCreateModal = true" class="text-sm">
                        <span class="hidden sm:inline">Create Account</span>
                        <span class="sm:hidden">Create</span>
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-8 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Mobile Filters Dropdown -->
                    <div v-if="showMobileFilters" class="sm:hidden border-b border-gray-200 p-4 space-y-4">
                        <div>
                            <InputLabel for="mobile-search" value="Search" />
                            <TextInput
                                id="mobile-search"
                                type="text"
                                :modelValue="search"
                                @update:modelValue="(value) => search = value"
                                class="w-full"
                                placeholder="Search accounts..."
                            />
                        </div>
                        <div>
                            <InputLabel for="mobile-status" value="Status" />
                            <select
                                id="mobile-status"
                                v-model="statusFilter"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">All Statuses</option>
                                <option v-for="(label, status) in statuses" :key="status" :value="status">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <SecondaryButton @click="clearFilters" class="flex-1">Clear</SecondaryButton>
                            <PrimaryButton @click="showMobileFilters = false" class="flex-1">Apply</PrimaryButton>
                        </div>
                    </div>

                    <!-- Desktop Filters -->
                    <div class="hidden sm:block p-6 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    </div>

                    <!-- Accounts List -->
                    <div class="p-4 sm:p-6">
                        <div class="space-y-4">
                            <!-- Mobile Card Layout -->
                            <div v-for="account in accounts.data" :key="account.id" class="bg-gray-50 rounded-lg p-4 sm:hidden">
                                <div class="space-y-3">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-lg text-gray-800 truncate">{{ account.account_name }}</h4>
                                            <p class="text-sm text-gray-600">{{ account.account_number }}</p>
                                        </div>
                                        <div class="flex flex-col items-end gap-1 ml-2">
                                            <span class="text-xs px-2 py-1 rounded whitespace-nowrap" :class="getStatusClass(account.status)">
                                                {{ statuses[account.status] }}
                                            </span>
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded whitespace-nowrap">
                                                {{ accountTypes[account.account_type] }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-2 text-sm">
                                        <div v-if="account.bank_name" class="flex justify-between">
                                            <span class="text-gray-600">Bank:</span>
                                            <span class="font-medium">{{ account.bank_name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Balance:</span>
                                            <span class="font-semibold text-green-600">{{ formatCurrency(account.balance) }}</span>
                                        </div>
                                    </div>

                                    <!-- Transaction Stats -->
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-white px-2 py-1 rounded text-center">
                                            <div class="font-medium">{{ account.savings_count }}</div>
                                            <div class="text-gray-600">Savings</div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded text-center">
                                            <div class="font-medium">{{ account.disbursed_loans_count }}</div>
                                            <div class="text-gray-600">Loans</div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded text-center">
                                            <div class="font-medium">{{ account.received_loan_repayments_count }}</div>
                                            <div class="text-gray-600">Repayments</div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded text-center">
                                            <div class="font-medium">{{ account.disbursed_withdrawals_count }}</div>
                                            <div class="text-gray-600">Withdrawals</div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="grid grid-cols-2 gap-2 pt-2">
                                        <Link :href="route('admin.accounts.cooperative.show', account.id)" class="w-full">
                                            <SecondaryButton class="w-full text-xs">View</SecondaryButton>
                                        </Link>
                                        <PrimaryButton @click="openEdit(account)" class="w-full text-xs">Edit</PrimaryButton>
                                        <SecondaryButton 
                                            @click="toggleStatus(account)"
                                            :class="account.status === 'active' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'"
                                            class="w-full text-xs"
                                        >
                                            {{ account.status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </SecondaryButton>
                                        <DangerButton 
                                            @click="deleteAccount(account)"
                                            :disabled="account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0"
                                            :title="(account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0) ? 'Cannot delete account with existing transactions' : ''"
                                            class="w-full text-xs"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop/Tablet Card Layout -->
                            <div v-for="account in accounts.data" :key="account.id" class="hidden sm:block bg-gray-50 rounded-lg p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="flex-grow space-y-3">
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <h4 class="font-semibold text-lg text-gray-800">{{ account.account_name }}</h4>
                                            <div class="flex items-center gap-4">
                                                <span class="text-sm px-2 py-1 rounded" :class="getStatusClass(account.status)">
                                                    {{ statuses[account.status] }}
                                                </span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ accountTypes[account.account_type] }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Details Grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 text-sm text-gray-600">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800">Account Number</span>
                                                <span>{{ account.account_number }}</span>
                                            </div>
                                            <div v-if="account.bank_name" class="flex flex-col">
                                                <span class="font-medium text-gray-800">Bank</span>
                                                <span>{{ account.bank_name }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800">Balance</span>
                                                <span class="font-semibold text-green-600">{{ formatCurrency(account.balance) }}</span>
                                            </div>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="bg-white px-2 py-1 rounded text-xs">{{ account.savings_count }} Savings</span>
                                                <span class="bg-white px-2 py-1 rounded text-xs">{{ account.disbursed_loans_count }} Loans</span>
                                                <span class="bg-white px-2 py-1 rounded text-xs">{{ account.received_loan_repayments_count }} Repay.</span>
                                                <span class="bg-white px-2 py-1 rounded text-xs">{{ account.disbursed_withdrawals_count }} Withdrw.</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-2 lg:flex-col lg:w-auto">
                                        <Link :href="route('admin.accounts.cooperative.show', account.id)">
                                            <SecondaryButton class="text-sm">View</SecondaryButton>
                                        </Link>
                                        <PrimaryButton @click="openEdit(account)" class="text-sm">Edit</PrimaryButton>
                                        <SecondaryButton 
                                            @click="toggleStatus(account)"
                                            :class="account.status === 'active' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'"
                                            class="text-sm"
                                        >
                                            {{ account.status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </SecondaryButton>
                                        <DangerButton 
                                            @click="deleteAccount(account)"
                                            :disabled="account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0"
                                            :title="(account.savings_count > 0 || account.disbursed_loans_count > 0 || account.received_loan_repayments_count > 0 || account.disbursed_withdrawals_count > 0) ? 'Cannot delete account with existing transactions' : ''"
                                            class="text-sm"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6" v-if="accounts.data.length > 0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="text-sm text-gray-600 text-center sm:text-left">
                                    Showing {{ accounts.from }} - {{ accounts.to }} of {{ accounts.total }} accounts
                                </div>
                                <div class="flex flex-wrap justify-center gap-1">
                                    <Link
                                        v-for="(link, i) in accounts.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-2 rounded text-sm min-w-[40px] text-center"
                                        :class="{ 
                                            'bg-blue-500 text-white': link.active,
                                            'text-gray-600 hover:bg-gray-100 bg-white': !link.active && link.url,
                                            'text-gray-400 bg-gray-100': !link.url 
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty States -->
                        <div v-else-if="search || statusFilter" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No accounts found</h3>
                            <p class="mt-1 text-sm text-gray-500">No accounts matching your current filters</p>
                            <div class="mt-6">
                                <SecondaryButton @click="clearFilters">Clear all filters</SecondaryButton>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No cooperative accounts</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new cooperative account</p>
                            <div class="mt-6">
                                <PrimaryButton @click="showCreateModal = true">Create Account</PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Account Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-4 sm:p-6 max-h-screen overflow-y-auto">
                <h2 class="text-lg font-medium mb-4">Create New Cooperative Account</h2>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
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
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2 pt-4">
                        <SecondaryButton @click="showCreateModal = false" class="order-2 sm:order-1">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="createForm.processing" class="order-1 sm:order-2">
                            {{ createForm.processing ? 'Creating...' : 'Create Account' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Account Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-4 sm:p-6 max-h-screen overflow-y-auto">
                <h2 class="text-lg font-medium mb-4">Edit Cooperative Account</h2>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
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
                    </div>
                    
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="editForm.processing">Update Account</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
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
    users: {
        type: Array,
        required: true
    },
    statuses: {
        type: Object,
        required: true
    }
});

const search = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const accountTypeFilter = ref(props.filters?.account_type ?? '');

// Debounced search function
const performSearch = () => {
    router.get(
        route('admin.accounts.members.index'),
        {
            search: search.value,
            status: statusFilter.value,
            account_type: accountTypeFilter.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedSearch = lodash.debounce(performSearch, 300);

watch([search, statusFilter, accountTypeFilter], () => {
    debouncedSearch();
});

const createForm = useForm({
    user_id: '',
    account_type_id: '',
    balance: ''
});

const editForm = useForm({
    id: '',
    balance: '',
    status: ''
});

const showCreateModal = ref(false);
const showEditModal = ref(false);

const submitCreate = () => {
    createForm.post(route('admin.accounts.members.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const openEdit = (account) => {
    editForm.id = account.id;
    editForm.balance = account.balance;
    editForm.status = account.status;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.accounts.members.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAccount = (account) => {
    const memberName = `${account.user.first_name} ${account.user.last_name}`;
    if (confirm(`Are you sure you want to delete ${memberName}'s ${account.account_type.name} account? This action cannot be undone.`)) {
        router.delete(route('admin.accounts.members.destroy', account.id));
    }
};

const toggleStatus = (account) => {
    const action = account.status === 'active' ? 'deactivate' : 'activate';
    const memberName = `${account.user.first_name} ${account.user.last_name}`;
    if (confirm(`Are you sure you want to ${action} ${memberName}'s ${account.account_type.name} account?`)) {
        router.patch(route('admin.accounts.members.toggle-status', account.id));
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
    accountTypeFilter.value = '';
    performSearch();
};

const getUserFullName = (user) => {
    return `${user.first_name} ${user.last_name}`;
};

const getUserOptions = computed(() => {
    return props.users.map(user => ({
        value: user.id,
        label: `${user.first_name} ${user.last_name} (${user.email})`
    }));
});
</script>

<template>
    <Head title="Member Accounts Management" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Member Accounts Management
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <h3 class="text-lg font-semibold">Member Accounts</h3>
                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <PrimaryButton @click="showCreateModal = true" class="w-full justify-center">Create Account</PrimaryButton>
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <InputLabel for="search" value="Search" class="sr-only sm:not-sr-only" />
                            <TextInput
                                id="search"
                                type="text"
                                :modelValue="search"
                                @update:modelValue="(value) => search = value"
                                class="w-full"
                                placeholder="Search by member, account number..."
                            />
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" class="sr-only sm:not-sr-only" />
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
                        <div>
                            <InputLabel for="account_type" value="Account Type" class="sr-only sm:not-sr-only" />
                            <select
                                id="account_type"
                                v-model="accountTypeFilter"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">All Types</option>
                                <option v-for="(name, id) in accountTypes" :key="id" :value="id">
                                    {{ name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <SecondaryButton @click="clearFilters" class="w-full justify-center">Clear Filters</SecondaryButton>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-4">
                            <div v-for="account in accounts.data" :key="account.id" class="bg-white p-4 rounded shadow-sm">
                                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                                    <div class="flex-grow w-full">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                            <h4 class="font-semibold text-lg text-gray-800">
                                                {{ getUserFullName(account.user) }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                                                <span class="text-sm px-2 py-1 rounded" :class="getStatusClass(account.status)">
                                                    {{ statuses[account.status] }}
                                                </span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ account.account_type.name }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-2 gap-x-4 text-sm text-gray-600">
                                            <div>
                                                <span class="font-medium">Account Number:</span>
                                                <span class="ml-1 break-all">{{ account.account_number }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Email:</span>
                                                <span class="ml-1 break-all">{{ account.user.email }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Balance:</span>
                                                <span class="ml-1 font-semibold text-green-600">{{ formatCurrency(account.balance) }}</span>
                                            </div>
                                            <div class="md:col-span-2 lg:col-span-1 flex flex-wrap gap-2 text-xs mt-2 md:mt-0">
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.savings_count }} Savings</span>
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ account.withdrawal_requests_count }} Withdrawals</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-2 w-full lg:w-auto mt-4 lg:mt-0">
                                        <Link :href="route('admin.accounts.members.show', account.id)" class="w-full sm:w-auto">
                                            <SecondaryButton class="w-full justify-center">View</SecondaryButton>
                                        </Link>
                                        <PrimaryButton @click="openEdit(account)" class="w-full sm:w-auto justify-center">Edit</PrimaryButton>
                                        <SecondaryButton
                                            @click="toggleStatus(account)"
                                            :class="[account.status === 'active' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white', 'w-full sm:w-auto justify-center']"
                                        >
                                            {{ account.status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </SecondaryButton>
                                        <DangerButton
                                            @click="deleteAccount(account)"
                                            :disabled="account.savings_count > 0 || account.withdrawal_requests_count > 0"
                                            :title="(account.savings_count > 0 || account.withdrawal_requests_count > 0) ? 'Cannot delete account with existing transactions' : ''"
                                            class="w-full sm:w-auto justify-center"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6" v-if="accounts.data.length > 0">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="text-sm text-gray-600">
                                    Showing {{ accounts.from }} - {{ accounts.to }} of {{ accounts.total }} accounts
                                </div>
                                <div class="flex flex-wrap justify-center gap-1">
                                    <Link
                                        v-for="(link, i) in accounts.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-1 rounded text-sm"
                                        :class="{
                                            'bg-blue-500 text-white': link.active,
                                            'text-gray-600 hover:bg-gray-100': !link.active && link.url,
                                            'text-gray-400 cursor-not-allowed': !link.url
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else-if="search || statusFilter || accountTypeFilter" class="text-center py-4 text-gray-500">
                            No member accounts found matching your filters
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            No member accounts available
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Create New Member Account</h2>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="createUserId" value="Member" />
                        <select
                            id="createUserId"
                            v-model="createForm.user_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option value="">Select Member</option>
                            <option v-for="option in getUserOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <div v-if="createForm.errors.user_id" class="text-red-600 text-sm mt-1">{{ createForm.errors.user_id }}</div>
                    </div>

                    <div>
                        <InputLabel for="createAccountTypeId" value="Account Type" />
                        <select
                            id="createAccountTypeId"
                            v-model="createForm.account_type_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option value="">Select Account Type</option>
                            <option v-for="(name, id) in accountTypes" :key="id" :value="id">
                                {{ name }}
                            </option>
                        </select>
                        <div v-if="createForm.errors.account_type_id" class="text-red-600 text-sm mt-1">{{ createForm.errors.account_type_id }}</div>
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
                            placeholder="0.00"
                        />
                        <div v-if="createForm.errors.balance" class="text-red-600 text-sm mt-1">{{ createForm.errors.balance }}</div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="createForm.processing">Create Account</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Edit Member Account</h2>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel for="editBalance" value="Balance" />
                        <TextInput
                            id="editBalance"
                            type="number"
                            step="0.01"
                            min="0"
                            v-model="editForm.balance"
                            required
                            class="mt-1 block w-full"
                        />
                        <div v-if="editForm.errors.balance" class="text-red-600 text-sm mt-1">{{ editForm.errors.balance }}</div>
                    </div>

                    <div>
                        <InputLabel for="editStatus" value="Status" />
                        <select
                            id="editStatus"
                            v-model="editForm.status"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option v-for="(label, status) in statuses" :key="status" :value="status">
                                {{ label }}
                            </option>
                        </select>
                        <div v-if="editForm.errors.status" class="text-red-600 text-sm mt-1">{{ editForm.errors.status }}</div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="editForm.processing">Update Account</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
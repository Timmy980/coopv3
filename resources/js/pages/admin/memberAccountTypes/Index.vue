<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import lodash from 'lodash';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
    accountTypes: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const search = ref(props.filters?.search ?? '');

// Debounced search function
const performSearch = () => {
    router.get(
        route('admin.accounts.types.index'),
        { search: search.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedSearch = lodash.debounce(performSearch, 300);

watch(search, () => {
    debouncedSearch();
});

const createForm = useForm({
    name: '',
    description: '',
    interest_rate: '',
    withdrawal_rules: {
        max_percentage_per_withdrawal: '',
        max_withdrawals_per_year: '',
        penalty_before_months: '',
        penalty_percentage: '',
        minimum_balance_required: ''
    }
});

const editForm = useForm({
    id: '',
    name: '',
    description: '',
    interest_rate: '',
    withdrawal_rules: {
        max_percentage_per_withdrawal: '',
        max_withdrawals_per_year: '',
        penalty_before_months: '',
        penalty_percentage: '',
        minimum_balance_required: ''
    }
});

const showCreateModal = ref(false);
const showEditModal = ref(false);

const submitCreate = () => {
    createForm.post(route('admin.accounts.types.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const openEdit = (accountType) => {
    editForm.id = accountType.id;
    editForm.name = accountType.name;
    editForm.description = accountType.description;
    editForm.interest_rate = accountType.interest_rate;
    editForm.withdrawal_rules = {
        max_percentage_per_withdrawal: accountType.withdrawal_rules?.max_percentage_per_withdrawal || '',
        max_withdrawals_per_year: accountType.withdrawal_rules?.max_withdrawals_per_year || '',
        penalty_before_months: accountType.withdrawal_rules?.penalty_before_months || '',
        penalty_percentage: accountType.withdrawal_rules?.penalty_percentage || '',
        minimum_balance_required: accountType.withdrawal_rules?.minimum_balance_required || ''
    };
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.accounts.types.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAccountType = (accountType) => {
    if (confirm(`Are you sure you want to delete "${accountType.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.accounts.types.destroy', accountType.id));
    }
};

const formatWithdrawalRules = (rules) => {
    if (!rules || typeof rules !== 'object') return 'No rules defined';
    
    const ruleTexts = [];
    if (rules.max_percentage_per_withdrawal) {
        ruleTexts.push(`Max ${rules.max_percentage_per_withdrawal}% per withdrawal`);
    }
    if (rules.max_withdrawals_per_year) {
        ruleTexts.push(`Max ${rules.max_withdrawals_per_year} withdrawals/year`);
    }
    if (rules.penalty_before_months) {
        ruleTexts.push(`${rules.penalty_percentage || 0}% penalty before ${rules.penalty_before_months} months`);
    }
    if (rules.minimum_balance_required) {
        ruleTexts.push(`Min balance: ${rules.minimum_balance_required}`);
    }
    
    return ruleTexts.length > 0 ? ruleTexts.join(', ') : 'No rules defined';
};
</script>

<template>
    <Head title="Account Types Management" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Account Types Management
            </h2>
        </template>

        <div class="py-6 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
                            <h3 class="text-lg font-semibold text-gray-900">Account Types</h3>
                            <PrimaryButton 
                                @click="showCreateModal = true" 
                                class="w-full sm:w-auto justify-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Account Type
                            </PrimaryButton>
                        </div>

                        <!-- Search -->
                        <div class="mt-4">
                            <div class="max-w-md">
                                <TextInput
                                    type="text"
                                    :modelValue="search"
                                    @update:modelValue="(value) => search = value"
                                    class="w-full"
                                    placeholder="Search account types..."
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="p-4 sm:p-6">
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Account Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Interest Rate
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Members
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Withdrawal Rules
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="accountType in accountTypes.data" :key="accountType.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ accountType.name }}</div>
                                                <div class="text-sm text-gray-500 mt-1">{{ accountType.description }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ accountType.interest_rate }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ accountType.member_accounts_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 max-w-xs">
                                                {{ formatWithdrawalRules(accountType.withdrawal_rules) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-2">
                                                <button
                                                    @click="openEdit(accountType)"
                                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    @click="deleteAccountType(accountType)"
                                                    :disabled="accountType.member_accounts_count > 0"
                                                    :title="accountType.member_accounts_count > 0 ? 'Cannot delete account type with active members' : ''"
                                                    class="inline-flex items-center px-3 py-1.5 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-4">
                            <div v-for="accountType in accountTypes.data" :key="accountType.id" class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="p-4">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-lg font-medium text-gray-900 truncate">{{ accountType.name }}</h4>
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ accountType.interest_rate }}% Interest
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ accountType.member_accounts_count }} Members
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <p class="text-sm text-gray-600 mb-3">{{ accountType.description }}</p>
                                    
                                    <!-- Withdrawal Rules -->
                                    <div class="mb-4">
                                        <span class="text-xs font-medium text-gray-700 uppercase tracking-wider">Withdrawal Rules:</span>
                                        <p class="text-sm text-gray-600 mt-1">{{ formatWithdrawalRules(accountType.withdrawal_rules) }}</p>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button
                                            @click="openEdit(accountType)"
                                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteAccountType(accountType)"
                                            :disabled="accountType.member_accounts_count > 0"
                                            :title="accountType.member_accounts_count > 0 ? 'Cannot delete account type with active members' : ''"
                                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty States -->
                        <div v-if="accountTypes.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                {{ search ? 'No account types found' : 'No account types' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ search ? 'Try adjusting your search to find what you\'re looking for.' : 'Get started by creating a new account type.' }}
                            </p>
                            <div class="mt-6" v-if="!search">
                                <PrimaryButton @click="showCreateModal = true">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Account Type
                                </PrimaryButton>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6" v-if="accountTypes.data.length > 0 && accountTypes.links">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ accountTypes.from }}</span> to <span class="font-medium">{{ accountTypes.to }}</span> of <span class="font-medium">{{ accountTypes.total }}</span> results
                                </div>
                                <div class="flex items-center space-x-1">
                                    <Link
                                        v-for="(link, i) in accountTypes.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium border"
                                        :class="{ 
                                            'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active && link.url,
                                            'bg-white border-gray-300 text-gray-300 cursor-default': !link.url 
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Account Type Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="4xl">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg font-medium mb-6">Create New Account Type</h2>
                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="createName" value="Account Type Name" />
                            <TextInput
                                id="createName"
                                type="text"
                                v-model="createForm.name"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="createForm.errors.name" class="text-red-600 text-sm mt-1">{{ createForm.errors.name }}</div>
                        </div>
                        <div>
                            <InputLabel for="createInterestRate" value="Interest Rate (%)" />
                            <TextInput
                                id="createInterestRate"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                v-model="createForm.interest_rate"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="createForm.errors.interest_rate" class="text-red-600 text-sm mt-1">{{ createForm.errors.interest_rate }}</div>
                        </div>
                    </div>
                    
                    <div>
                        <InputLabel for="createDescription" value="Description" />
                        <textarea
                            id="createDescription"
                            v-model="createForm.description"
                            required
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        ></textarea>
                        <div v-if="createForm.errors.description" class="text-red-600 text-sm mt-1">{{ createForm.errors.description }}</div>
                    </div>

                    <!-- Withdrawal Rules -->
                    <div>
                        <InputLabel value="Withdrawal Rules" class="text-base font-medium" />
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <InputLabel for="createMaxPercentage" value="Max % per withdrawal" />
                                <TextInput
                                    id="createMaxPercentage"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    v-model="createForm.withdrawal_rules.max_percentage_per_withdrawal"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="createMaxWithdrawals" value="Max withdrawals per year" />
                                <TextInput
                                    id="createMaxWithdrawals"
                                    type="number"
                                    min="0"
                                    v-model="createForm.withdrawal_rules.max_withdrawals_per_year"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="createPenaltyMonths" value="Penalty before months" />
                                <TextInput
                                    id="createPenaltyMonths"
                                    type="number"
                                    min="0"
                                    v-model="createForm.withdrawal_rules.penalty_before_months"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="createPenaltyPercentage" value="Penalty percentage (%)" />
                                <TextInput
                                    id="createPenaltyPercentage"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    v-model="createForm.withdrawal_rules.penalty_percentage"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div class="sm:col-span-2 lg:col-span-1">
                                <InputLabel for="createMinBalance" value="Minimum balance required" />
                                <TextInput
                                    id="createMinBalance"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    v-model="createForm.withdrawal_rules.minimum_balance_required"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <PrimaryButton type="submit" :disabled="createForm.processing" class="w-full sm:w-auto justify-center">
                            <span v-if="createForm.processing">Creating...</span>
                            <span v-else>Create Account Type</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Account Type Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false" maxWidth="4xl">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg font-medium mb-6">Edit Account Type</h2>
                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="editName" value="Account Type Name" />
                            <TextInput
                                id="editName"
                                type="text"
                                v-model="editForm.name"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="editForm.errors.name" class="text-red-600 text-sm mt-1">{{ editForm.errors.name }}</div>
                        </div>
                        <div>
                            <InputLabel for="editInterestRate" value="Interest Rate (%)" />
                            <TextInput
                                id="editInterestRate"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                v-model="editForm.interest_rate"
                                required
                                class="mt-1 block w-full"
                            />
                            <div v-if="editForm.errors.interest_rate" class="text-red-600 text-sm mt-1">{{ editForm.errors.interest_rate }}</div>
                        </div>
                    </div>
                    
                    <div>
                        <InputLabel for="editDescription" value="Description" />
                        <textarea
                            id="editDescription"
                            v-model="editForm.description"
                            required
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        ></textarea>
                        <div v-if="editForm.errors.description" class="text-red-600 text-sm mt-1">{{ editForm.errors.description }}</div>
                    </div>

                    <!-- Withdrawal Rules -->
                    <div>
                        <InputLabel value="Withdrawal Rules" class="text-base font-medium" />
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <InputLabel for="editMaxPercentage" value="Max % per withdrawal" />
                                <TextInput
                                    id="editMaxPercentage"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    v-model="editForm.withdrawal_rules.max_percentage_per_withdrawal"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="editMaxWithdrawals" value="Max withdrawals per year" />
                                <TextInput
                                    id="editMaxWithdrawals"
                                    type="number"
                                    min="0"
                                    v-model="editForm.withdrawal_rules.max_withdrawals_per_year"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="editPenaltyMonths" value="Penalty before months" />
                                <TextInput
                                    id="editPenaltyMonths"
                                    type="number"
                                    min="0"
                                    v-model="editForm.withdrawal_rules.penalty_before_months"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="editPenaltyPercentage" value="Penalty percentage (%)" />
                                <TextInput
                                    id="editPenaltyPercentage"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    v-model="editForm.withdrawal_rules.penalty_percentage"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div class="sm:col-span-2 lg:col-span-1">
                                <InputLabel for="editMinBalance" value="Minimum balance required" />
                                <TextInput
                                    id="editMinBalance"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    v-model="editForm.withdrawal_rules.minimum_balance_required"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <PrimaryButton type="submit" :disabled="editForm.processing" class="w-full sm:w-auto justify-center">
                            <span v-if="editForm.processing">Updating...</span>
                            <span v-else>Update Account Type</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
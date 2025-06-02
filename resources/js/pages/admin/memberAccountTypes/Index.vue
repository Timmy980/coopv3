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
        route('account_types.index'),
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
    createForm.post(route('account_types.store'), {
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
    editForm.put(route('account_types.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAccountType = (accountType) => {
    if (confirm(`Are you sure you want to delete "${accountType.name}"? This action cannot be undone.`)) {
        router.delete(route('account_types.destroy', accountType.id));
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <h3 class="text-lg font-semibold">Account Types</h3>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <PrimaryButton @click="showCreateModal = true">Create Account Type</PrimaryButton>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="mb-6">
                        <div class="w-full sm:w-64">
                            <TextInput
                                type="text"
                                :modelValue="search"
                                @update:modelValue="(value) => search = value"
                                class="w-full"
                                placeholder="Search account types..."
                            />
                        </div>
                    </div>

                    <!-- Account Types List -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-4">
                            <div v-for="accountType in accountTypes.data" :key="accountType.id" class="bg-white p-4 rounded shadow-sm">
                                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                                    <div class="flex-grow">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                            <h4 class="font-semibold text-lg text-gray-800">{{ accountType.name }}</h4>
                                            <div class="flex items-center gap-4 mt-2 sm:mt-0">
                                                <span class="text-sm bg-green-100 text-green-800 px-2 py-1 rounded">
                                                    {{ accountType.interest_rate }}% Interest
                                                </span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ accountType.member_accounts_count }} Members
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 mb-3">{{ accountType.description }}</p>
                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium">Withdrawal Rules:</span>
                                            {{ formatWithdrawalRules(accountType.withdrawal_rules) }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <PrimaryButton @click="openEdit(accountType)">Edit</PrimaryButton>
                                        <DangerButton 
                                            @click="deleteAccountType(accountType)"
                                            :disabled="accountType.member_accounts_count > 0"
                                            :title="accountType.member_accounts_count > 0 ? 'Cannot delete account type with active members' : ''"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6" v-if="accountTypes.data.length > 0">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Showing {{ accountTypes.from }} - {{ accountTypes.to }} of {{ accountTypes.total }} account types
                                </div>
                                <div class="flex gap-1">
                                    <Link
                                        v-for="(link, i) in accountTypes.links"
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
                        <div v-else-if="search" class="text-center py-4 text-gray-500">
                            No account types found matching your search
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            No account types available
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Account Type Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Create New Account Type</h2>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <InputLabel value="Withdrawal Rules" />
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <div class="md:col-span-2">
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
                    
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="createForm.processing">Create Account Type</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Account Type Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Edit Account Type</h2>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <InputLabel value="Withdrawal Rules" />
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <div class="md:col-span-2">
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
                    
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="editForm.processing">Update Account Type</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
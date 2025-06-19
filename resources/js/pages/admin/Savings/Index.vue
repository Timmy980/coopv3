<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import lodash from 'lodash';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Modal from '@/components/Modal.vue';
import SavingsTable from '@/components/savings/SavingsTable.vue';
import SavingsFilters from '@/components/savings/SavingsFilters.vue';
import SavingsHeader from '@/components/savings/SavingsHeader.vue';
import AccountFormFields from '@/components/savings/AccountFormFields.vue';
import type { PaginatedData, Saving, MemberAccount, CooperativeAccount, SavingsFilterData, BreadcrumbItem } from '@/types';

interface Props {
    savings: PaginatedData<Saving>;
    filters: SavingsFilterData;
    memberAccounts: MemberAccount[];
    cooperativeAccounts: CooperativeAccount[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Savings',
        href: route('admin.savings.index'),
    },
];

// Reactive filter state
const statusFilter = ref(props.filters?.status ?? '');
const sourceFilter = ref(props.filters?.source ?? '');
const dateFromFilter = ref(props.filters?.date_from ?? '');
const dateToFilter = ref(props.filters?.date_to ?? '');

// Computed property for active filters
const hasActiveFilters = computed(() => {
    return statusFilter.value || sourceFilter.value || dateFromFilter.value || dateToFilter.value;
});

// Debounced filter function
const performFilter = () => {
    router.get(
        route('admin.savings.index'),
        {
            status: statusFilter.value,
            source: sourceFilter.value,
            date_from: dateFromFilter.value,
            date_to: dateToFilter.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedFilter = lodash.debounce(performFilter, 300);

// Modal states
const showSingleEntryModal = ref(false);

// Forms
const singleEntryForm = useForm({
    member_account_id: '',
    cooperative_account_id: '',
    amount: '',
    transaction_date: '',
    reference_number: '',
    notes: ''
});

// Single entry functions
const closeSingleEntryModal = () => {
    showSingleEntryModal.value = false;
    singleEntryForm.reset();
};

const submitSingleEntry = () => {
    singleEntryForm.post(route('admin.savings.single.store'), {
        onSuccess: () => closeSingleEntryModal()
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    sourceFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    performFilter();
};

// Add watch implementation after debouncedFilter definition
watch([statusFilter, sourceFilter, dateFromFilter, dateToFilter], () => {
    debouncedFilter();
});
</script>

<template>
    <Head title="Savings Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <SavingsHeader 
                title="Savings Management"
                :status="statusFilter"
                :source="sourceFilter"
            />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <PrimaryButton @click="showSingleEntryModal = true">
                                New Single Entry
                            </PrimaryButton>
                            <Link 
                                :href="route('admin.savings.bulk.create')" 
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Bulk Upload
                            </Link>
                        </div>
                        <Link 
                            :href="route('admin.savings.approvals.pending')" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Pending Approval
                        </Link>
                    </div>
                </div>

                <!-- Filters -->
                <SavingsFilters
                    :filters="{
                        status: statusFilter,
                        source: sourceFilter,
                        date_from: dateFromFilter,
                        date_to: dateToFilter
                    }"
                    bulk-upload-label="Bulk Upload"
                    @update:status="statusFilter = $event"
                    @update:source="sourceFilter = $event"
                    @update:date_from="dateFromFilter = $event"
                    @update:date_to="dateToFilter = $event"
                    @clear-filters="clearFilters"
                />

                <!-- Savings Table -->
                <SavingsTable
                    :savings="savings"
                    :show-member-details="true"
                    member-column-label="Member"
                    :has-active-filters="Boolean(hasActiveFilters)"
                    :is-admin="true"
                />
            </div>
        </div>

        <!-- Single Entry Modal -->
        <Modal :show="showSingleEntryModal" @close="closeSingleEntryModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    New Single Saving Entry
                </h2>

                <form @submit.prevent="submitSingleEntry" class="space-y-4">
                    <AccountFormFields
                        v-model="singleEntryForm"
                        :member-accounts="memberAccounts"
                        :cooperative-accounts="cooperativeAccounts"
                        :errors="singleEntryForm.errors"
                        :show-member-names="true"
                        member-account-id="single_entry_member_account"
                        cooperative-account-id="single_entry_cooperative_account"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="amount" value="Amount" />
                            <TextInput
                                id="amount"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="singleEntryForm.amount"
                                required
                                class="mt-1 block w-full"
                            />
                            <InputError :message="singleEntryForm.errors.amount" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="transaction_date" value="Transaction Date" />
                            <TextInput
                                id="transaction_date"
                                type="date"
                                v-model="singleEntryForm.transaction_date"
                                required
                                class="mt-1 block w-full"
                            />
                            <InputError :message="singleEntryForm.errors.transaction_date" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="reference_number" value="Reference Number" />
                        <TextInput
                            id="reference_number"
                            type="text"
                            v-model="singleEntryForm.reference_number"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="singleEntryForm.errors.reference_number" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <textarea
                            id="notes"
                            v-model="singleEntryForm.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        ></textarea>
                        <InputError :message="singleEntryForm.errors.notes" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <SecondaryButton type="button" @click="closeSingleEntryModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="singleEntryForm.processing">
                            Create Entry
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
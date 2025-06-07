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
import type { PaginatedData, Saving, MemberAccount, CooperativeAccount, SavingsFilterData } from '@/types';

interface Props {
    savings: PaginatedData<Saving>;
    filters: SavingsFilterData;
    memberAccounts: MemberAccount[];
    cooperativeAccounts: CooperativeAccount[];
}

const props = defineProps<Props>();

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
        route('savings.index'),
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
const showGatewayModal = ref(false);
const showDepositModal = ref(false);
const showProofModal = ref(false);
const selectedSaving = ref<Saving | null>(null);

// Forms
const gatewayForm = useForm({
    member_account_id: '',
    cooperative_account_id: '',
    amount: ''
});

const depositForm = useForm({
    member_account_id: '',
    cooperative_account_id: '',
    amount: '',
    transaction_date: '',
    reference_number: '',
    payment_proof: null as File | null,
    notes: ''
});

const proofForm = useForm({
    payment_proof: null as File | null
});

// Gateway payment functions
const closeGatewayModal = () => {
    showGatewayModal.value = false;
    gatewayForm.reset();
};

const submitGatewayPayment = () => {
    gatewayForm.post(route('savings.gateway.initiate'), {
        onSuccess: (response: any) => {
            closeGatewayModal();
            // Redirect to payment gateway
            if (response?.authorization_url) {
                window.location.href = response.authorization_url;
            }
        }
    });
};

// Bank deposit functions
const closeDepositModal = () => {
    showDepositModal.value = false;
    depositForm.reset();
};

const submitBankDeposit = () => {
    depositForm.post(route('savings.deposit.store'), {
        onSuccess: () => closeDepositModal()
    });
};

// Proof upload functions
const showUploadProofModal = (saving: Saving) => {
    selectedSaving.value = saving;
    showProofModal.value = true;
};

const closeProofModal = () => {
    showProofModal.value = false;
    selectedSaving.value = null;
    proofForm.reset();
};

const submitProof = () => {
    if (selectedSaving.value) {
        proofForm.post(route('savings.deposit.proof', selectedSaving.value.id), {
            onSuccess: () => closeProofModal()
        });
    }
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
    <Head title="My Savings" />

    <AppLayout>
        <template #header>
            <SavingsHeader 
                title="My Savings"
                :status="statusFilter"
                :source="sourceFilter"
            />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <PrimaryButton @click="showGatewayModal = true">
                            Pay via Gateway
                        </PrimaryButton>
                        <SecondaryButton @click="showDepositModal = true">
                            Submit Bank Deposit
                        </SecondaryButton>
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
                    bulk-upload-label="Admin Upload"
                    @update:status="statusFilter = $event"
                    @update:source="sourceFilter = $event"
                    @update:date_from="dateFromFilter = $event"
                    @update:date_to="dateToFilter = $event"
                    @clear-filters="clearFilters"
                />

                <!-- Savings Table -->
                <SavingsTable
                    :savings="savings"
                    :show-member-details="false"
                    :show-upload-proof-button="true"
                    member-column-label="Account"
                    :has-active-filters="Boolean(hasActiveFilters)"
                    @upload-proof="showUploadProofModal"
                />
            </div>
        </div>

        <!-- Gateway Payment Modal -->
        <Modal :show="showGatewayModal" @close="closeGatewayModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Pay via Gateway
                </h2>

                <form @submit.prevent="submitGatewayPayment" class="space-y-4">
                    <AccountFormFields
                        v-model="gatewayForm"
                        :member-accounts="memberAccounts"
                        :cooperative-accounts="cooperativeAccounts"
                        :errors="gatewayForm.errors"
                        :show-member-names="false"
                        member-account-id="gateway_member_account"
                        cooperative-account-id="gateway_cooperative_account"
                    />

                    <div>
                        <InputLabel for="amount" value="Amount" />
                        <TextInput
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            v-model="gatewayForm.amount"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="gatewayForm.errors.amount" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-2">
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Submit Bank Deposit
                </h2>

                <form @submit.prevent="submitBankDeposit" class="space-y-4">
                    <AccountFormFields
                        v-model="depositForm"
                        :member-accounts="memberAccounts"
                        :cooperative-accounts="cooperativeAccounts"
                        :errors="depositForm.errors"
                        :show-member-names="false"
                        member-account-id="deposit_member_account"
                        cooperative-account-id="deposit_cooperative_account"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="deposit_amount" value="Amount" />
                            <TextInput
                                id="deposit_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="depositForm.amount"
                                required
                                class="mt-1 block w-full"
                            />
                            <InputError :message="depositForm.errors.amount" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="transaction_date" value="Transaction Date" />
                            <TextInput
                                id="transaction_date"
                                type="date"
                                v-model="depositForm.transaction_date"
                                required
                                class="mt-1 block w-full"
                            />
                            <InputError :message="depositForm.errors.transaction_date" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="reference_number" value="Reference Number" />
                        <TextInput
                            id="reference_number"
                            type="text"
                            v-model="depositForm.reference_number"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="depositForm.errors.reference_number" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="payment_proof" value="Payment Proof" />
                        <input
                            type="file"
                            id="payment_proof"
                            class="mt-1 block w-full"
                            @change="depositForm.payment_proof = $event.target.files[0]"
                            accept="image/*,.pdf"
                        />
                        <InputError :message="depositForm.errors.payment_proof" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <textarea
                            id="notes"
                            v-model="depositForm.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        ></textarea>
                        <InputError :message="depositForm.errors.notes" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-2">
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Upload Payment Proof
                </h2>

                <form @submit.prevent="submitProof" class="space-y-4">
                    <div>
                        <InputLabel for="new_payment_proof" value="Payment Proof" />
                        <input
                            type="file"
                            id="new_payment_proof"
                            class="mt-1 block w-full"
                            @change="proofForm.payment_proof = $event.target.files[0]"
                            accept="image/*,.pdf"
                            required
                        />
                        <InputError :message="proofForm.errors.payment_proof" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-2">
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
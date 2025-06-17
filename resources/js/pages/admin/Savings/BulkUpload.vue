<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import type { BreadcrumbItem, CooperativeAccount, AccountType } from '@/types';

interface Props {
    cooperative_accounts: CooperativeAccount[];
    account_types: AccountType[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Savings',
        href: route('savings.index'),
    },
    {
        title: 'Bulk Upload',
        href: route('savings.bulk.form'),
    },
];

// Forms
const form = useForm({
    cooperative_account_id: '',
    account_type_id: '',
    file: null as File | null
});

const templateForm = useForm({
    account_type_id: ''
});

// Reactive state
const fileInput = ref<HTMLInputElement>();
const dragOver = ref(false);
const selectedFileName = ref('');

// Computed properties
const canDownloadTemplate = computed(() => !!templateForm.account_type_id);
const hasSelectedFile = computed(() => !!form.file);

// Template download
const downloadTemplate = () => {
    if (!templateForm.account_type_id) return;
    
    window.location.href = route('savings.bulk.template', {
        account_type_id: templateForm.account_type_id
    });
};

// File handling
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        form.file = file;
        selectedFileName.value = file.name;
    }
};

const handleFileDrop = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = false;
    
    const files = event.dataTransfer?.files;
    const file = files?.[0];
    
    if (file && isValidFileType(file)) {
        form.file = file;
        selectedFileName.value = file.name;
        
        // Update file input
        if (fileInput.value) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.value.files = dt.files;
        }
    }
};

const isValidFileType = (file: File) => {
    const validTypes = ['.xlsx', '.xls', '.csv'];
    const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
    return validTypes.includes(fileExtension);
};

const removeFile = () => {
    form.file = null;
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Drag and drop handlers
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = true;
};

const handleDragLeave = () => {
    dragOver.value = false;
};

// Form submission
const submit = () => {
    form.post(route('savings.bulk.upload'), {
        preserveScroll: true
    });
};

// Navigation
const goBack = () => {
    router.visit(route('savings.index'));
};
</script>

<template>
    <Head title="Bulk Upload Savings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bulk Upload Savings
                </h2>
                <SecondaryButton @click="goBack">
                    Back to Savings
                </SecondaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Instructions Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 overflow-hidden shadow-sm sm:rounded-lg mb-8 border border-blue-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-blue-900">How to Upload Savings</h3>
                        </div>
                        
                        <div class="text-sm text-blue-800 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">1</span>
                                        <span>Select an account type from the dropdown</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">2</span>
                                        <span>Download the pre-filled template file</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">3</span>
                                        <span>Fill in the required information in the template</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">4</span>
                                        <span>Upload the completed template file</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">5</span>
                                        <span>Select the cooperative account</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">6</span>
                                        <span>Submit the form for processing</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-3 bg-blue-100 rounded-md border border-blue-200">
                                <h4 class="font-medium text-blue-900 mb-1">Required Fields in Template:</h4>
                                <ul class="text-xs text-blue-800 space-y-1">
                                    <li>• <strong>Amount:</strong> Required - The savings amount</li>
                                    <li>• <strong>Transaction Date:</strong> Optional - Defaults to today if not provided</li>
                                    <li>• <strong>Reference Number:</strong> Optional - For tracking purposes</li>
                                    <li>• <strong>Notes:</strong> Optional - Additional information</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Download Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Download Template</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="template_account_type" value="Select Account Type" />
                                <select
                                    id="template_account_type"
                                    v-model="templateForm.account_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Choose an account type...</option>
                                    <option
                                        v-for="type in account_types"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button
                                    @click="downloadTemplate"
                                    :disabled="!canDownloadTemplate"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Form Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <svg class="h-6 w-6 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Upload Savings Data</h3>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Account Type and Cooperative Account in Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="account_type" value="Account Type" />
                                    <select
                                        id="account_type"
                                        v-model="form.account_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Account Type</option>
                                        <option
                                            v-for="type in account_types"
                                            :key="type.id"
                                            :value="type.id"
                                        >
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.account_type_id" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="cooperative_account" value="Cooperative Account" />
                                    <select
                                        id="cooperative_account"
                                        v-model="form.cooperative_account_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Account</option>
                                        <option
                                            v-for="account in cooperative_accounts"
                                            :key="account.id"
                                            :value="account.id"
                                        >
                                            {{ account.account_name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.cooperative_account_id" class="mt-2" />
                                </div>
                            </div>

                            <!-- File Upload with Drag & Drop -->
                            <div>
                                <InputLabel for="file" value="Excel File" />
                                <div class="mt-1">
                                    <!-- Drag & Drop Area -->
                                    <div
                                        @dragover="handleDragOver"
                                        @dragleave="handleDragLeave"
                                        @drop="handleFileDrop"
                                        :class="[
                                            'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
                                            dragOver ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300',
                                            hasSelectedFile ? 'bg-green-50 border-green-300' : ''
                                        ]"
                                    >
                                        <input
                                            type="file"
                                            id="file"
                                            ref="fileInput"
                                            class="hidden"
                                            @change="handleFileSelect"
                                            accept=".xlsx,.xls,.csv"
                                            required
                                        />
                                        
                                        <div v-if="!hasSelectedFile">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-4">
                                                <label for="file" class="cursor-pointer">
                                                    <span class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                        Click to upload a file
                                                    </span>
                                                    <span class="text-gray-500"> or drag and drop</span>
                                                </label>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    XLSX, XLS, or CSV files only
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div v-else class="flex items-center justify-center">
                                            <svg class="h-8 w-8 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="text-center">
                                                <p class="text-sm font-medium text-green-800">{{ selectedFileName }}</p>
                                                <button
                                                    type="button"
                                                    @click="removeFile"
                                                    class="text-xs text-red-600 hover:text-red-500 mt-1"
                                                >
                                                    Remove file
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <SecondaryButton type="button" @click="goBack">
                                    Cancel
                                </SecondaryButton>
                                
                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing || !hasSelectedFile"
                                    class="min-w-[120px]"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Uploading...' : 'Upload Savings' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<template>
    <AppLayout title="Bulk Upload Savings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Bulk Upload Savings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Instructions -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Instructions</h3>
                            <div class="mt-2 text-sm text-gray-600">
                                <ol class="list-decimal list-inside space-y-2">
                                    <li>Select an account type</li>
                                    <li>Download the template file which will be pre-filled with member information</li>
                                    <li>Fill in the required information:
                                        <ul class="list-disc list-inside ml-4 mt-1">
                                            <li>Amount (required)</li>
                                            <li>Transaction Date (optional, defaults to today)</li>
                                            <li>Reference Number (optional)</li>
                                            <li>Notes (optional)</li>
                                        </ul>
                                    </li>
                                    <li>Upload the filled template</li>
                                    <li>Select the cooperative account</li>
                                    <li>Submit the form</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Account Type Selection and Template Download -->
                        <div class="mb-6 space-y-4">
                            <div>
                                <InputLabel for="template_account_type" value="Select Account Type" />
                                <select
                                    id="template_account_type"
                                    v-model="templateForm.account_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300"
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
                            </div>
                            
                            <div>
                                <button
                                    @click="downloadTemplate"
                                    :disabled="!templateForm.account_type_id"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                                >
                                    Download Template
                                </button>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="account_type" value="Account Type" />
                                <select
                                    id="account_type"
                                    v-model="form.account_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300"
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
                                    class="mt-1 block w-full rounded-md border-gray-300"
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

                            <div>
                                <InputLabel for="file" value="Excel File" />
                                <input
                                    type="file"
                                    id="file"
                                    ref="file"
                                    class="mt-1 block w-full"
                                    @change="form.file = $event.target.files[0]"
                                    accept=".xlsx,.xls,.csv"
                                    required
                                />
                                <InputError :message="form.errors.file" class="mt-2" />
                                <InputError :message="form.errors.import_error" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end">
                                <PrimaryButton :disabled="form.processing">
                                    Upload
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputLabel from '@/components/InputLabel.vue'
import InputError from '@/components/InputError.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'

export default {
    components: {
        AppLayout,
        Link,
        InputLabel,
        InputError,
        PrimaryButton
    },

    props: {
        cooperative_accounts: Array,
        account_types: Array
    },

    setup() {
        const form = useForm({
            cooperative_account_id: '',
            account_type_id: '',
            file: null
        });

        const templateForm = useForm({
            account_type_id: ''
        });

        const downloadTemplate = () => {
            window.location.href = route('savings.bulk.template', {
                account_type_id: templateForm.account_type_id
            });
        };

        const submit = () => {
            form.post(route('savings.bulk.upload'), {
                preserveScroll: true
            });
        };

        return {
            form,
            templateForm,
            downloadTemplate,
            submit
        }
    }
}
</script> 
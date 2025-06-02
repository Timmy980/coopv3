<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';


const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    phone_number: props.user.phone_number,
    date_of_birth: props.user.date_of_birth ? new Date(props.user.date_of_birth).toISOString().split('T')[0] : null,
    address: props.user.address,
    state: props.user.state,
    lga: props.user.lga,
    sex: props.user.sex,
    profile_picture: null,
    bank_account_number: props.user.bank_account_number,
    bank_name: props.user.bank_name,
    bank_branch: props.user.bank_branch,
    bank_account_type: props.user.bank_account_type,
    next_of_kin_name: props.user.next_of_kin_name,
    next_of_kin_phone: props.user.next_of_kin_phone,
    next_of_kin_relationship: props.user.next_of_kin_relationship,
}, {
    // Set form options to handle multipart/form-data
    forceFormData: true,
});

const profilePicturePreview = ref(
    props.user.profile_picture 
        ? `${usePage().props.appUrl}/storage/${props.user.profile_picture}`
        : null
);

function updateProfilePicture(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Check file size (2MB = 2 * 1024 * 1024 bytes)
    if (file.size > 2 * 1024 * 1024) {
        form.errors.profile_picture = 'The profile picture must not be greater than 2MB.';
        e.target.value = ''; // Clear the file input
        return;
    }

    form.profile_picture = file;
    form.errors.profile_picture = ''; // Clear any previous errors
    profilePicturePreview.value = URL.createObjectURL(file);
}
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <form @submit.prevent="form.post(route('profile.update'))" class="space-y-6">
                        <div class="space-y-2">
                            <!-- Profile Picture -->
                            <div class="flex flex-col items-center space-y-2">
                                <img
                                    v-if="profilePicturePreview"
                                    :src="profilePicturePreview"
                                    class="w-32 h-32 rounded-full object-cover"
                                    alt="Profile Picture"
                                />
                                <div v-else class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500">No Photo</span>
                                </div>
                                <input
                                    type="file"
                                    @input="updateProfilePicture"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <InputError :message="form.errors.profile_picture" class="mt-2" />
                            </div>

                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="first_name" value="First Name" />
                                    <TextInput
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.first_name" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="last_name" value="Last Name" />
                                    <TextInput
                                        id="last_name"
                                        v-model="form.last_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.last_name" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                        disabled
                                    />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="phone_number" value="Phone Number" />
                                    <TextInput
                                        id="phone_number"
                                        v-model="form.phone_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.phone_number" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="date_of_birth" value="Date of Birth" />
                                    <TextInput
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.date_of_birth" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="sex" value="Sex" />
                                    <select
                                        id="sex"
                                        v-model="form.sex"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select Sex</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    <InputError :message="form.errors.sex" class="mt-2" />
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="space-y-4 mt-6">
                                <h3 class="text-lg font-medium text-gray-900">Address Information</h3>
                                <div>
                                    <InputLabel for="address" value="Address" />
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3"
                                    ></textarea>
                                    <InputError :message="form.errors.address" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="state" value="State" />
                                        <TextInput
                                            id="state"
                                            v-model="form.state"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.state" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="lga" value="LGA" />
                                        <TextInput
                                            id="lga"
                                            v-model="form.lga"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.lga" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Details -->
                            <div class="space-y-4 mt-6">
                                <h3 class="text-lg font-medium text-gray-900">Bank Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="bank_name" value="Bank Name" />
                                        <TextInput
                                            id="bank_name"
                                            v-model="form.bank_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.bank_name" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="bank_account_number" value="Account Number" />
                                        <TextInput
                                            id="bank_account_number"
                                            v-model="form.bank_account_number"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.bank_account_number" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="bank_branch" value="Bank Branch" />
                                        <TextInput
                                            id="bank_branch"
                                            v-model="form.bank_branch"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.bank_branch" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="bank_account_type" value="Account Type" />
                                        <TextInput
                                            id="bank_account_type"
                                            v-model="form.bank_account_type"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.bank_account_type" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Next of Kin Details -->
                            <div class="space-y-4 mt-6">
                                <h3 class="text-lg font-medium text-gray-900">Next of Kin Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="next_of_kin_name" value="Name" />
                                        <TextInput
                                            id="next_of_kin_name"
                                            v-model="form.next_of_kin_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.next_of_kin_name" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="next_of_kin_phone" value="Phone Number" />
                                        <TextInput
                                            id="next_of_kin_phone"
                                            v-model="form.next_of_kin_phone"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.next_of_kin_phone" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="next_of_kin_relationship" value="Relationship" />
                                        <TextInput
                                            id="next_of_kin_relationship"
                                            v-model="form.next_of_kin_relationship"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.next_of_kin_relationship" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

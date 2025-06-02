<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PrimaryButton from '@/components/PrimaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true
    }
});

const showApprovalModal = ref(false);
const showRejectionModal = ref(false);
const selectedUser = ref(null);
const processingApproval = ref(false);
const processingRejection = ref(false);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const openApprovalModal = (user) => {
    selectedUser.value = user;
    showApprovalModal.value = true;
};

const openRejectionModal = (user) => {
    selectedUser.value = user;
    showRejectionModal.value = true;
};

const approveUser = () => {
    if (!selectedUser.value) return;
    
    processingApproval.value = true;
    
    router.post(route('user-approvals.approve', selectedUser.value.id), {}, {
        onSuccess: () => {
            showApprovalModal.value = false;
            selectedUser.value = null;
            processingApproval.value = false;
        },
        onError: () => {
            processingApproval.value = false;
        }
    });
};

const rejectUser = () => {
    if (!selectedUser.value) return;
    
    processingRejection.value = true;
    
    router.post(route('user-approvals.reject', selectedUser.value.id), {}, {
        onSuccess: () => {
            showRejectionModal.value = false;
            selectedUser.value = null;
            processingRejection.value = false;
        },
        onError: () => {
            processingRejection.value = false;
        }
    });
};

const closeModal = () => {
    showApprovalModal.value = false;
    showRejectionModal.value = false;
    selectedUser.value = null;
};
</script>

<template>
    <Head title="User Approvals" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Approvals
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">Pending User Approvals</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Review and approve or reject user registration requests
                            </p>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ users.total }} pending {{ users.total === 1 ? 'user' : 'users' }}
                        </div>
                    </div>

                    <!-- Users List -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div v-if="users.data.length > 0" class="space-y-4">
                            <div v-for="user in users.data" :key="user.id" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="flex-grow">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">
                                                    {{ user.first_name }} {{ user.last_name }}
                                                </h4>
                                                <div class="mt-1 flex items-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending Approval
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                                </svg>
                                                <span>{{ user.email }}</span>
                                            </div>
                                            <div class="flex items-center" v-if="user.phone_number">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                <span>{{ user.phone_number }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Registered {{ formatDate(user.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row gap-3 lg:ml-6">
                                        <PrimaryButton 
                                            @click="openApprovalModal(user)"
                                            class="justify-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </PrimaryButton>
                                        <DangerButton 
                                            @click="openRejectionModal(user)"
                                            class="justify-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Reject
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No pending approvals</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                All user registration requests have been processed.
                            </p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6" v-if="users.data.length > 0">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ users.from }} - {{ users.to }} of {{ users.total }} users
                            </div>
                            <div class="flex gap-1">
                                <Link
                                    v-for="(link, i) in users.links"
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
                </div>
            </div>
        </div>

        <!-- Approval Confirmation Modal -->
        <Modal :show="showApprovalModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Approve User</h3>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-gray-600">
                        Are you sure you want to approve 
                        <span class="font-semibold">{{ selectedUser?.first_name }} {{ selectedUser?.last_name }}</span>?
                        This will activate their account and grant them access to the application.
                    </p>
                    <div class="mt-3 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Email:</span> {{ selectedUser?.email }}
                        </p>
                        <p class="text-sm text-gray-700" v-if="selectedUser?.phone_number">
                            <span class="font-medium">Phone:</span> {{ selectedUser?.phone_number }}
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button 
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="processingApproval"
                    >
                        Cancel
                    </button>
                    <PrimaryButton 
                        @click="approveUser"
                        :disabled="processingApproval"
                        class="flex items-center"
                    >
                        <svg v-if="processingApproval" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processingApproval ? 'Approving...' : 'Approve User' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Rejection Confirmation Modal -->
        <Modal :show="showRejectionModal" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Reject User</h3>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-gray-600">
                        Are you sure you want to reject 
                        <span class="font-semibold">{{ selectedUser?.first_name }} {{ selectedUser?.last_name }}</span>?
                        This action will mark their registration as rejected and they will not be able to access the application.
                    </p>
                    <div class="mt-3 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Email:</span> {{ selectedUser?.email }}
                        </p>
                        <p class="text-sm text-gray-700" v-if="selectedUser?.phone_number">
                            <span class="font-medium">Phone:</span> {{ selectedUser?.phone_number }}
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button 
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="processingRejection"
                    >
                        Cancel
                    </button>
                    <DangerButton 
                        @click="rejectUser"
                        :disabled="processingRejection"
                        class="flex items-center"
                    >
                        <svg v-if="processingRejection" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processingRejection ? 'Rejecting...' : 'Reject User' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
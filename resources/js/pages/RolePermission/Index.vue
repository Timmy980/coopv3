<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true
    },
    permissions: {
        type: Array,
        required: true
    },
    users: {
        type: Array,
        required: true
    }
});

const createRoleForm = useForm({
    name: '',
    permissions: []
});

const createPermissionForm = useForm({
    name: ''
});

const assignRoleForm = useForm({
    user_id: '',
    role: ''
});

const editRoleForm = useForm({
    role_id: '',
    name: '',
    permissions: []
});

const showRoleModal = ref(false);
const showPermissionModal = ref(false);
const showAssignRoleModal = ref(false);
const showEditRoleModal = ref(false);

const submitRole = () => {
    createRoleForm.post(route('roles.create'), {
        onSuccess: () => {
            showRoleModal.value = false;
            createRoleForm.reset();
        }
    });
};

const submitPermission = () => {
    createPermissionForm.post(route('permissions.create'), {
        onSuccess: () => {
            showPermissionModal.value = false;
            createPermissionForm.reset();
        }
    });
};

const assignRole = () => {
    assignRoleForm.post(route('roles.assign'), {
        onSuccess: () => {
            showAssignRoleModal.value = false;
            assignRoleForm.reset();
        }
    });
};

const deleteRole = (roleId) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('roles.delete', roleId));
    }
};

const deletePermission = (permissionId) => {
    if (confirm('Are you sure you want to delete this permission?')) {
        router.delete(route('permissions.delete', permissionId));
    }
};

const openEditRole = (role) => {
    editRoleForm.role_id = role.id;
    editRoleForm.name = role.name;
    editRoleForm.permissions = role.permissions.map(p => p.name);
    showEditRoleModal.value = true;
};

const updateRole = () => {
    editRoleForm.put(route('roles.update', editRoleForm.role_id), {
        onSuccess: () => {
            showEditRoleModal.value = false;
            editRoleForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Role &amp; Permission Management" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role &amp; Permission Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Roles and Permissions</h3>
                        <div class="space-x-2">
                            <PrimaryButton @click="showRoleModal = true">Create Role</PrimaryButton>
                            <PrimaryButton @click="showPermissionModal = true">Create Permission</PrimaryButton>
                            <PrimaryButton @click="showAssignRoleModal = true">Assign Role</PrimaryButton>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Roles List -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-4">Roles</h4>
                            <div class="space-y-2">
                                <div v-for="role in roles" :key="role.id" class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                                    <div>
                                        <p class="font-medium">{{ role.name }}</p>
                                        <div class="text-sm text-gray-600">
                                            Permissions: {{ role.permissions.map(p => p.name).join(', ') }}
                                        </div>
                                    </div>
                                    <div class="space-x-2">
                                        <PrimaryButton @click="openEditRole(role)">Edit</PrimaryButton>
                                        <DangerButton @click="deleteRole(role.id)">Delete</DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions List -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-4">Permissions</h4>
                            <div class="space-y-2">
                                <div v-for="permission in permissions" :key="permission.id" class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                                    <p class="font-medium">{{ permission.name }}</p>
                                    <DangerButton @click="deletePermission(permission.id)">Delete</DangerButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Role Modal -->
        <Modal :show="showRoleModal" @close="showRoleModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Create New Role</h2>
                <form @submit.prevent="submitRole" class="space-y-4">
                    <div>
                        <InputLabel for="roleName" value="Role Name" />
                        <TextInput
                            id="roleName"
                            type="text"
                            v-model="createRoleForm.name"
                            required
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel value="Permissions" />
                        <div class="mt-2 space-y-2">
                            <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="permission.name"
                                    v-model="createRoleForm.permissions"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="createRoleForm.processing">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Create Permission Modal -->
        <Modal :show="showPermissionModal" @close="showPermissionModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Create New Permission</h2>
                <form @submit.prevent="submitPermission" class="space-y-4">
                    <div>
                        <InputLabel for="permissionName" value="Permission Name" />
                        <TextInput
                            id="permissionName"
                            type="text"
                            v-model="createPermissionForm.name"
                            required
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="createPermissionForm.processing">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Assign Role Modal -->
        <Modal :show="showAssignRoleModal" @close="showAssignRoleModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Assign Role to User</h2>
                <form @submit.prevent="assignRole" class="space-y-4">
                    <div>
                        <InputLabel for="userId" value="Select User" />
                        <select
                            id="userId"
                            v-model="assignRoleForm.user_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required
                        >
                            <option value="">Select a user</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.first_name }} {{ user.last_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="role" value="Select Role" />
                        <select
                            id="role"
                            v-model="assignRoleForm.role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="assignRoleForm.processing">Assign Role</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Role Modal -->
        <Modal :show="showEditRoleModal" @close="showEditRoleModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Edit Role Permissions</h2>
                <form @submit.prevent="updateRole" class="space-y-4">
                    <div>
                        <InputLabel for="editRoleName" value="Role Name" />
                        <TextInput
                            id="editRoleName"
                            type="text"
                            v-model="editRoleForm.name"
                            required
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel value="Permissions" />
                        <div class="mt-2 space-y-2">
                            <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="permission.name"
                                    v-model="editRoleForm.permissions"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="editRoleForm.processing">Update</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>

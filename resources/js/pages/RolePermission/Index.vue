<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import Modal from '@/components/Modal.vue';
import type { BreadcrumbItem } from '@/types';

// Simple debounce function
function debounce<T extends (...args: any[]) => any>(func: T, wait: number): T {
    let timeout: NodeJS.Timeout;
    return ((...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(null, args), wait);
    }) as T;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Permission {
    id: number;
    name: string;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    roles: Role[];
}

interface UserPaginated {
    data: User[];
    from: number;
    to: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    roles: Role[];
    permissions: Permission[];
    users: UserPaginated;
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Role & Permission Management',
        href: route('admin.users.roles.index'),
    },
];

const search = ref(props.filters?.search ?? '');

// Debounced search function
const performSearch = () => {
    router.get(
        route('admin.users.roles.index'),
        { search: search.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const debouncedSearch = debounce(performSearch, 300);

watch(search, () => {
    debouncedSearch();
});

const createRoleForm = useForm({
    name: '',
    permissions: [] as string[]
});

const createPermissionForm = useForm({
    name: ''
});

const assignRoleForm = useForm({
    user_id: '' as string,
    role: ''
});

const editRoleForm = useForm({
    role_id: '' as string,
    name: '',
    permissions: [] as string[]
});

const showRoleModal = ref(false);
const showPermissionModal = ref(false);
const showAssignRoleModal = ref(false);
const showEditRoleModal = ref(false);
const showQuickAssignModal = ref(false);
const selectedUser = ref<User | null>(null);

const quickAssignForm = useForm({
    role: ''
});

// Computed property for available roles (excluding already assigned roles)
const availableRoles = computed(() => {
    if (!selectedUser.value) return props.roles;
    return props.roles.filter(role => 
        !selectedUser.value!.roles.some(userRole => userRole.name === role.name)
    );
});

const submitRole = () => {
    createRoleForm.post(route('admin.users.roles.store'), {
        onSuccess: () => {
            showRoleModal.value = false;
            createRoleForm.reset();
        }
    });
};

const submitPermission = () => {
    createPermissionForm.post(route('admin.users.permissions.store'), {
        onSuccess: () => {
            showPermissionModal.value = false;
            createPermissionForm.reset();
        }
    });
};

const assignRole = () => {
    assignRoleForm.patch(route('admin.users.roles.assign', assignRoleForm.user_id.toString()), {
        onSuccess: () => {
            showAssignRoleModal.value = false;
            assignRoleForm.reset();
        }
    });
};

const deleteRole = (roleId: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('admin.users.roles.destroy', roleId));
    }
};

const deletePermission = (permissionId: number) => {
    if (confirm('Are you sure you want to delete this permission?')) {
        router.delete(route('admin.users.permissions.destroy', permissionId));
    }
};

const openEditRole = (role: Role) => {
    editRoleForm.role_id = role.id.toString();
    editRoleForm.name = role.name;
    editRoleForm.permissions = role.permissions.map(p => p.name);
    showEditRoleModal.value = true;
};

const updateRole = () => {
    editRoleForm.put(route('admin.users.roles.update', editRoleForm.role_id), {
        onSuccess: () => {
            showEditRoleModal.value = false;
            editRoleForm.reset();
        }
    });
};

const unassignRole = (userId: number, roleName: string) => {
    if (confirm(`Are you sure you want to remove the role "${roleName}" from this user?`)) {
        router.patch(route('admin.users.roles.unassign', userId.toString()), {
            role: roleName
        });
    }
};

const openQuickAssign = (user: User) => {
    selectedUser.value = user;
    quickAssignForm.role = '';
    showQuickAssignModal.value = true;
};

const quickAssignRole = () => {
    if (!selectedUser.value) return;
    
    quickAssignForm.patch(route('admin.users.roles.assign', selectedUser.value.id.toString()), {
        onSuccess: () => {
            showQuickAssignModal.value = false;
            quickAssignForm.reset();
            selectedUser.value = null;
        }
    });
};
</script>

<template>
    <Head title="Role &amp; Permission Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role &amp; Permission Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <h3 class="text-lg font-semibold">Roles and Permissions</h3>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <PrimaryButton @click="showRoleModal = true">Create Role</PrimaryButton>
                            <PrimaryButton @click="showPermissionModal = true">Create Permission</PrimaryButton>
                            <PrimaryButton @click="showAssignRoleModal = true">Assign Role</PrimaryButton>
                        </div>
                    </div>                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Roles List -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-4">Roles</h4>
                            <div class="space-y-2">
                                <div v-for="role in roles" :key="role.id" class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-3 rounded shadow-sm">
                                    <div class="mb-2 md:mb-0">
                                        <p class="font-medium">{{ role.name }}</p>
                                        <div class="text-sm text-gray-600">
                                            Permissions: {{ role.permissions.map(p => p.name).join(', ') }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-2">
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
                    </div>                    <!-- Users and Their Roles -->
                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold">Users and Their Roles</h4>                            <div class="w-64">
                                <TextInput
                                    type="text"
                                    :modelValue="search"
                                    @update:modelValue="(value) => search = value"
                                    class="w-full"
                                    placeholder="Search users..."
                                />
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-4">
                                <div v-for="user in users.data" :key="user.id" class="bg-white p-4 rounded shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-grow">
                                            <div class="flex justify-between items-center">
                                                <p class="font-medium text-lg">{{ user.first_name }} {{ user.last_name }}</p>
                                                <button 
                                                    @click="openQuickAssign(user)"
                                                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                                                >
                                                    Assign Role
                                                </button>
                                            </div>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <div v-for="role in user.roles" :key="role.id" 
                                                    class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1.5 rounded-full text-sm">
                                                    {{ role.name }}
                                                    <button @click="unassignRole(user.id, role.name)" 
                                                        class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none"
                                                        title="Remove role">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="user.roles.length === 0" class="text-gray-500 text-sm italic">
                                                    No roles assigned
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                            :href="link.url || '#'"
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
                                No users found matching your search
                            </div>
                            <div v-else class="text-center py-4 text-gray-500">
                                No users available
                            </div>
                        </div>
                    </div>

                    <!-- Quick Assign Role Modal -->
                    <Modal :show="showQuickAssignModal" @close="showQuickAssignModal = false">
                        <div class="p-6">
                            <h2 class="text-lg font-medium mb-4">Assign Role to {{ selectedUser ? selectedUser.first_name + ' ' + selectedUser.last_name : '' }}</h2>
                            <form @submit.prevent="quickAssignRole" class="space-y-4">
                                <div>
                                    <InputLabel for="quickAssignRole" value="Select Role" />
                                    <select
                                        id="quickAssignRole"
                                        v-model="quickAssignForm.role"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required
                                    >
                                        <option value="">Select a role</option>
                                        <option 
                                            v-for="role in availableRoles" 
                                            :key="role.id" 
                                            :value="role.name"
                                        >
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex justify-end">
                                    <PrimaryButton type="submit" :disabled="quickAssignForm.processing">
                                        Assign Role
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </Modal>
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
                            <option value="">Select a user</option>                            <option v-for="user in users.data" :key="user.id" :value="user.id">
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

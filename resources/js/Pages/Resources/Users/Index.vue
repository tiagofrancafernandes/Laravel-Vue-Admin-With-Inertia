<script setup>
import { computed, ref } from 'vue';

import TableActions from '@/Components/AppMaker/Table/TableActions.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    filters: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const searchQuery = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');

const users_list = computed(() => props.users.data);

// Configuração de actions para a tabela
const userActions = [
    {
        type: 'link',
        name: 'view',
        label: 'View',
        route: 'users.show',
        color: 'blue',
    },
    {
        type: 'link',
        name: 'edit',
        label: 'Edit',
        route: 'users.edit',
        color: 'green',
    },
    {
        type: 'delete',
        name: 'delete',
        label: 'Delete',
        route: 'users.destroy',
        color: 'red',
        confirmTitle: 'Delete User',
        confirmMessage: 'Are you sure you want to delete this user? This action cannot be undone.',
        condition: (user) => user.id !== currentUser.value.id, // Não pode deletar a si mesmo
    },
];

const handleSearch = () => {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (roleFilter.value) params.role = roleFilter.value;

    router.get(route('users.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleReset = () => {
    searchQuery.value = '';
    roleFilter.value = '';
    router.get(
        route('users.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const handleAction = (action, userId) => {
    // Callback opcional após ação
    console.log(`Action ${action.name} executed on user ${userId}`);
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #headerTitle>Users</template>

        <template #headerActions>
            <!-- actions here -->
            <Link
                :href="route('users.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Add User
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search
                            </label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Name or email..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                            <select
                                v-model="roleFilter"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                @click="handleSearch"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex-1"
                            >
                                Filter
                            </button>
                            <button
                                @click="handleReset"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex-1"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Email</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Role</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Joined</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users_list"
                                :key="user.id"
                                class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-medium',
                                            {
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                    user.role === 'admin',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                    user.role === 'user',
                                            },
                                        ]"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <TableActions
                                        :actions="userActions"
                                        :record="user"
                                        resource="users"
                                        @action="handleAction"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        class="px-6 py-4 flex justify-between items-center border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-700"
                    >
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                        </div>
                        <div class="space-x-2">
                            <Link
                                v-if="users.prev_page_url"
                                :href="users.prev_page_url"
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                            >
                                ← Previous
                            </Link>
                            <span
                                v-else
                                class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded text-gray-400 dark:text-gray-600"
                            >
                                ← Previous
                            </span>

                            <Link
                                v-if="users.next_page_url"
                                :href="users.next_page_url"
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                            >
                                Next →
                            </Link>
                            <span
                                v-else
                                class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded text-gray-400 dark:text-gray-600"
                            >
                                Next →
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

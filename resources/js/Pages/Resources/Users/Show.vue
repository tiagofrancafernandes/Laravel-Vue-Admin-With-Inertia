<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ConfirmDeleteModal from '@/Components/AppMaker/Actions/ConfirmDeleteModal.vue';
import Button from '@/Components/Common/Button.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    user: Object,
    pageType: {
        type: String,
        default: 'page',
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const toast = useToast();

const showDeleteModal = ref(false);
const deleteLoading = ref(false);

// Usuário não pode deletar a si mesmo
const canDelete = computed(() => props.user.id !== currentUser.value.id);

const handleDeleteClick = () => {
    if (canDelete.value) {
        showDeleteModal.value = true;
    }
};

const confirmDelete = () => {
    deleteLoading.value = true;

    router.delete(route('users.destroy', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            toast.success('User deleted successfully');
            router.visit(route('users.index'));
        },
        onError: (errors) => {
            deleteLoading.value = false;
            const errorMessage = errors.message || 'Failed to delete user';
            toast.error(errorMessage);
        },
    });
};
</script>

<template>
    <Head :title="user.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ user.name }}
                </h2>
                <Link
                    :href="route('users.edit', user)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Edit User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- User Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Basic Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Name
                                    </label>
                                    <p class="text-gray-900 dark:text-white">{{ user.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Email
                                    </label>
                                    <p class="text-gray-900 dark:text-white">{{ user.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Role
                                    </label>
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-medium inline-block',
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
                                </div>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Account Information
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Created At
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ new Date(user.created_at).toLocaleString() }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Last Updated
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ new Date(user.updated_at).toLocaleString() }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Email Verified
                                    </label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ user.email_verified_at ? 'Yes' : 'No' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t dark:border-gray-700 flex gap-4">
                        <Link
                            :href="route('users.edit', user)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Edit User
                        </Link>
                        <Button
                            v-if="canDelete"
                            variant="danger"
                            @click="handleDeleteClick"
                        >
                            Delete User
                        </Button>
                        <Link
                            :href="route('users.index')"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                        >
                            Back to Users
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmação de delete -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Delete User"
            message="Are you sure you want to delete this user? This action cannot be undone."
            :item-name="user.name"
            :loading="deleteLoading"
            @close="showDeleteModal = false"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

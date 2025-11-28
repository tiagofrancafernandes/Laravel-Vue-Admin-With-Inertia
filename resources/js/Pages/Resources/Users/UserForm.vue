<script setup>
import {
    computed,
    ref,
} from 'vue';

import {
    ifArray,
    ifObject,
} from '@/Utils/helpers';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    availableRoles: {
        type: Array,
        default: [],
    },
});

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    role: props.user?.role || 'user',
});

const showPassword = ref(false);

const submit = () => {
    if (props.isEditing) {
        form.patch(route('users.update', props.user.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('users.store'), {
            preserveScroll: true,
        });
    }
};

const roles = computed(() => {
    return (ifArray(props?.availableRoles) || [])?.map((i) => {
        i = ifObject(i) || { value: i };

        let key = i?.key || i?.name || i?.value;
        let value = i?.value || i?.name || key;
        let label = i?.label || value;

        return {
            ...i,
            key,
            value,
            label,
        };
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
            <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
            </p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                {{ form.errors.email }}
            </p>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ isEditing ? 'New Password (leave blank to keep current)' : 'Password' }}
            </label>
            <div class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-500': form.errors.password }"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-2.5 text-gray-600 dark:text-gray-400"
                >
                    {{ showPassword ? '🙈' : '👁️' }}
                </button>
            </div>
            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                {{ form.errors.password }}
            </p>
        </div>

        <!-- Password Confirmation -->
        <div v-if="form.password">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
            <input
                v-model="form.password_confirmation"
                type="password"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
            />
        </div>

        <!-- Role -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
            <select
                v-model="form.role"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': form.errors.role }"
            >
                <!-- <option value="user">User</option> -->
                <template v-for="(role, roleIndex) in roles" :key="roleIndex">
                    <option :value="role?.value">{{ role?.label }}</option>
                </template>
            </select>
            <p v-if="form.errors.role" class="text-red-500 text-sm mt-1">
                {{ form.errors.role }}
            </p>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4">
            <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
                {{ form.processing ? 'Saving...' : isEditing ? 'Update User' : 'Create User' }}
            </button>
            <button
                type="button"
                @click="$router.back()"
                class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
                Cancel
            </button>
        </div>
    </form>
</template>

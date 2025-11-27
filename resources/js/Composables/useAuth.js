import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user || null);

    const can = (permission) => {
        return user.value?.can?.[permission] || false;
    };

    const isAdmin = computed(() => user.value?.role === 'admin');

    const isUser = computed(() => user.value?.role === 'user');

    const canManageUsers = computed(() => isAdmin.value);

    return {
        user,
        can,
        isAdmin,
        isUser,
        canManageUsers,
    };
}

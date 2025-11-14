import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user || null);

    const can = (permission) => {
        return user.value?.can?.[permission] || false;
    };

    const isSuperAdmin = computed(
        () => user.value?.type === 'super_admin'
    );

    const isAttendant = computed(() => user.value?.type === 'attendant');

    const isClient = computed(() => user.value?.type === 'client');

    const canManageUsers = computed(() => isSuperAdmin.value);

    const canManageSales = computed(
        () => isSuperAdmin.value || isAttendant.value
    );

    const canManageClients = computed(
        () => isSuperAdmin.value || isAttendant.value
    );

    const canCancelSales = computed(() => isSuperAdmin.value);

    return {
        user,
        can,
        isSuperAdmin,
        isAttendant,
        isClient,
        canManageUsers,
        canManageSales,
        canManageClients,
        canCancelSales,
    };
}

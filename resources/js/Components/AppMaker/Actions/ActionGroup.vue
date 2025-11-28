<template>
    <div class="flex items-center gap-2">
        <template v-for="action in visibleActions" :key="action.name">
            <!-- Link Action -->
            <ActionLink
                v-if="action.type === 'link'"
                :action="action"
                :record="record"
                :resource="resource"
            />

            <!-- Delete Action (com modal de confirmação) -->
            <button
                v-else-if="action.type === 'delete'"
                type="button"
                :class="getActionClass(action)"
                @click="handleDeleteClick(action)"
            >
                <component :is="action.icon" v-if="action.icon" class="w-4 h-4 mr-1 inline-block" />
                {{ action.label }}
            </button>

            <!-- Button Action (emite evento) -->
            <button
                v-else-if="action.type === 'button' || action.type === 'emit'"
                type="button"
                :class="getActionClass(action)"
                @click="handleButtonClick(action)"
            >
                <component :is="action.icon" v-if="action.icon" class="w-4 h-4 mr-1 inline-block" />
                {{ action.label }}
            </button>
        </template>

        <!-- Modal de confirmação de delete -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            :title="pendingDeleteAction?.confirmTitle || 'Confirm Delete'"
            :message="pendingDeleteAction?.confirmMessage || 'Are you sure you want to delete this item?'"
            :item-name="getItemName()"
            :loading="deleteLoading"
            @close="closeDeleteModal"
            @confirm="confirmDelete"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ActionLink from './ActionLink.vue';
import ConfirmDeleteModal from './ConfirmDeleteModal.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    actions: {
        type: Array,
        required: true,
    },
    record: {
        type: Object,
        required: true,
    },
    resource: {
        type: String,
        default: '',
    },
    itemNameKey: {
        type: String,
        default: 'name',
    },
});

const emit = defineEmits(['action']);
const toast = useToast();

const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const pendingDeleteAction = ref(null);

// Filtra actions baseado em condições
const visibleActions = computed(() => {
    return props.actions.filter((action) => {
        // Se tem condição, verifica
        if (action.condition && typeof action.condition === 'function') {
            return action.condition(props.record);
        }
        return true;
    });
});

// Retorna classes CSS para actions baseado em color
const getActionClass = (action) => {
    const baseClasses = 'text-sm font-medium transition-colors';

    const colorClasses = {
        blue: 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300',
        green: 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300',
        red: 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300',
        yellow: 'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300',
        gray: 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300',
    };

    const color = action.color || 'gray';

    return `${baseClasses} ${colorClasses[color] || colorClasses.gray}`;
};

// Retorna o nome do item para exibir na modal
const getItemName = () => {
    if (!props.record) return '';
    return props.record[props.itemNameKey] || '';
};

// Abre modal de delete
const handleDeleteClick = (action) => {
    pendingDeleteAction.value = action;
    showDeleteModal.value = true;
};

// Fecha modal de delete
const closeDeleteModal = () => {
    if (!deleteLoading.value) {
        showDeleteModal.value = false;
        pendingDeleteAction.value = null;
    }
};

// Confirma delete
const confirmDelete = () => {
    if (!pendingDeleteAction.value) return;

    deleteLoading.value = true;

    const deleteRoute = pendingDeleteAction.value.route
        ? route(pendingDeleteAction.value.route, props.record.id)
        : route(`${props.resource}.destroy`, props.record.id);

    router.delete(deleteRoute, {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            pendingDeleteAction.value = null;
            toast.success('Item deleted successfully');

            // Callback de sucesso, se existir
            if (pendingDeleteAction.value?.onSuccess) {
                pendingDeleteAction.value.onSuccess();
            }

            emit('action', pendingDeleteAction.value, props.record.id);
        },
        onError: (errors) => {
            deleteLoading.value = false;
            const errorMessage = errors.message || 'Failed to delete item';
            toast.error(errorMessage);

            // Callback de erro, se existir
            if (pendingDeleteAction.value?.onError) {
                pendingDeleteAction.value.onError(errors);
            }
        },
    });
};

// Handle button click
const handleButtonClick = (action) => {
    // Executa onClick callback se existir
    if (action.onClick && typeof action.onClick === 'function') {
        action.onClick(props.record);
    }

    // Emite evento para parent
    emit('action', action, props.record.id);
};
</script>

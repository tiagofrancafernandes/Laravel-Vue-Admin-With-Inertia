<template>
    <Modal :show="show" :closeable="!loading" :close-on-backdrop="!loading" max-width="md" @close="handleClose">
        <div class="p-6">
            <!-- Icon and Title -->
            <div class="flex items-start gap-4 mb-6">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center"
                >
                    <svg
                        class="w-6 h-6 text-red-600 dark:text-red-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        {{ title }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ message }}
                    </p>
                    <p v-if="itemName" class="text-sm text-gray-900 dark:text-gray-100 font-medium mt-2">
                        "{{ itemName }}"
                    </p>
                </div>
            </div>

            <!-- Warning Message -->
            <div class="mb-6 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    ⚠️ This action cannot be undone.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <Button variant="ghost" :disabled="loading" @click="handleClose">
                    Cancel
                </Button>
                <Button variant="danger" :loading="loading" @click="handleConfirm">
                    {{ confirmButtonText }}
                </Button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Common/Modal.vue';
import Button from '@/Components/Common/Button.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Delete',
    },
    message: {
        type: String,
        default: 'Are you sure you want to delete this item?',
    },
    itemName: {
        type: String,
        default: '',
    },
    confirmButtonText: {
        type: String,
        default: 'Delete',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);

const handleClose = () => {
    if (!props.loading) {
        emit('close');
    }
};

const handleConfirm = () => {
    emit('confirm');
};
</script>

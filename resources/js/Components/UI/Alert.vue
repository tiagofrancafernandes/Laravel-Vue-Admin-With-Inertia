<template>
    <div :class="['rounded-md p-4 flex items-start space-x-3', typeClasses]" role="alert">
        <div :class="['flex-shrink-0', iconColorClass]">
            <svg v-if="type === 'success'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                />
            </svg>
            <svg v-else-if="type === 'error'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd"
                />
            </svg>
            <svg v-else-if="type === 'warning'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                    fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                />
            </svg>
        </div>
        <div class="flex-1">
            <p :class="['text-sm font-medium', textColorClass]">
                {{ message }}
            </p>
        </div>
        <button v-if="closable" @click="close" :class="['text-sm', buttonColorClass]">✕</button>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    type?: 'success' | 'error' | 'warning' | 'info';
    message: string;
    closable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    closable: true,
});

const isVisible = ref(true);

const typeClasses = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-50 dark:bg-green-900/20';
        case 'error':
            return 'bg-red-50 dark:bg-red-900/20';
        case 'warning':
            return 'bg-yellow-50 dark:bg-yellow-900/20';
        default:
            return 'bg-blue-50 dark:bg-blue-900/20';
    }
});

const textColorClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-800 dark:text-green-200';
        case 'error':
            return 'text-red-800 dark:text-red-200';
        case 'warning':
            return 'text-yellow-800 dark:text-yellow-200';
        default:
            return 'text-blue-800 dark:text-blue-200';
    }
});

const iconColorClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-400 dark:text-green-500';
        case 'error':
            return 'text-red-400 dark:text-red-500';
        case 'warning':
            return 'text-yellow-400 dark:text-yellow-500';
        default:
            return 'text-blue-400 dark:text-blue-500';
    }
});

const buttonColorClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-400 hover:text-green-600';
        case 'error':
            return 'text-red-400 hover:text-red-600';
        case 'warning':
            return 'text-yellow-400 hover:text-yellow-600';
        default:
            return 'text-blue-400 hover:text-blue-600';
    }
});

const close = () => {
    isVisible.value = false;
};
</script>

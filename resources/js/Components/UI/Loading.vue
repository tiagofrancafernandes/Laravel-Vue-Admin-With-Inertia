<template>
    <div
        v-if="visible"
        :class="['flex items-center justify-center', fullScreen ? 'fixed inset-0 bg-black bg-opacity-50 z-50' : 'p-4']"
    >
        <div class="flex flex-col items-center">
            <!-- Spinner -->
            <div class="relative w-12 h-12">
                <div class="absolute inset-0 rounded-full border-4 border-gray-300 dark:border-gray-600"></div>
                <div
                    class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-600 dark:border-t-blue-400 animate-spin"
                ></div>
            </div>

            <!-- Loading Text -->
            <p v-if="text" class="mt-4 text-gray-700 dark:text-gray-300 font-medium">
                {{ text }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    visible?: boolean;
    text?: string;
    fullScreen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    visible: true,
    fullScreen: false,
});
</script>

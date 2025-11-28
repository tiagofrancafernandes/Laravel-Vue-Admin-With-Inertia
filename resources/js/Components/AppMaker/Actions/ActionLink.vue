<template>
    <Link
        :href="computedHref"
        :method="action.method || 'get'"
        :as="action.method && action.method !== 'get' ? 'button' : 'a'"
        :class="linkClasses"
        :preserve-scroll="action.preserveScroll !== false"
        :preserve-state="action.preserveState || false"
    >
        <component :is="action.icon" v-if="action.icon" class="w-4 h-4 mr-1 inline-block" />
        {{ action.label }}
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    action: {
        type: Object,
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
});

const computedHref = computed(() => {
    // Se tem route definida, usa ela
    if (props.action.route) {
        // Se a route já é uma URL completa
        if (props.action.route.startsWith('http') || props.action.route.startsWith('/')) {
            return props.action.route;
        }

        // Se precisa do ID do record
        if (props.action.route.includes('.show') || props.action.route.includes('.edit')) {
            return route(props.action.route, props.record.id);
        }

        // Route simples
        return route(props.action.route);
    }

    // Fallback: usa o resource + nome da action
    return route(`${props.resource}.${props.action.name}`, props.record.id);
});

const linkClasses = computed(() => {
    const baseClasses = 'text-sm font-medium transition-colors';

    // Mapeia color para classes Tailwind
    const colorClasses = {
        blue: 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300',
        green: 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300',
        red: 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300',
        yellow: 'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300',
        gray: 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300',
    };

    const color = props.action.color || 'gray';

    return `${baseClasses} ${colorClasses[color] || colorClasses.gray}`;
});
</script>

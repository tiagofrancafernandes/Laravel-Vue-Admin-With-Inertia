<template>
    <div class="relative">
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Search Input -->
            <div class="flex-1 relative">
                <Input
                    v-model="search"
                    type="text"
                    :placeholder="placeholder"
                    @input="onSearch"
                    @focus="showDropdown = true"
                />

                <!-- Dropdown -->
                <div
                    v-if="showDropdown && clients.length > 0"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 md:max-h-60 overflow-auto"
                >
                    <button
                        v-for="client in clients"
                        :key="client.id"
                        type="button"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors"
                        @click="selectClient(client)"
                    >
                        <div class="font-medium text-gray-900">
                            {{ client.name }}<span v-if="client.phone" class="text-gray-600 font-normal"> - {{ formatPhone(client.phone) }}</span>
                        </div>
                        <div v-if="client.email" class="text-sm text-gray-500 mt-1">
                            {{ client.email }}
                        </div>
                        <div v-if="client.balance" class="text-xs text-gray-400 mt-1 flex gap-4">
                            <span class="text-green-600">
                                Saldo: {{ formatCurrency(client.balance.balance) }}
                            </span>
                            <span v-if="client.balance.credit_limit > 0" class="text-red-600">
                                Limite: {{ formatCurrency(client.balance.credit_limit) }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- New Client Button -->
            <Button type="button" variant="secondary" @click="openNewClientModal" class="w-full md:w-auto">+ Novo</Button>
        </div>

        <!-- Selected Client Display -->
        <div v-if="selectedClient" class="mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-900">
                        {{ selectedClient.name }}
                        <span v-if="selectedClient.phone" class="text-gray-600 font-normal">
                            - {{ formatPhone(selectedClient.phone) }}
                        </span>
                    </div>
                    <div v-if="selectedClient.email" class="text-sm text-gray-600">
                        {{ selectedClient.email }}
                    </div>
                </div>
                <Button type="button" variant="ghost" size="sm" @click="clearSelection">Remover</Button>
            </div>
        </div>

        <!-- Modal for New Client -->
        <ClientModal v-model="showModal" @created="onClientCreated" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useDebounce } from '@/Composables/useDebounce';
import { useClientBalance } from '@/Composables/useClientBalance';
import { formatCurrency, formatPhone } from '@/Utils/helpers';
import Input from '@/Components/Common/Input.vue';
import Button from '@/Components/Common/Button.vue';
import ClientModal from './ClientModal.vue';

const props = defineProps({
    modelValue: Number,
    defaultValue: Number,
    placeholder: {
        type: String,
        default: 'Buscar cliente...',
    },
});

const emit = defineEmits(['update:modelValue', 'balance-loaded']);

const search = ref('');
const clients = ref([]);
const selectedClient = ref(null);
const showDropdown = ref(false);
const showModal = ref(false);

const { balance, loading: balanceLoading, fetchBalance } = useClientBalance();

const onSearch = useDebounce(async () => {
    if (search.value.length < 2) {
        clients.value = [];
        return;
    }

    try {
        const response = await axios.get(route('api.clients.select'), {
            params: { search: search.value },
        });
        clients.value = response.data.data || [];
    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
        clients.value = [];
    }
}, 300);

const selectClient = async (client) => {
    selectedClient.value = client;
    emit('update:modelValue', client.id);
    showDropdown.value = false;
    search.value = '';

    // Load client balance in the background
    await fetchBalance(client.id);
    emit('balance-loaded', balance.value);
};

const clearSelection = () => {
    selectedClient.value = null;
    emit('update:modelValue', props.defaultValue);
    // Reset balance when clearing selection
    balance.value = null;
};

const openNewClientModal = () => {
    showModal.value = true;
};

const onClientCreated = (client) => {
    selectClient(client);
    showModal.value = false;
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="mb-4">
    <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <input
        :id="id"
        v-model="searchQuery"
        @input="onSearch"
        @focus="showDropdown = true"
        @blur="closeDropdown"
        :placeholder="placeholder"
        :class="[
          'w-full px-3 py-2 border rounded-lg',
          'bg-white dark:bg-gray-800',
          'text-gray-900 dark:text-gray-100',
          'focus:outline-none focus:ring-2 focus:ring-blue-500',
          error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
        ]"
      />
      <div
        v-if="showDropdown && filteredClients.length > 0"
        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
      >
        <div
          v-for="client in filteredClients"
          :key="client.id"
          @click="selectClient(client)"
          class="px-3 py-2 hover:bg-blue-100 dark:hover:bg-blue-900 cursor-pointer"
        >
          <div class="font-medium text-gray-900 dark:text-gray-100">
            {{ client.name }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ client.email || client.phone || "Sem contato" }}
          </div>
        </div>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-500 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from "vue";

interface Client {
  id: number;
  name: string;
  email?: string;
  phone?: string;
}

interface Props {
  modelValue: number | null;
  label?: string;
  placeholder?: string;
  required?: boolean;
  error?: string;
  id?: string;
}

const props = withDefaults(defineProps<Props>(), {
  label: "Cliente",
  placeholder: "Pesquisar cliente...",
  id: () => Math.random().toString(36).substr(2, 9),
  required: false,
});

const emit = defineEmits<{
  "update:modelValue": [value: number | null];
}>();

const searchQuery = ref("");
const showDropdown = ref(false);
const allClients = ref<Client[]>([]);
const loading = ref(false);

const filteredClients = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allClients.value.filter(
    (client) =>
      client.name.toLowerCase().includes(query) ||
      (client.email && client.email.toLowerCase().includes(query)) ||
      (client.phone && client.phone.toLowerCase().includes(query))
  );
});

const onSearch = async () => {
  if (searchQuery.value.length === 0) {
    allClients.value = [];
    return;
  }

  loading.value = true;
  try {
    const response = await fetch(
      `/api/clients/select?search=${encodeURIComponent(searchQuery.value)}`
    );
    const data = await response.json();
    allClients.value = data;
  } catch (error) {
    console.error("Error fetching clients:", error);
  } finally {
    loading.value = false;
  }
};

const selectClient = (client: Client) => {
  emit("update:modelValue", client.id);
  searchQuery.value = client.name;
  showDropdown.value = false;
};

const closeDropdown = () => {
  nextTick(() => {
    showDropdown.value = false;
  });
};
</script>

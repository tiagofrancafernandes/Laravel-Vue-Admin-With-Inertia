import { ref } from 'vue';
import axios from 'axios';

export function useClientBalance() {
    const balance = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchBalance = async (clientId) => {
        if (!clientId) {
            balance.value = null;
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(
                route('api.clients.balance', clientId)
            );
            balance.value = response.data;
        } catch (err) {
            error.value = err.message;
            console.error('Erro ao buscar saldo:', err);
        } finally {
            loading.value = false;
        }
    };

    const reset = () => {
        balance.value = null;
        error.value = null;
        loading.value = false;
    };

    return {
        balance,
        loading,
        error,
        fetchBalance,
        reset,
    };
}

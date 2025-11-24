import { ref, computed } from 'vue';

export function usePaymentSplit(totalAmount) {
    const payments = ref([]);

    const totalPaid = computed(() => {
        return payments.value.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
    });

    const remaining = computed(() => {
        return totalAmount.value - totalPaid.value;
    });

    const isValid = computed(() => {
        return Math.abs(remaining.value) < 0.01 && payments.value.length > 0;
    });

    const addPayment = (paymentMethodId = '', amount = 0) => {
        payments.value.push({
            payment_method_id: paymentMethodId,
            amount,
            metadata: {},
        });
    };

    const removePayment = (index) => {
        payments.value.splice(index, 1);
    };

    const reset = () => {
        payments.value = [];
    };

    const initialize = (initialPayments = []) => {
        payments.value =
            initialPayments.length > 0
                ? [...initialPayments]
                : [
                      {
                          payment_method_id: '',
                          amount: totalAmount.value,
                          metadata: {},
                      },
                  ];
    };

    return {
        payments,
        totalPaid,
        remaining,
        isValid,
        addPayment,
        removePayment,
        reset,
        initialize,
    };
}

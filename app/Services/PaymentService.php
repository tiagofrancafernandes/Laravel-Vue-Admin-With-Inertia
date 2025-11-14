<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {
    }

    /**
     * Process a payment for a sale.
     *
     * @param Sale $sale
     * @param array $paymentData
     * @return SalePayment
     */
    public function processPayment(Sale $sale, array $paymentData): SalePayment
    {
        return DB::transaction(function () use ($sale, $paymentData) {
            $amount = $paymentData['amount'];
            $methodId = $paymentData['payment_method_id'];

            // Create payment record
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'payment_method_id' => $methodId,
                'amount' => $amount,
                'received_amount' => $paymentData['received_amount'] ?? null,
                'change_amount' => $paymentData['change_amount'] ?? null,
                'metadata' => $paymentData['metadata'] ?? [],
            ]);

            // Get payment method
            $method = $payment->paymentMethod;

            // Process based on payment method code
            match ($method->code) {
                'balance' => $this->balanceService->debitBalance(
                    $sale->client_id,
                    $amount,
                    "Pagamento da venda {$sale->sale_number}",
                    $sale->id
                ),
                'account' => $this->balanceService->addTabDebit(
                    $sale->client_id,
                    $amount,
                    "Compra a prazo - venda {$sale->sale_number}",
                    $sale->id
                ),
                'cash' => $this->processCashPayment($sale, $paymentData),
                default => null,
            };

            return $payment;
        });
    }

    /**
     * Process cash payment (handles change if applicable).
     *
     * @param Sale $sale
     * @param array $paymentData
     * @return void
     */
    private function processCashPayment(Sale $sale, array $paymentData): void
    {
        $change = $paymentData['change_amount'] ?? 0;

        // If there's change and we should add as balance
        if ($change > 0 && ($paymentData['add_change_as_balance'] ?? false)) {
            $this->balanceService->addBalance(
                $sale->client_id,
                $change,
                "Troco de venda {$sale->sale_number}",
                $sale->id
            );
        }
    }

    /**
     * Reverse a payment (for sale cancellation).
     *
     * @param Sale $sale
     * @param SalePayment $payment
     * @return void
     */
    public function reversePayment(Sale $sale, SalePayment $payment): void
    {
        DB::transaction(function () use ($sale, $payment) {
            $method = $payment->paymentMethod;
            $amount = $payment->amount;

            // Reverse based on payment method code
            match ($method->code) {
                'balance' => $this->balanceService->addBalance(
                    $sale->client_id,
                    $amount,
                    "Estorno da venda {$sale->sale_number}",
                    $sale->id
                ),
                'account' => $this->balanceService->payTab(
                    $sale->client_id,
                    $amount,
                    "Estorno da venda {$sale->sale_number}",
                    $sale->id
                ),
                'cash' => null, // No reversal needed for cash
                default => null,
            };
        });
    }
}

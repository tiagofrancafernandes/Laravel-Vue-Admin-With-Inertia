<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BalanceService $balanceService
    ) {
    }

    /**
     * Create a new sale with payments.
     *
     * @param array $data
     * @return Sale
     */
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Use anonymous client if not specified
            $clientId = $data['client_id'] ?? $this->getAnonymousClientId();

            // Create sale
            $sale = Sale::create([
                'sale_number' => $this->generateCode(),
                'client_id' => $clientId,
                'user_id' => auth()->id(),
                'items' => $data['items'] ?? [],
                'subtotal' => $data['subtotal'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'total_amount' => $data['total_amount'] ?? $data['total'] ?? 0,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Calculate total payments
            $totalPayments = collect($data['payments'] ?? [])->sum('amount');
            $saleTotal = (float) $sale->total_amount;

            // Process payments
            foreach ($data['payments'] as $paymentData) {
                $payment = $this->paymentService->processPayment($sale, $paymentData);
            }

            // Handle payment discrepancies
            $difference = $totalPayments - $saleTotal;

            // If payment is less than total, add to client's credit/tab
            if ($difference < -0.01) {
                $debitAmount = abs($difference);
                $this->balanceService->addTabDebit(
                    $sale->client_id,
                    $debitAmount,
                    "Compra a prazo - venda {$sale->sale_number}",
                    $sale->id
                );
            }
            // If payment is more than total, handle change
            elseif ($difference > 0.01) {
                if ($data['payments'][0]['use_change_for_credit'] ?? false) {
                    // Use change to pay off credit/tab first, then add remainder as balance
                    $amountPaidToTab = $this->balanceService->payTab(
                        $sale->client_id,
                        $difference,
                        "Quitar crédito - venda {$sale->sale_number}",
                        $sale->id
                    );

                    // If there's a remainder after paying tab, add it as balance
                    $remainder = $difference - $amountPaidToTab;

                    if ($remainder > 0.01) {
                        $this->balanceService->addBalance(
                            $sale->client_id,
                            $remainder,
                            "Saldo do troco após quitar crédito - venda {$sale->sale_number}",
                            $sale->id
                        );
                    }
                } elseif ($data['payments'][0]['add_change_as_balance'] ?? false) {
                    // Add change as customer balance/prepaid (no credit limit validation applies here)
                    $this->balanceService->addBalance(
                        $sale->client_id,
                        $difference,
                        "Troco adicionado como saldo - venda {$sale->sale_number}",
                        $sale->id
                    );
                }
            }

            return $sale->load(['client', 'user', 'payments.paymentMethod']);
        });
    }

    /**
     * Cancel a sale and reverse transactions.
     *
     * @param Sale $sale
     * @return void
     */
    public function cancelSale(Sale $sale): void
    {
        if ($sale->status === 'cancelled') {
            throw new \Exception('Sale is already cancelled');
        }

        DB::transaction(function () use ($sale) {
            // Reverse all payments
            foreach ($sale->payments as $payment) {
                $this->paymentService->reversePayment($sale, $payment);
            }

            // Update sale status
            $sale->update(['status' => 'cancelled']);
        });
    }

    /**
     * Generate sequential sale code in VENDA-YYYYNNNN format.
     *
     * @return string
     */
    protected function generateCode(): string
    {
        $year = now()->year;
        $prefix = "VENDA-{$year}";

        $lastSale = Sale::where('sale_number', 'like', "{$prefix}%")
            ->orderBy('sale_number', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastSale) {
            $lastSequence = (int) substr($lastSale->sale_number, -4);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s%04d', $prefix, $sequence);
    }

    /**
     * Get anonymous client ID.
     *
     * @return int
     */
    protected function getAnonymousClientId(): int
    {
        return Client::where('name', 'Anônimo')->firstOrFail()->id;
    }
}

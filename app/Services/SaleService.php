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
                'total' => $data['total'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Process payments
            $totalPaid = 0;

            foreach ($data['payments'] as $paymentData) {
                $payment = $this->paymentService->processPayment($sale, $paymentData);
                $totalPaid += (float) $payment->amount;
            }

            // Validate total paid
            if (abs($totalPaid - (float) $sale->total) > 0.01) {
                throw new \Exception('Total paid does not match sale total');
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

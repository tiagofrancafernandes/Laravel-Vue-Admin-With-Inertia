<?php

namespace App\Services;

use App\Models\ClientBalance;
use App\Models\ClientLedger;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Add balance to a client account.
     *
     * @param int $clientId
     * @param float $amount
     * @param string $description
     * @param int|null $saleId
     * @return void
     */
    public function addBalance(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)
                ->lockForUpdate()
                ->firstOrFail();

            $previousBalance = (float) $balance->balance;
            $balance->increment('balance', $amount);
            $balance->touch('last_transaction_at');

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'user_id' => auth()->id(),
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $previousBalance,
                'balance_after' => $previousBalance + $amount,
                'description' => $description,
            ]);
        });
    }

    /**
     * Debit balance from client account.
     *
     * @param int $clientId
     * @param float $amount
     * @param string $description
     * @param int|null $saleId
     * @return void
     */
    public function debitBalance(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)
                ->lockForUpdate()
                ->firstOrFail();

            $currentBalance = (float) $balance->balance;

            if ($currentBalance < $amount) {
                throw new \Exception("Insufficient balance. Available: {$currentBalance}, Required: {$amount}");
            }

            $balance->decrement('balance', $amount);
            $balance->touch('last_transaction_at');

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'user_id' => auth()->id(),
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance - $amount,
                'description' => $description,
            ]);
        });
    }

    /**
     * Add debt to client's tab (caderneta).
     *
     * @param int $clientId
     * @param float $amount
     * @param string $description
     * @param int|null $saleId
     * @return void
     */
    public function addTabDebit(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)
                ->lockForUpdate()
                ->firstOrFail();

            $previousTabBalance = (float) $balance->credit_limit;
            $balance->increment('credit_limit', $amount);
            $balance->touch('last_transaction_at');

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'user_id' => auth()->id(),
                'type' => 'tab_debit',
                'amount' => $amount,
                'balance_before' => $previousTabBalance,
                'balance_after' => $previousTabBalance + $amount,
                'description' => $description,
            ]);
        });
    }

    /**
     * Pay off client's tab debt.
     *
     * @param int $clientId
     * @param float $amount
     * @param string $description
     * @param int|null $saleId
     * @return void
     */
    public function payTab(int $clientId, float $amount, string $description, ?int $saleId = null): void
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        DB::transaction(function () use ($clientId, $amount, $description, $saleId) {
            $balance = ClientBalance::where('client_id', $clientId)
                ->lockForUpdate()
                ->firstOrFail();

            $currentTab = (float) $balance->credit_limit;

            if ($currentTab < $amount) {
                throw new \Exception("Payment exceeds tab balance. Tab: {$currentTab}, Payment: {$amount}");
            }

            $balance->decrement('credit_limit', $amount);
            $balance->touch('last_transaction_at');

            ClientLedger::create([
                'client_id' => $clientId,
                'sale_id' => $saleId,
                'user_id' => auth()->id(),
                'type' => 'tab_credit',
                'amount' => $amount,
                'balance_before' => $currentTab,
                'balance_after' => $currentTab - $amount,
                'description' => $description,
            ]);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBalance extends Model
{
    protected $table = 'client_balances';

    protected $fillable = [
        'client_id',
        'balance',
        'credit_limit',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'last_transaction_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client associated with this balance.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Check if client has sufficient balance for a transaction.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    /**
     * Check if client has sufficient credit limit.
     */
    public function hasSufficientCreditLimit(float $amount): bool
    {
        return (float) $this->credit_limit >= $amount;
    }
}

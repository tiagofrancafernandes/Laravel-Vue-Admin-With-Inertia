<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $client_id
 * @property numeric $balance
 * @property numeric $credit_limit
 * @property \Illuminate\Support\Carbon|null $last_transaction_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereCreditLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereLastTransactionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

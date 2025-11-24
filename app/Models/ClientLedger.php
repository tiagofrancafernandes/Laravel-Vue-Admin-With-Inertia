<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property int|null $sale_id
 * @property string $type
 * @property numeric $amount
 * @property numeric $balance_before
 * @property numeric $balance_after
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @property-read Sale|null $sale
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger credit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger debit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger tabCredit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger tabDebit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLedger whereUserId($value)
 * @mixin \Eloquent
 */
class ClientLedger extends Model
{
    protected $table = 'client_ledger';

    protected $fillable = [
        'client_id',
        'user_id',
        'sale_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client associated with this ledger entry.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created this ledger entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale associated with this ledger entry.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Scope to filter by credit transactions.
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope to filter by debit transactions.
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope to filter by tab debit transactions.
     */
    public function scopeTabDebit($query)
    {
        return $query->where('type', 'tab_debit');
    }

    /**
     * Scope to filter by tab credit transactions.
     */
    public function scopeTabCredit($query)
    {
        return $query->where('type', 'tab_credit');
    }
}

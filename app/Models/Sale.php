<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $sale_number
 * @property int $client_id
 * @property int $user_id
 * @property array<array-key, mixed> $items
 * @property numeric $subtotal
 * @property numeric $discount
 * @property string $total_amount
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ClientLedger> $ledgerEntries
 * @property-read int|null $ledger_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SalePayment> $payments
 * @property-read int|null $payments_count
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSaleNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereUserId($value)
 * @mixin \Eloquent
 */
class Sale extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique code for the sale.
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = 'S' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'sale_number',
        'client_id',
        'user_id',
        'items',
        'subtotal',
        'discount',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'items' => 'json',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client associated with this sale.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created this sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all payments for this sale.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Get all ledger entries for this sale.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(ClientLedger::class);
    }

    /**
     * Scope to filter completed sales.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter cancelled sales.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get total amount paid on this sale.
     */
    public function getTotalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}

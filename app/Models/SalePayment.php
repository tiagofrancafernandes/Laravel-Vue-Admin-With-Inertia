<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $payment_method_id
 * @property numeric $amount
 * @property numeric|null $received_amount
 * @property numeric|null $change_amount
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read PaymentMethod $paymentMethod
 * @property-read Sale $sale
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereChangeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereReceivedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalePayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalePayment extends Model
{
    protected $table = 'sale_payments';

    protected $fillable = [
        'sale_id',
        'payment_method_id',
        'amount',
        'received_amount',
        'change_amount',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sale associated with this payment.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the payment method used.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Check if this is a complete payment (received equals amount).
     */
    public function isCompletePayment(): bool
    {
        return abs((float) $this->received_amount - (float) $this->amount) < 0.01;
    }

    /**
     * Calculate change amount based on received vs amount.
     */
    public function calculateChange(): float
    {
        if ($this->received_amount === null) {
            return 0;
        }

        return max(0, (float) $this->received_amount - (float) $this->amount);
    }
}

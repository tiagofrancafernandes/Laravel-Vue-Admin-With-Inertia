<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

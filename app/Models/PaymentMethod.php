<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'display_order',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all payments using this method.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Check if this is a balance payment method.
     */
    public function isBalanceType(): bool
    {
        return $this->code === 'balance';
    }

    /**
     * Check if this is an account (caderneta) payment method.
     */
    public function isAccountType(): bool
    {
        return $this->code === 'account';
    }

    /**
     * Check if this is a cash payment method.
     */
    public function isCashType(): bool
    {
        return $this->code === 'cash';
    }
}

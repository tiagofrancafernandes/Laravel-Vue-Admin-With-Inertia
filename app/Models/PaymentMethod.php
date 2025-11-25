<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property bool $requires_client
 * @property int $display_order
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SalePayment> $payments
 * @property-read int|null $payments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereRequiresClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique code for the payment method.
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(3));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'requires_client',
        'display_order',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_client' => 'boolean',
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

    /**
     * Check if this payment method requires a client to be selected.
     */
    public function requiresClient(): bool
    {
        return (bool) $this->requires_client;
    }

    /**
     * Get a user-friendly label for this payment method.
     */
    public function getLabel(): string
    {
        $label = $this->name;

        if ($this->requiresClient()) {
            $label .= ' *';
        }

        return $label;
    }
}

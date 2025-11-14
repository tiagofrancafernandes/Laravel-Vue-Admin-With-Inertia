<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf_cnpj',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the balance record for this client.
     */
    public function balance(): HasOne
    {
        return $this->hasOne(ClientBalance::class);
    }

    /**
     * Get all sales for this client.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all ledger entries for this client.
     */
    public function ledger(): HasMany
    {
        return $this->hasMany(ClientLedger::class);
    }

    /**
     * Get current balance (saldo).
     */
    public function getCurrentBalance(): float
    {
        return (float) ($this->balance?->balance ?? 0);
    }

    /**
     * Get available credit (caderneta).
     */
    public function getAvailableCredit(): float
    {
        return (float) ($this->balance?->credit_limit ?? 0);
    }
}

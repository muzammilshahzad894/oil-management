<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    /**
     * Get the extra paid transactions for the customer.
     */
    public function extraPayments(): HasMany
    {
        return $this->hasMany(CustomerExtraPayment::class);
    }

    /** Get current extra paid balance (sum of all transaction amounts). */
    public function getExtraPaidBalanceAttribute(): float
    {
        return (float) $this->extraPayments()->sum('amount');
    }

    /**
     * Get the sales for the customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get total quantity purchased by customer.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->sales()->sum('quantity');
    }
}

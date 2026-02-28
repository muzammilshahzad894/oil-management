<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerExtraPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'sale_id',
        'payment_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** Get total extra paid balance for a customer (sum of all amounts). */
    public static function balanceForCustomer(int $customerId): float
    {
        return (float) static::where('customer_id', $customerId)->sum('amount');
    }
}

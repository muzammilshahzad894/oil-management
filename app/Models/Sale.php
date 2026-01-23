<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'brand_id',
        'quantity',
        'price',
        'sale_date',
        'is_paid',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'price' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /**
     * Get the customer that owns the sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the brand that owns the sale.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}

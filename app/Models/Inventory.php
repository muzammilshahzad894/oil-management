<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventories';
    
    protected $fillable = [
        'brand_id',
        'quantity',
    ];

    /**
     * Get the brand that owns the inventory.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Add quantity to inventory.
     */
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove quantity from inventory.
     */
    public function removeStock(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
    }
}

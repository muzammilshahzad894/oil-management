<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'cost_price',
        'sale_price',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Get the sales for the brand.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get inventory batches (FIFO stock entries).
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Total available stock from FIFO batches (quantity_remaining).
     */
    public function getAvailableStockAttribute(): int
    {
        return (int) $this->inventoryBatches()->sum('quantity_remaining');
    }

    /**
     * Add quantity to stock (legacy). Prefer adding via inventory batches.
     */
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove quantity from stock (legacy). Sales use FIFO via InventoryService.
     */
    public function removeStock(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
    }
}

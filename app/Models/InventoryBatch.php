<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBatch extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'brand_id',
        'quantity',
        'quantity_remaining',
        'cost_per_unit',
        'sale_price',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'date',
        'cost_per_unit' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function saleBatchAllocations(): HasMany
    {
        return $this->hasMany(SaleBatchAllocation::class, 'inventory_batch_id');
    }

    /** Total quantity already sold from this batch (from allocations). */
    public function getTotalAllocatedAttribute(): int
    {
        return (int) $this->saleBatchAllocations()->sum('quantity');
    }
}

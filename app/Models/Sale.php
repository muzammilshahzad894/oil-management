<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'brand_id',
        'quantity',
        'price',
        'cost_at_sale',
        'sale_date',
        'is_paid',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'price' => 'decimal:2',
        'cost_at_sale' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function saleBatchAllocations(): HasMany
    {
        return $this->hasMany(SaleBatchAllocation::class);
    }

    /** Total amount paid (sum of all payments). */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /** Balance due (sale price - total paid). */
    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->price - $this->total_paid);
    }

    /** Total cost for this sale (cost per unit × quantity). */
    public function getTotalCostAttribute(): ?float
    {
        if ($this->cost_at_sale === null) {
            return null;
        }
        return (float) $this->cost_at_sale * (int) $this->quantity;
    }

    /** Profit for this sale (price - total cost). */
    public function getProfitAttribute(): ?float
    {
        $totalCost = $this->total_cost;
        if ($totalCost === null) {
            return null;
        }
        return (float) $this->price - $totalCost;
    }

    /** Sync is_paid from payments: fully paid when total_paid >= price. */
    public function refreshIsPaid(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->update(['is_paid' => $totalPaid >= (float) $this->price]);
    }
}

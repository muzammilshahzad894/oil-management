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
    ];

    /**
     * Get the sales for the brand.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Add quantity to stock.
     */
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove quantity from stock.
     */
    public function removeStock(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
    }
}

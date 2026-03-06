<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerCustomer extends Model
{
    protected $fillable = ['name', 'phone', 'address'];

    public function transactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class)->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
    }

    /** Amount you will give to this customer (positive balance = we owe them). */
    public function getYouWillGiveAttribute(): float
    {
        $b = $this->balance;
        return $b > 0 ? (float) $b : 0;
    }

    /** Amount you will get from this customer (negative balance = they owe us). */
    public function getYouWillGetAttribute(): float
    {
        $b = $this->balance;
        return $b < 0 ? (float) abs($b) : 0;
    }

    /** Total amount you received from this customer (they gave you). */
    public function getTotalReceivedAttribute(): float
    {
        if ($this->relationLoaded('transactions')) {
            return (float) $this->transactions->where('type', 'received')->sum('amount');
        }
        return (float) $this->transactions()->where('type', 'received')->sum('amount');
    }

    /** Total amount you gave to this customer. */
    public function getTotalGaveAttribute(): float
    {
        if ($this->relationLoaded('transactions')) {
            return (float) $this->transactions->where('type', 'gave')->sum('amount');
        }
        return (float) $this->transactions()->where('type', 'gave')->sum('amount');
    }

    /** Balance = total_received - total_gave. Negative = you gave more → they owe you; positive = you received more → you owe them. */
    public function getBalanceAttribute(): float
    {
        return $this->total_received - $this->total_gave;
    }
}

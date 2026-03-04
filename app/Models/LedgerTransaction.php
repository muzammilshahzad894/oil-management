<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerTransaction extends Model
{
    protected $fillable = ['ledger_customer_id', 'type', 'amount', 'description', 'transaction_date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public const TYPE_RECEIVED = 'received';
    public const TYPE_GAVE = 'gave';

    public function ledgerCustomer(): BelongsTo
    {
        return $this->belongsTo(LedgerCustomer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'contractor_id', 'sale_date', 'sale_price',
        'payment_method', 'payment_credit', 'payment_bank', 'payment_cash_deposit',
        'payment_trade', 'credit_contract_number', 'warranty', 'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sale_price' => 'decimal:2',
        'payment_credit' => 'decimal:2',
        'payment_bank' => 'decimal:2',
        'payment_cash_deposit' => 'decimal:2',
        'payment_trade' => 'decimal:2',
    ];

    public function paymentTotal(): float
    {
        return (float) ($this->payment_credit + $this->payment_bank
            + $this->payment_cash_deposit + $this->payment_trade);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}

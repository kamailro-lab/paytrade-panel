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
        'warranty_months', 'deposit', 'paid_cash', 'paid_bank',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sale_price' => 'decimal:2',
        'payment_credit' => 'decimal:2',
        'payment_bank' => 'decimal:2',
        'payment_cash_deposit' => 'decimal:2',
        'payment_trade' => 'decimal:2',
        'warranty_months' => 'integer',
        'deposit' => 'decimal:2',
        'paid_cash' => 'decimal:2',
        'paid_bank' => 'decimal:2',
    ];

    public function paymentTotal(): float
    {
        return (float) ($this->payment_credit + $this->payment_bank
            + $this->payment_cash_deposit + $this->payment_trade);
    }

    /**
     * Razem zapłacone = depozyt + gotówka + bank (nowe pola).
     */
    public function totalPaid(): float
    {
        return (float) $this->deposit + (float) $this->paid_cash + (float) $this->paid_bank;
    }

    /**
     * Słownik gwarancji w miesiącach.
     */
    public static function warrantyOptions(): array
    {
        return [
            0 => 'Bez gwarancji',
            1 => '1 miesiąc',
            3 => '3 miesiące',
            6 => '6 miesięcy',
            12 => '12 miesięcy (1 rok)',
            24 => '24 miesiące (2 lata)',
            36 => '36 miesięcy (3 lata)',
        ];
    }

    public function warrantyLabel(): string
    {
        return self::warrantyOptions()[$this->warranty_months ?? 0] ?? "{$this->warranty_months} mies.";
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }
}

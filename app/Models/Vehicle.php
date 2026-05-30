<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration', 'logbook_no', 'make', 'model', 'year', 'engine_cc',
        'fuel', 'color', 'mileage_km', 'body', 'doors', 'status',
        'nct_expiry', 'photos', 'notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'nct_expiry' => 'date',
        'year' => 'integer',
        'engine_cc' => 'integer',
        'mileage_km' => 'integer',
        'doors' => 'integer',
    ];

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function totalCost(): float
    {
        $purchase = (float) ($this->purchase?->purchase_price ?? 0);
        $vrt = (float) ($this->purchase?->vrt_paid ?? 0);
        $transport = (float) ($this->purchase?->transport_cost ?? 0);
        $costs = (float) $this->costs->sum('amount');
        return $purchase + $vrt + $transport + $costs;
    }

    public function margin(): ?float
    {
        if (!$this->sale) {
            return null;
        }
        return (float) $this->sale->sale_price - $this->totalCost();
    }
}

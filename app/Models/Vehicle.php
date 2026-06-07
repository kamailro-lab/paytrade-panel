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
        'fuel', 'color', 'mileage_km', 'mileage_unit', 'body', 'doors', 'status',
        'target_price',
        'order_source', 'done_deal', 'www_listed', 'motortrans',
        'nct_expiry', 'nct_passed',
        'service_done', 'service_date',
        'timing_belt_checked', 'timing_belt_date',
        'photos', 'notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'nct_expiry' => 'date',
        'service_date' => 'date',
        'timing_belt_date' => 'date',
        'year' => 'integer',
        'engine_cc' => 'integer',
        'mileage_km' => 'integer',
        'doors' => 'integer',
        'target_price' => 'decimal:2',
        'motortrans' => 'boolean',
        'nct_passed' => 'boolean',
        'service_done' => 'boolean',
        'timing_belt_checked' => 'boolean',
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

    /**
     * % gotowości do sprzedaży (NCT + serwis + rozrząd).
     */
    public function readinessPercent(): int
    {
        $checks = [
            $this->isNctValid(),
            (bool) $this->service_done,
            (bool) $this->timing_belt_checked,
        ];
        $passed = count(array_filter($checks));
        return (int) round(($passed / count($checks)) * 100);
    }

    public function readinessLabel(): string
    {
        $p = $this->readinessPercent();
        return match (true) {
            $p === 100 => '✅ Gotowe do sprzedaży',
            $p >= 67 => '🟡 Prawie gotowe',
            $p >= 34 => '🟠 W trakcie przygotowania',
            default => '🔴 Wymaga uwagi',
        };
    }

    public function readinessColor(): string
    {
        $p = $this->readinessPercent();
        return match (true) {
            $p === 100 => 'bg-green-500',
            $p >= 67 => 'bg-yellow-400',
            $p >= 34 => 'bg-orange-400',
            default => 'bg-red-500',
        };
    }

    /**
     * Status NCT: valid / expiring (do 30 dni) / expired / unknown.
     */
    public function nctStatus(): string
    {
        if (!$this->nct_expiry) return 'unknown';
        $now = now();
        if ($this->nct_expiry->lt($now)) return 'expired';
        if ($this->nct_expiry->diffInDays($now) <= 30) return 'expiring';
        return 'valid';
    }

    public function isNctValid(): bool
    {
        return $this->nctStatus() === 'valid' || $this->nctStatus() === 'expiring';
    }

    public function nctDaysLeft(): ?int
    {
        if (!$this->nct_expiry) return null;
        return (int) now()->diffInDays($this->nct_expiry, false);
    }
}

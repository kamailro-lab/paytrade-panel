<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'contractor_id', 'purchase_date', 'purchase_price',
        'currency', 'vrt_paid', 'transport_cost', 'notes',
        'source', 'source_detail', 'paid_cash', 'paid_bank',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'vrt_paid' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'paid_cash' => 'decimal:2',
        'paid_bank' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Słownik kategorii źródeł (skąd auto przyjechało).
     */
    public static function sourceOptions(): array
    {
        return [
            'uk_auction' => '🇬🇧 Aukcja UK (Copart/Manheim/IAA)',
            'uk_dealer' => '🇬🇧 Dealer UK',
            'ie_private' => '🇮🇪 Prywatny z Irlandii',
            'ie_dealer' => '🇮🇪 Dealer / komis IE',
            'ie_auction' => '🇮🇪 Aukcja IE (DoneDeal, Carzone)',
            'eu_import' => '🇪🇺 Import z UE (DE/NL/BE/PL)',
            'other' => '📦 Inne',
        ];
    }

    public function sourceLabel(): ?string
    {
        return self::sourceOptions()[$this->source] ?? null;
    }

    /**
     * Suma zapłacona = gotówka + przelew.
     * Jeśli oba są 0, fallback do purchase_price.
     */
    public function totalPaid(): float
    {
        $sum = (float) $this->paid_cash + (float) $this->paid_bank;
        return $sum > 0 ? $sum : (float) $this->purchase_price;
    }
}

<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Vehicle;

/**
 * Kalkulator VAT Margin Scheme (Irlandia, s.10A VATCA 2010).
 *
 * Dla aut używanych dealer może rozliczać VAT od MARŻY zamiast od całej ceny.
 *
 *   marża = cena_sprzedaży - (cena_zakupu + VRT + transport + koszty)
 *   VAT = max(0, marża) * 23/123
 *   netto = marża - VAT
 *
 * Faktura nie pokazuje VAT osobno; musi mieć adnotację "Margin Scheme — Second-Hand Goods".
 */
class VatMarginCalculator
{
    private const VAT_RATE = 23;

    public function fromSale(Sale $sale): array
    {
        $vehicle = $sale->vehicle;
        $totalCost = $this->totalCost($vehicle);
        $salePrice = (float) $sale->sale_price;
        $margin = $salePrice - $totalCost;
        $vat = $margin > 0 ? round($margin * self::VAT_RATE / (100 + self::VAT_RATE), 2) : 0.0;

        return [
            'sale_price' => round($salePrice, 2),
            'total_cost' => round($totalCost, 2),
            'margin' => round($margin, 2),
            'vat_amount' => $vat,
            'vat_rate' => self::VAT_RATE,
            'net_margin' => round($margin - $vat, 2),
            'scheme' => 'margin',
            'scheme_label' => 'Margin Scheme — Second-Hand Goods (s.10A VATCA 2010)',
        ];
    }

    private function totalCost(Vehicle $vehicle): float
    {
        $purchase = $vehicle->purchase;
        $base = 0.0;

        if ($purchase) {
            $base = (float) $purchase->purchase_price
                + (float) $purchase->vrt_paid
                + (float) $purchase->transport_cost;
        }

        $base += (float) $vehicle->costs()->sum('amount');

        return $base;
    }
}

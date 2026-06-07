<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Contractor;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Osobny kontroler dla karty sprzedaży auta.
 * Sprzedaż NIE jest częścią głównego formularza Vehicle - jest osobnym krokiem.
 */
class VehicleSaleController extends Controller
{
    /**
     * Pokaż formularz sprzedaży auta (GET /vehicles/{vehicle}/sell).
     */
    public function edit(Vehicle $vehicle): View
    {
        $vehicle->load(['sale', 'purchase']);

        return view('vehicles.sell', [
            'vehicle' => $vehicle,
            'customers' => Contractor::whereIn('type', ['customer', 'both'])
                ->orderBy('name')->get(),
        ]);
    }

    /**
     * Zapisz sprzedaż (PUT /vehicles/{vehicle}/sell).
     */
    public function update(SaleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();

        // Payload dla Sale - tylko nowe pola (nie ruszamy starych payment_*)
        $payload = [
            'sale_date' => $data['sale_date'] ?? now(),
            'sale_price' => $data['sale_price'] ?? 0,
            'contractor_id' => $data['contractor_id'] ?? null,
            'warranty_months' => $data['warranty_months'] ?? 0,
            'deposit' => $data['deposit'] ?? 0,
            'paid_cash' => $data['paid_cash'] ?? 0,
            'paid_bank' => $data['paid_bank'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'payment_method' => 'bank_transfer', // legacy field, wymagany w DB
        ];

        if ($vehicle->sale) {
            $vehicle->sale->update($payload);
        } else {
            $vehicle->sale()->create($payload);
        }

        // Auto-zmień status na 'sold' gdy sprzedaż dodana
        if ($vehicle->status === 'stock') {
            $vehicle->update(['status' => 'sold']);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Sprzedaż zapisana. Auto oznaczone jako sprzedane.');
    }

    /**
     * Usuń sprzedaż (DELETE /vehicles/{vehicle}/sell).
     * Auto wraca do statusu 'stock'.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->sale) {
            $vehicle->sale->delete();
            $vehicle->update(['status' => 'stock']);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Sprzedaż usunięta. Auto wróciło do W stocku.');
    }
}

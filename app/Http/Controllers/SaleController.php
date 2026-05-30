<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'new_contractor_name' => ['nullable', 'string', 'max:120'],
            'new_contractor_phone' => ['nullable', 'string', 'max:30'],
            'new_contractor_email' => ['nullable', 'email', 'max:120'],
            'new_contractor_eir_code' => ['nullable', 'string', 'max:8'],
            'sale_date' => ['required', 'date'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,financing,other'],
            'payment_credit' => ['nullable', 'numeric', 'min:0'],
            'payment_bank' => ['nullable', 'numeric', 'min:0'],
            'payment_cash_deposit' => ['nullable', 'numeric', 'min:0'],
            'payment_trade' => ['nullable', 'numeric', 'min:0'],
            'credit_contract_number' => ['nullable', 'string', 'max:30'],
            'warranty' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$data['contractor_id'] && !empty($data['new_contractor_name'])) {
            $contractor = Contractor::create([
                'type' => 'customer',
                'name' => $data['new_contractor_name'],
                'phone' => $data['new_contractor_phone'] ?? null,
                'email' => $data['new_contractor_email'] ?? null,
                'eir_code' => $data['new_contractor_eir_code'] ?? null,
            ]);
            $data['contractor_id'] = $contractor->id;
        }

        if (!$data['contractor_id']) {
            return back()->with('error', 'Wybierz klienta lub wpisz nowego.');
        }

        foreach (['new_contractor_name', 'new_contractor_phone', 'new_contractor_email', 'new_contractor_eir_code'] as $k) {
            unset($data[$k]);
        }

        $sale = $vehicle->sale ?: new Sale(['vehicle_id' => $vehicle->id]);
        $sale->fill($data)->save();

        $vehicle->update(['status' => 'sold']);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Sprzedaż zapisana.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->sale?->delete();
        $vehicle->update(['status' => 'stock']);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Sprzedaż usunięta. Auto wróciło do stocku.');
    }
}

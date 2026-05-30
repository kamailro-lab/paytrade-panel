<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Purchase;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'new_contractor_name' => ['nullable', 'string', 'max:120'],
            'purchase_date' => ['required', 'date'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'currency' => ['required', 'in:EUR,GBP,USD'],
            'vrt_paid' => ['nullable', 'numeric', 'min:0'],
            'transport_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$data['contractor_id'] && !empty($data['new_contractor_name'])) {
            $contractor = Contractor::create([
                'type' => 'supplier',
                'name' => $data['new_contractor_name'],
            ]);
            $data['contractor_id'] = $contractor->id;
        }

        if (!$data['contractor_id']) {
            return back()->with('error', 'Wybierz dostawcę lub wpisz nowego.');
        }

        unset($data['new_contractor_name']);

        $purchase = $vehicle->purchase ?: new Purchase(['vehicle_id' => $vehicle->id]);
        $purchase->fill($data)->save();

        if ($vehicle->status === 'sold') {
            // dont change status when already sold
        } else {
            $vehicle->update(['status' => 'stock']);
        }

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Zakup zapisany.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->purchase?->delete();
        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Zakup usunięty.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:repair,parts,advertising,cleaning,transport,other'],
            'description' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'cost_date' => ['required', 'date'],
        ]);

        $vehicle->costs()->create($data);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Koszt dodany.');
    }

    public function destroy(Vehicle $vehicle, Cost $cost): RedirectResponse
    {
        abort_unless($cost->vehicle_id === $vehicle->id, 404);
        $cost->delete();

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Koszt usunięty.');
    }
}

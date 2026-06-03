<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Services\MotorCheckLookup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vehicle::query()->with(['purchase', 'sale']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('q')) {
            $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $search));

            $query->where(function ($q) use ($search, $normalized) {
                $q->where('registration', 'like', "%{$search}%")
                  ->orWhereRaw("UPPER(REPLACE(registration, '-', '')) LIKE ?", ["%{$normalized}%"])
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('logbook_no', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, [20, 50, 100, 200]) ? $perPage : 50;

        $vehicles = $query->latest()->paginate($perPage)->withQueryString();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create', [
            'vehicle' => new Vehicle(['status' => 'stock']),
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Auto dodane.');
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load(['purchase.contractor', 'sale.contractor', 'costs']);

        return view('vehicles.show', ['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', ['vehicle' => $vehicle]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Auto zaktualizowane.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Auto usunięte.');
    }

    public function enrichFromMotorCheck(MotorCheckLookup $lookup): RedirectResponse
    {
        set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $candidates = Vehicle::query()
            ->where(function ($q) {
                $q->where('make', 'Unknown')
                  ->orWhere('make', '')
                  ->orWhereNull('make')
                  ->orWhere('model', '—')
                  ->orWhere('model', '')
                  ->orWhereNull('model')
                  ->orWhereNull('year')
                  ->orWhereNull('engine_cc')
                  ->orWhereNull('fuel')
                  ->orWhereNull('color');
            })
            ->limit(100)
            ->get();

        $stats = ['checked' => 0, 'enriched' => 0, 'not_found' => 0, 'errors' => 0];

        foreach ($candidates as $vehicle) {
            $stats['checked']++;
            try {
                $data = $lookup->lookup($vehicle->registration);
                if (!$data) {
                    $stats['not_found']++;
                    continue;
                }

                $updates = [];
                foreach (['make', 'model', 'year', 'engine_cc', 'fuel', 'color', 'body'] as $field) {
                    $newValue = $data[$field] ?? null;
                    $oldValue = $vehicle->{$field};
                    $isOldEmpty = $oldValue === null || $oldValue === '' || $oldValue === '—' || $oldValue === 'Unknown';

                    if ($newValue !== null && $newValue !== '' && $isOldEmpty) {
                        $updates[$field] = $newValue;
                    }
                }

                if (!empty($updates)) {
                    $vehicle->update($updates);
                    $stats['enriched']++;
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
            }
        }

        return redirect()->route('vehicles.index')->with(
            'success',
            sprintf(
                '🔄 MotorCheck: sprawdzono %d aut · uzupełniono %d · brak w bazie %d · błędy %d',
                $stats['checked'], $stats['enriched'], $stats['not_found'], $stats['errors']
            )
        );
    }
}

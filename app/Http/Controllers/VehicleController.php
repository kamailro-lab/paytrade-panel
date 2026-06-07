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
        if ($ready = $request->query('ready')) {
            if ($ready === 'full') {
                $query->where('nct_passed', true)
                      ->where('service_done', true)
                      ->where('timing_belt_checked', true);
            } elseif ($ready === 'partial') {
                $query->where(function ($q) {
                    $q->where('nct_passed', true)
                      ->orWhere('service_done', true)
                      ->orWhere('timing_belt_checked', true);
                })->where(function ($q) {
                    $q->where('nct_passed', false)
                      ->orWhere('service_done', false)
                      ->orWhere('timing_belt_checked', false);
                });
            } elseif ($ready === 'none') {
                $query->where('nct_passed', false)
                      ->where('service_done', false)
                      ->where('timing_belt_checked', false);
            }
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
        $data = $request->validated();
        $purchasePrice = $data['purchase_price'] ?? null;
        unset($data['purchase_price']);

        $vehicle = Vehicle::create($data);

        // Jeśli user podał cenę zakupu w głównym formularzu - utwórz szybki Purchase
        // (bez dostawcy - można uzupełnić później na stronie auta)
        if ($purchasePrice !== null && $purchasePrice > 0) {
            $vehicle->purchase()->create([
                'purchase_date' => now(),
                'purchase_price' => $purchasePrice,
                'currency' => 'EUR',
                'vrt_paid' => 0,
                'transport_cost' => 0,
                'contractor_id' => null,
            ]);
        }

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
        $data = $request->validated();
        $purchasePrice = $data['purchase_price'] ?? null;
        unset($data['purchase_price']);

        $vehicle->update($data);

        // Update purchase_price w powiązanym Purchase (jeśli istnieje)
        if ($purchasePrice !== null && $purchasePrice > 0) {
            if ($vehicle->purchase) {
                $vehicle->purchase->update(['purchase_price' => $purchasePrice]);
            } else {
                $vehicle->purchase()->create([
                    'purchase_date' => now(),
                    'purchase_price' => $purchasePrice,
                    'currency' => 'EUR',
                    'vrt_paid' => 0,
                    'transport_cost' => 0,
                    'contractor_id' => null,
                ]);
            }
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Auto zaktualizowane.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Auto usunięte.');
    }

    public function enrichList(): \Illuminate\Http\JsonResponse
    {
        $vehicles = Vehicle::query()
            ->where(function ($q) {
                $q->whereNull('year')
                  ->orWhereNull('engine_cc')
                  ->orWhereNull('fuel')
                  ->orWhereNull('color')
                  ->orWhere('make', 'Unknown')
                  ->orWhere('model', '—');
            })
            ->orderBy('id')
            ->get(['id', 'registration', 'make', 'model']);

        return response()->json([
            'total' => $vehicles->count(),
            'vehicles' => $vehicles->map(fn ($v) => [
                'id' => $v->id,
                'registration' => $v->registration,
                'label' => trim(($v->make ?? '') . ' ' . ($v->model ?? '')),
            ])->values(),
        ]);
    }

    public function enrichSingle(Vehicle $vehicle, MotorCheckLookup $lookup): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $lookup->lookup($vehicle->registration);
            if (!$data) {
                return response()->json(['ok' => true, 'enriched' => false, 'reason' => 'not_in_motorcheck']);
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
            }

            return response()->json([
                'ok' => true,
                'enriched' => !empty($updates),
                'updated_fields' => array_keys($updates),
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

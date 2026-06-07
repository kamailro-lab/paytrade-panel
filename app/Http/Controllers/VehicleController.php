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
            'suppliers' => \App\Models\Contractor::whereIn('type', ['supplier', 'both'])
                ->orderBy('name')->get(),
            'customers' => \App\Models\Contractor::whereIn('type', ['customer', 'both'])
                ->orderBy('name')->get(),
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $purchaseFields = $this->extractPurchaseFields($data);
        $saleFields = $this->extractSaleFields($data);

        $vehicle = Vehicle::create($data);

        if ($this->hasPurchaseData($purchaseFields)) {
            $vehicle->purchase()->create($this->buildPurchasePayload($purchaseFields));
        }

        if ($this->hasSaleData($saleFields)) {
            $vehicle->sale()->create($this->buildSalePayload($saleFields));
            // Auto-zmień status na 'sold' gdy dodano sprzedaż
            if ($vehicle->status === 'stock') {
                $vehicle->update(['status' => 'sold']);
            }
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Auto dodane.');
    }

    /**
     * Wyciąga pola Purchase z $data i je usuwa z $data (przekazane przez referencję).
     */
    private function extractPurchaseFields(array &$data): array
    {
        $fields = [];
        foreach (['purchase_price', 'supplier_contractor_id', 'source', 'source_detail', 'paid_cash', 'paid_bank'] as $field) {
            $fields[$field] = $data[$field] ?? null;
            unset($data[$field]);
        }
        return $fields;
    }

    private function hasPurchaseData(array $fields): bool
    {
        return ($fields['purchase_price'] ?? 0) > 0
            || !empty($fields['supplier_contractor_id'])
            || !empty($fields['source'])
            || !empty($fields['source_detail'])
            || ($fields['paid_cash'] ?? 0) > 0
            || ($fields['paid_bank'] ?? 0) > 0;
    }

    private function buildPurchasePayload(array $fields): array
    {
        return [
            'purchase_date' => now(),
            'purchase_price' => $fields['purchase_price'] ?? 0,
            'currency' => 'EUR',
            'vrt_paid' => 0,
            'transport_cost' => 0,
            'contractor_id' => $fields['supplier_contractor_id'] ?: null,
            'source' => $fields['source'] ?: null,
            'source_detail' => $fields['source_detail'] ?: null,
            'paid_cash' => $fields['paid_cash'] ?? 0,
            'paid_bank' => $fields['paid_bank'] ?? 0,
        ];
    }

    /**
     * Wyciąga pola Sale z $data (prefiks 'sale_').
     */
    private function extractSaleFields(array &$data): array
    {
        $fields = [];
        foreach (['sale_price', 'sale_customer_contractor_id', 'warranty_months',
                  'sale_deposit', 'sale_paid_cash', 'sale_paid_bank'] as $field) {
            $fields[$field] = $data[$field] ?? null;
            unset($data[$field]);
        }
        return $fields;
    }

    private function hasSaleData(array $fields): bool
    {
        return ($fields['sale_price'] ?? 0) > 0
            || !empty($fields['sale_customer_contractor_id'])
            || ($fields['warranty_months'] ?? 0) > 0
            || ($fields['sale_deposit'] ?? 0) > 0
            || ($fields['sale_paid_cash'] ?? 0) > 0
            || ($fields['sale_paid_bank'] ?? 0) > 0;
    }

    private function buildSalePayload(array $fields): array
    {
        return [
            'sale_date' => now(),
            'sale_price' => $fields['sale_price'] ?? 0,
            'payment_method' => 'bank_transfer',
            'contractor_id' => $fields['sale_customer_contractor_id'] ?: null,
            'warranty_months' => $fields['warranty_months'] ?? 0,
            'deposit' => $fields['sale_deposit'] ?? 0,
            'paid_cash' => $fields['sale_paid_cash'] ?? 0,
            'paid_bank' => $fields['sale_paid_bank'] ?? 0,
        ];
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load(['purchase.contractor', 'sale.contractor', 'costs']);

        return view('vehicles.show', ['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle): View
    {
        $vehicle->load(['purchase', 'sale']);
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
            'suppliers' => \App\Models\Contractor::whereIn('type', ['supplier', 'both'])
                ->orderBy('name')->get(),
            'customers' => \App\Models\Contractor::whereIn('type', ['customer', 'both'])
                ->orderBy('name')->get(),
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        $purchaseFields = $this->extractPurchaseFields($data);
        $saleFields = $this->extractSaleFields($data);

        $vehicle->update($data);

        if ($this->hasPurchaseData($purchaseFields)) {
            $payload = $this->buildPurchasePayload($purchaseFields);

            if ($vehicle->purchase) {
                $updateData = array_filter([
                    'purchase_price' => $payload['purchase_price'] ?: null,
                    'contractor_id' => $payload['contractor_id'],
                    'source' => $payload['source'],
                    'source_detail' => $payload['source_detail'],
                    'paid_cash' => $payload['paid_cash'],
                    'paid_bank' => $payload['paid_bank'],
                ], fn($v) => $v !== null);
                $vehicle->purchase->update($updateData);
            } else {
                $vehicle->purchase()->create($payload);
            }
        }

        if ($this->hasSaleData($saleFields)) {
            $payload = $this->buildSalePayload($saleFields);

            if ($vehicle->sale) {
                $updateData = array_filter([
                    'sale_price' => $payload['sale_price'] ?: null,
                    'contractor_id' => $payload['contractor_id'],
                    'warranty_months' => $payload['warranty_months'],
                    'deposit' => $payload['deposit'],
                    'paid_cash' => $payload['paid_cash'],
                    'paid_bank' => $payload['paid_bank'],
                ], fn($v) => $v !== null);
                $vehicle->sale->update($updateData);
            } else {
                $vehicle->sale()->create($payload);
            }

            // Auto-zmień status na 'sold' gdy sprzedaż dodana/zaktualizowana
            if ($vehicle->status === 'stock') {
                $vehicle->update(['status' => 'sold']);
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

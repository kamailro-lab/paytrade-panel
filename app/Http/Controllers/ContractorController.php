<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractorRequest;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contractor::query();

        if ($type = $request->query('type')) {
            if ($type === 'suppliers') {
                $query->suppliers();
            } elseif ($type === 'customers') {
                $query->customers();
            } else {
                $query->where('type', $type);
            }
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('eir_code', 'like', "%{$search}%");
            });
        }

        $contractors = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('contractors.index', [
            'contractors' => $contractors,
            'type' => $type,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('contractors.create', [
            'contractor' => new Contractor(['type' => 'customer']),
        ]);
    }

    public function store(ContractorRequest $request): RedirectResponse
    {
        $contractor = Contractor::create($request->validated());

        return redirect()->route('contractors.show', $contractor)
            ->with('success', 'Kontrahent dodany.');
    }

    public function show(Contractor $contractor): View
    {
        $contractor->load(['purchases.vehicle', 'sales.vehicle']);

        return view('contractors.show', ['contractor' => $contractor]);
    }

    public function edit(Contractor $contractor): View
    {
        return view('contractors.edit', ['contractor' => $contractor]);
    }

    public function update(ContractorRequest $request, Contractor $contractor): RedirectResponse
    {
        $contractor->update($request->validated());

        return redirect()->route('contractors.show', $contractor)
            ->with('success', 'Kontrahent zaktualizowany.');
    }

    public function destroy(Contractor $contractor): RedirectResponse
    {
        if ($contractor->purchases()->exists() || $contractor->sales()->exists()) {
            return back()->with('error', 'Nie można usunąć — kontrahent ma transakcje.');
        }

        $contractor->delete();

        return redirect()->route('contractors.index')
            ->with('success', 'Kontrahent usunięty.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Vehicle;
use App\Services\InvoiceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('sale.vehicle', 'sale.contractor')
            ->orderByDesc('issue_date')->orderByDesc('invoice_number');

        if ($year = $request->query('year')) {
            $query->whereYear('issue_date', $year);
        }

        $invoices = $query->paginate(30)->withQueryString();

        $totalVat = (clone $query)->sum('vat_amount');
        $totalGross = (clone $query)->sum('total_gross');

        return view('invoices.index', [
            'invoices' => $invoices,
            'year' => $year ?? date('Y'),
            'totalVat' => $totalVat,
            'totalGross' => $totalGross,
        ]);
    }

    public function generate(Vehicle $vehicle, InvoiceGenerator $generator): RedirectResponse
    {
        if (!$vehicle->sale) {
            return back()->with('error', 'Najpierw zapisz sprzedaż auta.');
        }

        $invoice = $generator->generate($vehicle->sale);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Faktura {$invoice->invoice_number} wygenerowana.");
    }

    public function download(Invoice $invoice): StreamedResponse
    {
        if (!$invoice->pdf_path || !Storage::disk('local')->exists($invoice->pdf_path)) {
            abort(404, 'PDF nie znaleziony — wygeneruj fakturę ponownie.');
        }

        return Storage::disk('local')->download($invoice->pdf_path, $invoice->invoice_number . '.pdf');
    }
}

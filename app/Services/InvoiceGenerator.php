<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceGenerator
{
    public function __construct(private readonly VatMarginCalculator $calc)
    {
    }

    public function generate(Sale $sale): Invoice
    {
        $sale->loadMissing(['vehicle.purchase', 'vehicle.costs', 'contractor']);

        $invoice = $sale->invoice ?? new Invoice(['sale_id' => $sale->id]);

        $calc = $this->calc->fromSale($sale);

        $invoice->fill([
            'invoice_number' => $invoice->invoice_number ?: $this->nextNumber(),
            'issue_date' => $sale->sale_date,
            'vat_scheme' => 'margin',
            'vat_amount' => $calc['vat_amount'],
            'total_gross' => $calc['sale_price'],
        ])->save();

        $pdfPath = $this->renderPdf($invoice, $calc);
        $invoice->update(['pdf_path' => $pdfPath]);

        return $invoice->fresh();
    }

    private function nextNumber(): string
    {
        $year = (int) date('Y');
        $last = Invoice::where('invoice_number', 'like', "{$year}-%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? (int) substr($last, 5) + 1 : 1;

        return sprintf('%d-%04d', $year, $next);
    }

    private function renderPdf(Invoice $invoice, array $calc): string
    {
        $company = [
            'name' => Setting::get('company_name', 'Paytrade / MRtardex'),
            'address' => Setting::get('company_address', 'Ireland'),
            'eir_code' => Setting::get('company_eir_code', ''),
            'vat_number' => Setting::get('company_vat_number', ''),
            'phone' => Setting::get('company_phone', ''),
            'email' => Setting::get('company_email', 'info@paytrade.ie'),
            'iban' => Setting::get('company_iban', ''),
            'bank' => Setting::get('company_bank', ''),
        ];

        $pdf = Pdf::loadView('invoices.template', [
            'invoice' => $invoice,
            'sale' => $invoice->sale,
            'vehicle' => $invoice->sale->vehicle,
            'customer' => $invoice->sale->contractor,
            'calc' => $calc,
            'company' => $company,
        ])->setPaper('a4');

        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }
}

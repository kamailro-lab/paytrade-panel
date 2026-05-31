<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11pt; color: #222; margin: 0; padding: 0; }
        .page { padding: 30px 40px; }
        h1 { font-size: 24pt; margin: 0 0 5px 0; color: #1a3a6f; }
        h2 { font-size: 14pt; margin: 20px 0 8px 0; color: #1a3a6f; border-bottom: 2px solid #1a3a6f; padding-bottom: 3px; }
        .header { display: table; width: 100%; margin-bottom: 25px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; }
        .header-right { text-align: right; }
        .meta { background: #f1f5f9; padding: 10px 14px; border-radius: 4px; margin-bottom: 18px; }
        .meta-row { display: table; width: 100%; }
        .meta-cell { display: table-cell; padding: 3px 12px 3px 0; vertical-align: top; }
        .meta-cell strong { color: #1a3a6f; }
        table.parties { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.parties td { vertical-align: top; padding: 12px; border: 1px solid #cbd5e1; width: 50%; }
        table.parties th { background: #1a3a6f; color: white; padding: 6px 12px; text-align: left; }
        table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.items th, table.items td { padding: 8px 10px; border: 1px solid #cbd5e1; text-align: left; }
        table.items th { background: #1a3a6f; color: white; }
        table.items td.r { text-align: right; }
        table.items td.amt { text-align: right; font-weight: bold; }
        .totals { width: 100%; margin-top: 10px; }
        .totals td { padding: 6px 12px; }
        .totals tr.grand td { background: #1a3a6f; color: white; font-weight: bold; font-size: 14pt; }
        .footer { margin-top: 30px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; font-size: 9pt; }
        .footer strong { color: #92400e; }
        .small { font-size: 9pt; color: #64748b; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            <h1>{{ $company['name'] ?: 'Paytrade' }}</h1>
            @if($company['address']) <div>{{ $company['address'] }}</div> @endif
            @if($company['eir_code']) <div>{{ $company['eir_code'] }}</div> @endif
            @if($company['phone']) <div>Tel: {{ $company['phone'] }}</div> @endif
            @if($company['email']) <div>Email: {{ $company['email'] }}</div> @endif
            @if($company['vat_number']) <div>VAT: {{ $company['vat_number'] }}</div> @endif
        </div>
        <div class="header-right">
            <div style="font-size: 18pt; font-weight: bold; color: #1a3a6f;">INVOICE</div>
            <div style="font-size: 16pt; margin-top: 5px;">{{ $invoice->invoice_number }}</div>
            <div class="small" style="margin-top: 8px;">Issue date: {{ $invoice->issue_date->format('d.m.Y') }}</div>
            <div class="small">Sale date: {{ $sale->sale_date->format('d.m.Y') }}</div>
        </div>
    </div>

    <table class="parties">
        <tr>
            <th>From / Seller</th>
            <th>To / Buyer</th>
        </tr>
        <tr>
            <td>
                <strong>{{ $company['name'] }}</strong><br>
                {{ $company['address'] }}<br>
                {{ $company['eir_code'] }}<br>
                @if($company['vat_number']) VAT: {{ $company['vat_number'] }}<br> @endif
                @if($company['phone']) Tel: {{ $company['phone'] }}<br> @endif
                {{ $company['email'] }}
            </td>
            <td>
                <strong>{{ $customer->name }}</strong><br>
                {{ $customer->address }}<br>
                {{ $customer->eir_code }}<br>
                @if($customer->vat_number) VAT: {{ $customer->vat_number }}<br> @endif
                @if($customer->phone) Tel: {{ $customer->phone }}<br> @endif
                {{ $customer->email }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 140px;">Registration</th>
                <th class="r" style="width: 110px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                    @if($vehicle->year) ({{ $vehicle->year }}) @endif
                    <br>
                    <span class="small">
                        @if($vehicle->engine_cc) {{ $vehicle->engine_cc }}ccm · @endif
                        @if($vehicle->fuel) {{ ucfirst($vehicle->fuel) }} · @endif
                        @if($vehicle->color) {{ $vehicle->color }} · @endif
                        @if($vehicle->mileage_km) {{ number_format($vehicle->mileage_km, 0, ',', ' ') }} {{ $vehicle->mileage_unit ?: 'km' }} @endif
                    </span>
                    @if($vehicle->logbook_no)
                        <br><span class="small">Logbook (VRC): {{ $vehicle->logbook_no }}</span>
                    @endif
                </td>
                <td><strong>{{ $vehicle->registration }}</strong></td>
                <td class="amt">€{{ number_format($calc['sale_price'], 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals" style="margin-left: auto; width: 50%;">
        <tr class="grand">
            <td>TOTAL (incl. VAT)</td>
            <td class="r">€{{ number_format($calc['sale_price'], 2, '.', ',') }}</td>
        </tr>
    </table>

    @if($sale->paymentTotal() > 0)
        <h2>Payment Breakdown</h2>
        <table class="items">
            <tbody>
                @if($sale->payment_credit > 0)
                    <tr><td>Credit (financing)</td><td class="amt">€{{ number_format($sale->payment_credit, 2, '.', ',') }}</td></tr>
                @endif
                @if($sale->payment_bank > 0)
                    <tr><td>Bank transfer</td><td class="amt">€{{ number_format($sale->payment_bank, 2, '.', ',') }}</td></tr>
                @endif
                @if($sale->payment_cash_deposit > 0)
                    <tr><td>Cash / deposit</td><td class="amt">€{{ number_format($sale->payment_cash_deposit, 2, '.', ',') }}</td></tr>
                @endif
                @if($sale->payment_trade > 0)
                    <tr><td>Trade-in</td><td class="amt">€{{ number_format($sale->payment_trade, 2, '.', ',') }}</td></tr>
                @endif
            </tbody>
        </table>
        @if($sale->credit_contract_number)
            <div class="small" style="margin-top: 8px;">Credit contract: <strong>{{ $sale->credit_contract_number }}</strong></div>
        @endif
    @endif

    @if($sale->warranty)
        <h2>Warranty</h2>
        <div>{{ $sale->warranty }}</div>
    @endif

    <div class="footer">
        <strong>VAT note — {{ $calc['scheme_label'] }}.</strong><br>
        This invoice is issued under the VAT margin scheme for second-hand goods (Section 10A of the Value-Added Tax Consolidation Act 2010). VAT is included in the total price and is not separately deductible by the buyer.
    </div>

    @if($company['iban'])
        <div style="margin-top: 20px;" class="small">
            <strong>Bank details:</strong> {{ $company['bank'] }} · IBAN: {{ $company['iban'] }}
        </div>
    @endif

    <div style="margin-top: 30px; text-align: center;" class="small">
        Thank you for your purchase — {{ $company['name'] }}
    </div>
</div>
</body>
</html>

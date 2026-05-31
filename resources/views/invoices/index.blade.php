<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">📄 Faktury</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Faktury ({{ $year }})</div>
                    <div class="text-2xl font-bold">{{ $invoices->total() }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Sprzedaż brutto</div>
                    <div class="text-2xl font-bold text-blue-700">€{{ number_format($totalGross, 2, ',', ' ') }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">VAT do zapłaty (Margin Scheme)</div>
                    <div class="text-2xl font-bold text-amber-700">€{{ number_format($totalVat, 2, ',', ' ') }}</div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-4 mb-4">
                <form method="GET" class="flex gap-2 items-center">
                    <label class="text-sm">Rok:</label>
                    <select name="year" onchange="this.form.submit()" class="px-3 py-2 border-2 border-gray-300 rounded-lg">
                        @for($y = (int) date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                @if($invoices->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-5xl mb-3">📄</div>
                        <p>Brak faktur. Wygeneruj fakturę ze strony sprzedanego auta.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nr</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Klient</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Auto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Brutto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">VAT (margin)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($invoices as $inv)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono font-semibold">{{ $inv->invoice_number }}</td>
                                    <td class="px-4 py-3">{{ $inv->issue_date->format('d.m.Y') }}</td>
                                    <td class="px-4 py-3">{{ $inv->sale->contractor->name }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('vehicles.show', $inv->sale->vehicle) }}" class="text-indigo-600 hover:underline">
                                            {{ $inv->sale->vehicle->registration }}
                                        </a>
                                        <span class="text-sm text-gray-500">— {{ $inv->sale->vehicle->make }} {{ $inv->sale->vehicle->model }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold">€{{ number_format($inv->total_gross, 2, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-amber-700">€{{ number_format($inv->vat_amount, 2, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($inv->pdf_path)
                                            <a href="{{ route('invoices.download', $inv) }}" class="text-indigo-600 hover:underline">📥 PDF</a>
                                        @else
                                            <span class="text-gray-400 text-sm">brak PDF</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $invoices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

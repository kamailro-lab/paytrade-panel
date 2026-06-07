@php
    $suppliers = \App\Models\Contractor::suppliers()->orderBy('name')->get(['id', 'name']);
    $customers = \App\Models\Contractor::customers()->orderBy('name')->get(['id', 'name']);
    $statusLabels = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
    $categoryLabels = ['repair' => 'Naprawa', 'parts' => 'Części', 'advertising' => 'Reklama', 'cleaning' => 'Czyszczenie', 'transport' => 'Transport', 'other' => 'Inne'];
    $paymentLabels = ['cash' => 'Gotówka', 'bank_transfer' => 'Przelew', 'card' => 'Karta', 'financing' => 'Finansowanie', 'other' => 'Inne'];

    $nctStatus = $vehicle->nctStatus();
    $daysLeft = $vehicle->nctDaysLeft();
    $nctBg = match($nctStatus) {
        'valid' => 'bg-green-100 border-green-400 text-green-900',
        'expiring' => 'bg-yellow-100 border-yellow-400 text-yellow-900',
        'expired' => 'bg-red-100 border-red-400 text-red-900',
        default => 'bg-gray-100 border-gray-300 text-gray-700',
    };
    $nctIcon = match($nctStatus) {
        'valid' => '✅', 'expiring' => '⚠️', 'expired' => '🚨', default => '❓',
    };
    $nctText = match($nctStatus) {
        'valid' => 'NCT ważne (' . abs($daysLeft) . ' dni do końca)',
        'expiring' => 'NCT wygasa za ' . abs($daysLeft) . ' dni!',
        'expired' => 'NCT WYGASŁO ' . abs($daysLeft) . ' dni temu',
        default => 'NCT — brak daty',
    };
    $readiness = $vehicle->readinessPercent();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $vehicle->make }} {{ $vehicle->model }}
                <span class="font-mono text-gray-500">({{ $vehicle->registration }})</span>
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-700">✏️ Edytuj</a>
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Czy na pewno usunąć auto {{ $vehicle->registration }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">🗑 Usuń</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 space-y-4">

            @if(session('success'))
                <div class="p-3 bg-green-100 border border-green-300 text-green-800 rounded text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 bg-red-100 border border-red-300 text-red-800 rounded text-sm">{{ session('error') }}</div>
            @endif

            {{-- ⭐ NCT BANNER (pełna szerokość, zawsze widoczny) --}}
            <div class="rounded-lg border-2 p-4 {{ $nctBg }} shadow-md">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-4">
                        <div class="text-4xl">{{ $nctIcon }}</div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide opacity-75">NCT — Przegląd techniczny</div>
                            <div class="text-xl font-bold">{{ $nctText }}</div>
                            @if($vehicle->nct_expiry)
                                <div class="text-sm mt-1">Ważne do: <strong>{{ $vehicle->nct_expiry->format('d.m.Y') }}</strong></div>
                            @endif
                        </div>
                    </div>
                    @if($vehicle->nct_passed)
                        <span class="px-3 py-1 bg-green-600 text-white text-sm font-bold rounded-full">✓ Zaliczone</span>
                    @endif
                </div>
            </div>

            {{-- ═══════════════ GRID 2 KOLUMNY ═══════════════ --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- ═══ LEWA KOLUMNA: DANE + GOTOWOŚĆ + P&L ═══ --}}
                <div class="space-y-4">

                    {{-- 📊 Gotowość do sprzedaży --}}
                    <div class="bg-white shadow rounded-lg p-4 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-800">📊 Gotowość do sprzedaży</h3>
                            <span class="text-2xl font-bold {{ $readiness === 100 ? 'text-green-600' : ($readiness >= 67 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $readiness }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden mb-2">
                            <div class="{{ $vehicle->readinessColor() }} h-full transition-all" style="width: {{ $readiness }}%"></div>
                        </div>
                        <div class="text-sm font-semibold text-gray-700 mb-3">{{ $vehicle->readinessLabel() }}</div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="p-2 rounded border-2 {{ $vehicle->isNctValid() ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }} text-center">
                                <div class="text-lg">{{ $vehicle->isNctValid() ? '✅' : '⬜' }}</div>
                                <div class="text-xs font-semibold">NCT</div>
                            </div>
                            <div class="p-2 rounded border-2 {{ $vehicle->service_done ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }} text-center">
                                <div class="text-lg">{{ $vehicle->service_done ? '✅' : '⬜' }}</div>
                                <div class="text-xs font-semibold">🔧 Serwis</div>
                            </div>
                            <div class="p-2 rounded border-2 {{ $vehicle->timing_belt_checked ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }} text-center">
                                <div class="text-lg">{{ $vehicle->timing_belt_checked ? '✅' : '⬜' }}</div>
                                <div class="text-xs font-semibold">⚙️ Rozrząd</div>
                            </div>
                        </div>
                    </div>

                    {{-- 📋 Dane auta --}}
                    <div class="bg-white shadow rounded-lg p-4">
                        <h3 class="font-bold text-gray-800 mb-3">📋 Dane auta</h3>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs text-gray-500">Rejestracja</dt><dd class="font-mono font-bold">{{ $vehicle->registration }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Logbook (VRC)</dt><dd>{{ $vehicle->logbook_no ?? '—' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-gray-500">Marka i model</dt><dd class="font-semibold">{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Rok</dt><dd>{{ $vehicle->year ?? '—' }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Silnik</dt><dd>{{ $vehicle->engine_cc ? $vehicle->engine_cc.' ccm' : '—' }} · {{ ucfirst($vehicle->fuel ?? '—') }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Przebieg</dt><dd>{{ $vehicle->mileage_km ? number_format($vehicle->mileage_km, 0, ',', ' ').' '.($vehicle->mileage_unit ?? 'km') : '—' }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Nadwozie / drzwi</dt><dd>{{ ucfirst($vehicle->body ?? '—') }} · {{ $vehicle->doors ?? '—' }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Kolor</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
                            <div><dt class="text-xs text-gray-500">Status</dt><dd class="font-semibold">{{ $statusLabels[$vehicle->status] }}</dd></div>
                        </dl>
                        @if($vehicle->notes)
                            <div class="mt-3 p-2 bg-gray-50 rounded text-sm">
                                <dt class="text-xs text-gray-500 mb-1">Notatki</dt>
                                <dd class="whitespace-pre-wrap">{{ $vehicle->notes }}</dd>
                            </div>
                        @endif
                    </div>

                    {{-- 💰 P&L kompaktowo --}}
                    <div class="bg-white shadow rounded-lg p-4">
                        <h3 class="font-bold text-gray-800 mb-3">💰 Finanse</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <div>
                                <div class="text-xs text-gray-500">Koszt</div>
                                <div class="text-lg font-bold text-amber-700">€{{ number_format($vehicle->totalCost(), 0, ',', ' ') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Cena docelowa</div>
                                <div class="text-lg font-bold text-purple-700">{{ $vehicle->target_price ? '€'.number_format($vehicle->target_price, 0, ',', ' ') : '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Sprzedaż</div>
                                <div class="text-lg font-bold text-blue-700">{{ $vehicle->sale ? '€'.number_format($vehicle->sale->sale_price, 0, ',', ' ') : '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Marża</div>
                                @if($vehicle->margin() !== null)
                                    <div class="text-lg font-bold {{ $vehicle->margin() >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $vehicle->margin() >= 0 ? '+' : '' }}€{{ number_format($vehicle->margin(), 0, ',', ' ') }}
                                    </div>
                                @else
                                    <div class="text-lg font-bold text-gray-400">—</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ═══ PRAWA KOLUMNA: TRANSAKCJE (Zakup + Koszty + Sprzedaż) ═══ --}}
                <div class="space-y-4">

                    {{-- 🚚 ZAKUP --}}
                    <div class="bg-white shadow rounded-lg p-4 border-l-4 border-amber-500">
                        <h3 class="font-bold text-gray-800 mb-3">🚚 Zakup od dostawcy</h3>
                        @if($vehicle->purchase)
                            <dl class="grid grid-cols-2 gap-2 mb-3 text-sm">
                                <div class="col-span-2"><dt class="text-xs text-gray-500">Dostawca</dt><dd class="font-semibold">{{ $vehicle->purchase->contractor->name }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Data</dt><dd>{{ $vehicle->purchase->purchase_date->format('d.m.Y') }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Cena</dt><dd class="font-bold">{{ $vehicle->purchase->currency }} {{ number_format($vehicle->purchase->purchase_price, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-gray-500">VRT</dt><dd>€{{ number_format($vehicle->purchase->vrt_paid, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Transport</dt><dd>€{{ number_format($vehicle->purchase->transport_cost, 0, ',', ' ') }}</dd></div>
                            </dl>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.getElementById('purchase-form').classList.toggle('hidden')" class="px-3 py-1.5 text-sm bg-gray-800 text-white rounded hover:bg-gray-700">✏️ Edytuj</button>
                                <form method="POST" action="{{ route('purchases.destroy', $vehicle) }}" onsubmit="return confirm('Usunąć dane zakupu?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">🗑</button>
                                </form>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 mb-2">Brak danych zakupu.</p>
                            <button type="button" onclick="document.getElementById('purchase-form').classList.toggle('hidden')" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700">➕ Dodaj zakup</button>
                        @endif

                        <form id="purchase-form" method="POST" action="{{ route('purchases.store', $vehicle) }}" class="mt-3 p-3 bg-gray-50 rounded @if(!$vehicle->purchase || !$errors->any()) hidden @endif">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Dostawca *</label>
                                    <select name="contractor_id" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded" onchange="document.getElementById('new-supplier').classList.toggle('hidden', this.value !== '')">
                                        <option value="">— wybierz lub wpisz nowego —</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" @selected($vehicle->purchase?->contractor_id == $s->id)>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    <input id="new-supplier" type="text" name="new_contractor_name" placeholder="lub nowy dostawca" class="mt-1 w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded @if($vehicle->purchase?->contractor_id) hidden @endif">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Data *</label>
                                    <input type="date" name="purchase_date" required value="{{ old('purchase_date', $vehicle->purchase?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Waluta *</label>
                                    <select name="currency" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        <option value="EUR" @selected(($vehicle->purchase?->currency ?? 'EUR') === 'EUR')>EUR €</option>
                                        <option value="GBP" @selected($vehicle->purchase?->currency === 'GBP')>GBP £</option>
                                        <option value="USD" @selected($vehicle->purchase?->currency === 'USD')>USD $</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Cena *</label>
                                    <input type="number" name="purchase_price" required step="0.01" min="0" value="{{ old('purchase_price', $vehicle->purchase?->purchase_price) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">VRT</label>
                                    <input type="number" name="vrt_paid" step="0.01" min="0" value="{{ old('vrt_paid', $vehicle->purchase?->vrt_paid ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Transport €</label>
                                    <input type="number" name="transport_cost" step="0.01" min="0" value="{{ old('transport_cost', $vehicle->purchase?->transport_cost ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Notatki</label>
                                    <textarea name="notes" rows="2" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">{{ old('notes', $vehicle->purchase?->notes) }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="mt-3 px-5 py-2 bg-green-600 text-white text-sm font-bold rounded hover:bg-green-700">💾 Zapisz</button>
                        </form>
                    </div>

                    {{-- 🔧 KOSZTY --}}
                    <div class="bg-white shadow rounded-lg p-4 border-l-4 border-orange-500">
                        <h3 class="font-bold text-gray-800 mb-3">
                            🔧 Koszty <span class="text-sm font-normal text-gray-500">({{ $vehicle->costs->count() }}) — €{{ number_format($vehicle->costs->sum('amount'), 2, ',', ' ') }}</span>
                        </h3>
                        @if($vehicle->costs->isNotEmpty())
                            <ul class="divide-y text-sm mb-3">
                                @foreach($vehicle->costs as $cost)
                                    <li class="py-1.5 flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-semibold">{{ $categoryLabels[$cost->category] }}:</span>
                                            {{ $cost->description }}
                                            <span class="text-xs text-gray-500">· {{ $cost->cost_date->format('d.m.Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 ml-2">
                                            <span class="font-bold">€{{ number_format($cost->amount, 0, ',', ' ') }}</span>
                                            <form method="POST" action="{{ route('costs.destroy', [$vehicle, $cost]) }}" onsubmit="return confirm('Usunąć ten koszt?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline text-xs">🗑</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" onclick="document.getElementById('cost-form').classList.toggle('hidden')" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700">➕ Dodaj koszt</button>

                        <form id="cost-form" method="POST" action="{{ route('costs.store', $vehicle) }}" class="mt-3 p-3 bg-gray-50 rounded hidden">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Kategoria *</label>
                                    <select name="category" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        @foreach($categoryLabels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Data *</label>
                                    <input type="date" name="cost_date" required value="{{ now()->format('Y-m-d') }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Opis *</label>
                                    <input type="text" name="description" required class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Kwota € *</label>
                                    <input type="number" name="amount" required step="0.01" min="0" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                            </div>
                            <button type="submit" class="mt-3 px-5 py-2 bg-green-600 text-white text-sm font-bold rounded hover:bg-green-700">💾 Dodaj</button>
                        </form>
                    </div>

                    {{-- 🤝 SPRZEDAŻ --}}
                    <div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-500">
                        <h3 class="font-bold text-gray-800 mb-3">🤝 Sprzedaż klientowi</h3>
                        @if($vehicle->sale)
                            <dl class="grid grid-cols-2 gap-2 mb-3 text-sm">
                                <div class="col-span-2"><dt class="text-xs text-gray-500">Klient</dt><dd class="font-semibold">{{ $vehicle->sale->contractor->name }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Data</dt><dd>{{ $vehicle->sale->sale_date->format('d.m.Y') }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Cena</dt><dd class="font-bold text-lg">€{{ number_format($vehicle->sale->sale_price, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Metoda</dt><dd>{{ $paymentLabels[$vehicle->sale->payment_method] }}</dd></div>
                                @if($vehicle->sale->warranty)
                                    <div><dt class="text-xs text-gray-500">Gwarancja</dt><dd class="text-xs">{{ $vehicle->sale->warranty }}</dd></div>
                                @endif
                                @if($vehicle->sale->paymentTotal() > 0)
                                    <div class="col-span-2">
                                        <dt class="text-xs text-gray-500 mb-1">Rozbicie</dt>
                                        <dd class="text-xs">
                                            @if($vehicle->sale->payment_credit > 0) Kredyt: €{{ number_format($vehicle->sale->payment_credit, 0, ',', ' ') }} · @endif
                                            @if($vehicle->sale->payment_bank > 0) Bank: €{{ number_format($vehicle->sale->payment_bank, 0, ',', ' ') }} · @endif
                                            @if($vehicle->sale->payment_cash_deposit > 0) Gotówka: €{{ number_format($vehicle->sale->payment_cash_deposit, 0, ',', ' ') }} · @endif
                                            @if($vehicle->sale->payment_trade > 0) Trade: €{{ number_format($vehicle->sale->payment_trade, 0, ',', ' ') }} @endif
                                        </dd>
                                    </div>
                                @endif
                                @if($vehicle->sale->credit_contract_number)
                                    <div class="col-span-2"><dt class="text-xs text-gray-500">Nr umowy</dt><dd class="text-xs">{{ $vehicle->sale->credit_contract_number }}</dd></div>
                                @endif
                            </dl>
                            <div class="flex gap-2 flex-wrap">
                                <form method="POST" action="{{ route('invoices.generate', $vehicle) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700">
                                        📄 {{ $vehicle->sale->invoice ? 'Regeneruj PDF' : 'Faktura PDF' }}
                                    </button>
                                </form>
                                @if($vehicle->sale->invoice && $vehicle->sale->invoice->pdf_path)
                                    <a href="{{ route('invoices.download', $vehicle->sale->invoice) }}" class="px-3 py-1.5 text-sm bg-green-600 text-white font-semibold rounded hover:bg-green-700">
                                        📥 {{ $vehicle->sale->invoice->invoice_number }}
                                    </a>
                                @endif
                                <button type="button" onclick="document.getElementById('sale-form').classList.toggle('hidden')" class="px-3 py-1.5 text-sm bg-gray-800 text-white rounded hover:bg-gray-700">✏️ Edytuj</button>
                                <form method="POST" action="{{ route('sales.destroy', $vehicle) }}" onsubmit="return confirm('Usunąć sprzedaż? Auto wróci do stocku.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">🗑</button>
                                </form>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 mb-2">Auto jeszcze nie sprzedane.</p>
                            <button type="button" onclick="document.getElementById('sale-form').classList.toggle('hidden')" class="px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-lg hover:bg-green-700">🟢 Sprzedaj auto</button>
                        @endif

                        <form id="sale-form" method="POST" action="{{ route('sales.store', $vehicle) }}" class="mt-3 p-3 bg-gray-50 rounded hidden">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Klient *</label>
                                    <select name="contractor_id" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded" onchange="document.getElementById('new-customer-block').classList.toggle('hidden', this.value !== '')">
                                        <option value="">— wybierz lub wpisz nowego —</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" @selected($vehicle->sale?->contractor_id == $c->id)>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="new-customer-block" class="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-1 @if($vehicle->sale?->contractor_id) hidden @endif">
                                        <input type="text" name="new_contractor_name" placeholder="Imię i nazwisko" class="px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        <input type="tel" name="new_contractor_phone" placeholder="Telefon" class="px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        <input type="email" name="new_contractor_email" placeholder="Email" class="px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        <input type="text" name="new_contractor_eir_code" placeholder="Eircode" class="px-2 py-1.5 text-sm border-2 border-gray-300 rounded uppercase">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Data *</label>
                                    <input type="date" name="sale_date" required value="{{ old('sale_date', $vehicle->sale?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Cena € *</label>
                                    <input type="number" name="sale_price" required step="0.01" min="0" value="{{ old('sale_price', $vehicle->sale?->sale_price) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded font-bold">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Płatność *</label>
                                    <select name="payment_method" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        @foreach($paymentLabels as $key => $label)
                                            <option value="{{ $key }}" @selected(($vehicle->sale?->payment_method ?? 'bank_transfer') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Gwarancja</label>
                                    <select name="warranty" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                        <option value="">— brak —</option>
                                        <option value="12 MX Car protect" @selected($vehicle->sale?->warranty === '12 MX Car protect')>12 MX</option>
                                        <option value="6 MX Car protect" @selected($vehicle->sale?->warranty === '6 MX Car protect')>6 MX</option>
                                        <option value="no warranty" @selected($vehicle->sale?->warranty === 'no warranty')>No warranty</option>
                                    </select>
                                </div>
                            </div>

                            <h4 class="mt-3 mb-1 text-xs font-bold text-gray-700">💰 Rozbicie płatności</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-xs mb-1">Kredyt</label>
                                    <input type="number" name="payment_credit" step="0.01" min="0" value="{{ old('payment_credit', $vehicle->sale?->payment_credit ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Bank</label>
                                    <input type="number" name="payment_bank" step="0.01" min="0" value="{{ old('payment_bank', $vehicle->sale?->payment_bank ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Gotówka</label>
                                    <input type="number" name="payment_cash_deposit" step="0.01" min="0" value="{{ old('payment_cash_deposit', $vehicle->sale?->payment_cash_deposit ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Trade</label>
                                    <input type="number" name="payment_trade" step="0.01" min="0" value="{{ old('payment_trade', $vehicle->sale?->payment_trade ?? 0) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="block text-xs font-semibold mb-1">Nr umowy (FFU/JRK)</label>
                                <input type="text" name="credit_contract_number" value="{{ old('credit_contract_number', $vehicle->sale?->credit_contract_number) }}" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">
                            </div>

                            <div class="mt-2">
                                <label class="block text-xs font-semibold mb-1">Notatki</label>
                                <textarea name="notes" rows="2" class="w-full px-2 py-1.5 text-sm border-2 border-gray-300 rounded">{{ old('notes', $vehicle->sale?->notes) }}</textarea>
                            </div>

                            <button type="submit" class="mt-3 px-6 py-2 bg-green-600 text-white text-sm font-bold rounded hover:bg-green-700">💾 Zapisz sprzedaż</button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>

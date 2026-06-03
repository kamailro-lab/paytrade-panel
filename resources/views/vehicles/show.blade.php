@php
    $suppliers = \App\Models\Contractor::suppliers()->orderBy('name')->get(['id', 'name']);
    $customers = \App\Models\Contractor::customers()->orderBy('name')->get(['id', 'name']);
    $statusLabels = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
    $categoryLabels = ['repair' => 'Naprawa', 'parts' => 'Części', 'advertising' => 'Reklama', 'cleaning' => 'Czyszczenie', 'transport' => 'Transport', 'other' => 'Inne'];
    $paymentLabels = ['cash' => 'Gotówka', 'bank_transfer' => 'Przelew', 'card' => 'Karta', 'financing' => 'Finansowanie', 'other' => 'Inne'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
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

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 border border-red-300 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            {{-- ⭐ NCT na samej górze - banner z kolorem statusu --}}
            @php
                $nctStatus = $vehicle->nctStatus();
                $daysLeft = $vehicle->nctDaysLeft();
                $nctBg = match($nctStatus) {
                    'valid' => 'bg-green-100 border-green-400 text-green-900',
                    'expiring' => 'bg-yellow-100 border-yellow-400 text-yellow-900',
                    'expired' => 'bg-red-100 border-red-400 text-red-900',
                    default => 'bg-gray-100 border-gray-300 text-gray-700',
                };
                $nctIcon = match($nctStatus) {
                    'valid' => '✅',
                    'expiring' => '⚠️',
                    'expired' => '🚨',
                    default => '❓',
                };
                $nctText = match($nctStatus) {
                    'valid' => 'NCT ważne (' . abs($daysLeft) . ' dni do końca)',
                    'expiring' => 'NCT wygasa za ' . abs($daysLeft) . ' dni!',
                    'expired' => 'NCT WYGASŁO ' . abs($daysLeft) . ' dni temu',
                    default => 'NCT — brak daty',
                };
            @endphp
            <div class="rounded-lg border-2 p-5 {{ $nctBg }} shadow-md">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-4">
                        <div class="text-5xl">{{ $nctIcon }}</div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide opacity-75">NCT — Przegląd techniczny</div>
                            <div class="text-2xl font-bold">{{ $nctText }}</div>
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

            {{-- 📊 Gotowość do sprzedaży --}}
            @php
                $readiness = $vehicle->readinessPercent();
            @endphp
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-gray-800">📊 Gotowość do sprzedaży</h3>
                    <span class="text-2xl font-bold {{ $readiness === 100 ? 'text-green-600' : ($readiness >= 67 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $readiness }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden mb-3">
                    <div class="{{ $vehicle->readinessColor() }} h-full transition-all" style="width: {{ $readiness }}%"></div>
                </div>
                <div class="text-sm font-semibold text-gray-700 mb-3">{{ $vehicle->readinessLabel() }}</div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- NCT --}}
                    <div class="p-3 rounded border-2 {{ $vehicle->isNctValid() ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $vehicle->isNctValid() ? '✅' : '⬜' }}</span>
                            <strong>NCT</strong>
                        </div>
                        @if($vehicle->nct_expiry)
                            <div class="text-xs text-gray-600 mt-1">do {{ $vehicle->nct_expiry->format('d.m.Y') }}</div>
                        @endif
                    </div>

                    {{-- Serwis --}}
                    <div class="p-3 rounded border-2 {{ $vehicle->service_done ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $vehicle->service_done ? '✅' : '⬜' }}</span>
                            <strong>🔧 Serwis</strong>
                        </div>
                        @if($vehicle->service_date)
                            <div class="text-xs text-gray-600 mt-1">{{ $vehicle->service_date->format('d.m.Y') }}</div>
                        @endif
                    </div>

                    {{-- Rozrząd --}}
                    <div class="p-3 rounded border-2 {{ $vehicle->timing_belt_checked ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $vehicle->timing_belt_checked ? '✅' : '⬜' }}</span>
                            <strong>⚙️ Rozrząd</strong>
                        </div>
                        @if($vehicle->timing_belt_date)
                            <div class="text-xs text-gray-600 mt-1">{{ $vehicle->timing_belt_date->format('d.m.Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vehicle data --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Dane auta</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Rejestracja</dt><dd class="font-mono font-bold">{{ $vehicle->registration }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Logbook (VRC)</dt><dd>{{ $vehicle->logbook_no ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Marka i model</dt><dd>{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Rok</dt><dd>{{ $vehicle->year ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Silnik</dt><dd>{{ $vehicle->engine_cc ? $vehicle->engine_cc.' ccm' : '—' }} · {{ ucfirst($vehicle->fuel ?? '—') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Przebieg</dt><dd>{{ $vehicle->mileage_km ? number_format($vehicle->mileage_km, 0, ',', ' ').' '.($vehicle->mileage_unit ?? 'km') : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Nadwozie / drzwi</dt><dd>{{ ucfirst($vehicle->body ?? '—') }} · {{ $vehicle->doors ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Kolor</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">NCT do</dt><dd>{{ $vehicle->nct_expiry?->format('d.m.Y') ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Status</dt><dd class="font-semibold">{{ $statusLabels[$vehicle->status] }}</dd></div>
                </dl>
                @if($vehicle->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded">
                        <dt class="text-sm text-gray-500 mb-1">Notatki</dt>
                        <dd class="whitespace-pre-wrap">{{ $vehicle->notes }}</dd>
                    </div>
                @endif
            </div>

            {{-- P&L summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Koszt całkowity</div>
                    <div class="text-2xl font-bold text-amber-700">€{{ number_format($vehicle->totalCost(), 2, ',', ' ') }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Cena sprzedaży</div>
                    <div class="text-2xl font-bold text-blue-700">{{ $vehicle->sale ? '€'.number_format($vehicle->sale->sale_price, 2, ',', ' ') : '—' }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Marża (zysk)</div>
                    @if($vehicle->margin() !== null)
                        <div class="text-2xl font-bold {{ $vehicle->margin() >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $vehicle->margin() >= 0 ? '+' : '' }}€{{ number_format($vehicle->margin(), 2, ',', ' ') }}
                        </div>
                    @else
                        <div class="text-2xl font-bold text-gray-400">—</div>
                    @endif
                </div>
            </div>

            {{-- PURCHASE SECTION --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🚚 Zakup od dostawcy</h3>
                @if($vehicle->purchase)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div><dt class="text-sm text-gray-500">Dostawca</dt><dd class="font-semibold">{{ $vehicle->purchase->contractor->name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Data zakupu</dt><dd>{{ $vehicle->purchase->purchase_date->format('d.m.Y') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Cena</dt><dd class="font-bold">{{ $vehicle->purchase->currency }} {{ number_format($vehicle->purchase->purchase_price, 2, ',', ' ') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">VRT</dt><dd>€{{ number_format($vehicle->purchase->vrt_paid, 2, ',', ' ') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Transport</dt><dd>€{{ number_format($vehicle->purchase->transport_cost, 2, ',', ' ') }}</dd></div>
                    </dl>
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('purchase-form').classList.toggle('hidden')" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">✏️ Edytuj zakup</button>
                        <form method="POST" action="{{ route('purchases.destroy', $vehicle) }}" onsubmit="return confirm('Usunąć dane zakupu?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">🗑 Usuń zakup</button>
                        </form>
                    </div>
                @else
                    <p class="text-gray-500 mb-3">Brak danych zakupu.</p>
                    <button type="button" onclick="document.getElementById('purchase-form').classList.toggle('hidden')" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">➕ Dodaj zakup</button>
                @endif

                <form id="purchase-form" method="POST" action="{{ route('purchases.store', $vehicle) }}" class="mt-4 p-4 bg-gray-50 rounded-lg @if(!$vehicle->purchase || !$errors->any()) hidden @endif">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold mb-1">Dostawca *</label>
                            <select name="contractor_id" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg" onchange="document.getElementById('new-supplier').classList.toggle('hidden', this.value !== '')">
                                <option value="">— wybierz lub wpisz nowego niżej —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected($vehicle->purchase?->contractor_id == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <input id="new-supplier" type="text" name="new_contractor_name" placeholder="lub: nowy dostawca - wpisz imię" class="mt-2 w-full px-3 py-2 border-2 border-gray-300 rounded-lg @if($vehicle->purchase?->contractor_id) hidden @endif">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Data zakupu *</label>
                            <input type="date" name="purchase_date" required value="{{ old('purchase_date', $vehicle->purchase?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Waluta *</label>
                            <select name="currency" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                                <option value="EUR" @selected(($vehicle->purchase?->currency ?? 'EUR') === 'EUR')>EUR €</option>
                                <option value="GBP" @selected($vehicle->purchase?->currency === 'GBP')>GBP £</option>
                                <option value="USD" @selected($vehicle->purchase?->currency === 'USD')>USD $</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Cena zakupu *</label>
                            <input type="number" name="purchase_price" required step="0.01" min="0" value="{{ old('purchase_price', $vehicle->purchase?->purchase_price) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">VRT zapłacone</label>
                            <input type="number" name="vrt_paid" step="0.01" min="0" value="{{ old('vrt_paid', $vehicle->purchase?->vrt_paid ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Transport €</label>
                            <input type="number" name="transport_cost" step="0.01" min="0" value="{{ old('transport_cost', $vehicle->purchase?->transport_cost ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold mb-1">Notatki</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">{{ old('notes', $vehicle->purchase?->notes) }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-4 px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">💾 Zapisz zakup</button>
                </form>
            </div>

            {{-- COSTS SECTION --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🔧 Koszty ({{ $vehicle->costs->count() }}) — łącznie €{{ number_format($vehicle->costs->sum('amount'), 2, ',', ' ') }}</h3>
                @if($vehicle->costs->isNotEmpty())
                    <ul class="divide-y mb-4">
                        @foreach($vehicle->costs as $cost)
                            <li class="py-2 flex items-center justify-between">
                                <div>
                                    <span class="font-semibold">{{ $categoryLabels[$cost->category] }}:</span>
                                    {{ $cost->description }}
                                    <span class="text-sm text-gray-500">· {{ $cost->cost_date->format('d.m.Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold">€{{ number_format($cost->amount, 2, ',', ' ') }}</span>
                                    <form method="POST" action="{{ route('costs.destroy', [$vehicle, $cost]) }}" onsubmit="return confirm('Usunąć ten koszt?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">🗑</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <button type="button" onclick="document.getElementById('cost-form').classList.toggle('hidden')" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">➕ Dodaj koszt</button>

                <form id="cost-form" method="POST" action="{{ route('costs.store', $vehicle) }}" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Kategoria *</label>
                            <select name="category" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                                @foreach($categoryLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold mb-1">Opis *</label>
                            <input type="text" name="description" required class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Kwota € *</label>
                            <input type="number" name="amount" required step="0.01" min="0" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Data *</label>
                            <input type="date" name="cost_date" required value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">💾 Dodaj koszt</button>
                </form>
            </div>

            {{-- SALE SECTION --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🤝 Sprzedaż klientowi</h3>
                @if($vehicle->sale)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div><dt class="text-sm text-gray-500">Klient</dt><dd class="font-semibold">{{ $vehicle->sale->contractor->name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Data sprzedaży</dt><dd>{{ $vehicle->sale->sale_date->format('d.m.Y') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Cena</dt><dd class="font-bold text-xl">€{{ number_format($vehicle->sale->sale_price, 2, ',', ' ') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Metoda</dt><dd>{{ $paymentLabels[$vehicle->sale->payment_method] }}</dd></div>
                        @if($vehicle->sale->paymentTotal() > 0)
                            <div class="sm:col-span-2">
                                <dt class="text-sm text-gray-500 mb-1">Rozbicie płatności</dt>
                                <dd class="text-sm">
                                    @if($vehicle->sale->payment_credit > 0) Kredyt: €{{ number_format($vehicle->sale->payment_credit, 0, ',', ' ') }} · @endif
                                    @if($vehicle->sale->payment_bank > 0) Bank: €{{ number_format($vehicle->sale->payment_bank, 0, ',', ' ') }} · @endif
                                    @if($vehicle->sale->payment_cash_deposit > 0) Gotówka/depozyt: €{{ number_format($vehicle->sale->payment_cash_deposit, 0, ',', ' ') }} · @endif
                                    @if($vehicle->sale->payment_trade > 0) Trade-in: €{{ number_format($vehicle->sale->payment_trade, 0, ',', ' ') }} @endif
                                </dd>
                            </div>
                        @endif
                        @if($vehicle->sale->warranty)
                            <div><dt class="text-sm text-gray-500">Gwarancja</dt><dd>{{ $vehicle->sale->warranty }}</dd></div>
                        @endif
                        @if($vehicle->sale->credit_contract_number)
                            <div><dt class="text-sm text-gray-500">Nr umowy kredytu</dt><dd>{{ $vehicle->sale->credit_contract_number }}</dd></div>
                        @endif
                    </dl>
                    <div class="flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('invoices.generate', $vehicle) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                                📄 {{ $vehicle->sale->invoice ? 'Regeneruj fakturę PDF' : 'Wygeneruj fakturę PDF' }}
                            </button>
                        </form>
                        @if($vehicle->sale->invoice && $vehicle->sale->invoice->pdf_path)
                            <a href="{{ route('invoices.download', $vehicle->sale->invoice) }}" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700">
                                📥 Pobierz {{ $vehicle->sale->invoice->invoice_number }}
                            </a>
                        @endif
                        <button type="button" onclick="document.getElementById('sale-form').classList.toggle('hidden')" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">✏️ Edytuj sprzedaż</button>
                        <form method="POST" action="{{ route('sales.destroy', $vehicle) }}" onsubmit="return confirm('Usunąć sprzedaż? Auto wróci do stocku.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">🗑 Usuń sprzedaż</button>
                        </form>
                    </div>
                @else
                    <p class="text-gray-500 mb-3">Auto jeszcze nie sprzedane.</p>
                    <button type="button" onclick="document.getElementById('sale-form').classList.toggle('hidden')" class="px-5 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700">🟢 Sprzedaj auto</button>
                @endif

                <form id="sale-form" method="POST" action="{{ route('sales.store', $vehicle) }}" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold mb-1">Klient *</label>
                            <select name="contractor_id" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg" onchange="document.getElementById('new-customer-block').classList.toggle('hidden', this.value !== '')">
                                <option value="">— wybierz lub wpisz nowego niżej —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected($vehicle->sale?->contractor_id == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div id="new-customer-block" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 @if($vehicle->sale?->contractor_id) hidden @endif">
                                <input type="text" name="new_contractor_name" placeholder="Imię i nazwisko klienta" class="px-3 py-2 border-2 border-gray-300 rounded-lg">
                                <input type="tel" name="new_contractor_phone" placeholder="Telefon" class="px-3 py-2 border-2 border-gray-300 rounded-lg">
                                <input type="email" name="new_contractor_email" placeholder="Email" class="px-3 py-2 border-2 border-gray-300 rounded-lg">
                                <input type="text" name="new_contractor_eir_code" placeholder="Eircode" class="px-3 py-2 border-2 border-gray-300 rounded-lg uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Data sprzedaży *</label>
                            <input type="date" name="sale_date" required value="{{ old('sale_date', $vehicle->sale?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Cena sprzedaży € *</label>
                            <input type="number" name="sale_price" required step="0.01" min="0" value="{{ old('sale_price', $vehicle->sale?->sale_price) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-lg font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Metoda płatności *</label>
                            <select name="payment_method" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                                @foreach($paymentLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($vehicle->sale?->payment_method ?? 'bank_transfer') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Gwarancja</label>
                            <select name="warranty" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                                <option value="">— brak —</option>
                                <option value="12 MX Car protect" @selected($vehicle->sale?->warranty === '12 MX Car protect')>12 MX Car protect</option>
                                <option value="6 MX Car protect" @selected($vehicle->sale?->warranty === '6 MX Car protect')>6 MX Car protect</option>
                                <option value="no warranty" @selected($vehicle->sale?->warranty === 'no warranty')>No warranty</option>
                            </select>
                        </div>
                    </div>

                    <h4 class="mt-5 mb-2 font-semibold text-gray-700">💰 Rozbicie płatności (opcjonalne)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Kredyt €</label>
                            <input type="number" name="payment_credit" step="0.01" min="0" value="{{ old('payment_credit', $vehicle->sale?->payment_credit ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Bank €</label>
                            <input type="number" name="payment_bank" step="0.01" min="0" value="{{ old('payment_bank', $vehicle->sale?->payment_bank ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Gotówka/depozyt €</label>
                            <input type="number" name="payment_cash_deposit" step="0.01" min="0" value="{{ old('payment_cash_deposit', $vehicle->sale?->payment_cash_deposit ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Trade-in €</label>
                            <input type="number" name="payment_trade" step="0.01" min="0" value="{{ old('payment_trade', $vehicle->sale?->payment_trade ?? 0) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-semibold mb-1">Nr umowy kredytu (FFU/JRK/tradehub/własny)</label>
                        <input type="text" name="credit_contract_number" value="{{ old('credit_contract_number', $vehicle->sale?->credit_contract_number) }}" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-semibold mb-1">Notatki</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">{{ old('notes', $vehicle->sale?->notes) }}</textarea>
                    </div>

                    <button type="submit" class="mt-4 px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 text-lg">💾 Zapisz sprzedaż</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

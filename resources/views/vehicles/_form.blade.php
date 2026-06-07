@php
    $fuels = ['petrol' => 'Benzyna', 'diesel' => 'Diesel', 'hybrid' => 'Hybryda', 'electric' => 'Elektryk', 'lpg' => 'LPG'];
    $bodies = ['sedan' => 'Sedan', 'hatchback' => 'Hatchback', 'suv' => 'SUV', 'coupe' => 'Coupe', 'estate' => 'Kombi', 'mpv' => 'Van', 'convertible' => 'Kabriolet', 'pickup' => 'Pickup'];
    $statuses = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">

    {{-- ═══════════════ LEWA KOLUMNA: DANE AUTA ═══════════════ --}}
    <section class="bg-white border-2 border-gray-200 rounded-lg p-5">
        <h3 class="text-base font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b-2 border-indigo-500">
            🚗 DANE AUTA
        </h3>

        <div class="space-y-4">
            {{-- Rejestracja --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Numer rejestracyjny *</label>
                <input type="text" id="registration-input" name="registration" required
                       value="{{ old('registration', $vehicle->registration) }}"
                       placeholder="np. 131D1108"
                       class="w-full px-3 py-2 text-lg border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
                @error('registration') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Bez myślników — system sam doda.</p>
                <div id="reg-decoded" class="hidden mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-900"></div>
            </div>

            {{-- AI section (gdy klucz ustawiony) --}}
            <div id="ai-section" class="bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-lg p-3 hidden">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-indigo-900">🤖 AI wypełnij</h4>
                    <span id="ai-status" class="text-xs text-green-600">✅ AI gotowe</span>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <button type="button" id="ai-tab-desc" class="px-2 py-1.5 text-xs bg-white border border-indigo-300 rounded font-semibold text-indigo-700 hover:bg-indigo-100">📝 Opis</button>
                    <button type="button" id="ai-tab-photo" class="px-2 py-1.5 text-xs bg-white border border-indigo-300 rounded font-semibold text-indigo-700 hover:bg-indigo-100">📷 Zdjęcie</button>
                </div>
                <div id="ai-panel-desc" class="hidden">
                    <textarea id="ai-description" rows="2" placeholder="np. BMW 116 z 2013, diesel 1995cc, czarny"
                              class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded"></textarea>
                    <button type="button" id="ai-submit-desc" class="mt-1 px-3 py-1.5 text-xs bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700">
                        🪄 Wypełnij
                    </button>
                </div>
                <div id="ai-panel-photo" class="hidden">
                    <input type="file" id="ai-image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
                    <button type="button" id="ai-submit-photo" class="mt-1 px-3 py-1.5 text-xs bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700">
                        🪄 Odczytaj
                    </button>
                </div>
                <div id="ai-message" class="hidden mt-2 p-2 rounded text-xs"></div>
            </div>

            {{-- Logbook + Status side by side --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Logbook (VRC)</label>
                    <input type="text" name="logbook_no"
                           value="{{ old('logbook_no', $vehicle->logbook_no) }}"
                           placeholder="np. AB1234567"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $vehicle->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Marka + Model --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Marka *</label>
                    <input type="text" name="make" required
                           value="{{ old('make', $vehicle->make) }}"
                           placeholder="np. Volkswagen"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                    @error('make') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Model *</label>
                    <input type="text" name="model" required
                           value="{{ old('model', $vehicle->model) }}"
                           placeholder="np. Passat"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                    @error('model') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Rok + CC --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Rok</label>
                    <input type="number" name="year" min="1950" max="{{ date('Y') + 1 }}"
                           value="{{ old('year', $vehicle->year) }}"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Silnik (ccm)</label>
                    <input type="number" name="engine_cc" min="50" max="10000"
                           value="{{ old('engine_cc', $vehicle->engine_cc) }}"
                           placeholder="1968"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- Paliwo + Nadwozie --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Paliwo</label>
                    <select name="fuel" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        <option value="">— wybierz —</option>
                        @foreach($fuels as $key => $label)
                            <option value="{{ $key }}" @selected(old('fuel', $vehicle->fuel) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nadwozie</label>
                    <select name="body" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        <option value="">— wybierz —</option>
                        @foreach($bodies as $key => $label)
                            <option value="{{ $key }}" @selected(old('body', $vehicle->body) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Kolor --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kolor</label>
                <input type="text" name="color"
                       value="{{ old('color', $vehicle->color) }}"
                       placeholder="np. Czarny metalik"
                       class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Przebieg + Drzwi --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Przebieg (km)</label>
                    <input type="number" name="mileage_km" min="0" max="2000000"
                           value="{{ old('mileage_km', $vehicle->mileage_km) }}"
                           placeholder="145000"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Drzwi</label>
                    <select name="doors" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        <option value="">—</option>
                        @foreach([2,3,4,5,7] as $d)
                            <option value="{{ $d }}" @selected(old('doors', $vehicle->doors) == $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ PRAWA KOLUMNA: ZAKUP + CENY + DANE SERWISOWE ═══════════════ --}}
    <section class="bg-white border-2 border-gray-200 rounded-lg p-5">
        <h3 class="text-base font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b-2 border-green-500">
            💰 ZAKUP &amp; CENY
        </h3>

        {{-- Cena zakupu + docelowa --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cena zakupu (€)</label>
                <input type="number" name="purchase_price" step="0.01" min="0"
                       value="{{ old('purchase_price', $vehicle->purchase?->purchase_price) }}"
                       placeholder="np. 11500"
                       class="w-full px-3 py-2 text-lg border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">Łączna cena auta</p>
                @error('purchase_price') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cena docelowa (€)</label>
                <input type="number" name="target_price" step="0.01" min="0"
                       value="{{ old('target_price', $vehicle->target_price) }}"
                       placeholder="np. 13999"
                       class="w-full px-3 py-2 text-lg border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">Za ile chcesz sprzedać</p>
                @error('target_price') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Skąd auto przyjechało --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">📍 Skąd auto przyjechało</label>
            <select name="source" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                <option value="">— wybierz —</option>
                @foreach(\App\Models\Purchase::sourceOptions() as $key => $label)
                    <option value="{{ $key }}" @selected(old('source', $vehicle->purchase?->source) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" name="source_detail"
                   value="{{ old('source_detail', $vehicle->purchase?->source_detail) }}"
                   placeholder="Szczegóły: np. Copart Birmingham, Manheim, Pan Tomek z Cork..."
                   class="w-full mt-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            @error('source') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Dostawca (kontrahent) --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">👤 Dostawca auta</label>
            <div class="flex gap-2">
                <select name="supplier_contractor_id" class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                    <option value="">— bez konkretnego —</option>
                    @foreach(($suppliers ?? collect()) as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_contractor_id', $vehicle->purchase?->contractor_id) == $supplier->id)>
                            {{ $supplier->name }}@if($supplier->phone) — {{ $supplier->phone }}@endif
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('contractors.create') }}" target="_blank"
                   class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-lg font-bold hover:bg-indigo-200 text-sm whitespace-nowrap"
                   title="Otwiera w nowej karcie">
                    + Nowy
                </a>
            </div>
            <p class="mt-1 text-xs text-gray-500">Wybierz z listy lub dodaj nowego (nowa karta)</p>
        </div>

        {{-- Jak zapłacone (gotówka vs bank) --}}
        <div class="mb-6 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <label class="block text-sm font-semibold text-gray-700 mb-2">💵 Jak zapłacone za zakup</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">💶 Gotówka (€)</label>
                    <input type="number" name="paid_cash" step="0.01" min="0"
                           value="{{ old('paid_cash', $vehicle->purchase?->paid_cash) }}"
                           placeholder="0"
                           class="payment-input w-full px-3 py-2 border-2 border-amber-300 rounded-lg focus:border-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">🏦 Przelew bank (€)</label>
                    <input type="number" name="paid_bank" step="0.01" min="0"
                           value="{{ old('paid_bank', $vehicle->purchase?->paid_bank) }}"
                           placeholder="0"
                           class="payment-input w-full px-3 py-2 border-2 border-amber-300 rounded-lg focus:border-amber-500 focus:outline-none">
                </div>
            </div>
            <div id="payment-sum" class="mt-2 text-xs text-gray-700 font-semibold"></div>
        </div>

        {{-- ═══════════════ SEKCJA SPRZEDAŻY ═══════════════ --}}
        <h3 class="text-base font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b-2 border-blue-500">
            💸 SPRZEDAŻ
        </h3>

        {{-- Cena sprzedaży + gwarancja --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cena sprzedaży (€)</label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       value="{{ old('sale_price', $vehicle->sale?->sale_price) }}"
                       placeholder="np. 13999"
                       class="w-full px-3 py-2 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">Cena za którą sprzedałeś</p>
                @error('sale_price') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">🛡 Gwarancja</label>
                <select name="warranty_months" class="w-full px-3 py-2 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    @foreach(\App\Models\Sale::warrantyOptions() as $months => $label)
                        <option value="{{ $months }}" @selected(old('warranty_months', $vehicle->sale?->warranty_months ?? 0) == $months)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Ile miesięcy gwarancji dla klienta</p>
            </div>
        </div>

        {{-- Klient (kontrahent) --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">👤 Kto kupił (klient)</label>
            <div class="flex gap-2">
                <select name="sale_customer_contractor_id" class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    <option value="">— bez konkretnego —</option>
                    @foreach(($customers ?? collect()) as $customer)
                        <option value="{{ $customer->id }}" @selected(old('sale_customer_contractor_id', $vehicle->sale?->contractor_id) == $customer->id)>
                            {{ $customer->name }}@if($customer->phone) — {{ $customer->phone }}@endif
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('contractors.create') }}" target="_blank"
                   class="px-3 py-2 bg-blue-100 text-blue-700 rounded-lg font-bold hover:bg-blue-200 text-sm whitespace-nowrap"
                   title="Otwiera w nowej karcie">
                    + Nowy
                </a>
            </div>
            <p class="mt-1 text-xs text-gray-500">Wybierz z listy lub dodaj nowego klienta (nowa karta)</p>
        </div>

        {{-- Jak zapłacone (depozyt + gotówka + bank) --}}
        <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <label class="block text-sm font-semibold text-gray-700 mb-2">💵 Jak zapłacono za sprzedaż</label>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">💰 Depozyt (€)</label>
                    <input type="number" name="sale_deposit" step="0.01" min="0"
                           value="{{ old('sale_deposit', $vehicle->sale?->deposit) }}"
                           placeholder="0"
                           class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">💶 Gotówka (€)</label>
                    <input type="number" name="sale_paid_cash" step="0.01" min="0"
                           value="{{ old('sale_paid_cash', $vehicle->sale?->paid_cash) }}"
                           placeholder="0"
                           class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">🏦 Bank (€)</label>
                    <input type="number" name="sale_paid_bank" step="0.01" min="0"
                           value="{{ old('sale_paid_bank', $vehicle->sale?->paid_bank) }}"
                           placeholder="0"
                           class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div id="sale-sum" class="mt-2 text-xs text-gray-700 font-semibold"></div>
            <p class="mt-1 text-xs text-gray-500 italic">Suma depozyt+gotówka+bank powinna = cena sprzedaży</p>
        </div>

        <h3 class="text-base font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b-2 border-amber-500">
            🛠 DANE SERWISOWE
        </h3>

        <div class="space-y-4">
            {{-- NCT — DUŻY, na pierwszym miejscu --}}
            <div class="p-4 bg-amber-50 rounded-lg border-2 border-amber-300">
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" name="nct_passed" id="nct_passed" value="1"
                           @checked(old('nct_passed', $vehicle->nct_passed))
                           class="w-6 h-6 accent-green-600 cursor-pointer">
                    <label for="nct_passed" class="text-lg font-bold cursor-pointer">🟢 NCT zaliczone</label>
                </div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NCT ważne do:</label>
                <input type="date" name="nct_expiry"
                       value="{{ old('nct_expiry', $vehicle->nct_expiry?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Serwis --}}
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="service_done" id="service_done" value="1"
                           @checked(old('service_done', $vehicle->service_done))
                           class="w-5 h-5 accent-green-600 cursor-pointer">
                    <label for="service_done" class="font-semibold cursor-pointer">🔧 Serwis zrobiony</label>
                </div>
                <label class="block text-xs text-gray-600 mb-1">Data serwisu:</label>
                <input type="date" name="service_date"
                       value="{{ old('service_date', $vehicle->service_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Rozrząd --}}
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="timing_belt_checked" id="timing_belt_checked" value="1"
                           @checked(old('timing_belt_checked', $vehicle->timing_belt_checked))
                           class="w-5 h-5 accent-green-600 cursor-pointer">
                    <label for="timing_belt_checked" class="font-semibold cursor-pointer">⚙️ Rozrząd sprawdzony</label>
                </div>
                <label class="block text-xs text-gray-600 mb-1">Data sprawdzenia:</label>
                <input type="date" name="timing_belt_date"
                       value="{{ old('timing_belt_date', $vehicle->timing_belt_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            </div>

        </div>
    </section>

    {{-- ═══════════════ TRZECIA KOLUMNA (tylko na 2xl): NOTATKI ═══════════════ --}}
    <section class="bg-white border-2 border-gray-200 rounded-lg p-5">
        <h3 class="text-base font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b-2 border-gray-400">
            📝 NOTATKI
        </h3>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Dodatkowe informacje o aucie</label>
            <textarea name="notes" rows="14"
                      placeholder="np. stan idealny, jedna ręka, serwisowane w ASO, kupione z UK, koło zapasowe pełnowymiarowe, hak, klimatyzacja sprawna..."
                      class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">{{ old('notes', $vehicle->notes) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Możesz używać mikrofonu 🎤 (prawy dolny) żeby dyktować notatki</p>
        </div>
    </section>

</div>

{{-- Akcje na dole, pełna szerokość --}}
<div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
    <a href="{{ route('vehicles.index') }}" class="px-6 py-3 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300 transition text-center">
        ✖ Anuluj
    </a>
    <button type="submit" class="px-8 py-3 bg-green-600 text-white text-lg font-bold rounded-lg hover:bg-green-700 transition">
        💾 Zapisz
    </button>
</div>

<script>
// Walidacja sumy płatności vs cena - reużywalne dla zakup i sprzedaż
function paymentSumHelper(boxId, priceField, payFields, ceneLabel) {
    const sum = payFields.reduce((acc, f) => {
        return acc + (parseFloat(document.querySelector(`[name="${f}"]`)?.value) || 0);
    }, 0);
    const price = parseFloat(document.querySelector(`[name="${priceField}"]`)?.value) || 0;
    const box = document.getElementById(boxId);
    if (!box) return;

    if (sum === 0 && price === 0) { box.textContent = ''; return; }

    let html = `💵 Razem: <strong>€${sum.toFixed(2)}</strong>`;
    if (price > 0) {
        const diff = sum - price;
        if (Math.abs(diff) < 0.01) {
            html += ` ✅ <span class="text-green-700">= ${ceneLabel}</span>`;
        } else if (diff < 0) {
            html += ` ⚠️ <span class="text-amber-700">do zapłaty: €${Math.abs(diff).toFixed(2)}</span>`;
        } else {
            html += ` ℹ️ <span class="text-blue-700">+€${diff.toFixed(2)} (więcej niż ${ceneLabel})</span>`;
        }
    }
    box.innerHTML = html;
}

function updatePaymentSum() {
    paymentSumHelper('payment-sum', 'purchase_price', ['paid_cash', 'paid_bank'], 'cena zakupu');
}
function updateSaleSum() {
    paymentSumHelper('sale-sum', 'sale_price', ['sale_deposit', 'sale_paid_cash', 'sale_paid_bank'], 'cena sprzedaży');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[name="paid_cash"], [name="paid_bank"], [name="purchase_price"]')
        .forEach(el => el.addEventListener('input', updatePaymentSum));
    document.querySelectorAll('[name="sale_deposit"], [name="sale_paid_cash"], [name="sale_paid_bank"], [name="sale_price"]')
        .forEach(el => el.addEventListener('input', updateSaleSum));
    updatePaymentSum();
    updateSaleSum();
});

(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const $ = (s) => document.querySelector(s);
    const regInput = $('#registration-input');
    const aiStatus = $('#ai-status');
    const aiMessage = $('#ai-message');
    const tabs = {desc: $('#ai-panel-desc'), photo: $('#ai-panel-photo')};
    const decodedBox = document.getElementById('reg-decoded');

    function setIfEmpty(name, value) {
        if (value === null || value === undefined || value === '') return;
        const el = document.querySelector(`[name="${name}"]`);
        if (!el || el.value) return;
        if (el.tagName === 'SELECT') {
            const opt = [...el.options].find(o => o.value === String(value));
            if (opt) el.value = opt.value;
        } else {
            el.value = value;
        }
    }

    // Nadpisuje pole NAWET jeśli ma wartość (dla MotorCheck - znamy dane lepiej niż user)
    function setForce(name, value) {
        if (value === null || value === undefined || value === '') return;
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;
        if (el.tagName === 'SELECT') {
            const opt = [...el.options].find(o => o.value === String(value));
            if (opt) el.value = opt.value;
        } else {
            el.value = value;
        }
        // Trigger event żeby Alpine/Vue/JS reagowały
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
        // Visual hint: krótkie podświetlenie
        const originalBg = el.style.backgroundColor;
        el.style.transition = 'background-color 0.3s';
        el.style.backgroundColor = '#d1fae5'; // light green
        setTimeout(() => { el.style.backgroundColor = originalBg; }, 800);
    }

    let lookupBusy = false;
    regInput.addEventListener('blur', async () => {
        const raw = regInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        const m = raw.match(/^(\d{2,3})([A-Z]{1,2})(\d{1,6})$/);
        if (m) regInput.value = `${m[1]}-${m[2]}-${m[3]}`;

        if (regInput.value.length < 4 || lookupBusy) return;
        lookupBusy = true;

        try {
            const r = await fetch('{{ route('vehicles.lookup.decode') }}?reg=' + encodeURIComponent(regInput.value));
            const j = await r.json();
            if (j.ok) {
                decodedBox.innerHTML = j.data.display + ' &nbsp; <span class="text-gray-500">(z rejestracji)</span>';
                decodedBox.classList.remove('hidden');
                setIfEmpty('year', j.data.year);
            } else {
                decodedBox.classList.add('hidden');
            }
        } catch (e) {}

        decodedBox.innerHTML += ' &nbsp; <span class="text-indigo-600">⏳ MotorCheck...</span>';
        decodedBox.classList.remove('hidden');

        try {
            const r = await fetch('{{ route('vehicles.lookup.motorcheck') }}?reg=' + encodeURIComponent(regInput.value));
            const j = await r.json();
            if (j.ok && j.data) {
                const d = j.data;
                // NADPISUJ wszystkie pola - MotorCheck zna najlepiej (oficjalne źródło IE)
                setForce('make', d.make);
                setForce('model', d.model);
                setForce('year', d.year);
                setForce('engine_cc', d.engine_cc);
                setForce('fuel', d.fuel);
                setForce('color', d.color);
                setForce('body', d.body);
                decodedBox.innerHTML = '✅ <strong>' + d.make + ' ' + d.model + ', ' + d.year + '</strong> — ' + (d.engine_cc || '?') + 'ccm · ' + (d.fuel || '?') + ' · ' + (d.color || '?') + ' <span class="text-gray-500">(motorcheck.ie — wypełniono pola)</span>';
                decodedBox.className = 'mt-2 p-2 bg-green-50 border border-green-300 rounded text-xs text-green-900';
            } else {
                decodedBox.innerHTML = decodedBox.innerHTML.replace(/⏳[^<]*MotorCheck[^<]*/, '<span class="text-amber-700">⚠️ Brak w MotorCheck</span>');
            }
        } catch (e) {
            decodedBox.innerHTML = decodedBox.innerHTML.replace(/⏳[^<]*MotorCheck[^<]*/, '<span class="text-amber-700">⚠️ MotorCheck niedostępny</span>');
        } finally {
            lookupBusy = false;
        }
    });

    fetch('{{ route('vehicles.lookup.status') }}', {headers: {Accept: 'application/json'}})
        .then(r => r.json())
        .then(d => {
            if (d.ai_configured) {
                document.getElementById('ai-section').classList.remove('hidden');
            }
        })
        .catch(() => {});

    $('#ai-tab-desc').addEventListener('click', () => { tabs.desc.classList.toggle('hidden'); tabs.photo.classList.add('hidden'); });
    $('#ai-tab-photo').addEventListener('click', () => { tabs.photo.classList.toggle('hidden'); tabs.desc.classList.add('hidden'); });

    function showMessage(text, ok) {
        aiMessage.textContent = text;
        aiMessage.className = 'mt-2 p-2 rounded text-xs ' + (ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
        aiMessage.classList.remove('hidden');
    }

    function setField(name, value) {
        if (value === null || value === undefined || value === '') return;
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;
        if (el.tagName === 'SELECT') {
            const opt = [...el.options].find(o => o.value === String(value));
            if (opt) el.value = opt.value;
        } else {
            el.value = value;
        }
    }

    function applyData(data) {
        const fields = ['logbook_no', 'make', 'model', 'year', 'engine_cc', 'fuel', 'color', 'mileage_km', 'body', 'doors'];
        let filled = 0;
        fields.forEach(f => {
            if (data[f] !== null && data[f] !== undefined && data[f] !== '') {
                setForce(f, data[f]);
                filled++;
            }
        });
        if (data.registration) setForce('registration', data.registration);
        showMessage(`✅ AI uzupełniło ${filled} pól.`, true);
    }

    $('#ai-submit-desc').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const desc = $('#ai-description').value.trim();
        if (!desc) { showMessage('Wpisz opis auta.', false); return; }
        if (!regInput.value.trim()) { showMessage('Wpisz najpierw rejestrację.', false); return; }
        btn.disabled = true; btn.textContent = '⏳...';
        try {
            const r = await fetch('{{ route('vehicles.lookup.description') }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({registration: regInput.value, description: desc}),
            });
            const j = await r.json();
            if (r.ok && j.ok) applyData(j.data);
            else showMessage(j.error || 'Błąd.', false);
        } catch (err) {
            showMessage('Błąd sieci: ' + err.message, false);
        } finally {
            btn.disabled = false; btn.textContent = '🪄 Wypełnij';
        }
    });

    $('#ai-submit-photo').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const file = $('#ai-image').files[0];
        if (!file) { showMessage('Wybierz zdjęcie.', false); return; }
        if (file.size > 5 * 1024 * 1024) { showMessage('Max 5MB.', false); return; }
        btn.disabled = true; btn.textContent = '⏳...';
        try {
            const fd = new FormData();
            fd.append('image', file);
            const r = await fetch('{{ route('vehicles.lookup.logbook') }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: fd,
            });
            const j = await r.json();
            if (r.ok && j.ok) applyData(j.data);
            else showMessage(j.error || 'Błąd.', false);
        } catch (err) {
            showMessage('Błąd sieci: ' + err.message, false);
        } finally {
            btn.disabled = false; btn.textContent = '🪄 Odczytaj';
        }
    });
})();
</script>

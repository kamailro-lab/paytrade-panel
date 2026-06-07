<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💸 Sprzedaż: {{ $vehicle->make }} {{ $vehicle->model }}
            <span class="text-gray-500 text-base">— {{ $vehicle->registration }}</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Skrót danych auta (read-only) --}}
            <div class="mb-6 bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Auto</div>
                        <div class="font-bold">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Cena zakupu</div>
                        <div class="font-bold text-amber-700">€{{ $vehicle->purchase ? number_format($vehicle->purchase->purchase_price, 0, ',', ' ') : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Cena docelowa</div>
                        <div class="font-bold text-purple-700">{{ $vehicle->target_price ? '€'.number_format($vehicle->target_price, 0, ',', ' ') : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Koszt total</div>
                        <div class="font-bold text-gray-700">€{{ number_format($vehicle->totalCost(), 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('vehicles.sale.update', $vehicle) }}">
                @csrf
                @method('PUT')

                <section class="bg-white border-2 border-blue-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-blue-500">
                        💸 Dane sprzedaży
                    </h3>

                    {{-- Cena sprzedaży + gwarancja --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Cena sprzedaży (€) *</label>
                            <input type="number" name="sale_price" step="0.01" min="0" required
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

                    {{-- Data sprzedaży --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">📅 Data sprzedaży</label>
                        <input type="date" name="sale_date"
                               value="{{ old('sale_date', $vehicle->sale?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>

                    {{-- Klient --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">👤 Kto kupił (klient)</label>
                        <div class="flex gap-2">
                            <select name="contractor_id" class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                                <option value="">— bez konkretnego —</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('contractor_id', $vehicle->sale?->contractor_id) == $customer->id)>
                                        {{ $customer->name }}@if($customer->phone) — {{ $customer->phone }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('contractors.create') }}" target="_blank"
                               class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-bold hover:bg-blue-200 whitespace-nowrap"
                               title="Otwiera w nowej karcie">
                                + Nowy
                            </a>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Wybierz z listy lub dodaj nowego klienta (nowa karta)</p>
                    </div>

                    {{-- Jak zapłacono --}}
                    <div class="mb-5 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                        <label class="block text-sm font-bold text-gray-700 mb-3">💵 Jak klient zapłacił</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">💰 Depozyt (€)</label>
                                <input type="number" name="deposit" step="0.01" min="0"
                                       value="{{ old('deposit', $vehicle->sale?->deposit) }}"
                                       placeholder="0"
                                       class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">💶 Gotówka (€)</label>
                                <input type="number" name="paid_cash" step="0.01" min="0"
                                       value="{{ old('paid_cash', $vehicle->sale?->paid_cash) }}"
                                       placeholder="0"
                                       class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">🏦 Przelew bank (€)</label>
                                <input type="number" name="paid_bank" step="0.01" min="0"
                                       value="{{ old('paid_bank', $vehicle->sale?->paid_bank) }}"
                                       placeholder="0"
                                       class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                        </div>
                        <div id="sale-payment-sum" class="mt-3 text-sm text-gray-700 font-semibold"></div>
                        <p class="mt-1 text-xs text-gray-500 italic">Suma depozyt + gotówka + bank musi się zgadzać z ceną sprzedaży</p>
                    </div>

                    {{-- Notatki --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">📝 Notatki do sprzedaży</label>
                        <textarea name="notes" rows="3"
                                  placeholder="np. klient zabrał auto 15.06, wymieniono klocki na koszt sprzedawcy..."
                                  class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('notes', $vehicle->sale?->notes) }}</textarea>
                    </div>

                    {{-- Akcje --}}
                    <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-200">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="px-6 py-3 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300 text-center">
                            ✖ Anuluj
                        </a>
                        @if($vehicle->sale)
                            <form method="POST" action="{{ route('vehicles.sale.destroy', $vehicle) }}" class="inline" onsubmit="return confirm('Usunąć dane sprzedaży? Auto wróci do statusu W stocku.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-6 py-3 bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 w-full">
                                    🗑 Usuń sprzedaż
                                </button>
                            </form>
                        @endif
                        <button type="submit" class="px-8 py-3 bg-blue-600 text-white text-lg font-bold rounded-lg hover:bg-blue-700">
                            💾 Zapisz sprzedaż
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>

    <script>
    function updateSalePaymentSum() {
        const deposit = parseFloat(document.querySelector('[name="deposit"]')?.value) || 0;
        const cash = parseFloat(document.querySelector('[name="paid_cash"]')?.value) || 0;
        const bank = parseFloat(document.querySelector('[name="paid_bank"]')?.value) || 0;
        const price = parseFloat(document.querySelector('[name="sale_price"]')?.value) || 0;
        const sum = deposit + cash + bank;
        const box = document.getElementById('sale-payment-sum');
        if (!box) return;

        if (sum === 0 && price === 0) { box.textContent = ''; return; }

        let html = `💵 Razem klient zapłacił: <strong>€${sum.toFixed(2)}</strong>`;
        if (price > 0) {
            const diff = sum - price;
            if (Math.abs(diff) < 0.01) {
                html += ` ✅ <span class="text-green-700">= cena sprzedaży</span>`;
            } else if (diff < 0) {
                html += ` ⚠️ <span class="text-amber-700">brakuje: €${Math.abs(diff).toFixed(2)}</span>`;
            } else {
                html += ` ℹ️ <span class="text-blue-700">+€${diff.toFixed(2)} (więcej niż cena)</span>`;
            }
        }
        box.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[name="deposit"], [name="paid_cash"], [name="paid_bank"], [name="sale_price"]')
            .forEach(el => el.addEventListener('input', updateSalePaymentSum));
        updateSalePaymentSum();
    });
    </script>
</x-app-layout>

@php
    $types = ['customer' => '🤝 Klient', 'supplier' => '🚚 Dostawca', 'both' => '🔁 Klient + Dostawca'];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Typ *</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach($types as $value => $label)
                <label class="flex items-center gap-2 px-4 py-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="type" value="{{ $value }}" @checked(old('type', $contractor->type) === $value) class="accent-indigo-600">
                    <span class="font-semibold">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('type') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Imię i nazwisko / nazwa firmy *</label>
        <input type="text" name="name" required
               value="{{ old('name', $contractor->name) }}"
               placeholder="np. Mr Doorveshteeka Sookun"
               class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('name') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">📱 Telefon</label>
        <input type="tel" name="phone"
               value="{{ old('phone', $contractor->phone) }}"
               placeholder="np. 837627492 lub +353 83 762 7492"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">📧 Email</label>
        <input type="email" name="email"
               value="{{ old('email', $contractor->email) }}"
               placeholder="np. martina.lane@example.ie"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('email') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">🏠 Adres</label>
        <input type="text" name="address"
               value="{{ old('address', $contractor->address) }}"
               placeholder="np. 123 O'Connell Street, Dublin"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">📮 Eircode</label>
        <input type="text" name="eir_code"
               value="{{ old('eir_code', $contractor->eir_code) }}"
               placeholder="np. R32RC43"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
        @error('eir_code') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">VAT Number (jeśli firma)</label>
        <input type="text" name="vat_number"
               value="{{ old('vat_number', $contractor->vat_number) }}"
               placeholder="np. IE1234567A"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">PPSN (jeśli osoba)</label>
        <input type="text" name="ppsn"
               value="{{ old('ppsn', $contractor->ppsn) }}"
               placeholder="np. 1234567A"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Notatki</label>
        <textarea name="notes" rows="2"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">{{ old('notes', $contractor->notes) }}</textarea>
    </div>
</div>

<div class="mt-8 flex flex-col sm:flex-row gap-3">
    <button type="submit" class="px-8 py-4 bg-green-600 text-white text-lg font-bold rounded-lg hover:bg-green-700 transition">
        💾 Zapisz
    </button>
    <a href="{{ route('contractors.index') }}" class="px-8 py-4 bg-gray-200 text-gray-800 text-lg font-bold rounded-lg hover:bg-gray-300 transition text-center">
        ✖ Anuluj
    </a>
</div>

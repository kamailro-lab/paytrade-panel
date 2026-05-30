@php
    $fuels = ['petrol' => 'Benzyna', 'diesel' => 'Diesel', 'hybrid' => 'Hybryda', 'electric' => 'Elektryk', 'lpg' => 'LPG'];
    $bodies = ['sedan' => 'Sedan', 'hatchback' => 'Hatchback', 'suv' => 'SUV', 'coupe' => 'Coupe', 'estate' => 'Kombi', 'mpv' => 'Van', 'convertible' => 'Kabriolet', 'pickup' => 'Pickup'];
    $statuses = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Numer rejestracyjny *</label>
        <input type="text" name="registration" required
               value="{{ old('registration', $vehicle->registration) }}"
               placeholder="np. 152-D-12345"
               class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
        @error('registration') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Format: 2-3 cyfry, kod hrabstwa (D, KE, CW...), numer</p>
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Numer logbooka (VRC)</label>
        <input type="text" name="logbook_no"
               value="{{ old('logbook_no', $vehicle->logbook_no) }}"
               placeholder="np. AB1234567"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
        @error('logbook_no') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $vehicle->status) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Marka *</label>
        <input type="text" name="make" required
               value="{{ old('make', $vehicle->make) }}"
               placeholder="np. Volkswagen"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('make') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Model *</label>
        <input type="text" name="model" required
               value="{{ old('model', $vehicle->model) }}"
               placeholder="np. Passat"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('model') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Rok</label>
        <input type="number" name="year" min="1950" max="{{ date('Y') + 1 }}"
               value="{{ old('year', $vehicle->year) }}"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('year') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Pojemność silnika (ccm)</label>
        <input type="number" name="engine_cc" min="50" max="10000"
               value="{{ old('engine_cc', $vehicle->engine_cc) }}"
               placeholder="np. 1968"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('engine_cc') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Paliwo</label>
        <select name="fuel" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            <option value="">— wybierz —</option>
            @foreach($fuels as $key => $label)
                <option value="{{ $key }}" @selected(old('fuel', $vehicle->fuel) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Nadwozie</label>
        <select name="body" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            <option value="">— wybierz —</option>
            @foreach($bodies as $key => $label)
                <option value="{{ $key }}" @selected(old('body', $vehicle->body) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Kolor</label>
        <input type="text" name="color"
               value="{{ old('color', $vehicle->color) }}"
               placeholder="np. Czarny metalik"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Przebieg (km)</label>
        <input type="number" name="mileage_km" min="0" max="2000000"
               value="{{ old('mileage_km', $vehicle->mileage_km) }}"
               placeholder="np. 145000"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('mileage_km') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">Drzwi</label>
        <select name="doors" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
            <option value="">—</option>
            @foreach([2,3,4,5,7] as $d)
                <option value="{{ $d }}" @selected(old('doors', $vehicle->doors) == $d)>{{ $d }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-base font-semibold text-gray-700 mb-1">NCT do (przegląd)</label>
        <input type="date" name="nct_expiry"
               value="{{ old('nct_expiry', $vehicle->nct_expiry?->format('Y-m-d')) }}"
               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Notatki</label>
        <textarea name="notes" rows="3"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">{{ old('notes', $vehicle->notes) }}</textarea>
    </div>
</div>

<div class="mt-8 flex flex-col sm:flex-row gap-3">
    <button type="submit" class="px-8 py-4 bg-green-600 text-white text-lg font-bold rounded-lg hover:bg-green-700 transition">
        💾 Zapisz
    </button>
    <a href="{{ route('vehicles.index') }}" class="px-8 py-4 bg-gray-200 text-gray-800 text-lg font-bold rounded-lg hover:bg-gray-300 transition text-center">
        ✖ Anuluj
    </a>
</div>

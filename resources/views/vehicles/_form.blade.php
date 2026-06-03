@php
    $fuels = ['petrol' => 'Benzyna', 'diesel' => 'Diesel', 'hybrid' => 'Hybryda', 'electric' => 'Elektryk', 'lpg' => 'LPG'];
    $bodies = ['sedan' => 'Sedan', 'hatchback' => 'Hatchback', 'suv' => 'SUV', 'coupe' => 'Coupe', 'estate' => 'Kombi', 'mpv' => 'Van', 'convertible' => 'Kabriolet', 'pickup' => 'Pickup'];
    $statuses = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-base font-semibold text-gray-700 mb-1">Numer rejestracyjny *</label>
        <input type="text" id="registration-input" name="registration" required
               value="{{ old('registration', $vehicle->registration) }}"
               placeholder="np. 131D1108 lub 131-D-1108"
               class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg uppercase focus:border-indigo-500 focus:outline-none">
        @error('registration') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Możesz wpisać bez myślników (np. <code>131D1108</code>) — system sam doda.</p>
        <div id="reg-decoded" class="hidden mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-900"></div>
    </div>

    <div id="ai-section" class="sm:col-span-2 bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-lg p-4 hidden">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-indigo-900">🤖 AI wypełnij za mnie</h3>
            <span id="ai-status" class="text-xs text-green-600">✅ AI gotowe</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <button type="button" id="ai-tab-desc" class="px-4 py-2 bg-white border-2 border-indigo-300 rounded-lg font-semibold text-indigo-700 hover:bg-indigo-100">📝 Wklej opis</button>
            <button type="button" id="ai-tab-photo" class="px-4 py-2 bg-white border-2 border-indigo-300 rounded-lg font-semibold text-indigo-700 hover:bg-indigo-100">📷 Zdjęcie logbooka</button>
        </div>

        <div id="ai-panel-desc" class="hidden">
            <textarea id="ai-description" rows="3" placeholder="np. BMW 116 z 2013, diesel 1995cc, czarny metalik, 5-drzwiowy, 145000km"
                      class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none"></textarea>
            <button type="button" id="ai-submit-desc" class="mt-2 px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                🪄 Wypełnij formularz
            </button>
        </div>

        <div id="ai-panel-photo" class="hidden">
            <input type="file" id="ai-image" accept="image/jpeg,image/png,image/webp" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg">
            <p class="mt-1 text-xs text-gray-500">Zrób telefonem ostre zdjęcie strony logbooka (max 5MB)</p>
            <button type="button" id="ai-submit-photo" class="mt-2 px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                🪄 Odczytaj i wypełnij
            </button>
        </div>

        <div id="ai-message" class="hidden mt-3 p-3 rounded text-sm"></div>
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

    <div class="sm:col-span-2 bg-amber-50 border-2 border-amber-200 rounded-lg p-4">
        <h3 class="font-bold text-amber-900 mb-3">📋 Stan techniczny / Gotowość do sprzedaży</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- NCT — najważniejszy, na pierwszym miejscu --}}
            <div class="sm:col-span-2 p-3 bg-white rounded-lg border-2 border-amber-300">
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="nct_passed" id="nct_passed" value="1"
                           @checked(old('nct_passed', $vehicle->nct_passed))
                           class="w-6 h-6 accent-green-600 cursor-pointer">
                    <label for="nct_passed" class="text-lg font-bold cursor-pointer">🟢 NCT zaliczone</label>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NCT ważne do:</label>
                    <input type="date" name="nct_expiry"
                           value="{{ old('nct_expiry', $vehicle->nct_expiry?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- Serwis --}}
            <div class="p-3 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="service_done" id="service_done" value="1"
                           @checked(old('service_done', $vehicle->service_done))
                           class="w-5 h-5 accent-green-600 cursor-pointer">
                    <label for="service_done" class="font-semibold cursor-pointer">🔧 Serwis zrobiony</label>
                </div>
                <label class="block text-xs text-gray-600 mb-1">Data serwisu:</label>
                <input type="date" name="service_date"
                       value="{{ old('service_date', $vehicle->service_date?->format('Y-m-d')) }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Rozrząd --}}
            <div class="p-3 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="timing_belt_checked" id="timing_belt_checked" value="1"
                           @checked(old('timing_belt_checked', $vehicle->timing_belt_checked))
                           class="w-5 h-5 accent-green-600 cursor-pointer">
                    <label for="timing_belt_checked" class="font-semibold cursor-pointer">⚙️ Rozrząd sprawdzony</label>
                </div>
                <label class="block text-xs text-gray-600 mb-1">Data sprawdzenia:</label>
                <input type="date" name="timing_belt_date"
                       value="{{ old('timing_belt_date', $vehicle->timing_belt_date?->format('Y-m-d')) }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:border-indigo-500 focus:outline-none">
            </div>
        </div>
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

<script>
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

    let lookupBusy = false;
    regInput.addEventListener('blur', async () => {
        const raw = regInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        const m = raw.match(/^(\d{2,3})([A-Z]{1,2})(\d{1,6})$/);
        if (m) regInput.value = `${m[1]}-${m[2]}-${m[3]}`;

        if (regInput.value.length < 4 || lookupBusy) return;
        lookupBusy = true;

        // 1. Decode (instant — rok + hrabstwo)
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

        // 2. MotorCheck lookup (~2s — pełne dane z motorcheck.ie)
        decodedBox.innerHTML += ' &nbsp; <span class="text-indigo-600">⏳ Pobieram z MotorCheck...</span>';
        decodedBox.classList.remove('hidden');

        try {
            const r = await fetch('{{ route('vehicles.lookup.motorcheck') }}?reg=' + encodeURIComponent(regInput.value));
            const j = await r.json();
            if (j.ok && j.data) {
                const d = j.data;
                setIfEmpty('make', d.make);
                setIfEmpty('model', d.model);
                setIfEmpty('year', d.year);
                setIfEmpty('engine_cc', d.engine_cc);
                setIfEmpty('fuel', d.fuel);
                setIfEmpty('color', d.color);
                setIfEmpty('body', d.body);
                decodedBox.innerHTML = '✅ <strong>' + d.make + ' ' + d.model + ', ' + d.year + '</strong> — ' + (d.engine_cc || '?') + 'ccm · ' + (d.fuel || '?') + ' · ' + (d.color || '?') + ' <span class="text-gray-500">(z motorcheck.ie)</span>';
                decodedBox.className = 'mt-2 p-2 bg-green-50 border border-green-300 rounded text-sm text-green-900';
            } else {
                decodedBox.innerHTML = decodedBox.innerHTML.replace(/⏳[^<]*MotorCheck[^<]*/, '<span class="text-amber-700">⚠️ Nie ma w MotorCheck — wypełnij ręcznie</span>');
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
        aiMessage.className = 'mt-3 p-3 rounded text-sm ' + (ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
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
                setField(f, data[f]);
                filled++;
            }
        });
        if (data.registration) setField('registration', data.registration);
        showMessage(`✅ AI uzupełniło ${filled} pól. Sprawdź i popraw jeśli trzeba.`, true);
    }

    $('#ai-submit-desc').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const desc = $('#ai-description').value.trim();
        if (!desc) { showMessage('Wpisz opis auta.', false); return; }
        if (!regInput.value.trim()) { showMessage('Wpisz najpierw numer rejestracyjny.', false); return; }
        btn.disabled = true; btn.textContent = '⏳ Pytam Claude...';
        try {
            const r = await fetch('{{ route('vehicles.lookup.description') }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({registration: regInput.value, description: desc}),
            });
            const j = await r.json();
            if (r.ok && j.ok) applyData(j.data);
            else showMessage(j.error || 'Coś poszło nie tak.', false);
        } catch (err) {
            showMessage('Błąd sieci: ' + err.message, false);
        } finally {
            btn.disabled = false; btn.textContent = '🪄 Wypełnij formularz';
        }
    });

    $('#ai-submit-photo').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const file = $('#ai-image').files[0];
        if (!file) { showMessage('Wybierz zdjęcie.', false); return; }
        if (file.size > 5 * 1024 * 1024) { showMessage('Zdjęcie za duże (max 5MB).', false); return; }
        btn.disabled = true; btn.textContent = '⏳ Czytam zdjęcie...';
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
            else showMessage(j.error || 'AI nie odczytało zdjęcia.', false);
        } catch (err) {
            showMessage('Błąd sieci: ' + err.message, false);
        } finally {
            btn.disabled = false; btn.textContent = '🪄 Odczytaj i wypełnij';
        }
    });
})();
</script>

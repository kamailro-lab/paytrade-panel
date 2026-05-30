<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $vehicle->make }} {{ $vehicle->model }}
                <span class="font-mono text-gray-500">({{ $vehicle->registration }})</span>
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-700">✏️ Edytuj</a>
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Czy na pewno usunąć auto {{ $vehicle->registration }}? Tej operacji nie da się cofnąć.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">🗑 Usuń</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Dane auta</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Rejestracja</dt><dd class="font-mono font-bold">{{ $vehicle->registration }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Logbook (VRC)</dt><dd>{{ $vehicle->logbook_no ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Marka i model</dt><dd>{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Rok</dt><dd>{{ $vehicle->year ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Silnik</dt><dd>{{ $vehicle->engine_cc ? $vehicle->engine_cc.' ccm' : '—' }} · {{ ucfirst($vehicle->fuel ?? '—') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Przebieg</dt><dd>{{ $vehicle->mileage_km ? number_format($vehicle->mileage_km, 0, ',', ' ').' km' : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Nadwozie / drzwi</dt><dd>{{ ucfirst($vehicle->body ?? '—') }} · {{ $vehicle->doors ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Kolor</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">NCT do</dt><dd>{{ $vehicle->nct_expiry?->format('d.m.Y') ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Status</dt><dd class="font-semibold">{{ ['stock'=>'W stocku','sold'=>'Sprzedane','service'=>'W serwisie','written_off'=>'Spisane'][$vehicle->status] }}</dd></div>
                </dl>
                @if($vehicle->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded">
                        <dt class="text-sm text-gray-500 mb-1">Notatki</dt>
                        <dd class="whitespace-pre-wrap">{{ $vehicle->notes }}</dd>
                    </div>
                @endif
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-amber-900">
                <h3 class="font-semibold mb-2">🚧 Już niedługo:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>Sekcja "Zakup" — od kogo kupione, cena, VRT, transport</li>
                    <li>Sekcja "Koszty" — naprawy, części, reklama (per auto)</li>
                    <li>Sekcja "Sprzedaż" — komu sprzedane, cena, marża, faktura PDF</li>
                    <li>AI Lookup — wpisz rejestrację, program pobiera dane</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

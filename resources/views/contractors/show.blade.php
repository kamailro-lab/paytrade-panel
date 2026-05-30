<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $contractor->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('contractors.edit', $contractor) }}" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-700">✏️ Edytuj</a>
                <form method="POST" action="{{ route('contractors.destroy', $contractor) }}" onsubmit="return confirm('Czy na pewno usunąć kontrahenta {{ $contractor->name }}?')">
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
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Dane</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Typ</dt><dd class="font-semibold">{{ ['customer' => '🤝 Klient', 'supplier' => '🚚 Dostawca', 'both' => '🔁 Oboje'][$contractor->type] }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Telefon</dt><dd>{{ $contractor->phone ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Email</dt><dd>{{ $contractor->email ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Eircode</dt><dd class="font-mono">{{ $contractor->eir_code ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-gray-500">Adres</dt><dd>{{ $contractor->address ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">VAT Number</dt><dd>{{ $contractor->vat_number ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">PPSN</dt><dd>{{ $contractor->ppsn ?? '—' }}</dd></div>
                </dl>
                @if($contractor->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded">
                        <dt class="text-sm text-gray-500 mb-1">Notatki</dt>
                        <dd class="whitespace-pre-wrap">{{ $contractor->notes }}</dd>
                    </div>
                @endif
            </div>

            @if($contractor->purchases->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">🚚 Sprzedał nam ({{ $contractor->purchases->count() }})</h3>
                    <ul class="divide-y">
                        @foreach($contractor->purchases as $p)
                            <li class="py-2 flex justify-between"><span>{{ $p->vehicle->registration }} — {{ $p->vehicle->make }} {{ $p->vehicle->model }}</span><span class="text-gray-600">€{{ number_format($p->purchase_price, 0, ',', ' ') }} · {{ $p->purchase_date?->format('d.m.Y') }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($contractor->sales->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">🤝 Kupił od nas ({{ $contractor->sales->count() }})</h3>
                    <ul class="divide-y">
                        @foreach($contractor->sales as $s)
                            <li class="py-2 flex justify-between"><span>{{ $s->vehicle->registration }} — {{ $s->vehicle->make }} {{ $s->vehicle->model }}</span><span class="text-gray-600">€{{ number_format($s->sale_price, 0, ',', ' ') }} · {{ $s->sale_date?->format('d.m.Y') }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

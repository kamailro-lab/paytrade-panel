<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🤝 Kontrahenci</h2>
            <a href="{{ route('contractors.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                ➕ Dodaj kontrahenta
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-4 mb-4">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Szukaj: imię, telefon, email, Eircode..."
                           class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none sm:col-span-2">
                    <select name="type" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        <option value="">— Wszyscy —</option>
                        <option value="customers" @selected($type === 'customers')>🤝 Klienci</option>
                        <option value="suppliers" @selected($type === 'suppliers')>🚚 Dostawcy</option>
                        <option value="both" @selected($type === 'both')>🔁 Klient + Dostawca</option>
                    </select>
                    <div class="sm:col-span-3 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">🔍 Filtruj</button>
                        @if($type || $search)
                            <a href="{{ route('contractors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">✖ Wyczyść</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                @if($contractors->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-5xl mb-3">🤝</div>
                        <p class="text-lg">Brak kontrahentów. Kliknij "Dodaj kontrahenta" żeby zacząć.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Imię / Nazwa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Typ</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Telefon</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Eircode</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @php
                                $typeLabels = ['customer' => ['🤝 Klient', 'bg-blue-100 text-blue-800'], 'supplier' => ['🚚 Dostawca', 'bg-amber-100 text-amber-800'], 'both' => ['🔁 Oboje', 'bg-purple-100 text-purple-800']];
                            @endphp
                            @foreach($contractors as $c)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold">{{ $c->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeLabels[$c->type][1] }}">
                                            {{ $typeLabels[$c->type][0] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $c->phone ?? '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-sm">{{ $c->eir_code ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('contractors.show', $c) }}" class="text-indigo-600 hover:underline mr-3">👁 Podgląd</a>
                                        <a href="{{ route('contractors.edit', $c) }}" class="text-gray-600 hover:underline">✏️ Edytuj</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $contractors->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

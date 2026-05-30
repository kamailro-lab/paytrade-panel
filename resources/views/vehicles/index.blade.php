<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🚗 Auta</h2>
            <a href="{{ route('vehicles.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                ➕ Dodaj auto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-4 mb-4">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Szukaj: rejestracja, marka, model..."
                           class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none sm:col-span-2">
                    <select name="status" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                        <option value="">— Wszystkie statusy —</option>
                        <option value="stock" @selected($status === 'stock')>W stocku</option>
                        <option value="sold" @selected($status === 'sold')>Sprzedane</option>
                        <option value="service" @selected($status === 'service')>W serwisie</option>
                        <option value="written_off" @selected($status === 'written_off')>Spisane</option>
                    </select>
                    <div class="sm:col-span-3 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">🔍 Filtruj</button>
                        @if($status || $search)
                            <a href="{{ route('vehicles.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">✖ Wyczyść</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                @if($vehicles->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-5xl mb-3">🚗</div>
                        <p class="text-lg">Brak aut. Kliknij "Dodaj auto" żeby zacząć.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rejestracja</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Auto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rok</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($vehicles as $v)
                                @php
                                    $statusColors = [
                                        'stock' => 'bg-blue-100 text-blue-800',
                                        'sold' => 'bg-green-100 text-green-800',
                                        'service' => 'bg-yellow-100 text-yellow-800',
                                        'written_off' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusLabels = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono font-bold">{{ $v->registration }}</td>
                                    <td class="px-4 py-3">{{ $v->make }} {{ $v->model }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $v->year ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$v->status] }}">
                                            {{ $statusLabels[$v->status] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('vehicles.show', $v) }}" class="text-indigo-600 hover:underline mr-3">👁 Podgląd</a>
                                        <a href="{{ route('vehicles.edit', $v) }}" class="text-gray-600 hover:underline">✏️ Edytuj</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $vehicles->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

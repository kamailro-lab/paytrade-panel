<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🚗 Auta <span class="text-sm font-normal text-gray-500">({{ $vehicles->total() }})</span></h2>
            <div class="flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('vehicles.enrich') }}" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='⏳ Pobieram z MotorCheck...'; return confirm('Sprawdzić wszystkie auta z brakującymi danymi w MotorCheck.ie? (potrwa ~2 min dla 100 aut)');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white font-semibold text-sm rounded-lg hover:bg-amber-600 transition disabled:opacity-50">
                        🔄 MotorCheck
                    </button>
                </form>
                <a href="{{ route('vehicles.create') }}" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700 transition">
                    ➕ Dodaj auto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto px-3 sm:px-4 lg:px-6">
            @if(session('success'))
                <div class="mb-3 p-3 bg-green-100 border border-green-300 text-green-800 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-4">

                {{-- SIDEBAR FILTRA --}}
                <aside class="lg:w-60 lg:shrink-0">
                    <div class="bg-white shadow rounded-lg p-3 lg:sticky lg:top-4">
                        <form method="GET" class="space-y-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">🔍 Szukaj</label>
                                <input type="text" name="q" value="{{ $search }}" placeholder="rejestracja, marka..."
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:border-indigo-500 focus:outline-none">
                                    <option value="">— Wszystkie —</option>
                                    <option value="stock" @selected($status === 'stock')>W stocku</option>
                                    <option value="sold" @selected($status === 'sold')>Sprzedane</option>
                                    <option value="service" @selected($status === 'service')>W serwisie</option>
                                    <option value="written_off" @selected($status === 'written_off')>Spisane</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Per page</label>
                                <select name="per_page" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:border-indigo-500 focus:outline-none">
                                    @foreach([20, 50, 100, 200] as $n)
                                        <option value="{{ $n }}" @selected(request('per_page', 50) == $n)>{{ $n }} na stronę</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="flex-1 px-3 py-2 bg-gray-800 text-white text-sm font-semibold rounded hover:bg-gray-700">🔍 Filtruj</button>
                                @if($status || $search)
                                    <a href="{{ route('vehicles.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">✖</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </aside>

                {{-- TABELA --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        @if($vehicles->isEmpty())
                            <div class="p-8 text-center text-gray-500">
                                <div class="text-5xl mb-3">🚗</div>
                                <p class="text-lg">Brak aut. Kliknij "Dodaj auto" żeby zacząć.</p>
                            </div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rejestracja</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Auto</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rok</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Paliwo</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @php
                                        $statusColors = [
                                            'stock' => 'bg-blue-100 text-blue-800',
                                            'sold' => 'bg-green-100 text-green-800',
                                            'service' => 'bg-yellow-100 text-yellow-800',
                                            'written_off' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = ['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'Serwis', 'written_off' => 'Spisane'];
                                    @endphp
                                    @foreach($vehicles as $v)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-1.5 font-mono font-bold text-indigo-700">
                                                <a href="{{ route('vehicles.show', $v) }}" class="hover:underline">{{ $v->registration }}</a>
                                            </td>
                                            <td class="px-3 py-1.5">{{ $v->make }} {{ $v->model }}</td>
                                            <td class="px-2 py-1.5 text-gray-600">{{ $v->year ?? '—' }}</td>
                                            <td class="px-2 py-1.5 text-gray-600">{{ $v->fuel ? ucfirst($v->fuel) : '—' }}</td>
                                            <td class="px-2 py-1.5">
                                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$v->status] }}">
                                                    {{ $statusLabels[$v->status] }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-1.5 text-right whitespace-nowrap">
                                                <a href="{{ route('vehicles.show', $v) }}" class="text-indigo-600 hover:underline text-xs">👁</a>
                                                <a href="{{ route('vehicles.edit', $v) }}" class="text-gray-600 hover:underline text-xs ml-2">✏️</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="px-3 py-2">{{ $vehicles->links() }}</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

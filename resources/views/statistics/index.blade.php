<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">📊 Statystyki finansowe</h2>
            <form method="POST" action="{{ route('statistics.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm bg-red-100 text-red-700 px-3 py-1.5 rounded hover:bg-red-200">
                    🔒 Wyloguj menedżera
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Selector okresu --}}
            <div class="mb-6 bg-white shadow rounded-lg p-4 flex flex-wrap gap-2 items-center">
                <span class="text-sm font-semibold text-gray-700 mr-2">📅 Okres:</span>
                @foreach([
                    'this_month' => 'Ten miesiąc',
                    'last_month' => 'Poprzedni miesiąc',
                    'this_year' => 'Ten rok',
                    'last_year' => 'Poprzedni rok',
                    'all_time' => 'Cały okres',
                ] as $key => $label)
                    <a href="{{ route('statistics.index', ['period' => $key]) }}"
                       class="px-3 py-1.5 text-sm rounded-lg font-medium {{ $period === $key ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <span class="ml-auto text-sm text-gray-500">Pokazywane: <strong>{{ $periodLabel }}</strong></span>
            </div>

            {{-- 4 główne kafelki KPI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-5 border-l-4 border-blue-500">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">🚗 Sprzedanych aut</div>
                    <div class="mt-2 text-3xl font-bold text-blue-700">{{ $totalCount }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-l-4 border-indigo-500">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">💰 Przychód</div>
                    <div class="mt-2 text-3xl font-bold text-indigo-700">€{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">📈 Zysk</div>
                    <div class="mt-2 text-3xl font-bold text-green-700">€{{ number_format($totalProfit, 0, ',', ' ') }}</div>
                    <div class="mt-1 text-xs text-gray-500">{{ number_format($avgMarginPercent, 1) }}% marża</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">🧾 VAT należny</div>
                    <div class="mt-2 text-3xl font-bold text-amber-700">€{{ number_format($vatDue, 0, ',', ' ') }}</div>
                    <div class="mt-1 text-xs text-gray-500">Margin Scheme 23/123</div>
                </div>
            </div>

            {{-- Dodatkowe KPI --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-xs text-gray-500">Średnia marża/auto</div>
                    <div class="mt-1 text-xl font-bold text-gray-800">€{{ number_format($avgMargin, 0, ',', ' ') }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-xs text-gray-500">Aut w stocku (teraz)</div>
                    <div class="mt-1 text-xl font-bold text-purple-700">{{ $stockCount }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-xs text-gray-500">Wartość stocku (zakup+koszty)</div>
                    <div class="mt-1 text-xl font-bold text-purple-700">€{{ number_format($totalStockValue, 0, ',', ' ') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Top 5 najlepszych --}}
                <div class="bg-white shadow rounded-lg p-5">
                    <h3 class="text-base font-bold text-gray-800 mb-3">🏆 TOP 5 zarabiających</h3>
                    @forelse($bestSales as $sale)
                        @php $margin = $sale->vehicle->margin() ?? 0; @endphp
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <a href="{{ route('vehicles.show', $sale->vehicle) }}" class="font-semibold text-gray-800 hover:text-indigo-600">
                                    {{ $sale->vehicle->registration }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $sale->vehicle->make }} {{ $sale->vehicle->model }} ({{ $sale->vehicle->year }})</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-green-700">+€{{ number_format($margin, 0, ',', ' ') }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->sale_date?->format('d.m.Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Brak sprzedaży w tym okresie.</p>
                    @endforelse
                </div>

                {{-- Top 5 najgorszych --}}
                <div class="bg-white shadow rounded-lg p-5">
                    <h3 class="text-base font-bold text-gray-800 mb-3">📉 TOP 5 najgorszych</h3>
                    @forelse($worstSales as $sale)
                        @php $margin = $sale->vehicle->margin() ?? 0; @endphp
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <a href="{{ route('vehicles.show', $sale->vehicle) }}" class="font-semibold text-gray-800 hover:text-indigo-600">
                                    {{ $sale->vehicle->registration }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $sale->vehicle->make }} {{ $sale->vehicle->model }} ({{ $sale->vehicle->year }})</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold {{ $margin < 0 ? 'text-red-700' : 'text-amber-700' }}">
                                    {{ $margin >= 0 ? '+' : '' }}€{{ number_format($margin, 0, ',', ' ') }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $sale->sale_date?->format('d.m.Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Brak sprzedaży w tym okresie.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sprzedaż per miesiąc (ostatnie 12) --}}
            <div class="bg-white shadow rounded-lg p-5 mb-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">📅 Sprzedaż per miesiąc (ostatnie 12)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200 text-xs text-gray-500 uppercase">
                                <th class="text-left py-2 pr-3">Miesiąc</th>
                                <th class="text-right py-2 px-3">Sprzedanych</th>
                                <th class="text-right py-2 px-3">Przychód</th>
                                <th class="text-right py-2 pl-3">Zysk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $month)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 pr-3 font-medium">{{ $month['label'] }}</td>
                                    <td class="text-right py-2 px-3">{{ $month['count'] }}</td>
                                    <td class="text-right py-2 px-3">€{{ number_format($month['revenue'], 0, ',', ' ') }}</td>
                                    <td class="text-right py-2 pl-3 font-bold {{ $month['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $month['profit'] >= 0 ? '+' : '' }}€{{ number_format($month['profit'], 0, ',', ' ') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-xs text-gray-500 text-center">
                🔒 Te dane są wrażliwe — nie dziel się z osobami trzecimi. Sesja menedżera wygasa po ~2 godzinach.
            </div>
        </div>
    </div>
</x-app-layout>

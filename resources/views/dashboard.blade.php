<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    @php
        $now = now();
        $startMonth = $now->copy()->startOfMonth();
        $endMonth = $now->copy()->endOfMonth();
        $inService = \App\Models\Vehicle::where('status', 'service')->count();
        $readyCount = \App\Models\Vehicle::where('status', 'stock')
            ->where('nct_passed', true)
            ->where('service_done', true)
            ->where('timing_belt_checked', true)
            ->count();
        $stockValue = \App\Models\Vehicle::where('status', 'stock')->with(['purchase', 'costs'])->get()
            ->sum(fn($v) => $v->totalCost());

        // Alerty
        $oldStock = \App\Models\Vehicle::where('status', 'stock')
            ->where('created_at', '<', $now->copy()->subDays(45))
            ->count();
        $noNct = \App\Models\Vehicle::where('status', 'stock')
            ->whereNull('nct_expiry')
            ->count();
    @endphp

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Sekcja: Podsumowanie --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">Podsumowanie</h2>
            <span class="text-sm text-slate-400">{{ $startMonth->translatedFormat('j') }} – {{ $endMonth->translatedFormat('j F Y') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Auta na stanie --}}
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl p-5 shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-blue-200 text-sm font-medium">Auta na stanie</div>
                        <div class="text-4xl font-bold text-white mt-1">{{ $stockCount }}</div>
                        <div class="text-blue-200 text-xs mt-2">Wartość: €{{ number_format($stockValue, 0, ',', ' ') }}</div>
                    </div>
                    <svg class="w-10 h-10 text-blue-300/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>

            {{-- W serwisie --}}
            <div class="bg-gradient-to-br from-amber-600 to-orange-700 rounded-xl p-5 shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-amber-200 text-sm font-medium">W serwisie</div>
                        <div class="text-4xl font-bold text-white mt-1">{{ $inService }}</div>
                        <div class="text-amber-200 text-xs mt-2">Przygotowywane do sprzedaży</div>
                    </div>
                    <svg class="w-10 h-10 text-amber-300/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </div>
            </div>

            {{-- Gotowe do sprzedaży --}}
            <div class="bg-gradient-to-br from-emerald-600 to-green-700 rounded-xl p-5 shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-emerald-200 text-sm font-medium">Gotowe do sprzedaży</div>
                        <div class="text-4xl font-bold text-white mt-1">{{ $readyCount }}</div>
                        <div class="text-emerald-200 text-xs mt-2">NCT + serwis + rozrząd OK</div>
                    </div>
                    <svg class="w-10 h-10 text-emerald-300/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>

            {{-- Sprzedane (m-c) --}}
            <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-xl p-5 shadow-lg border border-slate-600">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-slate-300 text-sm font-medium">Sprzedane ({{ $startMonth->translatedFormat('F') }})</div>
                        <div class="text-4xl font-bold text-white mt-1">{{ $soldThisMonth }}</div>
                        <div class="text-slate-400 text-xs mt-2">🔒 Zysk: w Statystykach</div>
                    </div>
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Sekcja: Wymaga uwagi --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white mb-4">⚠️ Wymaga uwagi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-800/50 border border-amber-700/50 rounded-xl p-4">
                <div class="flex items-center gap-2 text-amber-400 font-semibold text-sm">
                    ⚠️ <span>{{ $oldStock }} {{ $oldStock === 1 ? 'auto stoi' : 'aut stoi' }} ponad 45 dni</span>
                </div>
                <a href="{{ route('vehicles.index') }}" class="text-xs text-emerald-400 hover:underline mt-2 inline-block">Sprawdź listę</a>
            </div>
            <div class="bg-slate-800/50 border border-red-700/50 rounded-xl p-4">
                <div class="flex items-center gap-2 text-red-400 font-semibold text-sm">
                    ⚠️ <span>{{ $noNct }} {{ $noNct === 1 ? 'auto bez NCT' : 'aut bez NCT' }}</span>
                </div>
                <a href="{{ route('vehicles.index') }}" class="text-xs text-emerald-400 hover:underline mt-2 inline-block">Sprawdź listę</a>
            </div>
            <div class="bg-slate-800/50 border border-purple-700/50 rounded-xl p-4">
                <div class="flex items-center gap-2 text-purple-400 font-semibold text-sm">
                    🔗 <span>DealerHub sync</span>
                </div>
                <a href="#" onclick="alert('Uruchom: php artisan dealerhub:sync'); return false;" class="text-xs text-emerald-400 hover:underline mt-2 inline-block">Sync zdjęcia z DealerHub</a>
            </div>
            <div class="bg-gradient-to-br from-amber-900/30 to-orange-900/30 border border-amber-600/50 rounded-xl p-4">
                <div class="flex items-center gap-2 text-amber-300 font-semibold text-sm">
                    🔐 <span>Statystyki menedżera</span>
                </div>
                <a href="{{ route('statistics.login') }}" class="text-xs text-amber-300 hover:underline mt-2 inline-block">Zysk, marża, VAT →</a>
            </div>
        </div>
    </div>

    {{-- Szybkie akcje --}}
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4">⚡ Szybkie akcje</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('vehicles.create') }}" class="flex items-center gap-3 px-5 py-3.5 bg-gradient-to-r from-emerald-600 to-cyan-600 text-white rounded-lg hover:from-emerald-500 hover:to-cyan-500 transition font-semibold shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Dodaj nowe auto
            </a>
            <a href="{{ route('vehicles.index') }}" class="flex items-center gap-3 px-5 py-3.5 bg-slate-700 text-slate-100 rounded-lg hover:bg-slate-600 transition font-semibold border border-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Zobacz wszystkie auta
            </a>
        </div>
    </div>
</x-app-layout>

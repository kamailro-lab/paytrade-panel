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
        $serviceValue = \App\Models\Vehicle::where('status', 'service')->with(['costs'])->get()
            ->sum(fn($v) => (float) $v->costs->sum('amount'));
        $readyValue = \App\Models\Vehicle::where('status', 'stock')
            ->where('nct_passed', true)
            ->with(['purchase', 'costs'])->get()
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
        <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700/50 text-emerald-300 rounded-lg text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Top bar z datą + powiadomienia (jak w designie) --}}
    <div class="flex items-center justify-between mb-6">
        <div></div>
        <div class="flex items-center gap-2">
            <div class="px-3 py-2 bg-slate-800/60 border border-slate-700/50 rounded-lg text-sm text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Dzisiaj: {{ $now->translatedFormat('j F Y') }}
            </div>
        </div>
    </div>

    {{-- 4 KPI tiles (jak na designie) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Auta na stanie --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-5 hover:border-blue-600/50 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Auta na stanie</div>
                <div class="w-9 h-9 rounded-lg bg-blue-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1"/>
                    </svg>
                </div>
            </div>
            <div class="text-4xl font-bold text-white mb-1">{{ $stockCount }}</div>
            <div class="text-xs text-slate-400">Wartość: <span class="text-slate-300 font-medium">€{{ number_format($stockValue, 0, ',', ' ') }}</span></div>
        </div>

        {{-- W serwisie --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-5 hover:border-orange-600/50 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-slate-400 uppercase tracking-wide font-semibold">W serwisie</div>
                <div class="w-9 h-9 rounded-lg bg-orange-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </div>
            </div>
            <div class="text-4xl font-bold text-white mb-1">{{ $inService }}</div>
            <div class="text-xs text-slate-400">Koszt: <span class="text-slate-300 font-medium">€{{ number_format($serviceValue, 0, ',', ' ') }}</span></div>
        </div>

        {{-- Gotowe do sprzedaży --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-5 hover:border-emerald-600/50 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Gotowe do sprzedaży</div>
                <div class="w-9 h-9 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="text-4xl font-bold text-white mb-1">{{ $readyCount }}</div>
            <div class="text-xs text-slate-400">Wartość: <span class="text-slate-300 font-medium">€{{ number_format($readyValue, 0, ',', ' ') }}</span></div>
        </div>

        {{-- Sprzedane (m-c) --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-5 hover:border-rose-600/50 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Sprzedane ({{ $startMonth->translatedFormat('M') }})</div>
                <div class="w-9 h-9 rounded-lg bg-rose-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2"/>
                    </svg>
                </div>
            </div>
            <div class="text-4xl font-bold text-white mb-1">{{ $soldThisMonth }}</div>
            <div class="text-xs text-amber-400">🔒 Zysk: <a href="{{ route('statistics.login') }}" class="hover:underline">w Statystykach</a></div>
        </div>
    </div>

    {{-- Sekcja: Wymaga uwagi (jak w designie) --}}
    <div class="mb-6">
        <h2 class="text-base font-semibold text-white mb-3">Wymaga uwagi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-slate-800/60 border border-amber-700/30 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16h2v2h-2zm0-6h2v5h-2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white text-sm">{{ $oldStock }} {{ $oldStock === 1 ? 'auto stoi' : 'aut stoi' }} ponad 45 dni</div>
                        <a href="{{ route('vehicles.index') }}" class="text-xs text-rose-400 hover:underline mt-1 inline-block">Sprawdź listę</a>
                    </div>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-amber-700/30 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16h2v2h-2zm0-6h2v5h-2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white text-sm">{{ $noNct }} {{ $noNct === 1 ? 'auto bez NCT' : 'aut bez NCT' }}</div>
                        <a href="{{ route('vehicles.index') }}" class="text-xs text-rose-400 hover:underline mt-1 inline-block">Sprawdź listę</a>
                    </div>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-purple-700/30 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                        🔗
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white text-sm">DealerHub sync</div>
                        <a href="#" onclick="alert('Uruchom: php artisan dealerhub:sync'); return false;" class="text-xs text-rose-400 hover:underline mt-1 inline-block">Pobierz zdjęcia</a>
                    </div>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-blue-700/30 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                        🔐
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white text-sm">Statystyki menedżera</div>
                        <a href="{{ route('statistics.login') }}" class="text-xs text-rose-400 hover:underline mt-1 inline-block">Zysk, marża, VAT →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Szybkie akcje --}}
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-3 uppercase tracking-wide text-slate-400">Szybkie akcje</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('vehicles.create') }}" class="flex items-center gap-3 px-5 py-3.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition font-semibold shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Dodaj nowe auto
            </a>
            <a href="{{ route('vehicles.index') }}" class="flex items-center gap-3 px-5 py-3.5 bg-slate-700 hover:bg-slate-600 text-slate-100 rounded-lg transition font-semibold border border-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Zobacz wszystkie auta
            </a>
        </div>
    </div>
</x-app-layout>

{{-- Sidebar navigation — dark theme z ikonami SVG --}}
@php
    $routeName = request()->route()?->getName() ?? '';
    $isActive = fn($pattern) => str_starts_with($routeName, $pattern) ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white border-transparent';
@endphp
<aside class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col h-full flex-shrink-0">

    {{-- Logo --}}
    <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
            PT
        </div>
        <div>
            <div class="font-bold text-white text-lg leading-tight">PayTrade</div>
            <div class="text-xs text-emerald-400 font-medium">Ireland</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('dashboard') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('vehicles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('vehicles') && !str_contains($routeName, 'sale') ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white border-transparent' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
            </svg>
            <span>Auta na stanie</span>
            @php $stockBadge = \App\Models\Vehicle::where('status', 'stock')->count(); @endphp
            @if($stockBadge > 0)
                <span class="ml-auto px-2 py-0.5 text-xs bg-slate-700 rounded-full">{{ $stockBadge }}</span>
            @endif
        </a>

        <a href="{{ route('vehicles.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $routeName === 'vehicles.create' ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white border-transparent' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Dodaj auto</span>
        </a>

        <a href="{{ route('contractors.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('contractors') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Kontrahenci</span>
        </a>

        <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('invoices') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Faktury</span>
        </a>

        <a href="{{ route('import.form') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('import') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Import</span>
        </a>

        {{-- Separator --}}
        <div class="pt-4 mt-4 border-t border-slate-800">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Konto</div>
        </div>

        <a href="{{ route('statistics.login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('statistics') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Statystyki</span>
            <span class="ml-auto text-xs text-amber-400">🔒</span>
        </a>

        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('users') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Użytkownicy</span>
        </a>

        <a href="{{ route('settings.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-4 font-medium transition {{ $isActive('settings') }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Ustawienia</span>
        </a>
    </nav>

    {{-- Footer (logout) --}}
    <div class="px-3 py-4 border-t border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-red-600/10 hover:text-red-400 font-medium transition">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Wyloguj</span>
            </button>
        </form>
    </div>
</aside>

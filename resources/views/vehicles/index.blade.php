<x-app-layout>
    <x-slot name="header">
        Auta na stanie
    </x-slot>

    @php
        // Helper - status badge classes + label
        $statusBadge = function($vehicle) {
            $ready = $vehicle->status === 'stock'
                && $vehicle->nct_passed
                && $vehicle->service_done
                && $vehicle->timing_belt_checked;

            if ($vehicle->status === 'sold') return ['Sprzedane', 'bg-slate-700/60 text-slate-300 border-slate-600'];
            if ($vehicle->status === 'written_off') return ['Spisane', 'bg-red-900/40 text-red-300 border-red-700'];
            if ($vehicle->status === 'service') return ['W serwisie', 'bg-orange-600/20 text-orange-300 border-orange-700'];
            if ($ready) return ['Gotowe', 'bg-emerald-600/20 text-emerald-300 border-emerald-700'];
            return ['W stocku', 'bg-blue-600/20 text-blue-300 border-blue-700'];
        };

        $daysOnLot = function($vehicle) {
            $start = $vehicle->purchase?->purchase_date ?? $vehicle->created_at;
            return (int) $start->diffInDays(now());
        };

        $plannedProfit = function($vehicle) {
            if (!$vehicle->target_price) return null;
            return (float) $vehicle->target_price - $vehicle->totalCost();
        };
    @endphp

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700/50 text-emerald-300 rounded-lg text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
        <div class="text-lg font-bold text-white">
            Wyniki: <span class="text-blue-400">{{ $vehicles->total() }}</span>
        </div>
        <div class="flex gap-2">
            <button type="button" id="enrich-btn" class="px-4 py-2 bg-amber-600/20 border border-amber-600/40 text-amber-300 hover:bg-amber-600/30 text-sm font-medium rounded-lg transition">
                🔄 MotorCheck
            </button>
            <a href="{{ route('vehicles.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Dodaj auto
            </a>
        </div>
    </div>

    {{-- Filtry --}}
    <form method="GET" action="{{ route('vehicles.index') }}" class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="{{ $search ?? '' }}"
                       placeholder="Szukaj auta (rejestracja, marka, model)..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none">
            </div>
            <select name="status" class="px-3 py-2.5 bg-slate-900/60 border border-slate-700 rounded-lg text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
                <option value="">Wszystkie statusy</option>
                @foreach(['stock' => 'W stocku', 'sold' => 'Sprzedane', 'service' => 'W serwisie', 'written_off' => 'Spisane'] as $key => $label)
                    <option value="{{ $key }}" @selected(($status ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="per_page" onchange="this.form.submit()" class="px-3 py-2.5 bg-slate-900/60 border border-slate-700 rounded-lg text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
                @foreach([20, 50, 100, 200] as $perPage)
                    <option value="{{ $perPage }}" @selected(request('per_page', 50) == $perPage)>{{ $perPage }} na stronę</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition">
                🔍 Filtruj
            </button>
        </div>
    </form>

    {{-- Tabela --}}
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/60 border-b border-slate-700/50">
                    <tr class="text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3 font-semibold">Auto</th>
                        <th class="px-4 py-3 font-semibold text-center">Rok</th>
                        <th class="px-4 py-3 font-semibold">Rejestracja</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Cena</th>
                        <th class="px-4 py-3 font-semibold text-right">Zysk (plan)</th>
                        <th class="px-4 py-3 font-semibold text-center">Dni</th>
                        <th class="px-4 py-3 font-semibold w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($vehicles as $vehicle)
                        @php
                            [$badgeText, $badgeClass] = $statusBadge($vehicle);
                            $days = $daysOnLot($vehicle);
                            $profit = $plannedProfit($vehicle);
                            $photoUrl = !empty($vehicle->photos) && is_array($vehicle->photos) ? $vehicle->photos[0] : null;
                        @endphp
                        <tr class="hover:bg-slate-700/30 transition cursor-pointer" onclick="window.location='{{ route('vehicles.show', $vehicle) }}'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($photoUrl)
                                        <div class="w-14 h-10 bg-slate-700 rounded overflow-hidden flex-shrink-0">
                                            <img src="{{ $photoUrl }}" alt="{{ $vehicle->registration }}" class="w-full h-full object-cover" loading="lazy">
                                        </div>
                                    @else
                                        <div class="w-14 h-10 bg-slate-700/50 rounded flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold text-white truncate">{{ $vehicle->make ?: '—' }} {{ $vehicle->model ?: '' }}</div>
                                        @if($vehicle->color || $vehicle->fuel)
                                            <div class="text-xs text-slate-400 truncate">{{ $vehicle->color }}@if($vehicle->color && $vehicle->fuel) · @endif{{ ucfirst($vehicle->fuel ?? '') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ $vehicle->year ?: '—' }}</td>
                            <td class="px-4 py-3 font-mono text-slate-300 text-xs">{{ $vehicle->registration }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $badgeClass }}">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-white">
                                @if($vehicle->target_price)
                                    €{{ number_format($vehicle->target_price, 0, ',', ' ') }}
                                @elseif($vehicle->purchase)
                                    <span class="text-slate-400 text-xs">€{{ number_format($vehicle->purchase->purchase_price, 0, ',', ' ') }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                @if($profit !== null)
                                    <span class="{{ $profit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $profit >= 0 ? '+' : '' }}€{{ number_format($profit, 0, ',', ' ') }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-slate-400 text-xs">
                                @if($vehicle->status === 'sold')
                                    <span class="text-slate-500">—</span>
                                @else
                                    {{ $days }}d
                                @endif
                            </td>
                            <td class="px-4 py-3" onclick="event.stopPropagation()">
                                <div class="relative inline-block">
                                    <button type="button" onclick="event.stopPropagation(); this.nextElementSibling.classList.toggle('hidden')" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                    </button>
                                    <div class="hidden absolute right-0 top-9 z-20 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-xl py-1">
                                        <a href="{{ route('vehicles.show', $vehicle) }}" class="block px-3 py-2 text-sm text-slate-200 hover:bg-slate-700">👁 Podgląd</a>
                                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="block px-3 py-2 text-sm text-slate-200 hover:bg-slate-700">✏️ Edytuj</a>
                                        @if($vehicle->status !== 'sold')
                                            <a href="{{ route('vehicles.sale.edit', $vehicle) }}" class="block px-3 py-2 text-sm text-emerald-300 hover:bg-slate-700">💸 Sprzedaj</a>
                                        @endif
                                        <form method="POST" action="{{ route('vehicles.dealerhub.sync', $vehicle) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-purple-300 hover:bg-slate-700">🔗 Pobierz z DealerHub</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center text-slate-500">
                                <div class="text-4xl mb-3">🚗</div>
                                <div class="text-lg font-semibold mb-1">Brak aut</div>
                                <div class="text-sm mb-4">Dodaj pierwsze auto żeby zacząć</div>
                                <a href="{{ route('vehicles.create') }}" class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition">+ Dodaj auto</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="px-4 py-3 border-t border-slate-700/50 flex items-center justify-between flex-wrap gap-2">
                <div class="text-xs text-slate-400">
                    Wyświetlanie {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }} z {{ $vehicles->total() }}
                </div>
                <div class="flex gap-1">
                    {{ $vehicles->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- MotorCheck enrichment overlay --}}
    <div id="enrich-overlay" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold text-white mb-2">🔄 Pobieram dane z MotorCheck.ie</h3>
            <div id="enrich-current" class="text-sm text-slate-400 mb-3 truncate">Inicjalizuję...</div>
            <div class="w-full bg-slate-700 rounded-full h-3 overflow-hidden mb-2">
                <div id="enrich-bar" class="bg-blue-500 h-full transition-all duration-200" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-sm text-slate-400">
                <span id="enrich-counter">0 / 0</span>
                <span id="enrich-stats">✅ 0 · ❌ 0</span>
            </div>
            <div id="enrich-final" class="hidden mt-4 p-3 bg-emerald-900/30 border border-emerald-700/50 rounded text-sm text-emerald-300"></div>
            <button id="enrich-close" class="hidden mt-4 px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg transition">
                Zamknij i odśwież
            </button>
        </div>
    </div>

    <script>
    document.getElementById('enrich-btn')?.addEventListener('click', async () => {
        const overlay = document.getElementById('enrich-overlay');
        const bar = document.getElementById('enrich-bar');
        const counter = document.getElementById('enrich-counter');
        const stats = document.getElementById('enrich-stats');
        const current = document.getElementById('enrich-current');
        const final = document.getElementById('enrich-final');
        const closeBtn = document.getElementById('enrich-close');
        overlay.classList.remove('hidden');
        try {
            const res = await fetch('{{ route('vehicles.enrich.list') }}');
            const data = await res.json();
            const list = data.vehicles || [];
            let ok = 0, fail = 0;
            counter.textContent = `0 / ${list.length}`;
            for (let i = 0; i < list.length; i++) {
                const v = list[i];
                current.textContent = `${v.registration} (${v.make} ${v.model})`;
                try {
                    const r = await fetch('{{ url('vehicles') }}/' + v.id + '/enrich', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const j = await r.json();
                    if (j.ok) ok++; else fail++;
                } catch (e) { fail++; }
                bar.style.width = `${((i + 1) / list.length) * 100}%`;
                counter.textContent = `${i + 1} / ${list.length}`;
                stats.textContent = `✅ ${ok} · ❌ ${fail}`;
            }
            current.textContent = 'Zakończono!';
            final.classList.remove('hidden');
            final.textContent = `Wzbogacono ${ok} z ${list.length} aut. ${fail} pominiętych.`;
            closeBtn.classList.remove('hidden');
            closeBtn.onclick = () => location.reload();
        } catch (e) {
            current.textContent = '❌ Błąd: ' + e.message;
            closeBtn.classList.remove('hidden');
            closeBtn.onclick = () => overlay.classList.add('hidden');
        }
    });

    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', (e) => {
        if (!e.target.closest('button') && !e.target.closest('.absolute')) {
            document.querySelectorAll('.absolute').forEach(el => el.classList.add('hidden'));
        }
    });
    </script>
</x-app-layout>

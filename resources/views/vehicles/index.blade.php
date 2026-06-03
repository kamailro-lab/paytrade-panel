<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🚗 Auta <span class="text-sm font-normal text-gray-500">({{ $vehicles->total() }})</span></h2>
            <div class="flex gap-2 flex-wrap">
                <button type="button" id="enrich-btn" class="px-4 py-2 bg-amber-500 text-white font-semibold text-sm rounded-lg hover:bg-amber-600 transition disabled:opacity-50">
                    🔄 MotorCheck
                </button>
                <a href="{{ route('vehicles.create') }}" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700 transition">
                    ➕ Dodaj auto
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Progress overlay (hidden by default) --}}
    <div id="enrich-overlay" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold mb-2">🔄 Pobieram dane z MotorCheck.ie</h3>
            <div id="enrich-current" class="text-sm text-gray-600 mb-3 truncate">Inicjalizuję...</div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden mb-2">
                <div id="enrich-bar" class="bg-indigo-600 h-full transition-all duration-200" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span id="enrich-counter">0 / 0</span>
                <span id="enrich-stats">✅ 0 · ❌ 0</span>
            </div>
            <div id="enrich-final" class="hidden mt-4 p-3 bg-green-100 border border-green-300 rounded text-sm text-green-900"></div>
            <button id="enrich-close" class="hidden mt-4 px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                Zamknij i odśwież
            </button>
        </div>
    </div>

    <script>
    (function () {
        const btn = document.getElementById('enrich-btn');
        const overlay = document.getElementById('enrich-overlay');
        const current = document.getElementById('enrich-current');
        const bar = document.getElementById('enrich-bar');
        const counter = document.getElementById('enrich-counter');
        const stats = document.getElementById('enrich-stats');
        const final = document.getElementById('enrich-final');
        const closeBtn = document.getElementById('enrich-close');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        btn.addEventListener('click', async () => {
            if (!confirm('Pobrać brakujące dane z MotorCheck.ie? Można w każdej chwili zatrzymać zamykając okno.')) return;

            overlay.classList.remove('hidden');
            btn.disabled = true;

            // 1. Get list
            current.textContent = '⏳ Pobieram listę aut do uzupełnienia...';
            const listRes = await fetch('{{ route('vehicles.enrich.list') }}', { headers: { Accept: 'application/json' } });
            const { total, vehicles } = await listRes.json();

            if (total === 0) {
                current.textContent = '✓ Wszystkie auta mają już pełne dane.';
                closeBtn.classList.remove('hidden');
                return;
            }

            let enriched = 0, notFound = 0, errors = 0;
            counter.textContent = `0 / ${total}`;

            // 2. Loop one by one
            for (let i = 0; i < vehicles.length; i++) {
                const v = vehicles[i];
                current.textContent = `🔍 ${v.registration} — ${v.label}`;

                try {
                    const r = await fetch(`/vehicles/${v.id}/enrich`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const j = await r.json();
                    if (j.ok && j.enriched) enriched++;
                    else if (j.ok && !j.enriched) notFound++;
                    else errors++;
                } catch (e) {
                    errors++;
                }

                const pct = Math.round(((i + 1) / total) * 100);
                bar.style.width = pct + '%';
                counter.textContent = `${i + 1} / ${total}`;
                stats.textContent = `✅ ${enriched} · ⏭ ${notFound} · ⚠️ ${errors}`;
            }

            current.textContent = '✓ Skończone!';
            final.classList.remove('hidden');
            final.innerHTML = `<strong>Wynik:</strong><br>
                ✅ Uzupełniono <strong>${enriched}</strong> aut<br>
                ⏭ Brak w MotorCheck: ${notFound}<br>
                ⚠️ Błędy: ${errors}`;
            closeBtn.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            window.location.reload();
        });
    })();
    </script>

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
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">NCT</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Gotowość</th>
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
                                        @php
                                            $nctSt = $v->nctStatus();
                                            $nctBadge = match($nctSt) {
                                                'valid' => '<span class="text-green-700 font-semibold">✅ '.$v->nct_expiry->format('d.m.Y').'</span>',
                                                'expiring' => '<span class="text-yellow-700 font-semibold">⚠️ '.$v->nct_expiry->format('d.m.Y').'</span>',
                                                'expired' => '<span class="text-red-700 font-semibold">🚨 wygasło</span>',
                                                default => '<span class="text-gray-400">—</span>',
                                            };
                                            $r = $v->readinessPercent();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-1.5 font-mono font-bold text-indigo-700">
                                                <a href="{{ route('vehicles.show', $v) }}" class="hover:underline">{{ $v->registration }}</a>
                                            </td>
                                            <td class="px-3 py-1.5">{{ $v->make }} {{ $v->model }}</td>
                                            <td class="px-2 py-1.5 text-gray-600">{{ $v->year ?? '—' }}</td>
                                            <td class="px-2 py-1.5 text-xs">{!! $nctBadge !!}</td>
                                            <td class="px-2 py-1.5">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 overflow-hidden">
                                                        <div class="{{ $v->readinessColor() }} h-full" style="width: {{ $r }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-700">{{ $r }}%</span>
                                                </div>
                                            </td>
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

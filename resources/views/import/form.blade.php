<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">📥 Import ze Sheets (CSV)</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            @if(session('import_stats'))
                @php $s = session('import_stats'); @endphp
                <div class="mb-4 p-4 bg-blue-50 border border-blue-300 rounded">
                    <h3 class="font-semibold mb-2">📊 Wynik importu</h3>
                    <ul class="text-sm space-y-1">
                        <li>✅ Dodanych aut: <strong>{{ $s['created'] }}</strong></li>
                        <li>🔄 Zaktualizowanych: <strong>{{ $s['updated'] }}</strong></li>
                        <li>⏭ Pominiętych: <strong>{{ $s['skipped'] }}</strong></li>
                    </ul>
                    @if(!empty($s['errors']))
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer text-red-700 font-semibold">⚠️ Błędy ({{ count($s['errors']) }}) — kliknij żeby zobaczyć</summary>
                            <ul class="mt-2 pl-4 list-disc space-y-1 text-red-700">
                                @foreach($s['errors'] as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-3">Jak wyeksportować z Google Sheets?</h3>
                <ol class="list-decimal pl-6 space-y-2 text-sm text-gray-700">
                    <li>Otwórz swój Sheet (Stock albo Sold)</li>
                    <li>Menu <strong>File → Download → Comma Separated Values (.csv)</strong></li>
                    <li>Zapisz plik na komputerze</li>
                    <li>Wybierz plik niżej i kliknij "Importuj"</li>
                </ol>
                <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-400 text-sm">
                    <strong>Format:</strong> program rozpoznaje automatycznie czy to <em>Stock</em> czy <em>Sold</em> na podstawie kolumn.
                    <br>Kolumny: DATA, MAKE/MODEL, REG., MILEAGE, PRICE PURCHASE, ORDER, (+ SELLING PRICE, ODBIORCA, KREDYT itd. dla Sold)
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-base font-semibold mb-2">Wybierz plik CSV</label>
                        <input type="file" name="csv" required accept=".csv,.txt"
                               class="block w-full text-sm border-2 border-gray-300 rounded-lg p-3">
                        <p class="mt-1 text-xs text-gray-500">Max 5 MB · format CSV (przecinki)</p>
                        @error('csv') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">
                        📥 Importuj
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

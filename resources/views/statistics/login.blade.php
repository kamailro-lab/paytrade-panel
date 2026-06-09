<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">🔐 Statystyki - dostęp menedżera</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto">
            <div class="bg-white shadow-lg rounded-lg p-8 border-2 border-amber-200">
                <div class="text-center mb-6">
                    <div class="text-5xl mb-3">🔐</div>
                    <h3 class="text-xl font-bold text-gray-800">Strefa menedżera</h3>
                    <p class="text-sm text-gray-600 mt-2">Wpisz hasło żeby zobaczyć statystyki finansowe (zysk, marża, VAT, P&L).</p>
                </div>

                <form method="POST" action="{{ route('statistics.login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Hasło menedżera</label>
                        <input type="password" name="password" id="password" autofocus required
                               class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-amber-500 focus:outline-none"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">⚠️ {{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full px-6 py-3 bg-amber-600 text-white font-bold text-lg rounded-lg hover:bg-amber-700 transition">
                        🔓 Odblokuj statystyki
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Powrót do panelu
                    </a>
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-500 text-center">
                💡 To NIE jest hasło do konta. To osobne hasło tylko do statystyk finansowych.<br>
                Hasło ustawia administrator w pliku <code>.env</code> (zmienna <code>MANAGER_PASSWORD</code>).
            </p>
        </div>
    </div>
</x-app-layout>

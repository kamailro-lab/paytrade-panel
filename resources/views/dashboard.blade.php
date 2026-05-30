<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel główny</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm text-gray-500">Aut w stocku</div>
                    <div class="mt-2 text-3xl font-bold text-indigo-700">{{ $stockCount }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm text-gray-500">Sprzedane w tym miesiącu</div>
                    <div class="mt-2 text-3xl font-bold text-green-700">{{ $soldThisMonth }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm text-gray-500">Zysk w tym miesiącu</div>
                    <div class="mt-2 text-3xl font-bold text-emerald-700">€{{ number_format($profitThisMonth, 2, ',', ' ') }}</div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Szybkie akcje</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('vehicles.create') }}" class="flex items-center justify-center gap-2 px-6 py-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-lg font-semibold">
                        ➕ Dodaj auto
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="flex items-center justify-center gap-2 px-6 py-4 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition text-lg font-semibold">
                        🚗 Lista aut
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

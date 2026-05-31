<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">⚙️ Dane firmy (do faktur)</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <p class="mb-5 text-sm text-gray-600">Te dane pojawią się na każdej fakturze PDF.</p>
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach($keys as $key => $label)
                            <div class="@if(in_array($key, ['company_name', 'company_address'])) sm:col-span-2 @endif">
                                <label class="block text-base font-semibold text-gray-700 mb-1">{{ $label }}</label>
                                <input type="text" name="{{ $key }}" value="{{ old($key, $values[$key]) }}"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
                                @error($key) <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">💾 Zapisz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

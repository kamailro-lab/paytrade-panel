<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">➕ Dodaj nowe auto</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
            <form method="POST" action="{{ route('vehicles.store') }}">
                @csrf
                @include('vehicles._form')
            </form>
        </div>
    </div>
</x-app-layout>

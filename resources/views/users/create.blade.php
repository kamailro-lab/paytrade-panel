<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">➕ Dodaj użytkownika</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    @include('users._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
